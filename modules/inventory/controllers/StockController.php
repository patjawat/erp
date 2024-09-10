<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;;

use yii\web\Response;
use yii\db\Expression;

/**
 * StockController implements the CRUD actions for Stock model.
 */
class StockController extends Controller
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
     * Lists all Stock models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionProduct()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['like', 'title', $searchModel->q]);
        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('product/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('product/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    // public function actionWarehouse()
    // {
    //     $searchModel = new StockSearch();
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
    //     $dataProvider->query->andFilterWhere(['like', 'title', $searchModel->q]);
    //     $dataProvider->query->groupBy('asset_item');

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'count' => $dataProvider->getTotalCount(),
    //             'content' => $this->renderAjax('list', [
    //                 'searchModel' => $searchModel,
    //                 'dataProvider' => $dataProvider,
    //             ])
    //         ];
    //     } else {
    //         return $this->render('list', [
    //             'searchModel' => $searchModel,
    //             'dataProvider' => $dataProvider,
    //         ]);
    //     }
    // }

    public function actionInStock()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andFilterWhere(['like', 'title', $searchModel->q]);
        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('in_stock', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('in_stock', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }





    public function actionListStock($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Stock::find()
            ->leftJoin('categorise p', 'p.code=stock.asset_item')
            ->where(['warehouse_id' => 1])
            // ->andWhere(['or', ['LIKE', 'title',$q]])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                // 'text' => $model->Avatar(false),
                // 'fullname' => $model->title,
                // 'avatar' => $model->Avatar(false)
            ];
        }
        return $data;
        // return [
        //     'results' => $data,
        //     'items' => $model
        // ];
    }


    /**
     * Displays a single Stock model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $warehouse = Yii::$app->session->get('warehouse');
        $model = $this->findModel($id);
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->select([
            't.*',
            'o.category_id AS category_code',
            'w.warehouse_name',
            'o.code',
            new \yii\db\Expression('@running_total := IF(t.transaction_type = "IN", @running_total + t.qty, @running_total - t.qty) AS total'),
            new \yii\db\Expression('(t.unit_price * t.qty) AS total_price')
        ]);

        $dataProvider->query->from(['t' => 'stock_events'])
            ->leftJoin('warehouses w', 'w.id = t.from_warehouse_id')
            ->leftJoin('stock_events o', 'o.id = t.category_id AND o.name = "order"')
            ->join('JOIN', ['r' => new \yii\db\Expression('(SELECT @running_total := 0)')])
            // ->where(['t.asset_item' => $model->asset_item, 't.name' => 'order_item','t.order_status' => 'success','o.warehouse_id' => $model->warehouse_id])
            ->where(['t.asset_item' => $model->asset_item, 't.name' => 'order_item','t.warehouse_id' => $warehouse['warehouse_id'],'o.order_status' => 'success'])
            ->orderBy(['t.created_at' => SORT_ASC, 't.id' => SORT_ASC]);

            // Yii::$app->response->format = Response::FORMAT_JSON;

            // return $dataProvider->getModels();

        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Stock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stock();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Stock model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Stock model.
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


    public function actionViewChart()
    {

        $warehouse = Yii::$app->session->get('warehouse');
        if ($warehouse) {
            $sql = "SELECT thai_year,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 ) as in10,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 ) as out10,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as in11,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as out11,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as in12,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 ) as out12,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 ) as in1,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 ) as out1,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 ) as in2,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 ) as out2,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 ) as in3,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 ) as out3,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 ) as in4,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 ) as out4,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 ) as in5,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 ) as out5,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 ) as in6,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 ) as out6,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 ) as in7,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 ) as out7,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 ) as in8,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 ) as out8,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 ) as in9,
                (SELECT IFNULL(-ABS(CONVERT(SUM(qty), UNSIGNED)),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 ) as out9
                FROM stock_events
                GROUP BY thai_year";
            $query = \Yii::$app->db
                ->createCommand($sql)
                ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
                ->queryOne();
            try {
                $chartSummary = [
                    'in' => [$query['in10'], $query['in11'], $query['in12'], $query['in1'], $query['in3'], $query['in3'], $query['in4'], $query['in5'], $query['in6'], $query['in7'], $query['in8'], $query['in9']],
                    'out' => [$query['out10'], $query['out11'], $query['out12'], $query['out1'], $query['out3'], $query['out3'], $query['out4'], $query['out5'], $query['out6'], $query['out7'], $query['out8'], $query['out9']]
                ];
                //code...
            } catch (\Throwable $th) {
                $chartSummary = [
                    'in' => [],
                    'out' => [],
                ];
            }

            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('view_chart', [
                        'warehouse' => $warehouse,
                        'chartSummary' => $chartSummary
                    ])
                ];
            } else {
                return $this->render('view_chart', [
                    'warehouse' => $warehouse,
                    'chartSummary' => $chartSummary
                ]);
            }
        }
    }

    //แสดงรายการทั้งหมด
    public function actionViewChartTotal()
    {

            $sql = "SELECT thai_year,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 10 ) as in10,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 10 ) as out10,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 11 ) as in11,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 11 ) as out11,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 11 ) as in12,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 11 ) as out12,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 1 ) as in1,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 1 ) as out1,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 2 ) as in2,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 2 ) as out2,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 3 ) as in3,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 3 ) as out3,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 4 ) as in4,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 4 ) as out4,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 5 ) as in5,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 5 ) as out5,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 6 ) as in6,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 6 ) as out6,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 7 ) as in7,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 7 ) as out7,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 8 ) as in8,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 8 ) as out8,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN'  AND MONTH(created_at) = 9 ) as in9,
                (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT'  AND MONTH(created_at) = 9 ) as out9
                FROM stock_events
                GROUP BY thai_year";
            $query = \Yii::$app->db
                ->createCommand($sql)
                ->queryOne();
            try {
                $chartSummary = [
                    'in' => [$query['in10'], $query['in11'], $query['in12'], $query['in1'], $query['in3'], $query['in3'], $query['in4'], $query['in5'], $query['in6'], $query['in7'], $query['in8'], $query['in9']],
                    'out' => [$query['out10'], $query['out11'], $query['out12'], $query['out1'], $query['out3'], $query['out3'], $query['out4'], $query['out5'], $query['out6'], $query['out7'], $query['out8'], $query['out9']]
                ];
                //code...
            } catch (\Throwable $th) {
                $chartSummary = [
                    'in' => [],
                    'out' => [],
                ];
            }

            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('view_chart', [
                        'chartSummary' => $chartSummary
                    ])
                ];
            } else {
                return $this->render('view_chart', [
                    'chartSummary' => $chartSummary
                ]);
            }
        
    }

    /**
     * Finds the Stock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Stock the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stock::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
