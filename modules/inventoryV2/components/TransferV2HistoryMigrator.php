<?php

namespace app\modules\inventoryV2\components;

use Yii;
use yii\db\Query;
use app\modules\inventory\models\StockEvent;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Service: ย้ายประวัติ stock_events (V1) → stock_order + stock_detail (V2)
 * History-only mode: ไม่แตะ stock_balance / FIFO (remain_qty = 0)
 *
 * ใช้ร่วมกันระหว่าง web controller (TransferToV2Controller) และ console command
 * (TransferHistoryAllV2Controller). state-less, รับ userId ตอน construct เพื่อบันทึก audit
 */
class TransferV2HistoryMigrator
{
    /** @var int|null user id ผู้สั่งย้าย — null = console / guest */
    public $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId !== null ? (int) $userId : null;
    }

    /**
     * คืน distinct warehouse_id ของใบ V1 (status=success, txn IN/OUT) ที่ "มีอยู่ใน V2 warehouses"
     */
    public function getAllV1WarehouseIds()
    {
        $v1WarehouseIds = (new Query())
            ->select('warehouse_id')
            ->distinct()
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'order_status' => 'success',
            ])
            ->andWhere(['in', 'transaction_type', ['IN', 'OUT']])
            ->andWhere(['>', 'warehouse_id', 0])
            ->column();
        if (empty($v1WarehouseIds)) return [];

        return Warehouse::find()
            ->select('id')
            ->where(['id' => $v1WarehouseIds])
            ->column();
    }

    /**
     * ดึงประวัติทุกใบใน warehouse_id เดียว — ไม่ filter ด้วย asset_item
     * คืน [parents, childrenByParent, existingV2Items, existingOrderNos]
     */
    public function fetchHistoryDataByWarehouse($warehouseId)
    {
        $parents = (new Query())
            ->select([
                'id', 'code', 'transaction_type', 'movement_date', 'warehouse_id', 'from_warehouse_id',
                'order_status', 'emp_id', 'created_at', 'updated_at', 'created_by', 'updated_by',
            ])
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'warehouse_id' => $warehouseId,
                'order_status' => 'success',
            ])
            ->andWhere(['in', 'transaction_type', ['IN', 'OUT']])
            ->orderBy(['movement_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $children = [];
        $allParentIds = array_column($parents, 'id');
        if (!empty($allParentIds)) {
            $children = (new Query())
                ->select([
                    't.id', 't.category_id AS parent_id', 't.asset_item', 't.qty', 't.unit_price', 't.lot_number',
                    't.created_at', 't.updated_at', 't.created_by', 't.updated_by',
                ])
                ->from(['t' => StockEvent::tableName()])
                ->where([
                    't.name' => 'order_item',
                    't.category_id' => $allParentIds,
                    't.order_status' => 'success',
                ])
                ->all();
        }

        $childrenByParent = [];
        foreach ($children as $c) {
            $childrenByParent[(int) $c['parent_id']][] = $c;
        }

        $itemCodes = array_unique(array_filter(array_column($children, 'asset_item')));
        $existingV2Items = !empty($itemCodes)
            ? array_flip((new Query())
                ->select('code')
                ->distinct()
                ->from('categorise')
                ->where(['name' => 'asset_item', 'code' => $itemCodes])
                ->column())
            : [];

        $existingOrderNos = !empty($parents)
            ? array_flip(StockOrder::find()->select('order_no')->where(['order_no' => array_column($parents, 'code')])->column())
            : [];

        return [$parents, $childrenByParent, $existingV2Items, $existingOrderNos];
    }

    /**
     * Loop migrate หลายใบในคราวเดียว — selectedIds = parent ids ที่อนุญาตให้ย้าย
     * คืน summary array
     */
    public function migrateOrders(
        array $parents,
        array $childrenByParent,
        array $existingV2Items,
        array $existingOrderNos,
        array $existingWarehouses,
        array $selectedIds
    ) {
        $createdCount = 0;
        $skippedDuplicate = [];
        $skippedNoItems = [];
        $skippedNoWarehouse = [];
        $errors = [];
        $totalLinesMigrated = 0;
        $totalLinesSkipped = 0;
        $partialReports = [];
        $missingItemCodes = [];
        $selectedFlip = array_flip($selectedIds);

        foreach ($parents as $p) {
            if (!isset($selectedFlip[(int) $p['id']])) {
                continue;
            }
            $code = (string) $p['code'];
            if (isset($existingOrderNos[$code])) {
                $skippedDuplicate[] = $code;
                continue;
            }
            $mainWarehouseId = (int) ($p['warehouse_id'] ?? 0);
            if ($mainWarehouseId <= 0 || !isset($existingWarehouses[$mainWarehouseId])) {
                $skippedNoWarehouse[] = $code . ' (warehouse_id=' . $mainWarehouseId . ')';
                continue;
            }

            $lines = $childrenByParent[(int) $p['id']] ?? [];
            $result = $this->migrateOneOrder($p, $lines, $existingV2Items, $mainWarehouseId);

            if ($result['status'] === 'created') {
                $createdCount++;
                $totalLinesMigrated += $result['lines_migrated'];
                $totalLinesSkipped += $result['lines_skipped'];
                foreach ($result['missing_codes'] as $mc) {
                    $missingItemCodes[$mc] = true;
                }
                if ($result['partial_report']) {
                    $partialReports[] = $result['partial_report'];
                }
            } elseif ($result['status'] === 'noItems') {
                $skippedNoItems[] = $code;
                foreach ($result['missing_codes'] as $mc) {
                    $missingItemCodes[$mc] = true;
                }
            } else {
                $errors[] = $code . ' (' . $result['reason'] . ')';
            }
        }

        return compact(
            'createdCount',
            'skippedDuplicate',
            'skippedNoItems',
            'skippedNoWarehouse',
            'errors',
            'totalLinesMigrated',
            'totalLinesSkipped',
            'partialReports',
            'missingItemCodes'
        );
    }

    /**
     * ย้าย 1 ใบ — transactional
     */
    private function migrateOneOrder(array $p, array $lines, array $existingV2Items, $mainWarehouseId)
    {
        $code = (string) $p['code'];
        $validLines = [];
        $skippedCodesInDoc = [];
        $skippedQtyInDoc = [];
        foreach ($lines as $l) {
            $itemCode = trim((string) ($l['asset_item'] ?? ''));
            if ($itemCode === '' || !isset($existingV2Items[$itemCode])) {
                if ($itemCode !== '') {
                    $skippedCodesInDoc[$itemCode] = true;
                }
                continue;
            }
            $validLines[] = $l;
        }
        if (empty($validLines)) {
            return [
                'status' => 'noItems',
                'lines_migrated' => 0,
                'lines_skipped' => count($skippedCodesInDoc),
                'missing_codes' => array_keys($skippedCodesInDoc),
                'partial_report' => null,
            ];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $orderDate = !empty($p['movement_date'])
                ? $p['movement_date'] . ' 12:00:00'
                : ($p['created_at'] ?: date('Y-m-d H:i:s'));
            $isOut = ($p['transaction_type'] === 'OUT');

            $stockOrder = new StockOrder();
            $stockOrder->order_no = $code;
            $stockOrder->order_type = $isOut ? StockOrder::ORDER_TYPE_OUT : StockOrder::ORDER_TYPE_IN;
            $stockOrder->source_type = $isOut ? 'REQUEST' : 'NORMAL';
            $stockOrder->status = StockOrder::STATUS_CONFIRMED;
            $stockOrder->order_date = $orderDate;
            $stockOrder->main_warehouse_id = $mainWarehouseId;
            $stockOrder->sub_warehouse_id = (int) ($p['from_warehouse_id'] ?? 0) ?: null;
            $stockOrder->emp_id = !empty($p['emp_id']) ? (int) $p['emp_id'] : null;
            if ($isOut) {
                $stockOrder->disbursement_date = strtotime($orderDate) ?: time();
            }
            $stockOrder->ref = 'V1';
            $stockOrder->data_json = ['migrated_from_v1' => $this->buildV1Marker($p)];
            $stockOrder->created_by = !empty($p['created_by']) ? (int) $p['created_by'] : null;
            $stockOrder->updated_by = !empty($p['updated_by']) ? (int) $p['updated_by'] : ($stockOrder->created_by ?: null);
            $stockOrder->created_at = $this->toDateTime($p['created_at'] ?? null);
            $stockOrder->updated_at = $this->toDateTime($p['updated_at'] ?? null) ?: $stockOrder->created_at;

            if (!$stockOrder->save(false)) {
                throw new \RuntimeException('บันทึก stock_order ไม่สำเร็จ: ' . $code);
            }

            $linesInDoc = 0;
            foreach ($validLines as $l) {
                $qty = (float) $l['qty'];
                if ($qty < 0) {
                    $skippedQtyInDoc[] = $l['asset_item'] . ' qty=' . $qty;
                    continue;
                }

                $detail = new StockDetail();
                $detail->stock_order_id = $stockOrder->id;
                $detail->item_code = $l['asset_item'];
                $detail->qty = $qty;
                $detail->unit_price = $l['unit_price'] !== null ? (float) $l['unit_price'] : null;
                $detail->lot_number = !empty($l['lot_number']) ? $l['lot_number'] : ('V1-' . $code);
                $detail->remain_qty = 0;
                $detail->ref = 'V1';
                $detail->data_json = [
                    'migrated_from_v1' => [
                        'source_id' => (int) $l['id'],
                        'source_parent_id' => (int) $p['id'],
                        'source_code' => $code,
                        'source_status' => $p['order_status'] ?? null,
                        'migrated_at' => time(),
                        'migrated_by' => $this->userId,
                    ],
                ];
                $detail->created_by = !empty($l['created_by']) ? (int) $l['created_by'] : $stockOrder->created_by;
                $detail->updated_by = !empty($l['updated_by']) ? (int) $l['updated_by'] : ($detail->created_by ?: $stockOrder->updated_by);
                $detail->created_at = $this->toDateTime($l['created_at'] ?? null) ?: $stockOrder->created_at;
                $detail->updated_at = $this->toDateTime($l['updated_at'] ?? null) ?: $detail->created_at;
                if (!$detail->save(false)) {
                    throw new \RuntimeException('บันทึก stock_detail ไม่สำเร็จ: ' . $code . ' / ' . $l['asset_item']);
                }
                $linesInDoc++;
            }
            $tx->commit();

            $skipInDoc = count($skippedCodesInDoc) + count($skippedQtyInDoc);
            $partialReport = null;
            if ($skipInDoc > 0) {
                $skipNotes = [];
                if ($skippedCodesInDoc) {
                    $skipNotes[] = 'missing item: ' . implode(',', array_keys($skippedCodesInDoc));
                }
                if ($skippedQtyInDoc) {
                    $skipNotes[] = 'negative qty: ' . implode(',', $skippedQtyInDoc);
                }
                $partialReport = $code . ' ' . $linesInDoc . '/' . count($lines)
                    . ' (skip: ' . implode('; ', $skipNotes) . ')';
            }
            return [
                'status' => 'created',
                'lines_migrated' => $linesInDoc,
                'lines_skipped' => $skipInDoc,
                'missing_codes' => array_keys($skippedCodesInDoc),
                'partial_report' => $partialReport,
            ];
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('[TransferV2HistoryMigrator] ' . $code . ': ' . $e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'reason' => $e->getMessage(),
                'lines_migrated' => 0,
                'lines_skipped' => 0,
                'missing_codes' => [],
                'partial_report' => null,
            ];
        }
    }

    private function toDateTime($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int) $value);
        }

        $timestamp = strtotime((string) $value);
        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function buildV1Marker(array $p)
    {
        return [
            'source_id' => (int) $p['id'],
            'source_code' => (string) $p['code'],
            'source_status' => (string) ($p['order_status'] ?? ''),
            'migrated_at' => time(),
            'migrated_by' => $this->userId,
            'v1_movement_date' => $p['movement_date'] ?? null,
        ];
    }
}
