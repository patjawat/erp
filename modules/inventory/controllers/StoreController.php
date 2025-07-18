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
use app\modules\inventory\models\ProductSearch;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\components\UserHelper;
use app\modules\inventory\models\StockEvent;
use yii\db\Expression;

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
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->select(['stock.*',new Expression('SUM(stock.qty) AS total'),]);
        $dataProvider->query->leftJoin('categorise at', 'at.code=stock.asset_item');
        if(isset($searchModel->warehouse_id)){
            $dataProvider->query->where(['warehouse_id' => $searchModel->warehouse_id]);
        }else{
            $dataProvider->query->where(['warehouse_id' => 00]);
        }
        
        if(isset($selectWarehouse)){
            // $dataProvider->query->where(['warehouse_id' => $selectWarehouse['warehouse_id']]);
        }
        
        // if(isset($selectWarehouse) && $selectWarehouse['warehouse_type'] == 'SUB'){
            //     $dataProvider->query->where(['warehouse_id' => $selectWarehouse['category_id']]);
            // }
            $dataProvider->query->andWhere(['>', 'stock.qty', 0]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'at.title', $searchModel->q],
            // ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $searchModel->q],
        ]);
        $dataProvider->query->groupBy('asset_item');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }


        //เลือกคลังที่จะดำเนินการเบิก
    public function actionSelectWarehouse()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = Warehouse::find()->where(['id' => $id])->One();
        Yii::$app->session->set('select-warehouse', [
            'warehouse_id' => $model->id,
            'warehouse_name' => $model->warehouse_name,
        ]);
        \Yii::$app->cart->checkOut(false);
        return [
            'container' => '#store'
        ];
        // return $this->redirect(['/inventory/store']);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
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
        $userCreate = UserHelper::GetEmployee();
        $warehouse = Yii::$app->session->get('warehouse');
        $warehouseSelect = Yii::$app->session->get('select-warehouse');
        $model = new Stock([
            'name' => 'order',
            'movement_type' => 'OUT',
            'order_status' => 'pending',
            'from_warehouse_id' => $warehouse['warehouse_id'],
            'warehouse_id' => $warehouseSelect['warehouse_id'],
            'data_json' => [
                'checker' => (int)$userCreate->leaderUser()['leader1'],
                'checker_name' => $warehouse['checker_name'],
            ]
        ]);
        $cart = \Yii::$app->cart;
        $items = $cart->getItems();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                if($model->save()){
                    foreach ($items as $item) {
                       $orderItem = new Stock([
                        'name' => 'order_item',
                        'movement_type' => 'OUT',
                        'category_id' => $model->id,
                        'asset_item' => $item->asset_item,
                        'qty' => $item->qty
                       ]);
                       $orderItem->save(false);
                       \Yii::$app->cart->checkOut(false);
                    }
                }
                return $this->redirect(['/inventory/warehouse']);
                // return $this->redirect(['view', 'id' => $model->id]);
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
        $cart = \Yii::$app->cart;
        $itemsCount = \Yii::$app->cart->getCount();
        
        $model = Stock::findOne($id);
        if($itemsCount > 0){
            $item = $cart->getItemById($id);
            $item->getQuantity();
            if($item->getQuantity() > $model->qty){
                \Yii::$app->cart->create($model, -1);
                 return [
                     'status' => 'error',
                     'container' => '#inventory',
                    ];
                }else{
                    \Yii::$app->cart->create($model, 1);
                     return [
                         'status' => 'success',
                         'container' => '#inventory',
                     ];
        
             }
        }else{
            \Yii::$app->cart->create($model, 1);
            return [
                'status' => 'success',
                'container' => '#inventory',
            ];
        }

    }


 //กำหนดจำนวนที่จ่ายให้
 public function actionUpdateQty()
 {
     Yii::$app->response->format = Response::FORMAT_JSON;
     $id = $this->request->get('id');
     $qty = $this->request->get('qty');
     $model = StockEvent::findOne($id);
     $checkStock = Stock::findOne($id);
     if($qty > $checkStock->qty){
         return [
             'status' => 'error',
             'container' => '#inventory',
            ];
        }else{
            //  $model->qty = $qty;
            \Yii::$app->cart->update($checkStock,$qty);
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'status' => 'success',
                 'container' => '#inventory',
             ];

     }
         
 }


    // public function actionAddToCart($id)
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;

    //     $model = Stock::findOne($id);
    //     if ($model) {
    //         \Yii::$app->cart->create($model, 1);
    //         return [
    //             'container' => '#inventory',
    //             'status' => 'success',
    //             'data' => $model
    //         ];
    //     }
    //     throw new NotFoundHttpException();
    // }

    public function actionUpdateCart()
    {
        $id  = $this->request->get('id');
        $quantity  = $this->request->get('quantity');
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Stock::findOne($id);
        $checkStock = Stock::findOne($id);
        if($quantity > $checkStock->qty){
            return [
                'status' => 'error',
                'container' => '#inventory',
               ];
           }else{
            \Yii::$app->cart->update($model,$quantity);
            return [
                'container' => '#inventory',
                'status' => 'success'
            ];
           }
    
    }


    public function actionDeleteItem($id)
    {
        $product = Stock::findOne($id);
        if ($product) {
            \Yii::$app->cart->delete($product);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'container' => '#inventory',
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
