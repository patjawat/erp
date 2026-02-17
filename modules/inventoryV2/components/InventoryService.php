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
    public static function moveStock($itemId, $warehouseId, $qty, $type, $orderId, $detailId = null)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // 1. จัดการยอดคงเหลือรวม (StockBalance)
            self::updateBalance($itemId, $warehouseId, $qty, $type);

            // 2. ถ้าเป็น 'OUT' (จ่ายออก) ให้ไปตัดยอดแบบ FIFO
            if ($type === 'OUT') {
                self::processFIFO($itemId, $warehouseId, $qty);
            } 
            // 3. ถ้าเป็น 'IN' (รับเข้า) ให้ตั้งค่า remain_qty เริ่มต้น
            else if ($type === 'IN' && $detailId) {
                $detail = StockDetail::findOne($detailId);
                if ($detail) {
                    $detail->remain_qty = $qty; // รับเข้าเท่าไหร่ เหลือให้เบิกเท่านั้น
                    $detail->save(false);
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Inventory Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ปรับปรุงตาราง StockBalance (ยอดรวม)
     */
    private static function updateBalance($itemId, $warehouseId, $qty, $type)
    {
        $balance = StockBalance::findOne(['item_code' => $itemId, 'warehouse_id' => $warehouseId]);
        if (!$balance) {
            $balance = new StockBalance(['item_code' => $itemId, 'warehouse_id' => $warehouseId, 'balance_qty' => 0]);
        }
        
        $qtyChange = ($type === 'IN') ? (float)$qty : -(float)$qty;
        $balance->balance_qty += $qtyChange;

        if (!$balance->save()) {
            throw new \Exception("ไม่สามารถอัปเดตยอดคงเหลือรวมได้");
        }
    }

    /**
     * ลอจิกการตัดสต็อกแบบ FIFO (หักยอดจาก StockDetail รายล็อต)
     */
    private static function processFIFO($itemId, $warehouseId, $totalQtyToIssue)
    {
        $stocks = StockDetail::find()
            ->joinWith('stockOrder')
            ->where([
                'stock_detail.item_code' => $itemId,
                'stock_order.main_warehouse_id' => $warehouseId,
                'stock_order.order_type' => 'IN',
                'stock_order.status' => 'CONFIRMED'
            ])
            ->andWhere(['>', 'remain_qty', 0])
            ->orderBy(['stock_order.order_date' => SORT_ASC]) // ล็อตเก่าไปใหม่
            ->all();

        $remainingToIssue = (float)$totalQtyToIssue;

        foreach ($stocks as $stock) {
            if ($remainingToIssue <= 0) break;

            if ($stock->remain_qty >= $remainingToIssue) {
                $stock->remain_qty -= $remainingToIssue;
                $stock->save(false);
                $remainingToIssue = 0;
            } else {
                $remainingToIssue -= $stock->remain_qty;
                $stock->remain_qty = 0;
                $stock->save(false);
            }
        }

        if ($remainingToIssue > 0) {
            throw new \Exception("วัสดุในสต็อกไม่เพียงพอต่อการเบิก (ขาดอีก $remainingToIssue)");
        }
    }
}