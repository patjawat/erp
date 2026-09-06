<?php

namespace app\modules\inventoryV2\components;

use app\modules\inventoryV2\controllers\ReportController;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * เครื่องมือซ่อมข้อมูลปิดเดือนอัตโนมัติ (ทำงานคู่กับ ReportController::diagnoseCloseMonth)
 *
 * ซ่อม 2 กลุ่มที่ปลอดภัย โดย "ไม่เสกจำนวนสุทธิ" และ "ไม่แก้เอกสารที่ใช้ร่วมหลายพัสดุ":
 *
 *  1) zero_cost  — พัสดุที่มีแถวรับเข้า (IN) ราคาทุน = 0 ทั้งที่มีราคาซื้อจริงในระบบ
 *                  → เติม unit_price บนแถว IN ที่เป็น 0 ด้วยราคาซื้อล่าสุดที่ทราบ (เก็บราคาเดิมไว้ใน data_json)
 *
 *  2) negative_qty (เฉพาะกรณี "วันที่ผกผัน" ที่ยอดปลายทางไม่ติดลบ)
 *                  — ยอดคงเหลือรายเดือนดิ่งลบชั่วคราวเพราะใบรับเข้าถูกลงวันในเดือนหลังกว่าที่ควร
 *                  แต่ยอดสุทธิทั้งช่วง ≥ 0 → ย้าย "จำนวนรับ" จากเดือนที่เกินมาไปเดือนที่ขาด
 *                  ด้วยคู่รายการปรับยอดเฉพาะพัสดุ (+ เดือนที่ขาด / − เดือนต้นทาง) สุทธิเป็นศูนย์
 *                  ไม่กระทบยอดปลายทาง/พัสดุอื่น ทุกใบติดแท็ก close_month_reconcile จึงย้อนกลับได้
 *                  ** ถ้ายอดปลายทางติดลบจริง (จ่ายเกิน) จะข้าม ไม่ซ่อมอัตโนมัติ (ต้องตรวจนับ) **
 *
 * ทุก plan* เป็น READ-ONLY (ไม่เขียน DB) ให้ preview ก่อน ส่วน apply* เขียนภายใต้ transaction ของ caller
 */
class CloseMonthAutofixService
{
    private const EPS = 0.0001;
    public const RECONCILE_TAG = 'close_month_reconcile';

    /* ───────────────────────── ZERO COST ───────────────────────── */

    /**
     * วางแผนเติมราคาทุนให้พัสดุที่มีแถว IN ราคา 0 (dry-run)
     * @return array<int,array{warehouse_id:int,item_code:string,detail_ids:int[],to_price:float,affected_qty:float}>
     */
    public static function planZeroCost(int $warehouseId, array $itemCodes): array
    {
        $plans = [];
        foreach ($itemCodes as $code) {
            $price = self::latestKnownInPrice($code);
            if ($price === null) {
                continue; // ไม่เคยมีราคาซื้อที่ไหนเลย → เติมอัตโนมัติไม่ได้
            }
            $rows = (new Query())
                ->select(['sd.id', 'sd.qty'])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->where([
                    'sd.item_code' => $code,
                    'so.status' => StockOrder::STATUS_CONFIRMED,
                    'so.order_type' => StockOrder::ORDER_TYPE_IN,
                    'so.main_warehouse_id' => $warehouseId,
                ])
                ->andWhere(['or', ['sd.unit_price' => null], ['<', 'sd.unit_price', self::EPS]])
                ->all();
            if (empty($rows)) {
                continue;
            }
            $plans[] = [
                'warehouse_id' => $warehouseId,
                'item_code' => (string) $code,
                'detail_ids' => array_map(static fn($r) => (int) $r['id'], $rows),
                'to_price' => round($price, 2),
                'affected_qty' => round(array_sum(array_map(static fn($r) => (float) $r['qty'], $rows)), 2),
            ];
        }
        return $plans;
    }

    /**
     * ลงมือเติมราคาทุน — เก็บ unit_price เดิมไว้ใน data_json['autofix_zero_cost'] เพื่อย้อนกลับ
     * @return array{items:int,rows:int}
     */
    public static function applyZeroCost(array $plans): array
    {
        $rowCount = 0;
        foreach ($plans as $p) {
            foreach (StockDetail::findAll(['id' => $p['detail_ids']]) as $detail) {
                $json = is_array($detail->data_json)
                    ? $detail->data_json
                    : (is_string($detail->data_json) ? (json_decode($detail->data_json, true) ?: []) : []);
                if (!is_array($json)) {
                    $json = [];
                }
                $json['autofix_zero_cost'] = [
                    'old_unit_price' => (float) $detail->unit_price,
                    'at' => date('Y-m-d H:i:s'),
                    'by' => Yii::$app->has('user', true) ? (Yii::$app->user->id ?? null) : null,
                ];
                $detail->unit_price = (float) $p['to_price'];
                $detail->data_json = json_encode($json, JSON_UNESCAPED_UNICODE);
                if (!$detail->save(false, ['unit_price', 'data_json'])) {
                    throw new \RuntimeException("เติมราคาทุนไม่สำเร็จ (detail {$detail->id})");
                }
                $rowCount++;
            }
        }
        return ['items' => count($plans), 'rows' => $rowCount];
    }

