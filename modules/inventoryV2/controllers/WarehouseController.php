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
        } else {
            $model->loadDefaultValues();
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
        // all | configured | unconfigured | below_min | above_max
        $status = $this->request->get('status', 'all');
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
        $hasInventoryRole = Warehouse::currentUserHasInventoryRole();

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
            'hasInventoryRole' => $hasInventoryRole,
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
