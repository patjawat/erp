<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemSearch;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * StockItemController implements the CRUD actions for StockItem model.
 */
class StockItemController extends Controller
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
     * ปิด CSRF สำหรับ action ที่รับ JSON (นำเข้า CSV) เพื่อไม่ให้ได้ 400 เมื่อส่งจาก fetch
     */
    public function beforeAction($action)
    {
        if ($action->id === 'import-csv-items') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all StockItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'item_code', $searchModel->q],
            ['like', 'item_name', $searchModel->q],
        ]);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockItem model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new StockItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new StockItem();
        
        // ถ้ามี category_id จาก query string ให้ตั้งค่าไว้
        $categoryId = $this->request->get('category_id');
        if ($categoryId) {
            $model->category_id = $categoryId;
        }
        
        // ถ้ามี item_name จาก query string ให้ตั้งค่าไว้ (สำหรับ pre-fill เมื่อค้นหาไม่เจอ)
        $itemName = $this->request->get('item_name');
        if ($itemName) {
            $model->item_name = $itemName;
        }

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            if ($model->load($this->request->post())) {
                // จัดการ data_json
                $postData = $this->request->post()['StockItem'] ?? [];
                if (isset($postData['data_json']) && is_array($postData['data_json'])) {
                    $dataJson = [];
                    if (isset($postData['data_json']['unit_name']) && !empty($postData['data_json']['unit_name'])) {
                        $dataJson['unit_name'] = $postData['data_json']['unit_name'];
                    }
                    if (!empty($dataJson)) {
                        $model->data_json = json_encode($dataJson);
                    }
                }
                
                // จัดการ auto code
                if (isset($postData['auto']) && $postData['auto']) {
                    if (!$model->item_code || empty($model->item_code) || $model->item_code === 'อัตโนมัติ') {
                        $model->item_code = StockItem::nextCode($model->category_id);
                    }
                }
                
                // ตรวจสอบว่ามีรหัสพัสดุหรือไม่
                if (empty($model->item_code)) {
                    return [
                        'status' => 'error',
                        'message' => 'กรุณาระบุรหัสพัสดุหรือเลือกรหัสวัสดุอัตโนมัติ'
                    ];
                }
                
                $model->is_active = 1;
                $model->created_at = time();
                $model->created_by = Yii::$app->user->id ?? null;
                
                if ($model->save()) {
                    // Reload model เพื่อดึง relation
                    $model->refresh();
                    return [
                        'status' => 'success',
                        'message' => 'สร้างพัสดุใหม่สำเร็จ',
                        'item_code' => $model->item_code,
                        'item_name' => $model->item_name,
                        'category_title' => $model->categoryType ? $model->categoryType->title : '-',
                        'unit_name' => $model->unitName ?: '-'
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'ไม่สามารถสร้างพัสดุได้: ' . implode(', ', $model->getFirstErrors())
                    ];
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลได้'
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'สร้างพัสดุใหม่',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' => \yii\helpers\Html::button('บันทึก', ['class' => 'btn btn-primary form-submit', 'type' => 'submit', 'data' => ['id' => 'form-stock-item']]) .
                    \yii\helpers\Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal'])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing StockItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
                'status' => 'success',
                'container' => '#sm-container',
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing StockItem model.
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


    public function actionSetActive()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->post('id');
        $model = $this->findModel($id);
        if ($this->request->isPost && $this->request->post('id')) {
            $model->is_active = ($model->is_active == 1 ? 0 : 1);
            $model->is_innovation = ($model->is_innovation == 1 ? 0 : 1);
            if ($model->save(false)) {
                return
                    [
                        'status' => 'success',
                        'container' => '#sm'
                    ];
            }
        } else {
            $model->loadDefaultValues();
        }
    }


    /**
     * รายการพัสดุสำหรับ Tom-Select (รับเข้า/เบิก ฯลฯ)
     * warehouse_id = กรองเฉพาะประเภทที่คลังรับเข้าได้, category_id = กรองตามประเภทวัสดุที่เลือก
     */
    public function actionItemList($q = null, $warehouse_id = null, $category_id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $query = StockItem::find()
            ->select(['item_code', 'item_name', 'item_code', 'category_id', 'data_json'])
            ->where(['is_active' => 1]);

        if (!empty($warehouse_id)) {
            $warehouse = Warehouse::findOne((int) $warehouse_id);
            if ($warehouse) {
                $allowedCodes = $warehouse->getAllowedItemTypeCodes();
                if (!empty($allowedCodes)) {
                    $query->andWhere(['category_id' => $allowedCodes]);
                }
            }
        }

        if (!empty($category_id)) {
            $query->andWhere(['category_id' => $category_id]);
        }

        if (!empty($q)) {
            $query->andWhere([
                'or',
                ['like', 'item_name', $q],
                ['like', 'item_code', $q]
            ]);
        }

        $models = $query->limit(20)->all();
        
        $results = [];

        foreach ($models as $item) {
            $unitName = '-';
            if ($item->data_json) {
                $dataJson = is_string($item->data_json) ? json_decode($item->data_json, true) : $item->data_json;
                $unitName = $dataJson['unit_name'] ?? '-';
            }
            $results[] = [
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'category_title' => $item->categoryType ? $item->categoryType->title : '-',
                'unit_name' => $unitName,
            ];
        }

        return [
            'results' => $results
        ];
    }

    /**
     * รายการประเภทวัสดุตามคลังที่เลือก (สำหรับ Tom-Select)
     * ถ้าเลือกคลังและคลังกำหนดประเภทที่รับไว้ จะคืนเฉพาะประเภทนั้น ถ้าไม่กำหนดจะคืนทุกประเภท
     */
    public function actionItemTypeList($warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $codes = [];
        if (!empty($warehouse_id)) {
            $warehouse = Warehouse::findOne((int) $warehouse_id);
            if ($warehouse) {
                $codes = $warehouse->getAllowedItemTypeCodes();
            }
        }
        $query = Categorise::find()
            ->where(['name' => 'asset_type', 'category_id' => 4])
            ->orderBy('code');
        if (!empty($codes)) {
            $query->andWhere(['code' => $codes]);
        }
        $list = $query->all();
        $results = [];
        foreach ($list as $row) {
            $results[] = ['value' => $row->code, 'text' => $row->title ?: $row->code];
        }
        return ['results' => $results];
    }

    /**
     * รายการวัสดุที่มีในคลัง (สำหรับ TomSelect ในใบขอเบิก)
     * GET warehouse_id, q (optional ค้นหาชื่อ/รหัส)
     */
    public function actionGetItemsByWarehouse($warehouse_id, $q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse_id = (int) $warehouse_id;
        $q = trim((string) $q, " \t\n\r\0\x0B\"'");

        $query = StockBalance::find()
            ->select(['stock_balance.item_code'])
            ->joinWith('item')
            ->where(['stock_balance.warehouse_id' => $warehouse_id])
            ->andWhere(['>', 'stock_balance.balance_qty', 0]);

        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'stock_item.item_name', $q],
                ['like', 'stock_item.item_code', $q],
            ]);
        }

        $query->distinct();
        $models = $query->all();
        $results = [];
        foreach ($models as $m) {
            $item = $m->item;
            $itemName = $item ? $item->item_name : (string) $m->item_code;
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $results[] = [
                'item_code' => (string) $m->item_code,
                'item_name' => $itemName,
                'unit_name' => $unitName ? (string) $unitName : '-',
            ];
        }
        return $results;
    }

    /**
     * รายการหน่วยนับ (สำหรับ Tom-Select)
     */
    public function actionUnitList($q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = Categorise::find()
            ->where(['name' => 'unit'])
            ->andWhere(['active' => 1])
            ->orderBy('title');
        
        if (!empty($q)) {
            $query->andWhere(['like', 'title', $q]);
        }
        
        $units = $query->limit(50)->all();
        $results = [];
        foreach ($units as $unit) {
            $results[] = [
                'value' => $unit->title,
                'text' => $unit->title
            ];
        }
        
        return ['results' => $results];
    }

    /**
     * สร้างหน่วยนับใหม่
     */
    public function actionCreateUnit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        $data = json_decode(Yii::$app->request->rawBody, true);
        $unitName = trim($data['unit_name'] ?? '');

        if (empty($unitName)) {
            return ['success' => false, 'message' => 'กรุณาระบุชื่อหน่วยนับ'];
        }

        // เช็คว่ามีหน่วยนับนี้อยู่แล้วหรือไม่
        $unit = Categorise::findOne([
            'name' => 'unit',
            'title' => $unitName
        ]);

        if ($unit) {
            return [
                'success' => true,
                'message' => 'หน่วยนับนี้มีอยู่แล้ว',
                'unit_name' => $unitName
            ];
        }

        // สร้างหน่วยนับใหม่
        $unit = new Categorise();
        $unit->name = 'unit';
        $unit->title = $unitName;
        $unit->code = strtoupper(substr($unitName, 0, 3)) . '-' . time();
        $unit->category_id = 4;
        $unit->active = 1;

        if ($unit->save()) {
            return [
                'success' => true,
                'message' => 'สร้างหน่วยนับใหม่สำเร็จ',
                'unit_name' => $unitName
            ];
        } else {
            return [
                'success' => false,
                'message' => 'ไม่สามารถสร้างหน่วยนับได้: ' . implode(', ', $unit->getFirstErrors())
            ];
        }
    }

    /**
     * สร้างพัสดุใหม่
     */
    public function actionCreateItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        $data = json_decode(Yii::$app->request->rawBody, true);
        $itemCode = trim($data['item_code'] ?? '');
        $itemName = trim($data['item_name'] ?? '');
        $categoryId = $data['category_id'] ?? null;
        $unitName = trim($data['unit_name'] ?? '');

        if (empty($itemCode) || empty($itemName) || !$categoryId) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน'];
        }

        // เช็คว่ามีพัสดุนี้อยู่แล้วหรือไม่
        $existingItem = StockItem::findOne(['item_code' => $itemCode]);
        if ($existingItem) {
            return [
                'success' => false,
                'message' => 'รหัสพัสดุนี้มีอยู่แล้ว: ' . $existingItem->item_name
            ];
        }

        // สร้างพัสดุใหม่
        $stockItem = new StockItem();
        $stockItem->item_code = $itemCode;
        $stockItem->item_name = $itemName;
        $stockItem->category_id = $categoryId;
        $stockItem->is_active = 1;
        $stockItem->created_at = time();
        $stockItem->created_by = Yii::$app->user->id ?? null;
        
        // บันทึกหน่วยนับใน data_json ถ้ามี
        if (!empty($unitName)) {
            $stockItem->data_json = json_encode(['unit_name' => $unitName]);
        }
        
        if (!$stockItem->save()) {
            return [
                'success' => false,
                'message' => 'ไม่สามารถสร้างพัสดุได้: ' . implode(', ', $stockItem->getFirstErrors())
            ];
        }

        // สร้างหน่วยนับใน categorise ถ้ายังไม่มี
        if (!empty($unitName)) {
            $unit = Categorise::findOne([
                'name' => 'unit',
                'title' => $unitName
            ]);
            
            if (!$unit) {
                $unit = new Categorise();
                $unit->name = 'unit';
                $unit->title = $unitName;
                $unit->code = strtoupper(substr($unitName, 0, 3)) . '-' . time();
                $unit->category_id = 4;
                $unit->active = 1;
                $unit->save(false);
            }
        }

        // ดึงข้อมูล category title
        $categoryTitle = '-';
        if ($stockItem->categoryType) {
            $categoryTitle = $stockItem->categoryType->title;
        }

        return [
            'success' => true,
            'message' => 'สร้างพัสดุใหม่สำเร็จ',
            'item_code' => $stockItem->item_code,
            'item_name' => $stockItem->item_name,
            'category_title' => $categoryTitle,
            'unit_name' => $unitName ?: '-'
        ];
    }

    /**
     * Import items from CSV - สร้างพัสดุและหน่วยนับอัตโนมัติ
     */
    public function actionImportCsvItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        $data = json_decode(Yii::$app->request->rawBody, true);
        $items = $data['items'] ?? [];
        $warehouseId = $data['warehouse_id'] ?? null;
        $categoryId = $data['category_id'] ?? null;

        if (empty($items) || !$warehouseId || !$categoryId) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        
        try {
            $added = [];
            $created = [];
            $errors = [];
            $resultItems = [];

            foreach ($items as $itemData) {
                $itemCode = trim($itemData['item_code'] ?? '');
                $itemName = trim($itemData['item_name'] ?? '');
                $unitName = trim($itemData['unit_name'] ?? '');
                $qty = floatval($itemData['qty'] ?? 0);
                $unitPrice = floatval($itemData['unit_price'] ?? 0);
                $lotNumber = trim($itemData['lot_number'] ?? '');
                $expiryDate = trim($itemData['expiry_date'] ?? '');

                if (empty($itemCode) || empty($itemName) || $qty <= 0) {
                    $errors[] = $itemCode . ': ข้อมูลไม่ครบถ้วน';
                    continue;
                }

                // เช็คว่าพัสดุมีในระบบหรือไม่
                $stockItem = StockItem::findOne(['item_code' => $itemCode]);
                
                if (!$stockItem) {
                    // สร้างพัสดุใหม่
                    $stockItem = new StockItem();
                    $stockItem->item_code = $itemCode;
                    $stockItem->item_name = $itemName;
                    $stockItem->category_id = $categoryId;
                    $stockItem->is_active = 1;
                    $stockItem->created_at = time();
                    $stockItem->created_by = Yii::$app->user->id ?? null;
                    
                    // บันทึกหน่วยนับใน data_json ถ้ามี
                    if (!empty($unitName)) {
                        $stockItem->data_json = json_encode(['unit_name' => $unitName]);
                    }
                    
                    if (!$stockItem->save()) {
                        $errors[] = $itemCode . ': ' . implode(', ', $stockItem->getFirstErrors());
                        continue;
                    }
                    $created[] = $itemCode;
                } else {
                    // อัปเดตหน่วยนับถ้ามีและยังไม่มีใน data_json
                    if (!empty($unitName)) {
                        $dataJson = $stockItem->data_json ? json_decode($stockItem->data_json, true) : [];
                        if (empty($dataJson['unit_name'])) {
                            $dataJson['unit_name'] = $unitName;
                            $stockItem->data_json = json_encode($dataJson);
                            $stockItem->updated_at = time();
                            $stockItem->updated_by = Yii::$app->user->id ?? null;
                            $stockItem->save(false);
                        }
                    }
                }

                // สร้างหน่วยนับใน categorise ถ้ายังไม่มี
                if (!empty($unitName)) {
                    $unit = Categorise::findOne([
                        'name' => 'unit',
                        'title' => $unitName
                    ]);
                    
                    if (!$unit) {
                        $unit = new Categorise();
                        $unit->name = 'unit';
                        $unit->title = $unitName;
                        $unit->code = strtoupper(substr($unitName, 0, 3)) . '-' . time();
                        $unit->category_id = 4;
                        $unit->active = 1;
                        if (!$unit->save()) {
                            // ไม่ error ถ้าสร้างหน่วยนับไม่สำเร็จ แค่ log
                            Yii::error('Cannot create unit: ' . implode(', ', $unit->getFirstErrors()));
                        }
                    }
                }

                // ดึงข้อมูล category title
                $categoryTitle = '-';
                if ($stockItem->categoryType) {
                    $categoryTitle = $stockItem->categoryType->title;
                }

                $added[] = $itemCode;
                $resultItems[] = [
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'unit_name' => $unitName ?: '-',
                    'category_title' => $categoryTitle,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'lot_number' => $lotNumber,
                    'expiry_date' => $expiryDate
                ];
            }

            $transaction->commit();

            return [
                'success' => true,
                'added' => $added,
                'created' => $created,
                'errors' => $errors,
                'items' => $resultItems
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Finds the StockItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
