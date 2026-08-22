<?php

namespace app\modules\inventoryV2\components;

use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Expression;
use yii\db\Query;

/**
 * Read-only inventory health detector.
 *
 * Compares three independent views of stock for each warehouse/item/lot:
 * movement ledger, stock_balance and FIFO source remain_qty. This service must
 * never mutate inventory data; repair is deliberately kept out of this class.
 */
class StockHealthService
{
    public const EPSILON = 0.0001;

    public static function scan(array $warehouseIds, array $filters = []): array
    {
        $warehouseIds = array_values(array_unique(array_filter(array_map('intval', $warehouseIds))));
        if (empty($warehouseIds)) {
            return self::emptyResult();
        }

        $warehouseFilter = isset($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : 0;
        if ($warehouseFilter > 0 && in_array($warehouseFilter, $warehouseIds, true)) {
            $warehouseIds = [$warehouseFilter];
        }

        $maps = [];
        self::collectBalances($warehouseIds, $maps);
        self::collectSources($warehouseIds, $maps);
        $ledgerTotals = [];
        self::collectLedger($warehouseIds, $ledgerTotals);
        self::collectAuditSignals($warehouseIds, $maps);

        // Ledger is reliable at item/warehouse level. Legacy V1 data may use a
        // document number as the OUT lot, so comparing Ledger per lot creates
        // false positives. Keep lot reconciliation and item reconciliation as
        // two explicit scopes.
        $itemTotals = [];
        foreach ($maps as $row) {
            $itemKey = (int) $row['warehouse_id'] . '|' . $row['item_code'];
            if (!isset($itemTotals[$itemKey])) {
                $itemTotals[$itemKey] = $row;
                $itemTotals[$itemKey]['scope'] = 'item';
                $itemTotals[$itemKey]['lot_number'] = 'รวมทุก Lot';
                $itemTotals[$itemKey]['ledger_qty'] = null;
                foreach (['balance_qty', 'fifo_qty', 'received_qty', 'balance_rows', 'source_rows', 'missing_allocation_count', 'orphan_allocation_count', 'history_only_edit_count'] as $field) {
                    $itemTotals[$itemKey][$field] = 0;
                }
            }
            foreach (['balance_qty', 'fifo_qty', 'received_qty', 'balance_rows', 'source_rows', 'missing_allocation_count', 'orphan_allocation_count', 'history_only_edit_count'] as $field) {
                $itemTotals[$itemKey][$field] += $row[$field];
            }
            $maps[self::key($row['warehouse_id'], $row['item_code'], $row['lot_number'])]['scope'] = 'lot';
            $maps[self::key($row['warehouse_id'], $row['item_code'], $row['lot_number'])]['ledger_qty'] = null;
        }
        foreach ($ledgerTotals as $itemKey => $ledgerQty) {
            if (!isset($itemTotals[$itemKey])) {
                [$warehouseId, $itemCode] = explode('|', $itemKey, 2);
                $itemTotals[$itemKey] = [
                    'scope' => 'item', 'warehouse_id' => (int) $warehouseId, 'item_code' => $itemCode,
                    'lot_number' => 'รวมทุก Lot', 'balance_qty' => 0.0, 'fifo_qty' => 0.0,
                    'received_qty' => 0.0, 'balance_rows' => 0, 'source_rows' => 0,
                    'missing_allocation_count' => 0, 'orphan_allocation_count' => 0, 'history_only_edit_count' => 0,
                ];
            }
            $itemTotals[$itemKey]['ledger_qty'] = $ledgerQty;
        }
        $maps = array_merge($maps, array_values($itemTotals));

        $itemCodes = [];
        foreach ($maps as $row) {
            $itemCodes[$row['item_code']] = true;
        }
        $itemNames = empty($itemCodes) ? [] : (new Query())
            ->select(['code', 'title'])
            ->from(StockItem::tableName())
            ->where(['name' => 'asset_item', 'group_id' => 'MATER', 'code' => array_keys($itemCodes)])
            ->indexBy('code')
            ->all();
        $warehouseNames = (new Query())
            ->select(['id', 'warehouse_name'])
            ->from(Warehouse::tableName())
            ->where(['id' => $warehouseIds])
            ->indexBy('id')
            ->all();

        $rows = [];
        foreach ($maps as $row) {
            $classified = self::classify($row);
            if ($classified['status'] === 'healthy' && empty($filters['include_healthy'])) {
                continue;
            }
            $classified['item_name'] = $itemNames[$row['item_code']]['title'] ?? $row['item_code'];
            $classified['warehouse_name'] = $warehouseNames[$row['warehouse_id']]['warehouse_name'] ?? (string) $row['warehouse_id'];
            $rows[] = $classified;
        }

        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        if ($search !== '' || $status !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($search, $status) {
                if ($status !== '' && $row['status'] !== $status) {
                    return false;
                }
                if ($search === '') {
                    return true;
                }
                $haystack = mb_strtolower($row['item_code'] . ' ' . $row['item_name'] . ' ' . $row['lot_number'] . ' ' . $row['warehouse_name']);
                return mb_strpos($haystack, mb_strtolower($search)) !== false;
            }));
        }

