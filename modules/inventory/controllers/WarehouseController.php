<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\purchase\models\Order;
use Yii;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);
        // $dataProvider->query->andFilterWhere(['warehouse_name' => $searchModel->warehouse_name]);
        if (!\Yii::$app->user->can('admin')) {
            $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$id\"')"));
        } else {
        }

        // หากเลือกคลังแล้วให้แสดง หะนแา ในคลัง
        if ($warehouse) {
            $searchModel = new StockSearch();
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
            $dataProvider->query->andFilterWhere(['warehouse_id' => $warehouse['warehouse_id']]);
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'asset_item', $searchModel->q],
                ['like', 'title', $searchModel->q],
            ]);
            $dataProvider->query->groupBy('asset_item');

            return $this->render('view', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model' => $this->findModel($warehouse['warehouse_id']),
            ]);

        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
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
        $this->setWarehouse($id);
        return $this->redirect(['index']);
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
    protected function setWarehouse($id)
    {
        $model = Warehouse::find()->where(['id' => $id])->One();
        \Yii::$app->session->set('warehouse', [
            'id' => $model->id,
            'warehouse_id' => $model->id,
            'warehouse_code' => $model->warehouse_code,
            'warehouse_name' => $model->warehouse_name,
            'warehouse_type' => $model->warehouse_type,
            'category_id' => $model->category_id,
            'checker' => isset($model->data_json['checker']) ? $model->data_json['checker'] : '',
            'checker_name' => isset($model->data_json['checker_name']) ? $model->data_json['checker_name'] : '',
        ]);
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
            // $dataProvider->query->where(['transaction_type' => 'OUT','name' => 'order','warehouse_id' => $warehouse['warehouse_id'],'order_status' => 'pending']);
            $dataProvider->query->where(['transaction_type' => 'OUT', 'name' => 'order', 'order_status' => 'pending', 'warehouse_id' => $warehouse['warehouse_id']]);
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
            // $dataProvider->query->where(['transaction_type' => 'OUT','name' => 'order','warehouse_id' => $warehouse['warehouse_id']]);
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
                ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
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
