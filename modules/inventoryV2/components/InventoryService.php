<?php

namespace app\modules\inventoryV2\components;

use Yii;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Query;

class InventoryService
{
    /**
     * ฟังก์ชันหลักในการขยับสต็อก
     * @param string $type 'IN' หรือ 'OUT'
     */
    public static function moveStock($itemId, $warehouseId, $qty, $type, $orderId, $detailId = null, $lotNumber = null)
    {
        if ($type === 'IN') {
            return self::processReceive($itemId, $warehouseId, $qty, $orderId, $detailId, $lotNumber);
        } elseif ($type === 'OUT') {
            return self::processFIFO($itemId, $warehouseId, $qty, $orderId, $detailId);
        }
        return false;
    }

    /**
     * --- 1. กระบวนการรับเข้า (IN) ---
     */
    private static function processReceive($itemId, $warehouseId, $qty, $orderId, $detailId, $lotNumber)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // อัปเดตยอดรวมใน StockBalance (แยกตามคลังและ Lot)
            self::updateBalance($itemId, $warehouseId, $qty, 'IN', $lotNumber);

            // เซ็ตค่า remain_qty ใน StockDetail เพื่อเอาไว้ใช้ตัด FIFO ในอนาคต
            if ($detailId) {
                $detail = StockDetail::findOne($detailId);
                if ($detail) {
                    $detail->remain_qty = $qty;
                    $detail->save(false);
                }
            }

