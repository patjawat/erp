<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\inventory\models\StockEventSearch;

class WarehouseController extends Controller
{
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
     * Lists all Warehouse models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $userId = \Yii::$app->user->id;

        if ($warehouse) {
            // ใช้ StockEventSearch เมื่อมีคลัง
            $searchModel = new StockEventSearch([
                'thai_year'    => AppHelper::YearBudget(),
                'warehouse_id' => $warehouse->id,
            ]);
            $dataProvider = $searchModel->search($this->request->queryParams);

            $query = $dataProvider->query;

            // เงื่อนไขคงที่
            $query->andWhere([
                'name'             => 'order',
                'transaction_type' => 'OUT',
                'warehouse_id'     => $warehouse->id,
                'order_status'     => 'pending',
            ]);

            // ค้นหาตาม keyword q
            if (!empty($searchModel->q)) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'code', $searchModel->q],
                    ['like', 'thai_year', $searchModel->q],
                    ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $searchModel->q],
                ]);
            }

            return $this->render('view', [
                'searchModel'   => $searchModel,
                'dataProvider'  => $dataProvider,
            ]);
        } else {
            // ใช้ WarehouseSearch เมื่อไม่ได้เลือกคลัง
            $searchModel = new WarehouseSearch();
            $dataProvider = $searchModel->search($this->request->queryParams);

            // ลบรายการที่ถูกลบ
            $dataProvider->query->andWhere(['delete' => null]);

            // กรองสิทธิ์ user
            if (!\Yii::$app->user->can('admin')) {
                $dataProvider->query->andWhere(
                    new Expression("JSON_CONTAINS(data_json->'$.officer','\"$userId\"')")
                );
            }

            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    public function actionOrderRequest($all = null)
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        // เผื่อ session เก่าที่ยังเก็บเป็น array (ก่อนแก้ StockOrder/StockOutController::setWarehouse ให้เก็บเป็น object)
        $warehouseId = is_array($warehouse) ? ($warehouse['id'] ?? null) : ($warehouse->id ?? null);
        // หากเลือกคลังแล้วให้แสดง ในคลัง
        // if ($warehouse) {
        $searchModel = new StockEventSearch([
            'order_status' =>   ['pending'],
            'warehouse_id' => $warehouseId,
            'transaction_type' => 'OUT'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        /**
         * ---------------------------------------------------------
         * 1) บังคับ alias e และลบ ambiguous warehouse_id
         * ---------------------------------------------------------
         */
        $query = $dataProvider->query;
        $query->from(['e' => 'stock_events']);

        // ลบเงื่อนไข warehouse_id ที่ searchModel ใส่มาแบบไม่มี alias
        $query->where([]); // reset เฉพาะ WHERE ทั้งหมดให้สะอาด

        // ใส่เงื่อนไขใหม่แบบกำหนด alias ถูกต้อง
        $query->andFilterWhere(['e.warehouse_id' => $searchModel->warehouse_id])
            ->andFilterWhere(['e.from_warehouse_id' => $searchModel->from_warehouse_id])
            ->andFilterWhere(['e.transaction_type' => 'OUT'])
            ->andFilterWhere(['e.order_status' => $searchModel->order_status])
            ->andFilterWhere(['e.thai_year' => $searchModel->thai_year]);

        /**
         * ---------------------------------------------------------
         * 2) JOIN ตามที่ต้องการ
         * ---------------------------------------------------------
         */
        $query->joinWith(['fromWarehouse as from_warehouse']);

        $query->andFilterWhere(['e.name' => 'order']);

        if ($searchModel->q_warehouse_type) {
            $query->andFilterWhere(['from_warehouse.warehouse_type' => $searchModel->q_warehouse_type]);
        }

        // ค้นหาตาม keyword q
        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', 'e.code', $searchModel->q],
                ['like', 'e.thai_year', $searchModel->q],
                ['like', new Expression("JSON_EXTRACT(e.data_json, '$.vendor_name')"), $searchModel->q],
            ]);
        }


        /**
         * ---------------------------------------------------------
         * 3) เงื่อนไขวันที่
         * ---------------------------------------------------------
         */
        // วันที่คำขอ (req_date_start/end) → e.created_at
        $reqStart = $searchModel->req_date_start ? AppHelper::convertToGregorian($searchModel->req_date_start) . ' 00:00:00' : null;
        $reqEnd = $searchModel->req_date_end ? AppHelper::convertToGregorian($searchModel->req_date_end) . ' 23:59:59' : null;

        $query->andFilterWhere(['>=', 'e.created_at', $reqStart])
            ->andFilterWhere(['<=', 'e.created_at', $reqEnd]);

        // วันที่จ่าย (date_start/end) → e.movement_date
        $movStart = $searchModel->date_start ? AppHelper::convertToGregorian($searchModel->date_start) : null;
        $movEnd = $searchModel->date_end ? AppHelper::convertToGregorian($searchModel->date_end) : null;

        $query->andFilterWhere(['>=', 'e.movement_date', $movStart])
            ->andFilterWhere(['<=', 'e.movement_date', $movEnd]);

        if ($all) {
            $dataProvider->pagination = false;
        }


        /**
         * ---------------------------------------------------------
         * 4) SUM ORDER ITEM (i) แบบถูกต้อง — ไม่ ambiguous
         * ---------------------------------------------------------
         */
        $sumQuery = clone $query;

        $sumQuery->select(null);    // ★ เคลียร์ select เดิม
        $sumQuery->addSelect(['totalprice' => new Expression('SUM(i.qty * i.unit_price)')]);

        $sumQuery->leftJoin('stock_events i', 'i.name = "order_item" AND i.category_id = e.id');

        $sumQuery->andWhere(['i.order_status' => 'success']);

        $totalPrice = $sumQuery->scalar() ?: 0;


        /**
         * ---------------------------------------------------------
         * 5) Render
         * ---------------------------------------------------------
         */
        return $this->render('_order_request', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalPrice' => $totalPrice,
        ]);
        // } else {
        // }
    }



    public function actionList()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**f
     * Displays a single Warehouse model.
     * @param int $id Warehouse ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $this->setWarehouse($id);
        return $this->redirect(['/inventory/warehouse']);
        // return $this->render('view', [
        //     'model' => $this->findModel($id),
        // ]);
    }

    /**
     * Creates a new Warehouse model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Warehouse([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return [
                    'status' => 'success',
                    'container' => '#warehouse',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id Warehouse ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save(false)) {
                return [
                    'status' => 'success',
                    'container' => '#warehouse',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    // เลือกคลังที่จะทำงาน
    public function setWarehouse($id)
    {
        $model = Warehouse::find()->where(['id' => $id])->One();
        \Yii::$app->session->set('warehouse', $model);
        return [
            'status' => 'success',
            'container' => '#warehouse',
        ];
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }

    public function actionClearSelectWarehouse()
    {
        \Yii::$app->session->remove('select-warehouse');
        \Yii::$app->cart->checkOut(false);

        return $this->redirect(['/inventory/store']);
    }

    public function actionClear()
    {
        \Yii::$app->session->remove('warehouse');

        return $this->redirect(['index']);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }

    /**
     * Deletes an existing Warehouse model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id Warehouse ID
     *
     * @return Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete = date('Y-m-d H:i:s');
        $model->save(false);

        return [
            'status' => 'success',
            'container' => '#warehouse',
        ];
    }

    public function actionListOrderRequest()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $totalPrice = StockEvent::getTotalPriceWarehouse();
        $sumStockWarehouse = StockEvent::SumStockWarehouse();
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {
            // $dataProvider->query->where(['transaction_type' => 'OUT','name' => 'order','warehouse_id' => $warehouse->id,'order_status' => 'pending']);
            $dataProvider->query->where(['transaction_type' => 'OUT', 'name' => 'order', 'order_status' => 'pending', 'warehouse_id' => $warehouse->id]);
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'totalstock' => $sumStockWarehouse,
                'confirm' => $searchModel->getTotalCheckerY(),
                // 'totalOrder' => $searchModel->getTotalSuccessOrder(),
                'totalPrice' => number_format($totalPrice, 2),
                'content' => $this->renderAjax('list_order_request', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            // $dataProvider->query->where(['transaction_type' => 'OUT','name' => 'order','warehouse_id' => $warehouse->id]);
            $dataProvider->query->where(['transaction_type' => 'OUT', 'name' => 'order']);

            return $this->render('list_order_request', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    public function actionViewChart()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        if ($warehouse) {
            $sql = "SELECT thai_year,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 ) as in10,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 ) as out10,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as in11,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as out11,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as in12,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as out12,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 ) as in1,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 ) as out1,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 ) as in2,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 ) as out2,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 ) as in3,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 ) as out3,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 ) as in4,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 ) as out4,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 ) as in5,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 ) as out5,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 ) as in6,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 ) as out6,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 ) as in7,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 ) as out7,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 ) as in8,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 ) as out8,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 ) as in9,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 ) as out9
                FROM stock_events
                GROUP BY thai_year";
            $query = \Yii::$app->db
                ->createCommand($sql)
                ->bindValue(':warehouse_id', $warehouse->id)
                ->queryOne();
            try {
                $chartSummary = [
                    'in' => [$query['in10'], $query['in11'], $query['in12'], $query['in1'], $query['in3'], $query['in3'], $query['in4'], $query['in5'], $query['in6'], $query['in7'], $query['in8'], $query['in9']],
                    'out' => [$query['out10'], $query['out11'], $query['out12'], $query['out1'], $query['out3'], $query['out3'], $query['out4'], $query['out5'], $query['out6'], $query['out7'], $query['out8'], $query['out9']],
                ];
                // code...
            } catch (\Throwable $th) {
                $chartSummary = [
                    'in' => [],
                    'out' => [],
                ];
            }

            if ($this->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('view_chart', [
                        'warehouse' => $warehouse,
                        'chartSummary' => $chartSummary,
                    ]),
                ];
            } else {
                return $this->render('view_chart', [
                    'warehouse' => $warehouse,
                    'chartSummary' => $chartSummary,
                ]);
            }
        }
    }

    /**
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Warehouse ID
     *
     * @return Warehouse the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warehouse::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
