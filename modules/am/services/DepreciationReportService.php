<?php

namespace app\modules\am\services;

use yii\db\Query;
use app\modules\am\models\AccountingPeriod;

/**
 * รายงานค่าเสื่อม (ราชการ) — รวมจากรายการ asset_depreciations ระดับรายเดือนชุดเดียวกัน
 * เพื่อให้ยอดรายเดือน/ไตรมาส/ปีตรงกัน
 *
 * ฟิลด์รายงาน: ราคาทุน, ค่าเสื่อมงวดปัจจุบัน, ค่าเสื่อมสะสม, มูลค่าสุทธิ,
 * วันได้มา, วันเริ่มคำนวณ, อายุ, อัตรา, ประเภท/หมวด, หน่วยงาน
 */
class DepreciationReportService
{
    /**
     * ดึงรายการค่าเสื่อม join ข้อมูลทรัพย์สิน/ประเภท/หมวด สำหรับช่วงงวดที่กำหนด
     *
     * @param int[] $periodIds
     * @return array รายการดิบ (1 แถวต่อ asset_depreciation)
     */
    private function fetchRows(array $periodIds): array
    {
        if (empty($periodIds)) {
            return [];
        }
        return (new Query())
            ->select([
                'ad.asset_id',
                'ad.accounting_period_id',
                'ad.transaction_type',
                'ad.opening_cost',
                'ad.depreciation_amount',
                'ad.adjustment_amount',
                'ad.accumulated_depreciation',
                'ad.closing_net_book_value',
                'ad.rate_snapshot',
                'ad.useful_life_months_snapshot',
                'ad.status',
                'p.end_date AS period_end',
                'a.code',
                'a.asset_name',
                'a.price',
                'a.receive_date',
                'a.depreciation_start_date',
                'a.useful_life',
                'a.useful_life_months',
                'a.depreciation_rate',
                'a.asset_type_id',
                'a.asset_category_id',
                'a.department',
                'ct.title AS type_name',
                'cc.title AS category_name',
            ])
            ->from('{{%asset_depreciations}} ad')
            ->innerJoin('{{%asset}} a', 'a.id = ad.asset_id')
            ->innerJoin('{{%accounting_periods}} p', 'p.id = ad.accounting_period_id')
            // asset อ้างอิงลำดับชั้นด้วย categorise.code + name (ตาม Asset::getAssetType/getAssetCategory)
            ->leftJoin('{{%categorise}} ct', "ct.code = a.asset_type_id AND ct.name = 'asset_type'")
            ->leftJoin('{{%categorise}} cc', "cc.code = a.asset_category_id AND cc.name = 'asset_category'")
            ->where(['ad.accounting_period_id' => $periodIds])
            ->orderBy(['a.code' => SORT_ASC, 'p.end_date' => SORT_ASC])
            ->all();
    }

    /**
     * รายงานรายเดือน (1 งวดเดือน) — 1 แถวต่อทรัพย์สิน (รวม normal+adjustment+reversal)
     */
    public function monthly(int $periodId): array
    {
        return $this->aggregateByAsset($this->fetchRows([$periodId]));
    }

    /**
     * รายงานรายไตรมาส — รวมจากงวดเดือนในไตรมาสนั้น
     */
    public function quarter(int $fyBE, int $quarterNo): array
    {
        $q = AccountingPeriod::findOne(['fiscal_year' => $fyBE, 'period_type' => AccountingPeriod::TYPE_QUARTER, 'period_no' => $quarterNo]);
        if (!$q) {
            return [];
        }
        return $this->aggregateByAsset($this->fetchRows($this->monthPeriodIdsInRange($q->start_date, $q->end_date)));
    }

    /**
     * รายงานปีงบประมาณ — รวมจากงวดเดือนทั้งปี
     */
    public function fiscalYear(int $fyBE): array
    {
        $y = AccountingPeriod::findOne(['fiscal_year' => $fyBE, 'period_type' => AccountingPeriod::TYPE_FISCAL_YEAR, 'period_no' => 1]);
        if (!$y) {
            return [];
        }
        return $this->aggregateByAsset($this->fetchRows($this->monthPeriodIdsInRange($y->start_date, $y->end_date)));
    }

    /**
     * ยอดรวมทั้งรายงาน
     */
    public function totals(array $rows): array
    {
        $t = self::zeroTotals();
        foreach ($rows as $r) {
            self::accumTotals($t, $r);
        }
        return self::roundTotals($t);
    }

    private static function zeroTotals(): array
    {
        return ['cost' => 0.0, 'depreciation' => 0.0, 'accumulated' => 0.0, 'nbv' => 0.0, 'count' => 0];
    }

    private static function accumTotals(array &$t, array $r): void
    {
        $t['cost'] += (float) $r['cost'];
        $t['depreciation'] += (float) $r['depreciation_period'];
        $t['accumulated'] += (float) $r['accumulated'];
        $t['nbv'] += (float) $r['nbv'];
        $t['count']++;
    }

