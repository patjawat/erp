<?php

namespace app\modules\laundry\services;

use Yii;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;

/** Read-only piece-ledger checks; weight records never enter these totals. */
class InventoryAuditReport
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public function build(int $overdueHours = 48): array
    {
        $items = (new Query())->select(['id', 'item_code', 'item_name', 'stock_item_code'])->from('laundry_item')
            ->orderBy(['item_name' => SORT_ASC])->all($this->db);
        $inflows = (new Query())->select([
            'item_id', 'location' => 'to_location', 'department_id' => 'to_department_id',
            'qty' => new Expression('SUM(qty)'),
        ])->from('laundry_piece_event')->where(['status' => 'CONFIRMED'])
            ->groupBy(['item_id', 'to_location', 'to_department_id'])->all($this->db);
        $outflows = (new Query())->select([
            'item_id', 'location' => 'from_location', 'department_id' => 'from_department_id',
            'qty' => new Expression('SUM(qty)'),
        ])->from('laundry_piece_event')->where(['status' => 'CONFIRMED'])
            ->groupBy(['item_id', 'from_location', 'from_department_id'])->all($this->db);
        $byItem = self::summarize($inflows, $outflows);
        foreach ($items as &$item) {
            $item['audit'] = $byItem[$item['id']] ?? self::emptySummary();
        }
        unset($item);

        $warehouseId = (new Query())->select('receiving_warehouse_id')->from('laundry_config')
            ->where(['id' => 1])->scalar($this->db);
        $warehouseRows = [];
        if ($warehouseId) {
            $warehouseRows = (new Query())->select([
                'item_code', 'qty' => new Expression('SUM(balance_qty)'),
            ])->from('stock_balance')->where(['warehouse_id' => (int) $warehouseId])
                ->groupBy('item_code')->all($this->db);
        }
        $warehouseComparison = self::compareWarehouse($items, $warehouseRows);

        $cutoff = date('Y-m-d H:i:s', time() - max(1, $overdueHours) * 3600);
        $counts = (new Query())->select([
            'c.id', 'c.dry_batch_id', 'c.item_id', 'c.qty', 'c.approved_at',
            'batch_no' => 'b.batch_no', 'item_name' => 'i.item_name',
        ])->from(['c' => 'laundry_batch_piece_count'])
            ->innerJoin(['b' => 'laundry_processing_batch'], 'b.id = c.dry_batch_id')
            ->innerJoin(['i' => 'laundry_item'], 'i.id = c.item_id')
            ->where(['c.status' => 'APPROVED'])->andWhere(['<=', 'c.approved_at', $cutoff])
            ->orderBy(['c.approved_at' => SORT_ASC])->all($this->db);
        $qcUsed = (new Query())->select(['processing_batch_id', 'item_id', 'qty' => new Expression('SUM(qty)')])
            ->from('laundry_piece_event')->where(['event_type' => 'QC', 'status' => 'CONFIRMED'])
            ->groupBy(['processing_batch_id', 'item_id'])->all($this->db);
        $usedByBatch = [];
        foreach ($qcUsed as $row) {
            $usedByBatch[$row['processing_batch_id']][$row['item_id']] = (int) $row['qty'];
        }
        $overdue = [];
        foreach ($counts as $count) {
            $remaining = (int) $count['qty'] - ($usedByBatch[$count['dry_batch_id']][$count['item_id']] ?? 0);
            if ($remaining > 0) {
                $count['remaining_qty'] = $remaining;
                $overdue[] = $count;
            }
        }
        return [
            'items' => $items, 'overdue' => $overdue, 'overdue_hours' => $overdueHours,
            'warehouse_id' => $warehouseId ?: null, 'warehouse_comparison' => $warehouseComparison,
        ];
    }

    public static function compareWarehouse(array $items, array $stockRows): array
    {
        $stockByCode = [];
        foreach ($stockRows as $row) {
            $stockByCode[(string) $row['item_code']] = (float) $row['qty'];
        }
        $result = [];
        foreach ($items as $item) {
            $code = (string) ($item['stock_item_code'] ?? '');
            if ($code === '') {
                continue;
            }
            $warehouseQty = $stockByCode[$code] ?? 0.0;
            $circulatingQty = (int) $item['audit']['actual_qty'];
            $result[] = [
                'item_code' => $item['item_code'], 'item_name' => $item['item_name'],
                'stock_item_code' => $code, 'warehouse_qty' => $warehouseQty,
                'circulating_qty' => $circulatingQty,
                'difference_qty' => $warehouseQty - $circulatingQty,
            ];
        }
        return $result;
    }

    public static function summarize(array $inflows, array $outflows): array
    {
        $balances = [];
        foreach ([[$inflows, 1], [$outflows, -1]] as [$rows, $sign]) {
            foreach ($rows as $row) {
                $itemId = (int) $row['item_id'];
                $location = (string) $row['location'];
                $departmentId = $row['department_id'] === null ? 0 : (int) $row['department_id'];
                $key = $location . ':' . $departmentId;
                $balances[$itemId][$key] = ($balances[$itemId][$key] ?? 0) + $sign * (int) $row['qty'];
            }
        }
        $result = [];
        foreach ($balances as $itemId => $locations) {
            $summary = self::emptySummary();
            $summary['expected_qty'] = -($locations['EXTERNAL:0'] ?? 0) - ($locations['DISPOSED:0'] ?? 0);
            foreach ($locations as $key => $qty) {
                [$location, $departmentId] = explode(':', $key, 2);
                if (in_array($location, ['EXTERNAL', 'DISPOSED'], true)) {
                    continue;
                }
                $summary['actual_qty'] += $qty;
                if ($location === 'WARD') {
                    $summary['ward_qty'] += $qty;
                }
                if ($location === 'QC_HOLD') {
                    $summary['qc_hold_qty'] += $qty;
                }
                if ($qty < 0) {
                    $summary['negative_locations'][] = ['location' => $location, 'department_id' => (int) $departmentId, 'qty' => $qty];
                }
            }
            $summary['difference_qty'] = $summary['actual_qty'] - $summary['expected_qty'];
            $result[$itemId] = $summary;
        }
        return $result;
    }

    private static function emptySummary(): array
    {
        return ['expected_qty' => 0, 'actual_qty' => 0, 'difference_qty' => 0,
            'ward_qty' => 0, 'qc_hold_qty' => 0, 'negative_locations' => []];
    }
}
