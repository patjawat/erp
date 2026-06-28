<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\WarehouseSearch;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemWarehouseSetting;
use app\modules\inventoryV2\services\StockMinMaxImportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use yii\web\UploadedFile;

/**
 * Warehouse CRUD ใน inventoryV2 (ย้ายมาจาก modules/inventory)
 * เมื่อเลือกคลังแล้ว redirect ไป main-stock/dashboard
 */
class WarehouseController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'save-setting' => ['POST'],
                    'delete-setting' => ['POST'],
                    'save-setting-batch' => ['POST'],
                    'copy-from' => ['POST'],
                    'import-preview' => ['POST'],
                    'add-items' => ['POST'],
                    'delete-settings-batch' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Legacy table view — รวมเป็นหน้าเดียวที่ default/setting (card grid)
     * รักษา bookmark/legacy link ให้ยังทำงาน
     */
    public function actionIndex()
    {
        return $this->redirect(
            array_merge(['/inventory-v2/default/setting'], $this->request->queryParams)
        );
    }

    /**
     * เลือกคลัง (set session) แล้ว redirect ไป dashboard
     */
    public function actionView($id)
    {
        $this->setWarehouse($id);
        if ($this->request->isAjax && $this->request->get('format') === 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/inventory-v2/main-stock/dashboard'])];
        }
        return $this->redirect(['/inventory-v2/main-stock/dashboard']);
    }

    /**
     * AJAX: set warehouse ใน session แล้ว return JSON
     */
    public function actionSetWarehouse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->setWarehouse($id);
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    public function actionList()
    {
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Warehouse(['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return ['status' => 'success', 'container' => '#pjax-warehouse'];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save(false)) {
                return ['status' => 'success', 'container' => '#pjax-warehouse'];
            }

            return [
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกข้อมูลคลังได้',
                'errors' => \yii\widgets\ActiveForm::validate($model),
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * ล้างคลังที่เลือก
     */
    public function actionClear()
    {
        Yii::$app->session->remove('warehouse');
        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete = date('Y-m-d H:i:s');
        $model->save(false);
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    /**
     * หน้าตั้งค่า min/max ของวัสดุประจำคลังนี้
     */
    public function actionStockMinMax($id)
    {
        $warehouse = $this->findModel($id);

        $q = trim((string) $this->request->get('q', ''));
        // configured | below_min | above_max — หน้านี้แสดงเฉพาะ items ที่ตั้งค่าแล้ว
        // unconfigured ถูกนำออก: รายการที่ยังไม่ตั้งให้เข้ามาผ่าน modal "เพิ่มวัสดุเข้าตาราง"
        $status = $this->request->get('status', 'configured');
        if (!in_array($status, ['configured', 'below_min', 'above_max'], true)) {
            $status = 'configured';
        }
        $categoryId = trim((string) $this->request->get('category_id', ''));

        $allowedTypes = $warehouse->getAllowedItemTypeCodes();

        // balance subquery: SUM(balance_qty) per item_code ในคลังนี้ (รวมทุก lot)
        $balanceSub = (new Query())
            ->select(['item_code', 'balance_total' => new Expression('SUM(balance_qty)')])
            ->from('{{%stock_balance}}')
            ->where(['warehouse_id' => $warehouse->id])
            ->groupBy('item_code');

        $query = (new Query())
            ->select([
                'i.id',
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'i.category_id',
                'i.data_json AS item_data_json',
                's.id AS setting_id',
                's.min_qty AS setting_min_qty',
                's.max_qty AS setting_max_qty',
                's.note AS setting_note',
                's.is_active AS setting_is_active',
                'balance_qty' => new Expression('COALESCE(b.balance_total, 0)'),
            ])
            ->from(['i' => '{{%categorise}}'])
            ->leftJoin(
                ['s' => '{{%stock_item_warehouse_setting}}'],
                's.item_code = i.code AND s.warehouse_id = :wid',
                [':wid' => $warehouse->id]
            )
            ->leftJoin(['b' => $balanceSub], 'b.item_code = i.code')
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
            ->andWhere(['i.active' => 1]);

        if (!empty($allowedTypes)) {
            $query->andWhere(['i.category_id' => $allowedTypes]);
        }
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'i.code', $q],
                ['like', 'i.title', $q],
            ]);
        }
        if ($categoryId !== '') {
            $query->andWhere(['i.category_id' => $categoryId]);
        }
        if ($status === 'configured') {
            $query->andWhere(['IS NOT', 's.id', null]);
        } elseif ($status === 'unconfigured') {
            $query->andWhere(['s.id' => null]);
        } elseif ($status === 'below_min') {
            $query->andWhere(['IS NOT', 's.id', null])
                ->andWhere(new Expression('COALESCE(b.balance_total, 0) < s.min_qty'));
        } elseif ($status === 'above_max') {
            $query->andWhere(['IS NOT', 's.id', null])
                ->andWhere(new Expression('COALESCE(b.balance_total, 0) > s.max_qty'));
        }

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => (int) $countQuery->count('*', \Yii::$app->db),
            'pageSize' => 50,
        ]);

        $rows = $query
            ->orderBy(['i.code' => SORT_ASC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        // นับสรุป (รวม below_min / above_max) — query ครั้งเดียว
        $totalsQuery = (new Query())
            ->select([
                'total' => 'COUNT(DISTINCT i.code)',
                'configured' => 'COUNT(DISTINCT s.item_code)',
                'below_min' => new Expression('SUM(CASE WHEN s.id IS NOT NULL AND COALESCE(b.balance_total, 0) < s.min_qty THEN 1 ELSE 0 END)'),
                'above_max' => new Expression('SUM(CASE WHEN s.id IS NOT NULL AND COALESCE(b.balance_total, 0) > s.max_qty THEN 1 ELSE 0 END)'),
            ])
            ->from(['i' => '{{%categorise}}'])
            ->leftJoin(
                ['s' => '{{%stock_item_warehouse_setting}}'],
                's.item_code = i.code AND s.warehouse_id = :wid',
                [':wid' => $warehouse->id]
            )
            ->leftJoin(['b' => $balanceSub], 'b.item_code = i.code')
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
            ->andWhere(['i.active' => 1]);
        if (!empty($allowedTypes)) {
            $totalsQuery->andWhere(['i.category_id' => $allowedTypes]);
        }
        $totals = $totalsQuery->one();

        // หมวดวัสดุที่คลังนี้รับเข้า — สำหรับ dropdown filter
        $categoryOptions = [];
        if (!empty($allowedTypes)) {
            $categoryOptions = (new Query())
                ->select(['code', 'title'])
                ->from('{{%categorise}}')
                ->where(['name' => 'asset_type', 'code' => $allowedTypes])
                ->orderBy(['title' => SORT_ASC])
                ->all();
        }

        $accessibleWarehouses = Warehouse::findAllAccessibleWarehouses();

        $viewParams = [
            'warehouse' => $warehouse,
            'rows' => $rows,
            'pagination' => $pagination,
            'totals' => $totals,
            'q' => $q,
            'status' => $status,
            'categoryId' => $categoryId,
            'categoryOptions' => $categoryOptions,
            'accessibleWarehouses' => $accessibleWarehouses,
        ];

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'ตั้ง min/max วัสดุ: ' . $warehouse->warehouse_name),
                'content' => $this->renderAjax('stock-min-max', $viewParams),
            ];
        }

        return $this->render('stock-min-max', $viewParams);
    }

    /**
     * AJAX: บันทึก min/max ของ item ใน warehouse
     */
    public function actionSaveSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->post('warehouse_id');
        $itemCode = trim((string) $this->request->post('item_code'));
        $minQty = $this->request->post('min_qty');
        $maxQty = $this->request->post('max_qty');
        $note = $this->request->post('note');

        if (!$warehouseId || $itemCode === '') {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'];
        }
        if ($minQty === '' || $minQty === null || $maxQty === '' || $maxQty === null) {
            return ['status' => 'error', 'message' => 'กรอก min/max ให้ครบ'];
        }

        $model = StockItemWarehouseSetting::find()
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->one();
        $isNew = false;
        if (!$model) {
            $model = new StockItemWarehouseSetting();
            $model->item_code = $itemCode;
            $model->warehouse_id = $warehouseId;
            $isNew = true;
        }
        $model->min_qty = (float) $minQty;
        $model->max_qty = (float) $maxQty;
        if ($note !== null) {
            $model->note = $note === '' ? null : $note;
        }
        $model->is_active = 1;

        if (!$model->save()) {
            $errors = [];
            foreach ($model->getFirstErrors() as $e) {
                $errors[] = $e;
            }
            return ['status' => 'error', 'message' => implode(' / ', $errors)];
        }

        return [
            'status' => 'success',
            'isNew' => $isNew,
            'data' => [
                'id' => $model->id,
                'min_qty' => (float) $model->min_qty,
                'max_qty' => (float) $model->max_qty,
                'note' => $model->note,
            ],
        ];
    }

    /**
     * AJAX: บันทึก min/max หลายรายการพร้อมกัน (manual batch mode)
     * Payload: warehouse_id, items[] = [{item_code, min_qty, max_qty}]
     * จำกัด 500 รายการต่อ request (กัน DB heavy + timeout)
     */
    public function actionSaveSettingBatch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->post('warehouse_id');
        $items = $this->request->post('items', []);
        if (!is_array($items)) {
            $items = [];
        }

        if (!$warehouseId) {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ (warehouse_id)'];
        }
        if (empty($items)) {
            return ['status' => 'error', 'message' => 'ไม่มีรายการให้บันทึก'];
        }
        if (count($items) > 500) {
            return ['status' => 'error', 'message' => 'บันทึกได้ครั้งละไม่เกิน 500 รายการ'];
        }

        $warehouse = Warehouse::findOne(['id' => $warehouseId]);
        if (!$warehouse) {
            return ['status' => 'error', 'message' => 'ไม่พบคลังนี้'];
        }
        if (!$this->canAccessWarehouse($warehouse)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงคลังนี้'];
        }

        $saved = 0;
        $failed = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $i => $item) {
                $itemCode = trim((string) ($item['item_code'] ?? ''));
                $minQty = $item['min_qty'] ?? null;
                $maxQty = $item['max_qty'] ?? null;

                if ($itemCode === '' || $minQty === null || $minQty === '' || $maxQty === null || $maxQty === '') {
                    $failed[] = ['item_code' => $itemCode, 'message' => 'ข้อมูลไม่ครบ'];
                    continue;
                }
                if ((float) $minQty < 0 || (float) $maxQty < 0) {
                    $failed[] = ['item_code' => $itemCode, 'message' => 'ค่าต้องไม่ติดลบ'];
                    continue;
                }
                if ((float) $maxQty < (float) $minQty) {
                    $failed[] = ['item_code' => $itemCode, 'message' => 'max ต้องไม่น้อยกว่า min'];
                    continue;
                }

                $model = StockItemWarehouseSetting::find()
                    ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
                    ->one();
                if (!$model) {
                    $model = new StockItemWarehouseSetting();
                    $model->item_code = $itemCode;
                    $model->warehouse_id = $warehouseId;
                }
                $model->min_qty = (float) $minQty;
                $model->max_qty = (float) $maxQty;
                $model->is_active = 1;

                if (!$model->save()) {
                    $failed[] = ['item_code' => $itemCode, 'message' => implode(' / ', $model->getFirstErrors())];
                    continue;
                }
                $saved++;
            }

            if (!empty($failed)) {
                $transaction->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'พบข้อผิดพลาด ' . count($failed) . ' รายการ — ยกเลิกการบันทึกทั้งหมด',
                    'failed' => $failed,
                ];
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }

        return ['status' => 'success', 'saved' => $saved];
    }

    /**
     * AJAX (GET): preview จำนวนรายการที่จะคัดลอกจากคลังต้นทาง
     */
    public function actionCopyFromPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $sourceId = (int) $this->request->get('source_warehouse_id');
        $targetId = (int) $this->request->get('target_warehouse_id');

        $err = $this->validateCopyPair($sourceId, $targetId);
        if ($err) return ['status' => 'error', 'message' => $err];

        $target = Warehouse::findOne(['id' => $targetId]);
        $counts = $this->computeCopyCounts($sourceId, $target);

        return ['status' => 'success'] + $counts;
    }

    /**
     * AJAX (POST): คัดลอก min/max setting จากคลังต้นทาง → ปลายทาง
     * mode: skip (default) | overwrite | skip_manual
     */
    public function actionCopyFrom()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sourceId = (int) $this->request->post('source_warehouse_id');
        $targetId = (int) $this->request->post('target_warehouse_id');
        $mode = (string) $this->request->post('mode', 'skip');

        if (!in_array($mode, ['skip', 'overwrite', 'skip_manual'], true)) {
            return ['status' => 'error', 'message' => 'mode ไม่ถูกต้อง'];
        }
        $err = $this->validateCopyPair($sourceId, $targetId);
        if ($err) return ['status' => 'error', 'message' => $err];

        $target = Warehouse::findOne(['id' => $targetId]);
        $allowedTypes = $target->getAllowedItemTypeCodes();

        // ดึง source settings เฉพาะ item ที่ active ใน categorise และ target รับเข้าได้
        $sourceQuery = (new Query())
            ->select(['s.item_code', 's.min_qty', 's.max_qty', 's.note'])
            ->from(['s' => '{{%stock_item_warehouse_setting}}'])
            ->innerJoin(['i' => '{{%categorise}}'],
                'i.code = s.item_code AND i.name = :n AND i.group_id = :g AND i.active = 1',
                [':n' => 'asset_item', ':g' => 'MATER'])
            ->where(['s.warehouse_id' => $sourceId]);
        if (!empty($allowedTypes)) {
            $sourceQuery->andWhere(['i.category_id' => $allowedTypes]);
        }
        $sourceRows = $sourceQuery->all();

        if (empty($sourceRows)) {
            return ['status' => 'success', 'copied' => 0, 'skipped' => 0, 'message' => 'ไม่มีรายการให้คัดลอก'];
        }

        // ดึง existing setting ของ target → map ไว้เพื่อ check
        $existing = (new Query())
            ->select(['item_code', 'id', 'note'])
            ->from('{{%stock_item_warehouse_setting}}')
            ->where(['warehouse_id' => $targetId])
            ->indexBy('item_code')
            ->all();

        $userId = Yii::$app->user->id;
        $now = time();
        $copied = 0;
        $skipped = 0;
        $batch = [];

        foreach ($sourceRows as $r) {
            $code = $r['item_code'];
            $hasExisting = isset($existing[$code]);

            if ($hasExisting) {
                if ($mode === 'skip') {
                    $skipped++;
                    continue;
                }
                if ($mode === 'skip_manual' && ($existing[$code]['note'] ?? null) === '__manual') {
                    $skipped++;
                    continue;
                }
                // overwrite หรือ skip_manual ที่ไม่ใช่ manual → อัปเดต
                Yii::$app->db->createCommand()->update(
                    'stock_item_warehouse_setting',
                    [
                        'min_qty' => (float) $r['min_qty'],
                        'max_qty' => (float) $r['max_qty'],
                        'is_active' => 1,
                        'updated_at' => $now,
                        'updated_by' => $userId,
                    ],
                    ['id' => $existing[$code]['id']]
                )->execute();
                $copied++;
            } else {
                // INSERT
                $batch[] = [
                    $code, $targetId,
                    (float) $r['min_qty'], (float) $r['max_qty'],
                    1, $now, $now, $userId, $userId,
                ];
                $copied++;
            }
        }

        if (!empty($batch)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    'stock_item_warehouse_setting',
                    ['item_code', 'warehouse_id', 'min_qty', 'max_qty', 'is_active',
                     'created_at', 'updated_at', 'created_by', 'updated_by'],
                    $batch
                )->execute();
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                return ['status' => 'error', 'message' => 'INSERT ล้มเหลว: ' . $e->getMessage()];
            }
        }

        return ['status' => 'success', 'copied' => $copied, 'skipped' => $skipped];
    }

    /**
     * Download Excel: template (ว่าง) หรือ snapshot (เติมค่าเดิม)
     */
    public function actionExportSettings($id)
    {
        $warehouse = $this->findModel($id);
        if (!$this->canAccessWarehouse($warehouse)) {
            throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์เข้าถึงคลังนี้');
        }
        $mode = $this->request->get('mode') === StockMinMaxImportService::MODE_SNAPSHOT
            ? StockMinMaxImportService::MODE_SNAPSHOT
            : StockMinMaxImportService::MODE_TEMPLATE;

        $svc = new StockMinMaxImportService();
        $spread = $svc->generateTemplate((int) $warehouse->id, $mode);

        $modeLabel = $mode === StockMinMaxImportService::MODE_SNAPSHOT ? 'snapshot' : 'template';
        $filename = 'min-max-' . $warehouse->warehouse_name . '-' . $modeLabel . '-' . date('Y-m-d') . '.xlsx';
        $filename = preg_replace('/[\\/\\\\:*?"<>|]/u', '-', $filename); // sanitize

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $resp->headers->set('Content-Disposition',
            'attachment; filename="' . rawurlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        $resp->headers->set('Cache-Control', 'no-cache, must-revalidate');

        ob_start();
        $writer = new XlsxWriter($spread);
        $writer->save('php://output');
        $resp->content = ob_get_clean();
        $spread->disconnectWorksheets();
        return $resp;
    }

    /**
     * Upload file → parse → return preview JSON (ยังไม่บันทึก)
     */
    public function actionImportPreview($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse = $this->findModel($id);
        if (!$this->canAccessWarehouse($warehouse)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงคลังนี้'];
        }

        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์ที่อัปโหลด'];
        }
        $ext = strtolower($file->extension);
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะไฟล์ .xlsx / .xls / .csv'];
        }
        if ($file->size > 5 * 1024 * 1024) { // 5 MB
            return ['status' => 'error', 'message' => 'ไฟล์ใหญ่เกิน 5 MB'];
        }

        $tmp = $file->tempName;
        try {
            $svc = new StockMinMaxImportService();
            $result = $svc->parseFile($tmp, (int) $warehouse->id);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'อ่านไฟล์ไม่สำเร็จ: ' . $e->getMessage()];
        }

        $totalRows = $result['summary']['total'] ?? 0;
        if ($totalRows > 2000) {
            return ['status' => 'error', 'message' => 'รายการเกิน 2000 — ขอให้ตัดแบ่งไฟล์'];
        }

        return ['status' => 'success'] + $result;
    }

    /**
     * AJAX: ลบการตั้งค่า min/max ของ item + warehouse
     */
    public function actionDeleteSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->post('warehouse_id');
        $itemCode = trim((string) $this->request->post('item_code'));

        if (!$warehouseId || $itemCode === '') {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'];
        }

        $model = StockItemWarehouseSetting::find()
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->one();
        if (!$model) {
            return ['status' => 'success', 'message' => 'ไม่มีการตั้งค่าอยู่แล้ว'];
        }
        $model->delete();
        return ['status' => 'success'];
    }

    /**
     * AJAX (GET): list วัสดุที่ "ยังไม่ตั้งค่า min/max" ในคลังนี้ — สำหรับ modal เพิ่มวัสดุ
     * filter ตามหมวด (category_id) + ค้นหารหัส/ชื่อ
     * limit 500 row/request (ส่งทั้งหมดในหมวด เพื่อ select all ได้)
     */
    public function actionCandidateItems($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse = $this->findModel($id);
        if (!$this->canAccessWarehouse($warehouse)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงคลังนี้'];
        }

        $categoryId = trim((string) $this->request->get('category_id', ''));
        $q = trim((string) $this->request->get('q', ''));
        $allowedTypes = $warehouse->getAllowedItemTypeCodes();

        $query = (new Query())
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'i.category_id',
                'i.data_json AS item_data_json',
                'category_title' => 'cat.title',
            ])
            ->from(['i' => '{{%categorise}}'])
            ->leftJoin(['cat' => '{{%categorise}}'], 'cat.code = i.category_id AND cat.name = :cn', [':cn' => 'asset_type'])
            ->leftJoin(
                ['s' => '{{%stock_item_warehouse_setting}}'],
                's.item_code = i.code AND s.warehouse_id = :wid',
                [':wid' => $warehouse->id]
            )
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER', 'i.active' => 1])
            ->andWhere(['s.id' => null]);

        if (!empty($allowedTypes)) {
            $query->andWhere(['i.category_id' => $allowedTypes]);
        }
        if ($categoryId !== '') {
            $query->andWhere(['i.category_id' => $categoryId]);
        }
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'i.code', $q], ['like', 'i.title', $q]]);
        }

        $totalCount = (int) (clone $query)->count('*', Yii::$app->db);
        $rows = $query->orderBy(['i.code' => SORT_ASC])->limit(500)->all();

        foreach ($rows as &$r) {
            $j = $r['item_data_json'];
            if (is_string($j)) $j = json_decode($j, true);
            $r['unit_name'] = is_array($j) ? ($j['unit_name'] ?? '') : '';
            unset($r['item_data_json']);
        }

        return [
            'status' => 'success',
            'rows' => $rows,
            'total' => $totalCount,
            'limited' => $totalCount > count($rows),
        ];
    }

    /**
     * AJAX (POST): batch insert StockItemWarehouseSetting แบบ default min=0 max=0
     * payload: warehouse_id, item_codes[]
     * จำกัด 500 รายการ/request
     */
    public function actionAddItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->post('warehouse_id');
        $codes = $this->request->post('item_codes', []);
        if (!is_array($codes)) $codes = [];
        $codes = array_values(array_filter(array_map(static fn($c) => trim((string) $c), $codes), static fn($c) => $c !== ''));

        if (!$warehouseId || empty($codes)) {
            return ['status' => 'error', 'message' => 'ไม่มีรายการให้เพิ่ม'];
        }
        if (count($codes) > 500) {
            return ['status' => 'error', 'message' => 'เพิ่มได้ครั้งละไม่เกิน 500 รายการ'];
        }

        $warehouse = Warehouse::findOne(['id' => $warehouseId]);
        if (!$warehouse) {
            return ['status' => 'error', 'message' => 'ไม่พบคลังนี้'];
        }
        if (!$this->canAccessWarehouse($warehouse)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงคลังนี้'];
        }

        // validate ว่า code อยู่ใน categorise + allowedTypes ของคลังนี้
        $allowedTypes = $warehouse->getAllowedItemTypeCodes();
        $validQ = (new Query())
            ->select(['code'])
            ->from('{{%categorise}}')
            ->where(['name' => 'asset_item', 'group_id' => 'MATER', 'active' => 1, 'code' => $codes]);
        if (!empty($allowedTypes)) {
            $validQ->andWhere(['category_id' => $allowedTypes]);
        }
        $validCodes = $validQ->column();
        if (empty($validCodes)) {
            return ['status' => 'error', 'message' => 'ไม่มีรหัสที่ใช้ได้ในคลังนี้'];
        }

        // skip ตัวที่มี setting อยู่แล้ว (กัน race condition / double submit)
        $exists = (new Query())
            ->select(['item_code'])
            ->from('{{%stock_item_warehouse_setting}}')
            ->where(['warehouse_id' => $warehouseId, 'item_code' => $validCodes])
            ->column();
        $toInsert = array_values(array_diff($validCodes, $exists));

        if (empty($toInsert)) {
            return ['status' => 'success', 'added' => 0, 'skipped' => count($validCodes), 'message' => 'รายการที่เลือกตั้งค่าไว้แล้วทั้งหมด'];
        }

        $userId = Yii::$app->user->id;
        $now = time();
        $batch = [];
        foreach ($toInsert as $code) {
            $batch[] = [$code, $warehouseId, 0, 0, 1, $now, $now, $userId, $userId];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->batchInsert(
                'stock_item_warehouse_setting',
                ['item_code', 'warehouse_id', 'min_qty', 'max_qty', 'is_active',
                 'created_at', 'updated_at', 'created_by', 'updated_by'],
                $batch
            )->execute();
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['status' => 'error', 'message' => 'เพิ่มไม่สำเร็จ: ' . $e->getMessage()];
        }

        return [
            'status' => 'success',
            'added' => count($toInsert),
            'skipped' => count($validCodes) - count($toInsert),
        ];
    }

    /**
     * AJAX (POST): ลบ settings หลายรายการพร้อมกัน
     * payload: warehouse_id, item_codes[]
     */
    public function actionDeleteSettingsBatch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->post('warehouse_id');
        $codes = $this->request->post('item_codes', []);
        if (!is_array($codes)) $codes = [];
        $codes = array_values(array_filter(array_map(static fn($c) => trim((string) $c), $codes), static fn($c) => $c !== ''));

        if (!$warehouseId || empty($codes)) {
            return ['status' => 'error', 'message' => 'ไม่มีรายการให้ลบ'];
        }
        if (count($codes) > 500) {
            return ['status' => 'error', 'message' => 'ลบได้ครั้งละไม่เกิน 500 รายการ'];
        }

        $warehouse = Warehouse::findOne(['id' => $warehouseId]);
        if (!$warehouse) {
            return ['status' => 'error', 'message' => 'ไม่พบคลังนี้'];
        }
        if (!$this->canAccessWarehouse($warehouse)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงคลังนี้'];
        }

        $deleted = StockItemWarehouseSetting::deleteAll([
            'warehouse_id' => $warehouseId,
            'item_code' => $codes,
        ]);

        return ['status' => 'success', 'deleted' => (int) $deleted];
    }

    /**
     * เช็คว่า user ปัจจุบันเข้าถึงคลังนี้ได้หรือไม่
     * - เจ้าหน้าที่พัสดุ (role 'inventory') เข้าถึงได้ทุกคลัง (ใช้สำหรับ admin/setting)
     * - นอกนั้น เห็นเฉพาะคลังที่ตนรับผิดชอบ (officer) หรือคลังย่อยที่แผนกตัวเองมีสิทธิ
     */
    protected function canAccessWarehouse(Warehouse $warehouse)
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->can('inventory')) {
            return true;
        }
        $accessible = Warehouse::findAllAccessibleWarehouses();
        foreach ($accessible as $w) {
            if ((int) $w->id === (int) $warehouse->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate source/target pair สำหรับ copy-from
     * return error message หรือ null ถ้าผ่าน
     */
    protected function validateCopyPair($sourceId, $targetId)
    {
        if (!$sourceId || !$targetId) {
            return 'ข้อมูลไม่ครบ (source/target)';
        }
        if ($sourceId === $targetId) {
            return 'คลังต้นทางและปลายทางต้องไม่ใช่คลังเดียวกัน';
        }
        $source = Warehouse::findOne(['id' => $sourceId]);
        $target = Warehouse::findOne(['id' => $targetId]);
        if (!$source || !$target) {
            return 'ไม่พบคลัง';
        }
        if (!$this->canAccessWarehouse($source) || !$this->canAccessWarehouse($target)) {
            return 'ไม่มีสิทธิ์เข้าถึงคลัง';
        }
        return null;
    }

    /**
     * นับ preview สำหรับ copy-from modal:
     *   source_total = settings ในคลังต้นทาง (ถูกกรองด้วย allowed types ของ target)
     *   target_existing = item_code ที่ตั้งใน target แล้ว (intersect source)
     *   target_new = item_code ที่ยังไม่ได้ตั้งใน target (intersect source)
     *   target_manual = ที่ตั้งใน target พร้อม note='__manual' (intersect source)
     */
    protected function computeCopyCounts($sourceId, Warehouse $target)
    {
        $allowedTypes = $target->getAllowedItemTypeCodes();

        $sourceQ = (new Query())
            ->select(['s.item_code'])
            ->from(['s' => '{{%stock_item_warehouse_setting}}'])
            ->innerJoin(['i' => '{{%categorise}}'],
                'i.code = s.item_code AND i.name = :n AND i.group_id = :g AND i.active = 1',
                [':n' => 'asset_item', ':g' => 'MATER'])
            ->where(['s.warehouse_id' => $sourceId]);
        if (!empty($allowedTypes)) {
            $sourceQ->andWhere(['i.category_id' => $allowedTypes]);
        }
        $sourceCodes = $sourceQ->column();
        $sourceTotal = count($sourceCodes);

        if ($sourceTotal === 0) {
            return ['source_total' => 0, 'target_existing' => 0, 'target_new' => 0, 'target_manual' => 0];
        }

        $existingRows = (new Query())
            ->select(['item_code', 'note'])
            ->from('{{%stock_item_warehouse_setting}}')
            ->where(['warehouse_id' => $target->id, 'item_code' => $sourceCodes])
            ->all();
        $existing = 0;
        $manual = 0;
        foreach ($existingRows as $r) {
            $existing++;
            if (($r['note'] ?? null) === '__manual') $manual++;
        }
        return [
            'source_total' => $sourceTotal,
            'target_existing' => $existing,
            'target_new' => $sourceTotal - $existing,
            'target_manual' => $manual,
        ];
    }

    protected function setWarehouse($id)
    {
        $model = Warehouse::find()->where(['id' => $id])->one();
        if ($model) {
            Yii::$app->session->set('warehouse', $model);
        }
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    protected function findModel($id)
    {
        if (($model = Warehouse::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