    private static function roundTotals(array $t): array
    {
        foreach (['cost', 'depreciation', 'accumulated', 'nbv'] as $k) {
            $t[$k] = round($t[$k], 2);
        }
        return $t;
    }

    /**
     * จัดกลุ่มรายงานเป็น ประเภท → หมวด พร้อมยอดรวมย่อยแต่ละระดับ
     *
     * @param array $rows ผลจาก monthly()/quarter()/fiscalYear()
     * @return array<int,array{type_name:string,totals:array,categories:array<int,array{category_name:string,totals:array,rows:array}>}>
     */
    public function groupByTypeCategory(array $rows): array
    {
        $groups = [];
        foreach ($rows as $r) {
            $type = ($r['type_name'] ?? '') !== '' ? $r['type_name'] : '(ไม่ระบุประเภท)';
            $cat = ($r['category_name'] ?? '') !== '' ? $r['category_name'] : '(ไม่ระบุหมวด)';
            if (!isset($groups[$type])) {
                $groups[$type] = ['type_name' => $type, 'totals' => self::zeroTotals(), 'categories' => []];
            }
            if (!isset($groups[$type]['categories'][$cat])) {
                $groups[$type]['categories'][$cat] = ['category_name' => $cat, 'totals' => self::zeroTotals(), 'rows' => []];
            }
            $groups[$type]['categories'][$cat]['rows'][] = $r;
            self::accumTotals($groups[$type]['categories'][$cat]['totals'], $r);
            self::accumTotals($groups[$type]['totals'], $r);
        }

        ksort($groups);
        $out = [];
        foreach ($groups as $g) {
            ksort($g['categories']);
            $g['totals'] = self::roundTotals($g['totals']);
            $cats = [];
            foreach ($g['categories'] as $c) {
                $c['totals'] = self::roundTotals($c['totals']);
                $cats[] = $c;
            }
            $g['categories'] = array_values($cats);
            $out[] = $g;
        }
        return $out;
    }

    private function monthPeriodIdsInRange(string $start, string $end): array
    {
        return AccountingPeriod::find()
            ->select('id')
            ->where(['period_type' => AccountingPeriod::TYPE_MONTH])
            ->andWhere(['>=', 'start_date', $start])
            ->andWhere(['<=', 'end_date', $end])
            ->column();
    }

    /**
     * รวมหลายแถว (หลายงวด/หลาย transaction_type) เป็น 1 แถวต่อทรัพย์สิน
     * - ค่าเสื่อมงวด = SUM(depreciation_amount + adjustment_amount) [reversal เป็นลบอยู่แล้ว]
     * - ค่าเสื่อมสะสม/มูลค่าสุทธิ = ค่าจากงวดเดือนล่าสุด (period_end มากสุด) ของ transaction ปกติ
     */
    private function aggregateByAsset(array $rows): array
    {
        $byAsset = [];
        foreach ($rows as $r) {
            $aid = $r['asset_id'];
            if (!isset($byAsset[$aid])) {
                $byAsset[$aid] = [
                    'asset_id' => $aid,
                    'code' => $r['code'],
                    'asset_name' => $r['asset_name'],
                    'cost' => (float) $r['price'],
                    'receive_date' => $r['receive_date'],
                    'depreciation_start_date' => $r['depreciation_start_date'],
                    'useful_life' => $r['useful_life'],
                    'useful_life_months' => $r['useful_life_months'],
                    'depreciation_rate' => $r['depreciation_rate'],
                    'type_name' => $r['type_name'],
                    'category_name' => $r['category_name'],
                    'department' => $r['department'],
                    'depreciation_period' => 0.0,
                    'accumulated' => 0.0,
                    'nbv' => (float) $r['price'],
                    '_latest_end' => null,
                ];
            }
            $byAsset[$aid]['depreciation_period'] += (float) $r['depreciation_amount'] + (float) $r['adjustment_amount'];

            // ใช้ค่าสะสม/สุทธิจากงวดล่าสุด (เฉพาะ normal เพื่อไม่ให้ reversal ทำเพี้ยน)
            if ($r['transaction_type'] === 'normal') {
                if ($byAsset[$aid]['_latest_end'] === null || $r['period_end'] >= $byAsset[$aid]['_latest_end']) {
                    $byAsset[$aid]['_latest_end'] = $r['period_end'];
                    $byAsset[$aid]['accumulated'] = (float) $r['accumulated_depreciation'];
                    $byAsset[$aid]['nbv'] = (float) $r['closing_net_book_value'];
                }
            }
        }

        $out = array_values($byAsset);
        foreach ($out as &$row) {
            $row['depreciation_period'] = round($row['depreciation_period'], 2);
            $row['accumulated'] = round($row['accumulated'], 2);
            $row['nbv'] = round($row['nbv'], 2);
            unset($row['_latest_end']);
        }
        return $out;
    }
}
