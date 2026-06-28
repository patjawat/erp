<?php

namespace app\modules\inventoryV2\components;

use Yii;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;

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
            // 1. ค้นหารายการที่เคยรับเข้า (IN) และยังมีของเหลือ (remain_qty > 0)
            // เรียงตามวันที่รับเข้าจากเก่าไปใหม่ (FIFO)
            $availableLots = StockDetail::find()
                ->joinWith('stockOrder')
                ->where([
                    'stock_detail.item_code' => $itemId,
                    'stock_order.main_warehouse_id' => $warehouseId,
                    'stock_order.order_type' => 'IN',
                ])
                ->andWhere(['>', 'remain_qty', 0])
                ->orderBy([
                    'stock_order.order_date' => SORT_ASC, 
                    'stock_detail.id' => SORT_ASC
                ])
                ->all();

            $remainingToProcess = (float)$totalQtyToOut;

            foreach ($availableLots as $lot) {
                if ($remainingToProcess <= 0) break;

                // จำนวนที่จะหยิบออกจาก Lot นี้
                $take = min($remainingToProcess, (float)$lot->remain_qty);

                // ลด remain_qty ใน StockDetail (ต้นทางที่รับเข้า)
                $lot->remain_qty -= $take;
                if (!$lot->save(false)) throw new \Exception("ไม่สามารถปรับปรุง remain_qty ได้");

                // ตัดยอดออกจาก StockBalance (แยกตาม Lot ที่หยิบจริง)
                self::updateBalance($itemId, $warehouseId, $take, 'OUT', $lot->lot_number);

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
                    $outDetail->remain_qty = $totalQtyToOut - $remainingToProcess;
                    $outDetail->save(false);
                }
            }

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
        $lot = (!empty($lotNumber)) ? $lotNumber : '-';

        $balance = StockBalance::findOne([
            'item_code' => $itemId,
            'warehouse_id' => $warehouseId,
            'lot_number' => $lot
        ]);

        if (!$balance) {
            // ถ้าเป็นรายการรับเข้าใหม่ที่ยังไม่มี Lot นี้ในคลัง
            if ($type === 'IN') {
                $balance = new StockBalance([
                    'item_code' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'lot_number' => $lot,
                    'balance_qty' => 0
                ]);
            } else {
                throw new \Exception("ไม่พบยอดคงเหลือของ Lot: {$lot} ในระบบ (ไม่สามารถหักออกได้)");
            }
        }

        $qtyChange = ($type === 'IN') ? (float)$qty : -(float)$qty;
        $balance->balance_qty += $qtyChange;

        // ป้องกันสต็อกติดลบในระดับ Balance
        if ($balance->balance_qty < 0) {
            throw new \Exception("สต็อกติดลบ: พัสดุ {$itemId} ใน Lot {$lot} ไม่เพียงพอ");
        }

        if (!$balance->save()) {
            throw new \Exception("อัปเดตยอดคงเหลือไม่สำเร็จ: " . json_encode($balance->getErrors()));
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

        $balance->updated_at = time();
        $balance->updated_by = Yii::$app->user->id;
        if (!$balance->save()) {
            throw new \Exception("อัปเดตยอดคงเหลือไม่สำเร็จ: " . json_encode($balance->getErrors()));
        }
    }
}