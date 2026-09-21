<?php

namespace app\modules\inventoryV2\services;

use app\modules\inventoryV2\models\MaterialPlan;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockOrder;
use yii\db\Expression;
use yii\db\Query;

/**
 * เปรียบเทียบ "แผนจัดซื้อ" ที่บันทึกไว้ กับ "จัดซื้อจริง" ในปีงบเดียวกัน
 *
 * จัดซื้อจริง = การรับเข้าคลังจริง (StockOrder order_type = IN, status = CONFIRMED)
 * ยึดคลังหลักและปีงบตามที่แผนบันทึกไว้ จับคู่รายตัวด้วย item_code แล้วบักเก็ตเป็นไตรมาสงบประมาณ
 *
 * คำนวณสดทุกครั้งจากธุรกรรมจริง จึงตรงกับระบบเสมอ ไม่ต้องกรอกซ้ำ
 */
class MaterialPlanVarianceService
{
    /**
     * โครงผลเปรียบเทียบทั้งฉบับ พร้อมส่วน "นอกแผน" (ซื้อจริงแต่ไม่มีในแผน)
     *
     * @return array{
     *     plan: MaterialPlan,
     *     rows: array<int, array>,
     *     off_plan: array<int, array>,
     *     summary: array,
     *     quarter_labels: array<int, string>
     * }
     */
    public function build(MaterialPlan $plan): array
    {
        $fiscalYear = (int) $plan->fiscal_year;
        $actualMap = $this->actualReceiptsByQuarter($fiscalYear, $plan->warehouse_id);

        $rows = [];
        $planCodes = [];
        $summary = $this->zeroSummary();

        foreach ($plan->items as $item) {
            $code = (string) $item->item_code;
            $planCodes[$code] = true;
            $unitPrice = (float) $item->unit_price;

            $planQty = [
                (int) round((float) $item->q1_qty),
                (int) round((float) $item->q2_qty),
                (int) round((float) $item->q3_qty),
                (int) round((float) $item->q4_qty),
            ];
            $planValue = array_map(static fn ($qty) => round($qty * $unitPrice, 2), $planQty);

            $actualQty = [0.0, 0.0, 0.0, 0.0];
            $actualValue = [0.0, 0.0, 0.0, 0.0];
            foreach (($actualMap[$code] ?? []) as $q => $agg) {
                $actualQty[$q - 1] = (float) $agg['qty'];
                $actualValue[$q - 1] = (float) $agg['value'];
            }

            $planQtyTotal = array_sum($planQty);
            $planValueTotal = (float) $item->plan_value;
            $actualQtyTotal = array_sum($actualQty);
            $actualValueTotal = array_sum($actualValue);

            $rows[] = [
                'item_code' => $code,
                'item_name' => (string) $item->item_name,
                'category_id' => (string) $item->category_id,
                'category_title' => (string) $item->category_title,
                'unit_name' => (string) $item->unit_name,
                'unit_price' => $unitPrice,
                'plan_quarters' => $planQty,
                'plan_quarter_values' => $planValue,
                'actual_quarters' => $actualQty,
                'actual_quarter_values' => $actualValue,
                'plan_qty' => $planQtyTotal,
                'plan_value' => round($planValueTotal, 2),
                'actual_qty' => $actualQtyTotal,
                'actual_value' => round($actualValueTotal, 2),
                'variance_qty' => $actualQtyTotal - $planQtyTotal,
                'variance_value' => round($actualValueTotal - $planValueTotal, 2),
                'achieve_pct' => $planQtyTotal > 0 ? round($actualQtyTotal / $planQtyTotal * 100, 1) : null,
            ];

            $summary['item_count']++;
            $summary['plan_value'] += $planValueTotal;
            $summary['actual_value'] += $actualValueTotal;
            if ($actualQtyTotal > 0) {
                $summary['purchased_count']++;
            }
        }

        usort($rows, static function ($a, $b) {
            return [$a['category_title'], $a['item_name']] <=> [$b['category_title'], $b['item_name']];
        });

        $offPlanCodes = array_values(array_diff(array_keys($actualMap), array_keys($planCodes)));
        $offPlan = $this->buildOffPlanRows($offPlanCodes, $actualMap, $summary);

        $summary['plan_value'] = round($summary['plan_value'], 2);
        $summary['actual_value'] = round($summary['actual_value'], 2);
        $summary['variance_value'] = round($summary['actual_value'] - $summary['plan_value'], 2);
        $summary['achieve_pct'] = $summary['plan_value'] > 0
            ? round($summary['actual_value'] / $summary['plan_value'] * 100, 1)
            : null;

        return [
            'plan' => $plan,
            'rows' => $rows,
            'off_plan' => $offPlan,
            'summary' => $summary,
            'quarter_labels' => MaterialPlanForecastService::quarterLabels(),
        ];
    }

