<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
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
                        'cancel' => ['POST'],
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
        $dataProvider->query->andWhere(['order_type' => 'IN']);
        $dataProvider->query->with(['mainWarehouse', 'stockDetails']);

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        if ($start !== null && $start !== '') {
            $dataProvider->query->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $dataProvider->query->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }

        $dataProvider->sort->defaultOrder = ['order_date' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        $statusSummary = StockOrder::find()
            ->where(['order_type' => 'IN'])
            ->select(['status', 'COUNT(*) as cnt'])
            ->groupBy('status')
            ->asArray()
            ->all();
        $statusSummaryMap = array_column($statusSummary, 'cnt', 'status');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusSummary' => $statusSummaryMap,
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
        $model->status = 'CONFIRMED';

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                $isDraft = (bool) $this->request->post('save_as_draft', false);
                if ($isDraft) {
                    $model->status = 'DRAFT';
                }

                $model->load($this->request->post());
                $orderNoEmpty = trim((string) $model->order_no) === '';
                if ($orderNoEmpty && $model->source_type === StockOrder::SOURCE_PO) {
                    throw new \Exception('กรุณากรอกเลขที่ใบรับเข้า');
                }
                if ($orderNoEmpty && $model->source_type !== StockOrder::SOURCE_PO) {
                    $model->order_no = $this->generateReceiveOrderNo();
                }
                // แปลงวันที่จาก พ.ศ. (ไทย) เป็น ค.ศ. ก่อนบันทึก
                if (!empty($model->order_date)) {
                    $model->order_date = AppHelper::convertToGregorian($model->order_date);
                    if ($model->order_date !== null) {
                        $model->order_date .= ' ' . date('H:i:s');
                    }
                }
                $details = $this->request->post('StockDetail', []);
                if (!empty($details) && $model->main_warehouse_id) {
                    $warehouse = Warehouse::findOne($model->main_warehouse_id);
                    if ($warehouse) {
                        foreach ($details as $i => $data) {
                            $itemCode = $data['item_code'] ?? null;
                            if (!$itemCode) continue;
                            $item = StockItem::findOne(['item_code' => $itemCode]);
                            if ($item && !$warehouse->allowsItemType($item->category_id)) {
                                throw new \Exception("พัสดุ [{$item->item_name}] ไม่สามารถรับเข้าคลังนี้ได้ เนื่องจากคลังกำหนดเฉพาะประเภทที่รับเข้า (รายการที่ " . ($i + 1) . ")");
                            }
                        }
                    }
                }
                if ($model->save()) {

                    // บันทึกฉบับร่าง: อนุญาตให้ไม่มีรายการ; validate จำนวน/ราคา/หน่วย; บันทึกรายการที่ครบ; ไม่อัปเดตสต็อก
                    if ($isDraft) {
                        foreach ($details as $i => $data) {
                            $rowNum = $i + 1;
                            $itemCode = trim($data['item_code'] ?? '');
                            if ($itemCode === '') continue;
                            $lot = trim($data['lot_number'] ?? '');
                            $qty = isset($data['qty']) ? (float) $data['qty'] : 0;
                            $price = isset($data['unit_price']) ? (float) $data['unit_price'] : 0;
                            if ($lot === '') {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                            }
                            if ($qty <= 0) {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                            }
                            if ($price <= 0) {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องมากกว่า 0)");
                            }
                            $detail = new StockDetail();
                            $detail->stock_order_id = $model->id;
                            $detail->item_code = $itemCode;
                            $detail->lot_number = $lot;
                            $detail->qty = $qty;
                            $detail->unit_price = $price;
                            $detail->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
                            if (!$detail->save(false)) {
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ {$rowNum}: " . $errors);
                            }
                        }
                        $transaction->commit();
                        return [
                            'success' => true,
                            'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                        ];
                    }

                    if (empty($details)) {
                        throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                    }

                    foreach ($details as $i => $data) {
                        $rowNum = $i + 1;
                        $lot = trim($data['lot_number'] ?? '');
                        $qty = isset($data['qty']) ? (float) $data['qty'] : null;
                        $price = isset($data['unit_price']) ? (float) $data['unit_price'] : null;
                        if ($lot === '') {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                        }
                        if ($qty === null || $qty <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                        }
                        if ($price === null || $price === '' || $price <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องมากกว่า 0)");
                        }
                    }

                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();

                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if (!$detail->save()) {
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ " . ($i + 1) . " ติดปัญหา: " . $errors);
                            }

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

        // กรณีโหลดหน้าเว็บปกติ (GET) - แสดงวันที่เป็น พ.ศ.
        $model->order_date = AppHelper::convertToThai(date('Y-m-d'));
        return $this->render('create', [
            'model' => $model,
            'listWarehouse' => \yii\helpers\ArrayHelper::map(
                Warehouse::find()
                    ->where(['warehouse_type' => 'MAIN'])
                    ->andWhere(['or', ['delete' => null], ['delete' => '']])
                    ->orderBy('warehouse_name')
                    ->all(),
                'id',
                'warehouse_name'
            ),
            'listItemType' => StockItem::ListStockItemType(),
            'items' => [], // สำหรับหน้า create จะไม่มีรายการเริ่มต้น
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
    $model = StockOrder::find()
        ->where(['id' => $id])
        ->with(['stockDetails', 'stockDetails.item', 'stockDetails.item.categoryType'])
        ->one();
    if (!$model) {
        throw new \yii\web\NotFoundHttpException("ไม่พบข้อมูลเอกสาร");
    }

    $oldItems = $model->stockDetails;
    $oldWarehouseId = $model->main_warehouse_id;
    $wasDraft = ($model->status === 'DRAFT');

    if ($this->request->isPost) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $isDraft = (bool) $this->request->post('save_as_draft', false);
            if (!$wasDraft && $isDraft) {
                $isDraft = false; // เอกสารรับเข้าคลังแล้ว ไม่ให้เปลี่ยนกลับเป็นร่าง
            }

            // Reverse สต็อกเก่าเฉพาะเมื่อเอกสารเคยรับเข้าจริงแล้ว (CONFIRMED)
            if (!$wasDraft) {
                foreach ($oldItems as $oldItem) {
                    InventoryService::updateBalance(
                        $oldItem->item_code,
                        $oldWarehouseId,
                        $oldItem->qty,
                        'OUT',
                        $oldItem->lot_number
                    );
                }
            }

            StockDetail::deleteAll(['stock_order_id' => $model->id]);

            $model->load($this->request->post());
            if ($isDraft) {
                $model->status = 'DRAFT';
            } else {
                $model->status = 'CONFIRMED';
            }
            $orderNoEmpty = trim((string) $model->order_no) === '';
            if ($orderNoEmpty && $model->source_type === StockOrder::SOURCE_PO) {
                throw new \Exception('กรุณากรอกเลขที่ใบรับเข้า');
            }
            if ($orderNoEmpty && $model->source_type !== StockOrder::SOURCE_PO) {
                $model->order_no = $this->generateReceiveOrderNo();
            }
            if (!empty($model->order_date)) {
                $model->order_date = AppHelper::convertToGregorian($model->order_date);
                if ($model->order_date !== null) {
                    $model->order_date .= ' ' . date('H:i:s');
                }
            }
            $detailsData = $this->request->post('StockDetail', []);
            if (!empty($detailsData) && $model->main_warehouse_id) {
                $warehouse = Warehouse::findOne($model->main_warehouse_id);
                if ($warehouse) {
                    foreach ($detailsData as $i => $data) {
                        $itemCode = $data['item_code'] ?? null;
                        if (!$itemCode) continue;
                        $item = StockItem::findOne(['item_code' => $itemCode]);
                        if ($item && !$warehouse->allowsItemType($item->category_id)) {
                            throw new \Exception("พัสดุ [{$item->item_name}] ไม่สามารถรับเข้าคลังนี้ได้ เนื่องจากคลังกำหนดเฉพาะประเภทที่รับเข้า (รายการที่ " . ($i + 1) . ")");
                        }
                    }
                }
            }
            if ($model->save()) {

                if ($isDraft) {
                    foreach ($detailsData as $i => $data) {
                        $rowNum = $i + 1;
                        $itemCode = trim($data['item_code'] ?? '');
                        if ($itemCode === '') continue;
                        $lot = trim($data['lot_number'] ?? '');
                        $qty = isset($data['qty']) ? (float) $data['qty'] : 0;
                        $price = isset($data['unit_price']) ? (float) $data['unit_price'] : 0;
                        if ($lot === '') {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                        }
                        if ($qty <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                        }
                        if ($price <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องมากกว่า 0)");
                        }
                        $detail = new StockDetail();
                        $detail->isNewRecord = true;
                        $detail->id = null;
                        $detail->stock_order_id = $model->id;
                        $detail->item_code = $itemCode;
                        $detail->lot_number = $lot;
                        $detail->qty = $qty;
                        $detail->unit_price = $price;
                        $detail->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
                        if (!$detail->save(false)) {
                            $errors = implode(', ', $detail->getFirstErrors());
                            throw new \Exception("รายการที่ {$rowNum}: " . $errors);
                        }
                    }
                    $transaction->commit();
                    return [
                        'success' => true,
                        'message' => 'บันทึกฉบับร่างเรียบร้อย',
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                }

                if (empty($detailsData)) {
                    throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                }

                foreach ($detailsData as $i => $data) {
                    $rowNum = $i + 1;
                    $lot = trim($data['lot_number'] ?? '');
                    $qty = isset($data['qty']) ? (float) $data['qty'] : null;
                    $price = isset($data['unit_price']) ? (float) $data['unit_price'] : null;
                    if ($lot === '') {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                    }
                    if ($qty === null || $qty <= 0) {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                    }
                    if ($price === null || $price === '' || $price <= 0) {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องมากกว่า 0)");
                    }
                }

                foreach ($detailsData as $i => $data) {
                    $detail = new StockDetail();
                    if ($detail->load($data, '')) {
                        $detail->isNewRecord = true;
                        $detail->id = null;
                        $detail->stock_order_id = $model->id;

                        if ($detail->save()) {
                            InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id,
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

    // กรณีโหลดหน้าแก้ไขปกติ (GET) - แสดงวันที่เป็น พ.ศ.
    $model->order_date = $model->order_date ? AppHelper::convertToThai($model->order_date) : AppHelper::convertToThai(date('Y-m-d'));
    return $this->render('update', [
        'model' => $model,
        'items' => $oldItems,
        'listWarehouse' => ArrayHelper::map(
                Warehouse::find()
                    ->where(['warehouse_type' => 'MAIN'])
                    ->andWhere(['or', ['delete' => null], ['delete' => '']])
                    ->orderBy('warehouse_name')
                    ->all(),
                'id',
                'warehouse_name'
            ),
        'listItemType' => StockItem::ListStockItemType(),
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
     * สร้างเลขที่ใบรับเข้าให้อัตโนมัติ (ใช้เมื่อไม่ใช่การจัดซื้อและผู้ใช้เว้นว่าง)
     * รูปแบบ RCV-YYYYMMDD-HHmmss-XXX ให้ไม่ซ้ำ
     */
    protected function generateReceiveOrderNo()
    {
        $prefix = 'RCV-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
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
