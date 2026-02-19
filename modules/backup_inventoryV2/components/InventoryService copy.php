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
     * ฟังก์ชันหลักในการบันทึกการ รับ-จ่าย
     */
   public static function moveStock($itemId, $warehouseId, $qty, $type, $orderId)
    {
        // หมายเหตุ: เราจะไม่สร้าง new StockDetail() ซ้ำที่นี่ 
        // เพราะใน Controller ของคุณได้บันทึก $detail->save() ไปเรียบร้อยแล้วก่อนเรียกฟังก์ชันนี้

        try {
            // 1. ค้นหายอดคงเหลือ (Balance) ของวัสดุนี้ในคลังที่ระบุ
            $balance = StockBalance::findOne([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId
            ]);

            // 2. ถ้ายังไม่เคยมีบันทึกในคลังนี้เลย ให้สร้างใหม่
            if (!$balance) {
                $balance = new StockBalance();
                $balance->item_id = $itemId;
                $balance->warehouse_id = $warehouseId;
                $balance->balance_qty = 0;
            }

            // 3. คำนวณยอดใหม่ (ถ้าเป็นรับเข้า IN ให้บวกเพิ่ม, ถ้าไม่ใช่ให้ลบออก)
            $qtyChange = ($type === 'IN') ? (float)$qty : -(float)$qty;
            $balance->balance_qty += $qtyChange;

            // 4. บันทึกยอดคงเหลือ
            if (!$balance->save()) {
                $errorMsg = implode(', ', $balance->getFirstErrors());
                throw new \Exception("ไม่สามารถอัปเดตยอดคงเหลือได้: " . $errorMsg);
            }

            return true;
        } catch (\Exception $e) {
            // บันทึก Log ลงระบบของ Yii กรณีเกิดข้อผิดพลาด
            Yii::error("InventoryService Error (Order ID: $orderId): " . $e->getMessage(), 'inventory');
            // โยน Exception กลับไปให้ Controller จัดการ Transaction Rollback
            throw $e; 
        }
    }

    public static function moveStockOutFIFO($itemId, $warehouseId, $totalQtyToIssue) 
{
    // 1. ค้นหา StockDetail ที่มี remain_qty > 0 
    // โดย Join กับ StockOrder เพื่อเรียงตามวันที่รับเข้า (เก่าไปใหม่)
    $stocks = StockDetail::find()
        ->joinWith('stockOrder')
        ->where([
            'item_id' => $itemId,
            'stock_order.warehouse_id' => $warehouseId,
            'stock_order.order_type' => 'IN',
        ])
        ->andWhere(['>', 'remain_qty', 0])
        ->orderBy(['stock_order.order_date' => SORT_ASC])
        ->all();

    $remainingToIssue = $totalQtyToIssue;

    foreach ($stocks as $stock) {
        if ($remainingToIssue <= 0) break;

        if ($stock->remain_qty >= $remainingToIssue) {
            // ล็อตนี้มีพอตัดทั้งหมด
            $stock->remain_qty -= $remainingToIssue;
            $stock->save(false);
            $remainingToIssue = 0;
        } else {
            // ล็อตนี้มีไม่พอ ตัดเท่าที่มีจนหมด แล้วไปตัดล็อตถัดไป
            $remainingToIssue -= $stock->remain_qty;
            $stock->remain_qty = 0;
            $stock->save(false);
        }
    }
    
    // ถ้าวนลูปจนจบแล้วยังเหลือ remainingToIssue > 0 แสดงว่าของในคลังไม่พอจริงๆ
}

}