        usort($rows, static function ($a, $b) {
            $weight = ['critical' => 0, 'mismatch' => 1, 'review' => 2, 'healthy' => 3];
            return [$weight[$a['status']] ?? 9, $a['warehouse_name'], $a['item_code'], $a['lot_number']]
                <=> [$weight[$b['status']] ?? 9, $b['warehouse_name'], $b['item_code'], $b['lot_number']];
        });

        $summary = ['total' => count($rows), 'critical' => 0, 'mismatch' => 0, 'review' => 0, 'healthy' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']]++;
        }

        return ['rows' => $rows, 'summary' => $summary, 'generated_at' => date('Y-m-d H:i:s')];
    }

    public static function classify(array $row): array
    {
        $row += [
            'scope' => 'lot', 'warehouse_id' => 0, 'item_code' => '', 'lot_number' => '-',
            'ledger_qty' => 0.0, 'balance_qty' => 0.0, 'fifo_qty' => 0.0,
            'received_qty' => 0.0, 'balance_rows' => 0, 'source_rows' => 0,
            'missing_allocation_count' => 0, 'orphan_allocation_count' => 0,
            'history_only_edit_count' => 0,
        ];
        foreach (['balance_qty', 'fifo_qty', 'received_qty'] as $field) {
            $row[$field] = round((float) $row[$field], 4);
        }
        $row['ledger_qty'] = $row['ledger_qty'] === null ? null : round((float) $row['ledger_qty'], 4);

        $issues = [];
        if ($row['scope'] === 'lot' && $row['balance_rows'] > 1) $issues[] = 'duplicate_balance';
        if ($row['fifo_qty'] < -self::EPSILON) $issues[] = 'negative_fifo';
        if ($row['balance_qty'] < -self::EPSILON) $issues[] = 'negative_balance';
        if ($row['fifo_qty'] > $row['received_qty'] + self::EPSILON) $issues[] = 'fifo_over_received';
        if ($row['balance_qty'] > self::EPSILON && $row['source_rows'] === 0) $issues[] = 'orphan_balance';
        if ($row['balance_qty'] > self::EPSILON && $row['fifo_qty'] <= self::EPSILON) $issues[] = 'balance_without_fifo';
        if (abs($row['balance_qty'] - $row['fifo_qty']) > self::EPSILON) $issues[] = 'balance_fifo_mismatch';
        if ($row['scope'] === 'item' && $row['ledger_qty'] === null
            && (abs($row['balance_qty']) > self::EPSILON || abs($row['fifo_qty']) > self::EPSILON)) $issues[] = 'ledger_unavailable';
        if ($row['scope'] === 'item' && $row['ledger_qty'] !== null
            && abs($row['ledger_qty'] - $row['balance_qty']) > self::EPSILON) $issues[] = 'ledger_balance_mismatch';
        if ($row['orphan_allocation_count'] > 0) $issues[] = 'orphan_allocation';
        if ($row['missing_allocation_count'] > 0) $issues[] = 'missing_allocation';
        if ($row['history_only_edit_count'] > 0) $issues[] = 'history_only_edit';
        $issues = array_values(array_unique($issues));

        $criticalCodes = ['negative_fifo', 'negative_balance', 'fifo_over_received', 'orphan_balance', 'balance_without_fifo', 'orphan_allocation'];
        $mismatchCodes = ['duplicate_balance', 'balance_fifo_mismatch', 'ledger_balance_mismatch'];
        $status = 'healthy';
        if (array_intersect($criticalCodes, $issues)) {
            $status = 'critical';
        } elseif (array_intersect($mismatchCodes, $issues)) {
            $status = 'mismatch';
        } elseif (!empty($issues)) {
            $status = 'review';
        }

        $row['issues'] = $issues;
        $row['status'] = $status;
        $row['variance_ledger_balance'] = $row['ledger_qty'] === null ? null : round($row['ledger_qty'] - $row['balance_qty'], 4);
        $row['variance_balance_fifo'] = round($row['balance_qty'] - $row['fifo_qty'], 4);
        $row['repair_mode'] = self::suggestRepairMode($row);
        return $row;
    }

    private static function suggestRepairMode(array $row): string
    {
        if ($row['status'] === 'healthy') return 'none';
        if ($row['scope'] === 'lot'
            && abs($row['balance_qty'] - $row['fifo_qty']) > self::EPSILON
            && $row['source_rows'] > 0) {
            return 'dry_run_sync_fifo';
        }
        if ($row['ledger_qty'] !== null && abs($row['ledger_qty'] - $row['balance_qty']) <= self::EPSILON
            && abs($row['balance_qty'] - $row['fifo_qty']) > self::EPSILON
            && $row['source_rows'] > 0) {
            return 'dry_run_sync_fifo';
        }
        if ($row['ledger_qty'] !== null && abs($row['ledger_qty'] - $row['fifo_qty']) <= self::EPSILON
            && abs($row['ledger_qty'] - $row['balance_qty']) > self::EPSILON) {
            return 'dry_run_sync_balance';
        }
        if ($row['scope'] === 'item' && $row['ledger_qty'] !== null
            && abs($row['balance_qty'] - $row['fifo_qty']) <= self::EPSILON
            && abs($row['ledger_qty'] - $row['balance_qty']) > self::EPSILON) {
            return 'dry_run_sync_ledger';
        }
        return 'manual_count_required';
    }

    private static function collectBalances(array $warehouseIds, array &$maps): void
    {
        $rows = (new Query())
            ->select(['warehouse_id', 'item_code', 'lot_number', 'balance_qty', 'id'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds])
            ->all();
        foreach ($rows as $r) {
            $key = self::key($r['warehouse_id'], $r['item_code'], $r['lot_number']);
            self::ensure($maps, $key, $r['warehouse_id'], $r['item_code'], $r['lot_number']);
            $maps[$key]['balance_qty'] += (float) $r['balance_qty'];
            $maps[$key]['balance_rows']++;
        }
    }

    private static function collectSources(array $warehouseIds, array &$maps): void
    {
        $rows = (new Query())
            ->select([
                'warehouse_id' => new Expression("CASE WHEN so.order_type IN ('TRANSFER','OUT') THEN so.sub_warehouse_id ELSE so.main_warehouse_id END"),
                'sd.item_code', 'sd.lot_number',
                'received_qty' => new Expression('SUM(ABS(sd.qty))'),
                'fifo_qty' => new Expression('SUM(sd.remain_qty)'),
                'source_rows' => new Expression('COUNT(sd.id)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and', ['so.main_warehouse_id' => $warehouseIds], ['or',
                    ['so.order_type' => StockOrder::ORDER_TYPE_IN],
                    ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]],
                ]],
                ['and', ['so.sub_warehouse_id' => $warehouseIds], ['so.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]], ['>', 'sd.qty', 0]],
            ])
            ->groupBy(['warehouse_id', 'sd.item_code', 'sd.lot_number'])
            ->all();
        foreach ($rows as $r) {
            $key = self::key($r['warehouse_id'], $r['item_code'], $r['lot_number']);
            self::ensure($maps, $key, $r['warehouse_id'], $r['item_code'], $r['lot_number']);
            $maps[$key]['received_qty'] += (float) $r['received_qty'];
            $maps[$key]['fifo_qty'] += (float) $r['fifo_qty'];
            $maps[$key]['source_rows'] += (int) $r['source_rows'];
        }
    }

    private static function collectLedger(array $warehouseIds, array &$totals): void
    {
        $rows = (new Query())
            ->select(['so.order_type', 'so.main_warehouse_id', 'so.sub_warehouse_id', 'sd.item_code', 'sd.lot_number', 'sd.qty', 'sd.ref', 'sd.data_json'])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or', ['so.main_warehouse_id' => $warehouseIds], ['so.sub_warehouse_id' => $warehouseIds]])
            ->all();
        foreach ($rows as $r) {
            $isMigrated = (string) ($r['ref'] ?? '') === 'V1' || strpos((string) ($r['data_json'] ?? ''), 'migrated_from_v1') !== false;
            foreach ($warehouseIds as $warehouseId) {
                $isMain = (int) $r['main_warehouse_id'] === $warehouseId;
                $isSub = (int) $r['sub_warehouse_id'] === $warehouseId;
                if (!$isMain && (!$isSub || $isMigrated)) continue;
                $sign = self::movementSign((string) $r['order_type'], $isMain, $isSub, (float) $r['qty']);
                if ($sign === 0) continue;
                $key = $warehouseId . '|' . trim((string) $r['item_code']);
                if (!isset($totals[$key])) $totals[$key] = 0.0;
                $totals[$key] += $sign * abs((float) $r['qty']);
            }
        }
    }

    private static function collectAuditSignals(array $warehouseIds, array &$maps): void
    {
        $reconciledAt = [];
        $reconcileRows = (new Query())
            ->select(['so.main_warehouse_id', 'sd.item_code', 'reconciled_at' => new Expression('MAX(so.created_at)')])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.status' => StockOrder::STATUS_CONFIRMED,
                'so.order_type' => StockOrder::ORDER_TYPE_ADJUST,
                'so.main_warehouse_id' => $warehouseIds,
            ])
            ->andWhere(['like', 'sd.data_json', '"history_reconcile"'])
            ->groupBy(['so.main_warehouse_id', 'sd.item_code'])
            ->all();
        foreach ($reconcileRows as $reconcileRow) {
            $reconciledAt[(int) $reconcileRow['main_warehouse_id'] . '|' . (string) $reconcileRow['item_code']]
                = (string) $reconcileRow['reconciled_at'];
        }
        $rows = (new Query())
            ->select(['sd.id', 'sd.item_code', 'sd.lot_number', 'sd.data_json', 'so.main_warehouse_id'])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.status' => StockOrder::STATUS_CONFIRMED, 'so.order_type' => StockOrder::ORDER_TYPE_OUT, 'so.main_warehouse_id' => $warehouseIds])
            ->all();
        $sourceIds = [];
        foreach ($rows as $r) {
            $data = is_array($r['data_json']) ? $r['data_json'] : json_decode((string) $r['data_json'], true);
            $data = is_array($data) ? $data : [];
            $key = self::key($r['main_warehouse_id'], $r['item_code'], $r['lot_number']);
            self::ensure($maps, $key, $r['main_warehouse_id'], $r['item_code'], $r['lot_number']);
            $allocations = isset($data['fifo_allocations']) && is_array($data['fifo_allocations']) ? $data['fifo_allocations'] : [];
            $hasHistoryEdit = !empty($data['history_inline_qty_edits']);
            if (empty($allocations) && $hasHistoryEdit) {
                $maps[$key]['missing_allocation_count']++;
            }
            foreach ($allocations as $allocation) {
                $id = (int) ($allocation['source_detail_id'] ?? 0);
                if ($id > 0) $sourceIds[$id] = true;
            }
            foreach (($data['history_inline_qty_edits'] ?? []) as $edit) {
                if (isset($edit['return_stock']) && !$edit['return_stock']) {
                    $itemKey = (int) $r['main_warehouse_id'] . '|' . (string) $r['item_code'];
                    $editAt = (string) ($edit['at'] ?? '');
                    // การแก้เก่าที่ถูกรวมไว้ใน controlled ledger reconciliation แล้ว
                    // คงอยู่เป็น Audit แต่ไม่ควรเตือนซ้ำ เว้นแต่มีการแก้ใหม่ภายหลัง
                    if (!isset($reconciledAt[$itemKey]) || $editAt === '' || $editAt > $reconciledAt[$itemKey]) {
                        $maps[$key]['history_only_edit_count']++;
                    }
                }
            }
        }
        if (!empty($sourceIds)) {
            $existing = (new Query())->select('id')->from(StockDetail::tableName())->where(['id' => array_keys($sourceIds)])->column();
            $missing = array_diff(array_keys($sourceIds), array_map('intval', $existing));
            if (!empty($missing)) {
                foreach ($rows as $r) {
                    $data = is_array($r['data_json']) ? $r['data_json'] : json_decode((string) $r['data_json'], true);
                    foreach (($data['fifo_allocations'] ?? []) as $allocation) {
                        if (in_array((int) ($allocation['source_detail_id'] ?? 0), $missing, true)) {
                            $key = self::key($r['main_warehouse_id'], $r['item_code'], $r['lot_number']);
                            $maps[$key]['orphan_allocation_count']++;
                        }
                    }
                }
            }
        }
    }

    private static function movementSign(string $type, bool $isMain, bool $isSub, float $qty): int
    {
        if ($type === StockOrder::ORDER_TYPE_ADJUST && $isMain) return $qty >= 0 ? 1 : -1;
        if ($type === StockOrder::ORDER_TYPE_IN) return $isMain ? 1 : ($isSub ? -1 : 0);
        if ($type === StockOrder::ORDER_TYPE_OUT) return $isMain ? -1 : ($isSub ? 1 : 0);
        if ($type === StockOrder::ORDER_TYPE_TRANSFER) return $isMain && !$isSub ? -1 : ($isSub && !$isMain ? 1 : 0);
        return 0;
    }

    private static function key($warehouseId, $itemCode, $lot): string
    {
        return (int) $warehouseId . '|' . trim((string) $itemCode) . '|' . self::lot($lot);
    }

    private static function lot($lot): string
    {
        $lot = trim((string) $lot);
        return $lot === '' ? '-' : $lot;
    }

    private static function ensure(array &$maps, string $key, $warehouseId, $itemCode, $lot): void
    {
        if (isset($maps[$key])) return;
        $maps[$key] = [
            'warehouse_id' => (int) $warehouseId,
            'item_code' => trim((string) $itemCode),
            'lot_number' => self::lot($lot),
            'ledger_qty' => 0.0,
            'balance_qty' => 0.0,
            'fifo_qty' => 0.0,
            'received_qty' => 0.0,
            'balance_rows' => 0,
            'source_rows' => 0,
            'missing_allocation_count' => 0,
            'orphan_allocation_count' => 0,
            'history_only_edit_count' => 0,
        ];
    }

    private static function emptyResult(): array
    {
        return ['rows' => [], 'summary' => ['total' => 0, 'critical' => 0, 'mismatch' => 0, 'review' => 0, 'healthy' => 0], 'generated_at' => date('Y-m-d H:i:s')];
    }
}
