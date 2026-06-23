<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemSearch;
use app\modules\inventoryV2\models\Warehouse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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
        if ($searchModel->q !== null && $searchModel->q !== '') {
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'categorise.code', $searchModel->q],
                ['like', 'categorise.title', $searchModel->q],
            ]);
        }
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        $warehouseId = $searchModel->warehouse_id ? (int) $searchModel->warehouse_id : null;
        $balanceMap = [];
        if ($warehouseId > 0) {
            $models = $dataProvider->getModels();
            $itemCodes = array_map(function ($m) {
                return $m->item_code;
            }, $models);
            if (!empty($itemCodes)) {
                $rows = StockBalance::find()
                    ->select(['item_code', 'SUM([[balance_qty]]) AS balance_qty'])
                    ->where(['warehouse_id' => $warehouseId])
                    ->andWhere(['item_code' => $itemCodes])
                    ->groupBy('item_code')
                    ->asArray()
                    ->all();
                foreach ($rows as $r) {
                    $balanceMap[$r['item_code']] = (float) $r['balance_qty'];
                }
            }
        }

        $listWarehouse = Warehouse::find()
            ->orderBy(['warehouse_type' => SORT_ASC, 'warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลัง (ไม่แสดงยอดคงเหลือ) --'];
        foreach ($listWarehouse as $w) {
            $prefix = $w->warehouse_type === 'MAIN' ? 'คลังหลัก: ' : ($w->warehouse_type === 'SUB' ? 'คลังย่อย: ' : '');
            $warehouses[$w->id] = $prefix . $w->warehouse_name;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'warehouseId' => $warehouseId,
            'balanceMap' => $balanceMap,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * ส่งออกรายการพัสดุเป็น Excel (ตามตัวกรองที่ใช้ใน index)
     */
    public function actionExportExcel()
    {
        $searchModel = new StockItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        if ($searchModel->q !== null && $searchModel->q !== '') {
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'categorise.code', $searchModel->q],
                ['like', 'categorise.title', $searchModel->q],
            ]);
        }
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $dataProvider->pagination = false;

        $warehouseId = $searchModel->warehouse_id ? (int) $searchModel->warehouse_id : null;
        $balanceMap = [];
        $models = $dataProvider->getModels();
        if ($warehouseId > 0 && !empty($models)) {
            $itemCodes = array_map(function ($m) {
                return $m->item_code;
            }, $models);
            $rows = StockBalance::find()
                ->select(['item_code', 'SUM([[balance_qty]]) AS balance_qty'])
                ->where(['warehouse_id' => $warehouseId])
                ->andWhere(['item_code' => $itemCodes])
                ->groupBy('item_code')
                ->asArray()
                ->all();
            foreach ($rows as $r) {
                $balanceMap[$r['item_code']] = (float) $r['balance_qty'];
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายการพัสดุ');

        $headers = [
            'รหัสพัสดุ',
            'รายการวัสดุ',
            'หมวดวัสดุ',
            'ประเภทวัสดุ',
            'หน่วยนับ',
            'จำนวนคงเหลือ' . ($warehouseId ? ' (คลังที่เลือก)' : ''),
            'บัญชีนวัตกรรม',
            'จำนวนสูงสุด',
            'จำนวนต่ำสุด',
            'สถานะ',
        ];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A1:' . $lastCol . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $rowNum = 2;
        foreach ($models as $i => $item) {
            $bal = $warehouseId ? (float) ($balanceMap[$item->item_code] ?? 0) : null;

            $metterType = is_array($item->data_json) ? ($item->data_json['metter_type'] ?? '-') : '-';
            if (!is_array($item->data_json) && is_string($item->data_json)) {
                $dataJson = json_decode($item->data_json, true);
                $metterType = $dataJson['metter_type'] ?? '-';
            }
            $unitName = method_exists($item, 'getUnitName') ? ($item->getUnitName() ?: null) : null;
            if ($unitName === null && $item->data_json) {
                $dataJson = is_array($item->data_json) ? $item->data_json : json_decode($item->data_json, true);
                $unitName = $dataJson['unit_name'] ?? $dataJson['unit'] ?? null;
            }
            $unitName = $unitName ?: '-';

            $categoryTitle = $item->categoryType ? $item->categoryType->title : '-';
            $sheet->setCellValue('A' . $rowNum, $item->item_code);
            $sheet->setCellValue('B' . $rowNum, $item->item_name);
            $sheet->setCellValue('C' . $rowNum, $categoryTitle);
            $sheet->setCellValue('D' . $rowNum, $metterType);
            $sheet->setCellValue('E' . $rowNum, $unitName);
            $sheet->setCellValue('F' . $rowNum, $bal !== null ? $bal : '—');
            $sheet->setCellValue('G' . $rowNum, $item->is_innovation == 1 ? 'ใช่' : 'ไม่');
            $sheet->setCellValue('H' . $rowNum, $item->max_qty !== null && $item->max_qty !== '' ? (float) $item->max_qty : '');
            $sheet->setCellValue('I' . $rowNum, $item->min_qty !== null && $item->min_qty !== '' ? (float) $item->min_qty : '');
            $sheet->setCellValue('J' . $rowNum, $item->is_active == 1 ? 'เปิด' : 'ปิด');
            $rowNum++;
        }

        $sheet->getStyle('F2:F' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H2:I' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filenameUtf8 = 'รายการพัสดุ-' . date('Ymd-His') . '.xlsx';
        $filenameAscii = 'stock-items-' . date('Ymd-His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filenameAscii . '"; filename*=UTF-8\'\'' . rawurlencode($filenameUtf8) . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
     * รายการวัสดุสำหรับเลือกในใบขอเบิก (แสดงทุกวัสดุที่เปิดใช้ พร้อมยอดคงเหลือในคลังที่เลือก)
     * GET warehouse_id, q (optional ค้นหาชื่อ/รหัส)
     */
    public function actionGetItemsByWarehouse($warehouse_id, $q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse_id = (int) $warehouse_id;
        $q = trim((string) $q, " \t\n\r\0\x0B\"'");

        $balanceSubQuery = (new \yii\db\Query())
            ->select(['item_code', 'SUM([[balance_qty]]) AS balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouse_id])
            ->groupBy('item_code');

        $query = StockItem::find()
            ->select([
                'categorise.code AS item_code',
                'categorise.title AS item_name',
                'COALESCE(b.balance_qty, 0) AS balance_qty',
            ])
            ->leftJoin(['b' => $balanceSubQuery], 'b.item_code = categorise.code')
            ->andWhere(['categorise.active' => 1])
            ->orderBy(['categorise.code' => SORT_ASC]);

        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'categorise.title', $q],
                ['like', 'categorise.code', $q],
            ]);
        }

        $models = $query->asArray()->all();
        $results = [];
        foreach ($models as $row) {
            $item = StockItem::findOne($row['item_code']);
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $balance = (float) ($row['balance_qty'] ?? 0);
            $results[] = [
                'item_code' => (string) $row['item_code'],
                'item_name' => (string) $row['item_name'],
                'unit_name' => $unitName ? (string) $unitName : '-',
                'balance_qty' => $balance,
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
        // dry_run = true → คำนวณว่าจะเกิดอะไร แต่ไม่ save จริง (ใช้สำหรับ confirm dialog)
        $dryRun = !empty($data['dry_run']);

        if (empty($items) || !$warehouseId || !$categoryId) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $added = [];
            $created = [];      // สร้าง master ใหม่ จริง (real run)
            $createdInfo = [];  // [{item_code, item_name}] สำหรับ preview / confirm
            $reused = [];       // match จากชื่อ — ใช้ master เดิม (codes only, backward compat)
            $reusedInfo = [];   // [{item_code, item_name}] ใช้สำหรับตารางสรุป
            $errors = [];
            $resultItems = [];

            // โหลด candidates สำหรับ name-match dedup ครั้งเดียว (ต่อหมวด) — ลด query ใน loop
            $nameIndex = null;
            $loadNameIndex = function () use ($categoryId, &$nameIndex) {
                if ($nameIndex !== null) return;
                $nameIndex = [];
                $cands = StockItem::find()
                    ->where(['category_id' => $categoryId, 'active' => 1])
                    ->all();
                foreach ($cands as $c) {
                    $norm = mb_strtolower(trim((string) $c->item_name), 'UTF-8');
                    if ($norm !== '' && !isset($nameIndex[$norm])) {
                        $nameIndex[$norm] = $c;
                    }
                }
            };

            foreach ($items as $itemData) {
                $itemCode = trim($itemData['item_code'] ?? '');
                $itemName = trim($itemData['item_name'] ?? '');
                $unitName = trim($itemData['unit_name'] ?? '');
                $qty = floatval($itemData['qty'] ?? 0);
                $unitPrice = floatval($itemData['unit_price'] ?? 0);
                $lotNumber = trim($itemData['lot_number'] ?? '');
                $expiryDate = trim($itemData['expiry_date'] ?? '');

                // validation ใหม่: ต้องมีชื่อ + qty>0 (รหัสว่างได้ — ระบบจะสร้าง/หาให้)
                if (empty($itemName) || $qty <= 0) {
                    $errors[] = ($itemCode ?: '(ไม่ระบุรหัส)') . ': ข้อมูลไม่ครบถ้วน — ต้องมีชื่อวัสดุและจำนวน>0';
                    continue;
                }

                $stockItem = null;
                $matchedFromName = false;

                if ($itemCode !== '') {
                    // มี code → flow เดิม
                    $stockItem = StockItem::findOne(['item_code' => $itemCode]);
                } else {
                    // ไม่มี code → match ด้วยชื่อ (case-insensitive + trim) ในหมวดเดียวกัน
                    $loadNameIndex();
                    $norm = mb_strtolower(trim($itemName), 'UTF-8');
                    if (isset($nameIndex[$norm])) {
                        $stockItem = $nameIndex[$norm];
                        $itemCode = $stockItem->item_code;
                        $matchedFromName = true;
                        $reused[] = $itemCode;
                        $reusedInfo[] = ['item_code' => $itemCode, 'item_name' => (string) $stockItem->item_name];
                    } else {
                        // generate code ใหม่ตาม pattern {categoryId}-{seq}
                        $itemCode = StockItem::nextCode($categoryId);
                        if (empty($itemCode)) {
                            $errors[] = '(ชื่อ: ' . $itemName . '): สร้างรหัสอัตโนมัติไม่สำเร็จ';
                            continue;
                        }
                    }
                }

                if (!$stockItem) {
                    // สร้าง master ใหม่ (ทั้งกรณี user ใส่ code เองที่ยังไม่มี + กรณี auto-gen)
                    $stockItem = new StockItem();
                    $stockItem->item_code = $itemCode;
                    $stockItem->item_name = $itemName;
                    $stockItem->category_id = $categoryId;
                    $stockItem->is_active = 1;
                    $stockItem->created_at = time();
                    $stockItem->created_by = Yii::$app->user->id ?? null;
                    if (!empty($unitName)) {
                        $stockItem->data_json = ['unit_name' => $unitName]; // array → Yii encode
                    }
                    if (!$dryRun) {
                        if (!$stockItem->save()) {
                            $errors[] = $itemCode . ': ' . implode(', ', $stockItem->getFirstErrors());
                            continue;
                        }
                    }
                    $created[] = $itemCode;
                    $createdInfo[] = ['item_code' => $itemCode, 'item_name' => $itemName];
                    // ใส่ใน nameIndex เพื่อไม่ให้ดับเบิลถ้าแถวถัดมาในไฟล์ชื่อเดียวกัน (ใช้ทั้ง dry_run + real)
                    if ($nameIndex !== null) {
                        $nameIndex[mb_strtolower(trim($itemName), 'UTF-8')] = $stockItem;
                    }
                } elseif (!$matchedFromName) {
                    // มี code อยู่แล้วใน DB → update หน่วยถ้ายังไม่มี (ไม่แตะ master ถ้า match จากชื่อ)
                    if (!empty($unitName)) {
                        $raw = $stockItem->data_json;
                        if (is_array($raw)) {
                            $dataJson = $raw;
                        } elseif (is_string($raw) && $raw !== '') {
                            $dataJson = json_decode($raw, true) ?: [];
                        } else {
                            $dataJson = [];
                        }
                        if (empty($dataJson['unit_name'])) {
                            $dataJson['unit_name'] = $unitName;
                            $stockItem->data_json = $dataJson;
                            $stockItem->updated_at = time();
                            $stockItem->updated_by = Yii::$app->user->id ?? null;
                            if (!$dryRun) {
                                $stockItem->save(false);
                            }
                        }
                    }
                }

                // สร้างหน่วยนับใน categorise ถ้ายังไม่มี (skip ใน dry_run)
                if (!$dryRun && !empty($unitName)) {
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

            // dry_run = preview only — rollback ทุกอย่าง ไม่บันทึกจริง
            if ($dryRun) {
                $transaction->rollBack();
                return [
                    'success' => true,
                    'dry_run' => true,
                    'would_create' => $createdInfo,   // [{item_code, item_name}, ...]
                    'would_reuse' => $reused,         // [item_code, ...]
                    'would_reuse_info' => $reusedInfo,
                    'would_total' => count($added),
                    'errors' => $errors,
                ];
            }

            $transaction->commit();

            return [
                'success' => true,
                'added' => $added,
                'created' => $created,
                'created_info' => $createdInfo,
                'reused' => $reused,
                'reused_info' => $reusedInfo,
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
