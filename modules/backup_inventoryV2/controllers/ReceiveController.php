<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventory\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockOrderSearch;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * StockOrderController implements the CRUD actions for StockOrder model.
 */
class ReceiveController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all StockOrder models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['order_type' => 'IN']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single StockOrder model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new StockOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
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
                                $model->main_warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id,
                                $detail->id,
                                $detail->lot_number
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
        return $this->render('create', [
            'model' => $model,
            'listWarehouse' => \yii\helpers\ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
        ]);
    }

    /**
     * Updates an existing StockOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */

   public function actionUpdate($id)
{
    // 1. ดึงข้อมูลเดิมมาเตรียมไว้
    $model = StockOrder::findOne($id);
    if (!$model) {
        throw new \yii\web\NotFoundHttpException("ไม่พบข้อมูลเอกสาร");
    }

    // เก็บรายการเก่าและคลังสินค้าเดิมไว้ก่อนจะโดน Load ค่าใหม่ทับ
    $oldItems = $model->stockDetails;
    $oldWarehouseId = $model->main_warehouse_id;

    if ($this->request->isPost) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            // --- ขั้นตอนที่ 1: Reverse สต็อกเก่าออก ---
            // เราต้องหักยอดใน StockBalance ออกก่อนที่จะลบรายการทิ้ง
            foreach ($oldItems as $oldItem) {
                InventoryService::updateBalance(
                    $oldItem->item_code,
                    $oldWarehouseId,
                    $oldItem->qty,
                    'OUT', // หักออกเพื่อคืนค่า
                    $oldItem->lot_number
                );
            }

            // --- ขั้นตอนที่ 2: ลบรายการ Detail เดิมทิ้งทั้งหมด ---
            StockDetail::deleteAll(['stock_order_id' => $model->id]);

            // --- ขั้นตอนที่ 3: บันทึกหัวเอกสารและรายการใหม่ ---
            if ($model->load($this->request->post()) && $model->save()) {
                $detailsData = $this->request->post('StockDetail', []);

                if (empty($detailsData)) {
                    throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                }

                foreach ($detailsData as $i => $data) {
                    $detail = new StockDetail();
                    
                    // โหลดข้อมูลเข้า Model รายย่อย
                    if ($detail->load($data, '')) {
                        // บังคับให้เป็นรายการใหม่เสมอ ป้องกัน ID เดิมจาก Form มาขัดขวางการ Save
                        $detail->isNewRecord = true;
                        $detail->id = null; 
                        $detail->stock_order_id = $model->id;

                        if ($detail->save()) {
                            // อัปเดตสต็อกใหม่ (IN) และตั้งค่า remain_qty
                            InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id, // ใช้คลังใหม่จาก $model ที่โหลดค่ามาแล้ว
                                $detail->qty,
                                'IN',
                                $model->id,
                                $detail->id,
                                $detail->lot_number
                            );
                        } else {
                            $errors = implode(', ', $detail->getFirstErrors());
                            throw new \Exception("รายการที่ " . ($i + 1) . " บันทึกไม่สำเร็จ: " . $errors);
                        }
                    }
                }

                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'แก้ไขข้อมูลและปรับปรุงสต็อกเรียบร้อยแล้ว',
                    'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                ];
            } else {
                $errors = implode(', ', $model->getFirstErrors());
                throw new \Exception("ข้อมูลหัวเอกสารไม่ถูกต้อง: " . $errors);
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    // กรณีโหลดหน้าแก้ไขปกติ (GET)
    return $this->render('update', [
        'model' => $model,
        'items' => $oldItems,
        'listWarehouse' => ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
    ]);
}

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        // ป้องกันการยกเลิกซ้ำ หรือยกเลิกใบที่ยังไม่ได้ยืนยัน (ถ้าต้องการ)
        if ($model->status === 'CANCELLED') {
            \Yii::$app->session->setFlash('warning', 'เอกสารนี้ถูกยกเลิกไปแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 1. คืนสต็อก (Reverse Stock)
            // เนื่องจากเป็นใบ RECEIVE (IN) เมื่อยกเลิกต้องจ่ายออก (OUT) เพื่อหักยอด
            foreach ($model->stockDetails as $detail) {
                $success = InventoryService::moveStock(
                    $detail->item_code,
                    $model->main_warehouse_id,
                    $detail->qty,
                    'OUT', // หักออกเพราะยกเลิกการรับเข้า
                    $model->id,
                    $detail->id
                );

                if (!$success) {
                    throw new \Exception("ไม่สามารถหักยอดสต็อกคืนได้ สำหรับรหัส: " . $detail->item_code);
                }
            }

            // 2. เปลี่ยนสถานะเอกสาร
            $model->status = 'CANCELLED';
            if ($model->save(false)) { // ใช้ false เพื่อข้าม validation บางตัวถ้าจำเป็น
                $transaction->commit();
                \Yii::$app->session->setFlash('success', 'ยกเลิกเอกสารและคืนสต็อกเรียบร้อยแล้ว');
            } else {
                throw new \Exception("ไม่สามารถบันทึกสถานะการยกเลิกได้");
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }


    /**
     * Deletes an existing StockOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the StockOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