    /* ─────────────────────── NEGATIVE QTY (date inversion) ─────────────────────── */

    /**
     * วางแผนย้ายจำนวนรับข้ามเดือนเพื่อลบยอดติดลบชั่วคราว (dry-run)
     * ใช้ closing_qty รายเดือนจาก computeMonthlyRows (แหล่งเดียวกับปิดเดือนจริง) เป็นความจริง
     *
     * @return array{fixable:array<int,array<string,mixed>>, skipped:array<int,array<string,mixed>>}
     */
    public static function planReceiptShift(int $warehouseId, int $targetYear, int $targetMonth): array
    {
        [$sy, $sm] = ReportController::firstStockOrderMonthPublic($warehouseId);
        $target = $targetYear * 12 + $targetMonth;
        if ($sy === null || ($sy * 12 + $sm) > $target) {
            return ['fixable' => [], 'skipped' => []];
        }

        // 1) เก็บ closing_qty รายเดือนต่อพัสดุ (chain in-memory)
        $months = [];
        $closingByItem = []; // item => [ym => closing_qty]
        $opening = [];
        $ty = $sy;
        $tm = $sm;
        while (($ty * 12 + $tm) <= $target) {
            $ym = sprintf('%04d-%02d', $ty, $tm);
            $months[] = $ym;
            $rows = ReportController::computeMonthlyRows($warehouseId, $ty, $tm, $opening);
            $next = [];
            foreach ($rows as $r) {
                $code = (string) $r['item_code'];
                $next[$code] = ['closing_qty' => (float) $r['closing_qty'], 'closing_value' => (float) $r['closing_value']];
                $closingByItem[$code][$ym] = (float) $r['closing_qty'];
            }
            $opening = $next;
            $tm++;
            if ($tm > 12) { $tm = 1; $ty++; }
        }

        $fixable = [];
        $skipped = [];
        foreach ($closingByItem as $code => $closingMap) {
            // สร้าง delta รายเดือนจาก closing (delta = closing[m] - closing[m-1])
            $deltas = [];
            $prev = 0.0;
            foreach ($months as $ym) {
                $c = $closingMap[$ym] ?? $prev; // เดือนที่ไม่มีรายการ = คงยอดเดิม
                $deltas[] = ['ym' => $ym, 'delta' => $c - $prev];
                $prev = $c;
            }
            $endpoint = $prev;
            $minRunning = 0.0;
            $run = 0.0;
            foreach ($deltas as $d) {
                $run += $d['delta'];
                if ($run < $minRunning) {
                    $minRunning = $run;
                }
            }
            if ($minRunning >= -self::EPS) {
                continue; // ไม่ติดลบเลย
            }
            if ($endpoint < -self::EPS) {
                $skipped[] = ['item_code' => (string) $code, 'reason' => 'endpoint_negative', 'endpoint' => round($endpoint, 2), 'worst' => round($minRunning, 2)];
                continue; // จ่ายเกินจริง — ไม่ย้ายอัตโนมัติ
            }

            // greedy: ดึงจำนวนรับจากเดือนอนาคตที่เกิน มาชดเชยเดือนที่ขาด
            $work = $deltas; // สำเนาเพื่อแก้
            $shifts = [];
            $run = 0.0;
            $n = count($work);
            for ($i = 0; $i < $n; $i++) {
                $run += $work[$i]['delta'];
                if ($run >= -self::EPS) {
                    continue;
                }
                $need = -$run;
                for ($j = $i + 1; $j < $n && $need > self::EPS; $j++) {
                    $avail = $work[$j]['delta'];
                    if ($avail <= self::EPS) {
                        continue;
                    }
                    $take = min($need, $avail);
                    $work[$j]['delta'] -= $take;
                    $work[$i]['delta'] += $take;
                    $shifts[] = ['qty' => round($take, 4), 'from_ym' => $work[$j]['ym'], 'to_ym' => $work[$i]['ym']];
                    $need -= $take;
                    $run += $take;
                }
                if ($need > self::EPS) {
                    // ไม่ควรเกิดเมื่อ endpoint ≥ 0 — กันไว้
                    $shifts = [];
                    $skipped[] = ['item_code' => (string) $code, 'reason' => 'unbalanced', 'endpoint' => round($endpoint, 2), 'worst' => round($minRunning, 2)];
                    break;
                }
            }
            if (empty($shifts)) {
                continue;
            }
            $price = self::latestKnownInPrice((string) $code);
            $fixable[] = [
                'warehouse_id' => $warehouseId,
                'item_code' => (string) $code,
                'worst' => round($minRunning, 2),
                'endpoint' => round($endpoint, 2),
                'price' => $price === null ? 0.0 : round($price, 2),
                'shifts' => $shifts,
            ];
        }

        return ['fixable' => $fixable, 'skipped' => $skipped];
    }

