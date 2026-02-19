<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class IssueController extends Controller
{
    /**
     * แสดงรายการใบขอเบิกที่คลังหลักต้องจัดการ
     */
    public function actionIndex()
    {
        $query = StockOrder::find()->where([
            'order_type' => 'OUT', // หรือตามที่เก็บใน DB
            'source_type' => 'REQUEST'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * หน้าจอสำหรับดูรายละเอียดและกดจ่าย (คล้าย View ของ Requisition แต่เน้นฝั่งคนจ่าย)
     */
    public function actionProcess($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = Yii::$app->request->post('Issue', []);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($data as $item) {
                    // 1. ตรวจสอบเบื้องต้น: ถ้าจำนวนเป็น 0 หรือไม่มีข้อมูลให้ข้าม
                    if (empty($item['qty_issued']) || $item['qty_issued'] <= 0) continue;

                    $detail = StockDetail::findOne($item['detail_id']);
                    if (!$detail) continue;

                    $qtyToProcess = (float)$item['qty_issued'];
                    $selectedLot = $item['lot_number'];

                    // 2. หักลบ remain_qty จาก "รายการรับเข้า (IN)" ต้นทาง 
                    // เพื่อให้ยอดเหลือรายแถวในหน้า process.php อัปเดตถูกต้อง
                    $sourceLots = StockDetail::find()
                        ->joinWith('stockOrder')
                        ->where([
                            'stock_detail.item_code' => $detail->item_code,
                            'stock_detail.lot_number' => $selectedLot,
                            'stock_order.order_type' => 'IN',
                            'stock_order.main_warehouse_id' => $model->main_warehouse_id
                        ])
                        ->andWhere(['>', 'remain_qty', 0])
                        ->orderBy(['stock_detail.id' => SORT_ASC]) // ตัดตัวที่เข้าก่อน (FIFO ภายใน Lot)
                        ->all();

                    $tempQty = $qtyToProcess;
                    $lastUnitPrice = 0;

                    foreach ($sourceLots as $sourceIn) {
                        if ($tempQty <= 0) break;

                        $take = min($tempQty, (float)$sourceIn->remain_qty);
                        $sourceIn->remain_qty -= $take;
                        $sourceIn->save(false);

                        $lastUnitPrice = $sourceIn->unit_price; // เก็บราคาทุนไว้บันทึกกลับ
                        $tempQty -= $take;
                    }

                    // 3. อัปเดตยอดรวมใน StockBalance (แยกตามคลังและ Lot) 
                    // เรียกผ่าน Service เพื่อบันทึกยอดสรุป
                    InventoryService::updateBalance(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        $qtyToProcess,
                        'OUT',
                        $selectedLot
                    );

                    // 4. บันทึกข้อมูลกลับลงใน StockDetail ของ "ใบเบิกใบนี้"
                    $detail->qty = $qtyToProcess;        // จำนวนที่จ่ายจริง
                    $detail->lot_number = $selectedLot;    // ล็อตที่เลือกจ่าย
                    $detail->unit_price = $lastUnitPrice;   // ราคาทุนที่ดึงมาจากต้นทาง

                    if (!$detail->save(false)) {
                        throw new \Exception("ไม่สามารถบันทึกรายละเอียดพัสดุรหัส: " . $detail->item_code);
                    }
                }

                // 5. เปลี่ยนสถานะหัวเอกสารใบเบิก
                $model->status = 'CONFIRMED';
                if (!$model->save(false)) {
                    throw new \Exception("ไม่สามารถบันทึกสถานะใบเบิกได้");
                }

                $transaction->commit();
                return ['success' => true];
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ];
            }
        }

        return $this->render('process', ['model' => $model]);
    }


    public function actionGetAvailableLots($item_code, $warehouse_id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    return \app\modules\inventoryV2\models\StockDetail::find()
        ->joinWith('stockOrder')
        ->select(['stock_detail.lot_number', 'stock_detail.remain_qty', 'stock_detail.unit_price'])
        ->where([
            'stock_detail.item_code' => $item_code,
            'stock_order.main_warehouse_id' => $warehouse_id,
            'stock_order.order_type' => 'IN'
        ])
        ->andWhere(['>', 'remain_qty', 0])
        ->asArray()
        ->all();
}

    protected function findModel($id)
    {
        if (($model = StockOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
    }
}