    /**
     * ยอดรับเข้าจริงรายไตรมาสงบประมาณ (Q1 = ต.ค.-ธ.ค. ... Q4 = ก.ค.-ก.ย.)
     *
     * @return array<string, array<int, array{qty: float, value: float}>>
     */
    protected function actualReceiptsByQuarter(int $fiscalYear, $warehouseId): array
    {
        [$start, $end] = MaterialPlanForecastService::fiscalRange($fiscalYear);

        // เดือนปฏิทิน → ไตรมาสงบประมาณ: 10-12 = 1, 1-3 = 2, 4-6 = 3, 7-9 = 4
        $quarterExpr = new Expression(
            'CASE
                WHEN MONTH(so.order_date) IN (10, 11, 12) THEN 1
                WHEN MONTH(so.order_date) IN (1, 2, 3) THEN 2
                WHEN MONTH(so.order_date) IN (4, 5, 6) THEN 3
                ELSE 4
            END'
        );

        $query = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'fiscal_quarter' => $quarterExpr,
                'in_qty' => new Expression('SUM(COALESCE(sd.qty, 0))'),
                'in_value' => new Expression('SUM(COALESCE(sd.qty, 0) * COALESCE(sd.unit_price, 0))'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['between', 'so.order_date', $start, $end])
            ->groupBy(['sd.item_code', $quarterExpr]);

        if ($warehouseId) {
            $query->andWhere(['so.main_warehouse_id' => (int) $warehouseId]);
        }

        $map = [];
        foreach ($query->all() as $row) {
            $code = (string) $row['item_code'];
            $quarter = (int) $row['fiscal_quarter'];
            $map[$code][$quarter] = [
                'qty' => (float) $row['in_qty'],
                'value' => (float) $row['in_value'],
            ];
        }

        return $map;
    }

    /**
     * รายการที่ซื้อจริงแต่ไม่มีในแผน — เตือนให้ทบทวนความครบถ้วนของแผน
     *
     * @param array<int, string> $codes
     * @param array<string, array<int, array{qty: float, value: float}>> $actualMap
     * @return array<int, array>
     */
    protected function buildOffPlanRows(array $codes, array $actualMap, array &$summary): array
    {
        if ($codes === []) {
            return [];
        }

        $names = $this->itemNames($codes);
        $rows = [];
        foreach ($codes as $code) {
            $actualQty = [0.0, 0.0, 0.0, 0.0];
            $actualValue = [0.0, 0.0, 0.0, 0.0];
            foreach (($actualMap[$code] ?? []) as $q => $agg) {
                $actualQty[$q - 1] = (float) $agg['qty'];
                $actualValue[$q - 1] = (float) $agg['value'];
            }
            $qtyTotal = array_sum($actualQty);
            $valueTotal = array_sum($actualValue);

            $rows[] = [
                'item_code' => $code,
                'item_name' => $names[$code] ?? $code,
                'actual_quarters' => $actualQty,
                'actual_quarter_values' => $actualValue,
                'actual_qty' => $qtyTotal,
                'actual_value' => round($valueTotal, 2),
            ];

            $summary['off_plan_count']++;
            $summary['off_plan_value'] += $valueTotal;
        }

        usort($rows, static fn ($a, $b) => $b['actual_value'] <=> $a['actual_value']);
        $summary['off_plan_value'] = round($summary['off_plan_value'], 2);

        return $rows;
    }

    /**
     * ชื่อวัสดุจากทะเบียนพัสดุ สำหรับรายการนอกแผน
     *
     * @param array<int, string> $codes
     * @return array<string, string>
     */
    protected function itemNames(array $codes): array
    {
        if ($codes === []) {
            return [];
        }

        $rows = (new Query())
            ->select(['code' => 'i.code', 'title' => 'i.title'])
            ->from(['i' => StockItem::tableName()])
            ->where(['i.code' => $codes])
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['code']] = trim((string) $row['title']);
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    protected function zeroSummary(): array
    {
        return [
            'item_count' => 0,
            'purchased_count' => 0,
            'plan_value' => 0.0,
            'actual_value' => 0.0,
            'variance_value' => 0.0,
            'achieve_pct' => null,
            'off_plan_count' => 0,
            'off_plan_value' => 0.0,
        ];
    }
}
