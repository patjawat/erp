<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventory\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionDashboard()
    {
        return $this->render('dashboard');
    }
    //     public function actionReceive()
    // {
    //     $model = new StockOrder();
    //     $model->order_type = 'IN'; // ระบุเป็นประเภทรับเข้า
    //     $model->order_date = date('Y-m-d H:i:s');
    //     $model->order_no = 'RCV' . date('YmdHis'); // แนะนำให้ทำ Auto Gen ใน model
    //     $model->status = 'CONFIRMED'; // หรือ DRAFT ตาม workflow

    //     $items = [new StockDetail()];

    //     if ($this->request->isPost) {
    //         $model->load($this->request->post());

    //         // รับค่ารายการพัสดุจากหน้าฟอร์ม (Array of objects)
    //         $postDetails = $this->request->post('StockDetail', []);
    //         $items = [];

    //         // Database Transaction เพื่อความปลอดภัยของข้อมูล
    //         $dbTransaction = Yii::$app->db->beginTransaction();
    //         try {
    //             if ($model->save()) {
    //                 foreach ($postDetails as $i => $detailData) {
    //                     $detail = new StockDetail();
    //                     $detail->load($detailData, ''); // load ข้อมูลรายบรรทัด
    //                     $detail->stock_order_id = $model->id; // เชื่อม id กับหัวเอกสาร

    //                     if ($detail->save()) {
    //                         // 🚀 ส่งข้อมูลไป Update ยอดคงเหลือ Real-time ใน StockBalance
    //                         // และบันทึกลง StockDetail (Transaction Log) ผ่าน Service
    //                         $isUpdated = InventoryService::moveStock(
    //                             $detail->item_code, 
    //                             $model->warehouse_id, 
    //                             $detail->qty, 
    //                             'IN', 
    //                             $model->id
    //                         );

    //                         if (!$isUpdated) {
    //                             throw new \Exception("ไม่สามารถอัปเดตยอดสต็อกรายการที่ " . ($i + 1));
    //                         }
    //                     } else {
    //                         throw new \Exception("บันทึกรายการพัสดุไม่สำเร็จ: " . implode(', ', $detail->getFirstErrors()));
    //                     }
    //                 }

    //                 $dbTransaction->commit();
    //                 Yii::$app->session->setFlash('success', 'บันทึกรับเข้าวัสดุเรียบร้อยแล้ว');
    //                 return $this->redirect(['view', 'id' => $model->id]);
    //             }
    //         } catch (\Exception $e) {
    //             $dbTransaction->rollBack();
    //             Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    //         }
    //     }

    //     return $this->render('_form_receive', [
    //         'model' => $model,
    //         'items' => (empty($items)) ? [new StockDetail()] : $items,
    //         'listWarehouse' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name'),
    //         // ดึงรายการพัสดุไปใช้ใน Tom-Select
    //         'listItems' => StockItem::find()->where(['is_active' => 1])->all(),
    //     ]);
    // }

    public function actionReceive()
    {
        $model = new StockOrder();
        $model->order_type = 'IN';
        $model->status = 'CONFIRMED'; // กำหนดสถานะรอไว้เลย

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. โหลดข้อมูลหัวเอกสาร
                if ($model->load($this->request->post()) && $model->save()) {

                    // 2. รับค่า StockDetail จากฟอร์ม
                    $details = $this->request->post('StockDetail', []);

                    if (empty($details)) {
                        throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                    }

                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();

                        // สำคัญ: ต้องโหลดแบบเซตค่าตรงๆ หรือ load($data, '') 
                        // เพราะเราสร้าง Array name="StockDetail[i][field]" ใน JS
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if (!$detail->save()) {
                                // ถ้า Detail บันทึกไม่สำเร็จ ให้ส่ง Error กลับไปดู
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ " . ($i + 1) . " ติดปัญหา: " . $errors);
                            }

                            // 3. อัปเดตสต็อกจริง (Service ที่เราทำไว้ตอนแรก)
                            $success = InventoryService::moveStock(
                                $detail->item_code,
                                $model->warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id
                            );

                            if (!$success) {
                                throw new \Exception("ระบบไม่สามารถอัปเดตยอดคงเหลือในคลังได้");
                            }
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                } else {
                    // ถ้า Model หลัก (StockOrder) save ไม่ผ่าน
                    $errors = implode(', ', $model->getFirstErrors());
                    throw new \Exception("ข้อมูลหลักไม่ถูกต้อง: " . $errors);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // กรณีโหลดหน้าเว็บปกติ (GET)
        return $this->render('_form_receive', [
            'model' => $model,
            'listWarehouse' => \yii\helpers\ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
        ]);
    }

    public function actionUpdateReceive($id)
    {
        $model = StockOrder::find()
            ->where(['id' => $id])
            ->with('stockDetails.item') // โหลดรายละเอียดและข้อมูลพัสดุมาพร้อมกันเลย
            ->one();

        $oldItems = $model->stockDetails; // เก็บรายการเก่าไว้สำหรับเปรียบเทียบหรือคืนสต็อก

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. ลบรายการเก่าใน DB ออกก่อน (ภายใต้ Transaction)
                StockDetail::deleteAll(['stock_order_id' => $model->id]);
                // 2. รับค่าจากฟอร์ม (ซึ่งตอนนี้ rowIndex จะไม่ซ้ำกันแล้ว)
                $details = $this->request->post('StockDetail', []);
                // 1. คืนสต็อกของรายการเก่าทั้งหมดก่อน (Reverse Stock)
                foreach ($oldItems as $oldItem) {
                    InventoryService::moveStock(
                        $oldItem->item_code,
                        $model->warehouse_id,
                        $oldItem->qty,
                        'IN', // ใช้ OUT เพื่อหักลดยอดที่เคยรับเข้า (Reverse IN)
                        $model->id
                    );
                }

                // 2. โหลดและบันทึกข้อมูลหัวเอกสารใหม่
                if ($model->load($this->request->post()) && $model->save()) {

                    // ลบรายการ Detail เดิมทั้งหมดเพื่อเตรียมบันทึกใหม่ (แบบ Re-insert)
                    // หรือจะใช้วิธีเช็ค ID เพื่อ Update รายบรรทัดก็ได้ แต่ Re-insert จะจัดการง่ายกว่าในเคสนี้
                    StockDetail::deleteAll(['stock_order_id' => $model->id]);

                    $details = $this->request->post('StockDetail', []);
                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if ($detail->save()) {
                                // 3. ปรับปรุงสต็อกตามยอดใหม่ (Update Balance)
                                InventoryService::moveStock(
                                    $detail->item_code,
                                    $model->warehouse_id,
                                    $detail->qty,
                                    'IN',
                                    $model->id
                                );
                            } else {
                                throw new \Exception("รายการที่ " . ($i + 1) . " บันทึกไม่สำเร็จ");
                            }
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ];
            }
        }

        return $this->render('_form_receive', [
            'model' => $model,
            'items' => $model->stockDetails, // ส่งรายการเดิมไปแสดงในตาราง
            'listWarehouse' => \yii\helpers\ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
        ]);
    }


    protected function findModel($id)
    {
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