            self::assertBalanceMatchesFifo($itemId, $warehouseId);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * --- 2. กระบวนการจ่ายออก (FIFO OUT) ---
     */
    public static function processFIFO($itemId, $warehouseId, $totalQtyToOut, $orderId, $orderDetailId)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            self::lockStockPool($itemId, $warehouseId);
            // 1. ค้นหารายการ source lot ที่ยังมีของเหลือ (remain_qty > 0)
            // เรียงตามวันที่รับเข้าจากเก่าไปใหม่ (FIFO)
            $availableLots = StockDetail::find()
                ->joinWith('stockOrder')
                ->where(['stock_detail.item_code' => $itemId])
                ->andWhere(['stock_order.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['or',
                    ['and',
                        ['stock_order.main_warehouse_id' => $warehouseId],
                        ['or',
                            ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                            ['and',
                                ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST],
                                ['>', 'stock_detail.qty', 0],
                            ],
                        ],
                    ],
                    ['and',
                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_TRANSFER],
                        ['stock_order.sub_warehouse_id' => $warehouseId],
                        ['>', 'stock_detail.qty', 0],
                    ],
                ])
                ->andWhere(['>', 'stock_detail.remain_qty', 0])
                ->orderBy([
                    'stock_order.order_date' => SORT_ASC, 
                    'stock_detail.id' => SORT_ASC
                ])
                ->all();

            $remainingToProcess = (float)$totalQtyToOut;
            $allocations = [];

            foreach ($availableLots as $lot) {
                if ($remainingToProcess <= 0) break;

                // จำนวนที่จะหยิบออกจาก Lot นี้
                $take = min($remainingToProcess, (float)$lot->remain_qty);

                // ลด remain_qty ใน StockDetail (ต้นทางที่รับเข้า)
                $lot->remain_qty -= $take;
                if (!$lot->save(false)) throw new \Exception("ไม่สามารถปรับปรุง remain_qty ได้");

                // ตัดยอดออกจาก StockBalance (แยกตาม Lot ที่หยิบจริง)
                self::updateBalance($itemId, $warehouseId, $take, 'OUT', $lot->lot_number);
                $allocations[] = [
                    'source_detail_id' => (int) $lot->id,
                    'source_order_id' => (int) $lot->stock_order_id,
                    'lot_number' => (string) $lot->lot_number,
                    'qty' => (float) $take,
                ];

                $remainingToProcess -= $take;
            }

            // ถ้าวนลูปจนจบแล้วยังเหลือยอดที่หักไม่ได้ แปลว่าของไม่พอ
            if ($remainingToProcess > 0) {
                throw new \Exception("พัสดุรหัส {$itemId} ในคลังมีไม่พอจ่าย (ขาดอีก {$remainingToProcess})");
            }

            // อัปเดต remain_qty ของรายการจ่ายออก (OUT) ถ้ามีการส่ง orderDetailId มา
            // เพื่อบันทึกว่าจ่ายไปเท่าไหร่แล้ว (สำหรับการติดตาม)
            if ($orderDetailId) {
                $outDetail = StockDetail::findOne($orderDetailId);
                if ($outDetail) {
                    // บันทึกจำนวนที่จ่ายจริง (อาจจะน้อยกว่าที่ขอเบิก)
                    $processedQty = $totalQtyToOut - $remainingToProcess;
                    $outDetail->remain_qty = (float) $outDetail->remain_qty + $processedQty;
                    $data = is_array($outDetail->data_json)
                        ? $outDetail->data_json
                        : (is_string($outDetail->data_json) ? (json_decode($outDetail->data_json, true) ?: []) : []);
                    if (!is_array($data)) {
                        $data = [];
                    }
                    $existingAllocations = isset($data['fifo_allocations']) && is_array($data['fifo_allocations'])
                        ? $data['fifo_allocations']
                        : [];
                    $data['fifo_allocations'] = array_values(array_merge($existingAllocations, $allocations));
                    $outDetail->data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $outDetail->save(false);
                }
            }

            self::assertBalanceMatchesFifo($itemId, $warehouseId);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * --- 3. ฟังก์ชันอัปเดตยอดคงเหลือสะสม (StockBalance) ---
     */
    public static function updateBalance($itemId, $warehouseId, $qty, $type, $lotNumber = null)
    {
        $db = Yii::$app->db;
        $ownsTransaction = $db->getTransaction() === null;
        $transaction = $ownsTransaction ? $db->beginTransaction() : null;
        $lot = (!empty($lotNumber)) ? trim((string) $lotNumber) : '-';

        try {
            self::lockStockPool($itemId, $warehouseId, $lot);
            $balances = StockBalance::find()->where([
                'item_code' => $itemId,
                'warehouse_id' => $warehouseId,
                'lot_number' => $lot,
            ])->orderBy(['id' => SORT_ASC])->all();

            if (count($balances) > 1) {
                throw new \Exception("พบยอดคงเหลือซ้ำสำหรับพัสดุ {$itemId} Lot {$lot} กรุณาตรวจสุขภาพสต็อกก่อนทำรายการ");
            }
            $balance = $balances[0] ?? null;
            if (!$balance) {
                if ($type === 'IN') {
                    $balance = new StockBalance([
                        'item_code' => $itemId,
                        'warehouse_id' => $warehouseId,
                        'lot_number' => $lot,
                        'balance_qty' => 0,
                    ]);
                } else {
                    throw new \Exception("ไม่พบยอดคงเหลือของ Lot: {$lot} ในระบบ (ไม่สามารถหักออกได้)");
                }
            }

            $qtyChange = ($type === 'IN') ? (float) $qty : -(float) $qty;
            $balance->balance_qty = (float) $balance->balance_qty + $qtyChange;
            if ($balance->balance_qty < -0.000001) {
                throw new \Exception("สต็อกติดลบ: พัสดุ {$itemId} ใน Lot {$lot} ไม่เพียงพอ");
            }
            if (abs((float) $balance->balance_qty) < 0.000001) {
                $balance->balance_qty = 0;
            }
            if (!$balance->save()) {
                throw new \Exception("อัปเดตยอดคงเหลือไม่สำเร็จ: " . json_encode($balance->getErrors()));
            }
            if ($ownsTransaction) $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            if ($ownsTransaction && $transaction && $transaction->isActive) $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Lock a stable item row plus affected balance/source rows.
     * Must be called inside a transaction; the item row prevents a race when
     * the stock_balance row does not exist yet.
     */
    public static function lockStockPool($itemId, $warehouseId, $lotNumber = null, bool $allowMissingRegistry = false): void
    {
        $db = Yii::$app->db;
        $tx = $db->getTransaction();
        if ($tx === null || !$tx->isActive) {
            throw new \LogicException('ต้องเปิด transaction ก่อน lock stock pool');
        }
        $itemQuery = (new Query())
            ->select('code')
            ->from(StockItem::tableName())
            ->where(['code' => (string) $itemId, 'name' => 'asset_item', 'group_id' => 'MATER']);
        $itemCode = $db->createCommand($itemQuery->createCommand($db)->getRawSql() . ' FOR UPDATE')->queryScalar();
        if ($itemCode === false) {
            if (!$allowMissingRegistry) {
                throw new \Exception("ไม่พบพัสดุรหัส {$itemId} ในทะเบียนวัสดุ");
            }
            // Legacy hospitals can retain confirmed stock history after the
            // material master was removed.  Repairs still need a stable lock;
            // serialize only this exceptional path on the warehouse row.
            $warehouseQuery = (new Query())
                ->select('id')
                ->from(Warehouse::tableName())
                ->where(['id' => (int) $warehouseId]);
            $lockedWarehouse = $db->createCommand($warehouseQuery->createCommand($db)->getRawSql() . ' FOR UPDATE')->queryScalar();
            if ($lockedWarehouse === false) {
                throw new \Exception("ไม่พบคลังรหัส {$warehouseId}");
            }
        }

        $balanceCondition = ['item_code' => (string) $itemId, 'warehouse_id' => (int) $warehouseId];
        if ($lotNumber !== null) $balanceCondition['lot_number'] = trim((string) $lotNumber) ?: '-';
        $balanceQuery = (new Query())->select('id')->from(StockBalance::tableName())->where($balanceCondition);
        $db->createCommand($balanceQuery->createCommand($db)->getRawSql() . ' FOR UPDATE')->queryColumn();

        $sourceQuery = (new Query())
            ->select('sd.id')
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => (string) $itemId, 'so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and', ['so.main_warehouse_id' => (int) $warehouseId], ['or',
                    ['so.order_type' => StockOrder::ORDER_TYPE_IN],
                    ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]],
                ]],
                ['and', ['so.sub_warehouse_id' => (int) $warehouseId], ['so.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]], ['>', 'sd.qty', 0]],
            ]);
        if ($lotNumber !== null) $sourceQuery->andWhere(['sd.lot_number' => trim((string) $lotNumber) ?: '-']);
        $db->createCommand($sourceQuery->createCommand($db)->getRawSql() . ' FOR UPDATE')->queryColumn();
    }

    public static function lockOrder($orderId): array
    {
        $db = Yii::$app->db;
        $tx = $db->getTransaction();
        if ($tx === null || !$tx->isActive) {
            throw new \LogicException('ต้องเปิด transaction ก่อน lock stock order');
        }
        $query = (new Query())->select(['id', 'status'])->from(StockOrder::tableName())->where(['id' => (int) $orderId]);
        $row = $db->createCommand($query->createCommand($db)->getRawSql() . ' FOR UPDATE')->queryOne();
        if (!$row) throw new \Exception('ไม่พบเอกสารสต็อกที่ต้องการ');
        return $row;
    }

    /**
     * Fail closed before commit when the operational balance and FIFO sources diverge.
     * Ledger is validated after the confirmed document has been fully saved by the caller.
     */
    public static function assertBalanceMatchesFifo($itemCode, $warehouseId): void
    {
        $balanceRows = (new Query())
            ->select(['lot_number', 'qty' => new \yii\db\Expression('SUM(balance_qty)'), 'rows' => new \yii\db\Expression('COUNT(*)')])
            ->from(StockBalance::tableName())
            ->where(['item_code' => (string) $itemCode, 'warehouse_id' => (int) $warehouseId])
            ->groupBy('lot_number')->all();
        $fifoRows = (new Query())
            ->select(['lot_number' => 'sd.lot_number', 'qty' => new \yii\db\Expression('SUM(sd.remain_qty)')])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => (string) $itemCode, 'so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and', ['so.main_warehouse_id' => (int) $warehouseId], ['or',
                    ['so.order_type' => StockOrder::ORDER_TYPE_IN],
                    ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]],
                ]],
                ['and', ['so.sub_warehouse_id' => (int) $warehouseId], ['so.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]], ['>', 'sd.qty', 0]],
            ])->groupBy('sd.lot_number')->all();
        $balance = [];
        foreach ($balanceRows as $row) {
            if ((int) $row['rows'] > 1) throw new \RuntimeException("พบ Balance ซ้ำ พัสดุ {$itemCode} Lot {$row['lot_number']}");
            $balance[(string) $row['lot_number']] = (float) $row['qty'];
        }
        $fifo = [];
        foreach ($fifoRows as $row) $fifo[(string) $row['lot_number']] = (float) $row['qty'];
        foreach (array_unique(array_merge(array_keys($balance), array_keys($fifo))) as $lot) {
            $balanceQty = (float) ($balance[$lot] ?? 0);
            $fifoQty = (float) ($fifo[$lot] ?? 0);
            if (abs($balanceQty - $fifoQty) > 0.0001) {
                throw new \RuntimeException("ยกเลิกรายการเพื่อป้องกันสต๊อกคลาดเคลื่อน: {$itemCode} Lot {$lot} (Balance {$balanceQty}, FIFO {$fifoQty}) กรุณาตรวจสุขภาพสต๊อก");
            }
        }
    }

    /** จำนวนที่ใบเบิก APPROVED ซึ่งมาก่อนเอกสารปัจจุบันจองไว้ (ยังไม่ตัด stock จริง) */
    public static function reservedAheadQty($itemCode, $warehouseId, $currentOrderId): float
    {
        return (float) (new Query())
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'sd.item_code' => (string) $itemCode,
                'so.main_warehouse_id' => (int) $warehouseId,
                'so.order_type' => StockOrder::ORDER_TYPE_OUT,
                'so.status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['<', 'so.id', (int) $currentOrderId])
            ->sum('sd.qty');
    }

    /**
     * คืนยอดจากรายการจ่ายกลับเข้า FIFO/stock_balance เมื่อแก้จำนวนใบเบิกให้ลดลง
     */
    public static function returnFifoAllocation(StockDetail $outDetail, $warehouseId, $qtyToReturn)
    {
        $remaining = (float) $qtyToReturn;
        if ($remaining <= 0) {
            return true;
        }

        $data = is_array($outDetail->data_json)
            ? $outDetail->data_json
            : (is_string($outDetail->data_json) ? (json_decode($outDetail->data_json, true) ?: []) : []);
        if (!is_array($data)) {
            $data = [];
        }

        $allocations = isset($data['fifo_allocations']) && is_array($data['fifo_allocations'])
            ? $data['fifo_allocations']
            : [];

        for ($i = count($allocations) - 1; $i >= 0 && $remaining > 0; $i--) {
            $allocatedQty = (float) ($allocations[$i]['qty'] ?? 0);
            if ($allocatedQty <= 0) {
                continue;
            }

            $take = min($remaining, $allocatedQty);
            $lotNumber = (string) ($allocations[$i]['lot_number'] ?? $outDetail->lot_number ?? 'ADJUST');
            $sourceDetailId = (int) ($allocations[$i]['source_detail_id'] ?? 0);
            self::lockStockPool($outDetail->item_code, $warehouseId, $lotNumber);
            if ($sourceDetailId > 0) {
                $sourceDetail = self::findEligibleSource($sourceDetailId, $outDetail->item_code, $warehouseId, $lotNumber);
                if (!$sourceDetail) {
                    throw new \Exception("ไม่พบ Lot ต้นทางของ allocation (detail {$sourceDetailId}) จึงไม่คืน Balance เพื่อป้องกันยอดคลาดเคลื่อน");
                }
                self::restoreSourceCapacity($sourceDetail, $take);
            } else {
                self::returnToLotSource($outDetail->item_code, $warehouseId, $lotNumber, $take);
            }

            self::updateBalance($outDetail->item_code, $warehouseId, $take, 'IN', $lotNumber);
            $allocations[$i]['qty'] = $allocatedQty - $take;
            $remaining -= $take;
        }

        if ($remaining > 0) {
            $lotNumber = !empty($outDetail->lot_number) ? $outDetail->lot_number : 'ADJUST';
            self::returnToLotSource($outDetail->item_code, $warehouseId, $lotNumber, $remaining);
            self::updateBalance($outDetail->item_code, $warehouseId, $remaining, 'IN', $lotNumber);
            $remaining = 0;
        }

        $data['fifo_allocations'] = array_values(array_filter($allocations, function ($allocation) {
            return (float) ($allocation['qty'] ?? 0) > 0.000001;
        }));
        $outDetail->remain_qty = max(0, (float) $outDetail->remain_qty - (float) $qtyToReturn);
        $outDetail->data_json = json_encode($data, JSON_UNESCAPED_UNICODE);

        return true;
    }

    private static function returnToLotSource($itemCode, $warehouseId, $lotNumber, $qty)
    {
        self::lockStockPool($itemCode, $warehouseId, $lotNumber);
        $sourceDetails = StockDetail::find()
            ->joinWith('stockOrder')
            ->where([
                'stock_detail.item_code' => $itemCode,
                'stock_detail.lot_number' => $lotNumber,
                'stock_order.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['or',
                ['and',
                    ['stock_order.main_warehouse_id' => $warehouseId],
                    ['or',
                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                        ['and', ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'stock_detail.qty', 0]],
                    ],
                ],
                ['and',
                    ['stock_order.sub_warehouse_id' => $warehouseId],
                    ['stock_order.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]],
                    ['>', 'stock_detail.qty', 0],
                ],
            ])
            ->orderBy([
                'stock_order.order_date' => SORT_DESC,
                'stock_detail.id' => SORT_DESC,
            ])
            ->all();

        $remaining = (float) $qty;
        foreach ($sourceDetails as $sourceDetail) {
            if ($remaining <= 0.000001) break;
            $capacity = max(0.0, abs((float) $sourceDetail->qty) - (float) $sourceDetail->remain_qty);
            if ($capacity <= 0.000001) continue;
            $take = min($remaining, $capacity);
            self::restoreSourceCapacity($sourceDetail, $take);
            $remaining -= $take;
        }
        if ($remaining > 0.000001) {
            throw new \Exception("ไม่พบความจุใน Lot ต้นทาง {$lotNumber} เพียงพอสำหรับคืน {$qty} หน่วย จึงไม่คืน Balance");
        }
        return true;
    }

    private static function findEligibleSource($sourceDetailId, $itemCode, $warehouseId, $lotNumber)
    {
        return StockDetail::find()
            ->joinWith('stockOrder')
            ->where([
                'stock_detail.id' => (int) $sourceDetailId,
                'stock_detail.item_code' => (string) $itemCode,
                'stock_detail.lot_number' => (string) $lotNumber,
                'stock_order.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['or',
                ['and', ['stock_order.main_warehouse_id' => (int) $warehouseId], ['or',
                    ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                    ['and', ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'stock_detail.qty', 0]],
                ]],
                ['and', ['stock_order.sub_warehouse_id' => (int) $warehouseId], ['stock_order.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]], ['>', 'stock_detail.qty', 0]],
            ])
            ->one();
    }

    private static function restoreSourceCapacity(StockDetail $sourceDetail, $qty): void
    {
        $capacity = max(0.0, abs((float) $sourceDetail->qty) - (float) $sourceDetail->remain_qty);
        if ((float) $qty > $capacity + 0.000001) {
            throw new \Exception("ยอดคืนเกินจำนวนที่เคยตัดจาก Lot ต้นทาง (detail {$sourceDetail->id})");
        }
        $sourceDetail->remain_qty = (float) $sourceDetail->remain_qty + (float) $qty;
        if (!$sourceDetail->save(false)) {
            throw new \Exception("ไม่สามารถคืนยอด Lot ต้นทาง (detail {$sourceDetail->id})");
        }
    }

    /**
     * ปรับยอดคงเหลือโดยตรง (ใช้กับเอกสารประเภท ADJUST หรือ migration จาก V1)
     * @param string $itemCode รหัสพัสดุ
     * @param int $warehouseId คลัง
     * @param string $lotNumber เลข Lot (เช่น 'ADJUST')
     * @param float $delta จำนวนที่เพิ่ม (+) หรือลด (-)
     * @param bool $allowNegative อนุญาตให้ยอดติดลบได้ (ใช้กับการย้ายข้อมูลจาก V1 ที่อาจมี snapshot ไม่ครบ)
     */
    public static function adjustBalance($itemCode, $warehouseId, $lotNumber, $delta, $allowNegative = false)
    {
        $lot = !empty($lotNumber) ? $lotNumber : '-';

        $balance = StockBalance::findOne([
            'item_code' => $itemCode,
            'warehouse_id' => $warehouseId,
            'lot_number' => $lot,
        ]);

        if (!$balance) {
            $balance = new StockBalance([
                'item_code' => $itemCode,
                'warehouse_id' => $warehouseId,
                'lot_number' => $lot,
                'balance_qty' => 0,
            ]);
        }

        $balance->balance_qty += (float) $delta;
        // Lot ADJUST และโหมด migration (allowNegative) อนุญาตให้ยอดติดลบได้
        if ($balance->balance_qty < 0 && $lot !== 'ADJUST' && !$allowNegative) {
            throw new \Exception("ยอดคงเหลือหลังปรับจะติดลบ (พัสดุ {$itemCode} Lot {$lot})");
        }

        $balance->updated_at = date('Y-m-d H:i:s');
        $balance->updated_by = Yii::$app->user->id;
        if (!$balance->save()) {
            throw new \Exception("อัปเดตยอดคงเหลือไม่สำเร็จ: " . json_encode($balance->getErrors()));
        }
    }
}
