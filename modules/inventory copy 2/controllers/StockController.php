<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\Store;
use app\modules\inventory\models\StoreSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\sm\models\Product;
use Yii;
use app\modules\inventory\models\Warehouse;
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
        $dataProvider->query->andFilterWhere(['name' => 'order','movement_type' => 'IN']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionShop()
    {
        $selectWarehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StoreSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=store.asset_item');
        if(isset($selectWarehouse) && $selectWarehouse['warehouse_type'] == 'SUB'){
            $dataProvider->query->where(['warehouse_id' => $selectWarehouse['category_id']]);
        }
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'at.title', $searchModel->q],
            // ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $searchModel->q],
        ]);
        return $this->render('shop', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // แสดงรายการส่งซื้อที่รอรับเข้าคลัง
    public function actionListOrderRequest()
    {

        $warehouse = Yii::$app->session->get('warehouse');
        $warehouseModel = Warehouse::findOne($warehouse['warehouse_id']);
       
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_order_request', [
                     'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_order_request', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


    //Form ขอเบิกวัสดุ
    public function actionOrderRequest()
    {
        $model = new Stock([
            'name' => 'request',
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // $thaiYear = substr((date('Y') + 543), 2);
                // if ($model->rq_number == '') {
                //     $model->rq_number = \mdm\autonumber\AutoNumber::generate('RQ-' . $thaiYear . '????');
                // }
                $model->save(false);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_order_request', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_order_request', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Displays a single Stock model.
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


    public function actionProduct($id)
    {
        $model = Product::findOne($id);
        return $this->render('product_history', [
            'model' => $model,
        ]);
    }


    /**
     * Creates a new Stock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stock([
            'name' => $this->request->get('name'),
            'movement_type' => 'IN',
            'po_number' => $this->request->get('category_id'),
            'receive_type' => $this->request->get('receive_type')
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $thaiYear = substr((date('Y') + 543), 2);
                if ($model->rq_number == '') {
                    $model->rq_number = \mdm\autonumber\AutoNumber::generate('RQ-' . $thaiYear . '????');
                }
                //$model->save(false);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
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

    public function actionListItemInStock($id)
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $id = $this->request->get('id');
        $lot_number = $this->request->get('lot_number');
        // $lotModel = Stock::findOne($id);
        $model = Stock::findOne($id);
   

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->product->avatar(),
                'content' => $this->renderAjax('list_in_stock', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('list_in_stock', [
                'model' => $model,
            ]);
        }

    }

    public function actionSelectLot()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouse = Yii::$app->session->get('warehouse');
        $id = $this->request->get('id');
        $order_id = $this->request->get('order_id');
        // $category_id = $this->request->get('category_id');
        $lotModel =  Stock::findOne($id);
        $model = $this->findModel($order_id);
        $model->lot_number = $lotModel->lot_number;
        $model->movement_type = 'OUT';

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // $thaiYear = substr((date('Y') + 543), 2);
                // if ($model->rq_number == '') {
                //     $model->rq_number = \mdm\autonumber\AutoNumber::generate('RQ-' . $thaiYear . '????');
                // }
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $lotModel->product->avatar(),
                'content' => $this->renderAjax('_select_lot', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_select_lot', [
                'model' => $model,
            ]);
        }

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