    /**
     * ลงมือย้ายจำนวนรับ: สร้างคู่ ADJUST เฉพาะพัสดุ (+ เดือนที่ขาด / − เดือนต้นทาง) ต่อ 1 shift
     * ทุกใบ order_type=ADJUST, source_type=RECON, ติดแท็ก close_month_reconcile
     * @return array{orders:int,items:int}
     */
    public static function applyReceiptShift(array $fixablePlans): array
    {
        $orderCount = 0;
        foreach ($fixablePlans as $plan) {
            $wid = (int) $plan['warehouse_id'];
            $code = (string) $plan['item_code'];
            $price = (float) ($plan['price'] ?? 0);
            foreach ($plan['shifts'] as $shift) {
                $qty = (float) $shift['qty'];
                if ($qty <= self::EPS) {
                    continue;
                }
                // + จำนวน ที่เดือนที่ขาด (to_ym), − จำนวน ที่เดือนต้นทาง (from_ym)
                self::createReconcileAdjust($wid, $code, $shift['to_ym'], $qty, $price, $shift['from_ym']);
                self::createReconcileAdjust($wid, $code, $shift['from_ym'], -$qty, $price, $shift['to_ym']);
                $orderCount += 2;
            }
        }
        return ['orders' => $orderCount, 'items' => count($fixablePlans)];
    }

    private static function createReconcileAdjust(int $warehouseId, string $itemCode, string $ym, float $qty, float $price, string $pairYm): void
    {
        $lastDay = (int) date('t', strtotime($ym . '-01'));
        $orderDate = sprintf('%s-%02d 12:00:00', $ym, $lastDay);
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->has('user', true) ? (Yii::$app->user->id ?? null) : null;

        $order = new StockOrder();
        $order->order_no = 'RECON-QTY-' . date('YmdHis') . '-' . substr(Yii::$app->security->generateRandomString(6), 0, 6);
        $order->order_type = StockOrder::ORDER_TYPE_ADJUST;
        $order->source_type = 'RECON';
        $order->order_date = $orderDate;
        $order->main_warehouse_id = $warehouseId;
        $order->status = StockOrder::STATUS_CONFIRMED;
        $order->data_json = [
            self::RECONCILE_TAG => true,
            'kind' => 'receipt_shift',
            'pair_ym' => $pairYm,
            'created_at' => $now,
        ];
        $order->created_at = $now;
        $order->created_by = $uid;
        if (!$order->save(false)) {
            throw new \RuntimeException('สร้างเอกสารปรับยอด (reconcile) ไม่สำเร็จ: ' . json_encode($order->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        $detail = new StockDetail();
        $detail->stock_order_id = $order->id;
        $detail->item_code = $itemCode;
        $detail->qty = $qty; // signed: บวก=ย้ายมาเดือนนี้, ลบ=ย้ายออกจากเดือนนี้
        $detail->remain_qty = 0;
        $detail->unit_price = $price;
        $detail->lot_number = 'RECON';
        $detail->data_json = json_encode([self::RECONCILE_TAG => true, 'kind' => 'receipt_shift'], JSON_UNESCAPED_UNICODE);
        $detail->created_at = $now;
        $detail->created_by = $uid;
        if (!$detail->save(false)) {
            throw new \RuntimeException('สร้างรายการปรับยอด (reconcile) ไม่สำเร็จ: ' . json_encode($detail->getErrors(), JSON_UNESCAPED_UNICODE));
        }
    }

    /** ราคาซื้อ (IN) ล่าสุดที่ทราบของพัสดุ (ทุกคลัง) หรือ null ถ้าไม่เคยมี */
    private static function latestKnownInPrice(string $itemCode): ?float
    {
        $price = (new Query())
            ->select('sd.unit_price')
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'sd.item_code' => $itemCode,
                'so.order_type' => StockOrder::ORDER_TYPE_IN,
                'so.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['>', 'sd.unit_price', self::EPS])
            ->orderBy(['so.order_date' => SORT_DESC, 'sd.id' => SORT_DESC])
            ->scalar();
        return ($price === false || $price === null) ? null : (float) $price;
    }
}
