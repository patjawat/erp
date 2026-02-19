<?php

namespace app\modules\inventoryV2\components;

use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use Yii;
use yii\base\Component;
use yii\db\Exception;

class InventoryService extends Component
{
    /**
     * ฟังก์ชันหลักในการขยับสต็อก
     * @param int $itemId
     * @param int $warehouseId
     * @param float $qty
     * @param string $type ('IN' หรือ 'OUT')
     * @param int $orderId
     * @param int $detailId (เพิ่มเข้ามาเพื่อใช้อ้างอิงตอนรับเข้า)
     */
public static function moveStock($itemId, $warehouseId, $qty, $type, $orderId, $detailId = null, $lotNumber = null)
{
    $db = Yii::$app->db;
    $transaction = $db->beginTransaction();
    try {
        // 1. จัดการยอดรวม (แยกตาม Lot)
        self::updateBalance($itemId, $warehouseId, $qty, $type, $lotNumber);

        // 2. ถ้าเป็น 'IN' ให้เซ็ตค่า remain_qty ใน StockDetail
        if ($type === 'IN' && $detailId) {
            $detail = StockDetail::findOne($detailId);
            if ($detail) {
                $detail->remain_qty = $qty;
                $detail->save(false);
            }
        }
        
        // 3. ถ้าเป็น 'OUT' ค่อยไปเรียก processFIFO (ถ้าคุณใช้ระบบตัด Lot อัตโนมัติ)
        // แต่ถ้าเป็นการรับเข้า (IN) เราไม่ต้องการ processFIFO

        $transaction->commit();
        return true;
    } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
    }
}

    /**
     * ปรับปรุงตาราง StockBalance (ยอดรวม)
     */

    // public static function updateBalance($itemId, $warehouseId, $qty, $type)
    // {
    //     $balance = StockBalance::findOne(['item_code' => $itemId, 'warehouse_id' => $warehouseId]);
    //     if (!$balance) {
    //         $balance = new StockBalance(['item_code' => $itemId, 'warehouse_id' => $warehouseId, 'balance_qty' => 0]);
    //     }

    //     $qtyChange = ($type === 'IN') ? (float)$qty : -(float)$qty;
    //     $balance->balance_qty += $qtyChange;

    //     if (!$balance->save()) {
    //         throw new \Exception("ไม่สามารถอัปเดตยอดคงเหลือรวมได้");
    //     }
    // }
    public static function updateBalance($itemId, $warehouseId, $qty, $type, $lotNumber = null)
{
    // ป้องกัน Error notNull: ถ้าไม่มี Lot ให้ใส่ '-' หรือ 'N/A'
    $lot = (!empty($lotNumber)) ? $lotNumber : '-';

    $balance = StockBalance::findOne([
        'item_code' => $itemId, 
        'warehouse_id' => $warehouseId, 
        'lot_number' => $lot
    ]);

    if (!$balance) {
        $balance = new StockBalance([
            'item_code' => $itemId,
            'warehouse_id' => $warehouseId,
            'lot_number' => $lot,
            'balance_qty' => 0
        ]);
    }

    $qtyChange = ($type === 'IN') ? (float)$qty : -(float)$qty;
    $balance->balance_qty += $qtyChange;

    if (!$balance->save()) {
        throw new \Exception("บันทึก StockBalance ไม่สำเร็จ: " . json_encode($balance->getErrors()));
    }
}



    public static function reverseStock($itemId, $warehouseId, $qty)
    {
        // เรียกใช้ updateBalance ภายใน Class เดียวกัน (ทำได้แม้เป็น private)
        return self::updateBalance($itemId, $warehouseId, $qty, 'OUT');
    }

    // เพิ่ม Method นี้ใน InventoryService.php
    public static function reverseInflow($detailId, $warehouseId)
    {
        $detail = StockDetail::findOne($detailId);
        if (!$detail) return;

        // 1. ลดยอดใน StockBalance (ยอดรวม)
        self::updateBalance($detail->item_code, $warehouseId, $detail->qty, 'OUT');

        // 2. ลบรายการนี้ทิ้ง (หรือจะให้ deleteAll ใน Controller ทำงานก็ได้)
        // แต่หัวใจคือไม่ต้องไปเรียก processFIFO เพราะนี่คือการ "ยกเลิกการรับเข้า" ไม่ใช่การ "เบิกออก"
    }

    /**
     * ลอจิกการตัดสต็อกแบบ FIFO (หักยอดจาก StockDetail รายล็อต)
     */
    public static function processFIFO($itemId, $warehouseId, $totalQtyToOut, $orderId, $orderDetailId)
{
    // 1. หา StockDetail (รายการรับเข้า) ที่ยังมีของเหลือ (remain_qty > 0) 
    // และเรียงตามวันที่รับเข้า/ลำดับการบันทึก (FIFO)
    $availableLots = StockDetail::find()
        ->joinWith('stockOrder')
        ->where([
            'stock_detail.item_code' => $itemId,
            'stock_order.main_warehouse_id' => $warehouseId,
            'stock_order.order_type' => 'IN', // เอาเฉพาะรายการที่รับเข้า
        ])
        ->andWhere(['>', 'remain_qty', 0])
        ->orderBy(['stock_order.order_date' => SORT_ASC, 'stock_detail.id' => SORT_ASC])
        ->all();

    $remainingToProcess = $totalQtyToOut;

    foreach ($availableLots as $lot) {
        if ($remainingToProcess <= 0) break;

        $take = min($remainingToProcess, $lot->remain_qty);

        // 2. หักลบจากล็อตรับเข้า (ลดยอดคงเหลือของล็อตนั้น)
        $lot->remain_qty -= $take;
        $lot->save(false);

        // 3. บันทึกยอดที่ตัดออกจากยอดรวมคลัง (Balance) โดยระบุ Lot ที่ระบบเลือกให้
        self::updateBalance($itemId, $warehouseId, $take, 'OUT', $lot->lot_number);

        // 4. (Optional) คุณอาจต้องการบันทึกว่าใบเบิกนี้ ตัดจาก Lot ไหนบ้าง
        // ลงในตารางเชื่อมโยง หรือ Update กลับไปที่ StockDetail ของใบเบิก
        // ... (Logic การบันทึกประวัติการตัด Lot) ...

        $remainingToProcess -= $take;
    }

    if ($remainingToProcess > 0) {
        throw new \Exception("วัสดุ {$itemId} ในคลังมีไม่พอสำหรับจำนวนที่ต้องการ (ขาดอีก {$remainingToProcess})");
    }

    return true;
}
}
