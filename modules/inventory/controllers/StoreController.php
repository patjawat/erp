<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Store;
use app\modules\inventory\models\StoreSearch;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\WarehouseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use app\modules\inventory\models\Product;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
/**
 * StoreController implements the CRUD actions for Store model.
 */
class StoreController extends Controller 
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
     * Lists all Store models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $selectWarehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StoreSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=store.asset_item');
        if(isset( $selectWarehouse)){
            $dataProvider->query->where(['warehouse_id' => $selectWarehouse['category_id']]);
        }
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'at.title', $searchModel->q],
            // ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $searchModel->q],
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionListInWarehouse()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StoreSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['warehouse_id' =>  $warehouse['warehouse_id']]);
        $dataProvider->pagination->pageSize = 5;
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_in_warehouse', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_in_warehouse', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }




    /**
     * Displays a single Store model.
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
     * Creates a new Store model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $model = new Stock([
            'movement_type' => 'OUT',
            'order_status' => 'pending',
            'from_warehouse_id' => $warehouse['category_id'],
            'to_warehouse_id' => $warehouse['warehouse_id']
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create',['model' => $model]),
            ];
        } else {
            return $this->render('create',['model' => $model]);
        }
    }

    /**
     * Updates an existing Store model.
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
     * Deletes an existing Store model.
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



    public function actionViewCart()
    {
        $cart = \Yii::$app->cart;
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view_cart'),
                'countItem' => $cart->getCount()
            ];
        } else {
            return $this->render('view_cart');
        }
    }

 
    public function actionAddToCart($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Store::findOne(['asset_item' => $id]);
        $model->stock_qty = 1;
        if ($model) {
            \Yii::$app->cart->create($model, 1);
            // return  $this->redirect(['/inventory/store']);
            return [
                'container' => '#viewCart',
                'status' => 'success',
                'data' => $model
            ];
        }
        throw new NotFoundHttpException();
    }

    public function actionUpdateCart($id, $quantity)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Store::findOne($id);
        // return $model->qty;
        if ($model) {
             \Yii::$app->cart->update($model,$quantity);

            return [
                'container' => '#viewCart',
                'status' => 'success'
            ];
        }
    }


    public function actionDeleteItem($id)
    {
        $product = Product::findOne($id);
        if ($product) {
            \Yii::$app->cart->delete($product);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'container' => '#viewCart',
                'status' => 'success'
            ];
        }
    }


    public function actionCheckout()
    {
        \Yii::$app->cart->checkOut(false);
        $this->redirect(['index']);
    }

    // public function actionSetToWarehouse()
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $id = $this->request->get('id');
    //     $model = Warehouse::find()->where(['id' => $id])->One();
    //     Yii::$app->session->set('to_warehouse', [
    //         'warehouse_id' => $model->id,
    //         'warehouse_name' => $model->warehouse_name,
    //     ]);
    //     return $this->redirect(['index']);
    //     // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    // }


    /**
     * Finds the Store model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Store the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Store::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    
}
