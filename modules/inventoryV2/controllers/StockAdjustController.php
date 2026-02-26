<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;

/**
 * ปรับยอด stock สินค้า (เพิ่ม/ลดยอดคงเหลือโดยตรง)
 */
class StockAdjustController extends Controller
{
    /**
     * ฟอร์มปรับยอด
     */
    public function actionIndex()
    {
        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- เลือกคลัง --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        return $this->render('index', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * API: คืนยอดคงเหลือรวมของพัสดุในคลัง (สำหรับแสดงในฟอร์ม)
     */
    public function actionGetBalance()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouseId = (int) Yii::$app->request->get('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->get('item_code', ''));

        if ($warehouseId <= 0 || $itemCode === '') {
            return ['balance' => 0, 'error' => 'กรุณาเลือกคลังและรหัสพัสดุ'];
        }

        $sum = (new Query())
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
            ->sum('balance_qty');

        return ['balance' => (float) $sum];
    }

    /**
     * บันทึกการปรับยอด (สร้างเอกสาร ADJUST และอัปเดต stock_balance)
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $adjustmentQty = (float) Yii::$app->request->post('adjustment_qty', 0);
        $note = trim((string) Yii::$app->request->post('note', ''));

        if ($warehouseId <= 0) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลัง'];
        }
        if ($itemCode === '') {
            return ['success' => false, 'message' => 'กรุณาเลือกรหัสพัสดุ'];
        }
        if ($adjustmentQty == 0) {
            return ['success' => false, 'message' => 'กรุณาระบุจำนวนที่ปรับ (บวก = เพิ่ม, ลบ = ลด)'];
        }

        $item = StockItem::findOne(['item_code' => $itemCode]);
        if (!$item) {
            return ['success' => false, 'message' => 'ไม่พบรหัสพัสดุในระบบ'];
        }

        $warehouse = Warehouse::findOne($warehouseId);
        if (!$warehouse) {
            return ['success' => false, 'message' => 'ไม่พบคลังในระบบ'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $orderNo = $this->generateAdjustOrderNo();

            $order = new StockOrder();
            $order->order_no = $orderNo;
            $order->order_type = StockOrder::ORDER_TYPE_ADJUST;
            $order->order_date = date('Y-m-d H:i:s');
            $order->main_warehouse_id = $warehouseId;
            $order->status = StockOrder::STATUS_CONFIRMED;
            $order->ref = $note ?: 'ปรับยอด';
            $order->created_at = time();
            $order->created_by = Yii::$app->user->id;
            if (!$order->save(false)) {
                throw new \Exception('บันทึกหัวเอกสารไม่สำเร็จ');
            }

            $detail = new StockDetail();
            $detail->stock_order_id = $order->id;
            $detail->item_code = $itemCode;
            $detail->qty = $adjustmentQty;
            $detail->lot_number = 'ADJUST';
            $detail->remain_qty = 0;
            $detail->created_at = time();
            $detail->created_by = Yii::$app->user->id;
            if (!$detail->save(false)) {
                throw new \Exception('บันทึกรายละเอียดไม่สำเร็จ');
            }

            InventoryService::adjustBalance($itemCode, $warehouseId, 'ADJUST', $adjustmentQty);

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ปรับยอดสำเร็จ',
                'order_no' => $orderNo,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function generateAdjustOrderNo()
    {
        $prefix = 'ADJ-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    /**
     * ล้างยอดคงเหลือในคลังเป็น 0 ทั้งหมด (สำหรับทดสอบระบบเท่านั้น)
     * ไม่ลบเอกสารรับ/จ่าย — แค่ set stock_balance.balance_qty = 0 ในคลังที่เลือก
     */
    public function actionResetWarehouse()
    {
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $confirm = trim((string) Yii::$app->request->post('confirm_text', ''));

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- เลือกคลัง --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        if (Yii::$app->request->isPost && $warehouseId > 0) {
            $warehouse = Warehouse::findOne($warehouseId);
            if (!$warehouse) {
                Yii::$app->session->setFlash('error', 'ไม่พบคลังที่เลือก');
                return $this->redirect(['reset-warehouse']);
            }
            if ($confirm !== 'ล้าง') {
                Yii::$app->session->setFlash('error', 'กรุณาพิมพ์คำว่า "ล้าง" เพื่อยืนยัน');
                return $this->render('reset-warehouse', [
                    'warehouses' => $warehouses,
                    'selectedWarehouseId' => $warehouseId,
                ]);
            }

            $count = StockBalance::updateAll(
                ['balance_qty' => 0, 'updated_at' => time(), 'updated_by' => Yii::$app->user->id],
                ['warehouse_id' => $warehouseId]
            );
            Yii::$app->session->setFlash('success', "ล้างยอดคลัง \"{$warehouse->warehouse_name}\" เรียบร้อย — ตั้งยอดคงเหลือเป็น 0 แล้ว {$count} รายการ (สำหรับทดสอบ)");
            return $this->redirect(['index']);
        }

        return $this->render('reset-warehouse', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => null,
        ]);
    }
}
