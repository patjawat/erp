<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockOutSearch;
use app\modules\inventory\models\StockEventSearch;
use app\modules\sm\models\Product;
use app\components\AppHelper;
use app\modules\inventory\models\Warehouse;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\inventory\models\StockSearch;
use app\components\UserHelper;
use yii\db\Expression;

/**
 * StockOutController implements the CRUD actions for StockOut model.
 */
class StockOutController extends Controller
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
     * Lists all StockOut models.
     *
     * @return string
     */
    public function actionIndex()
    {

    $warehouse = Yii::$app->session->get('warehouse');
    if(!$warehouse){
        $id = $this->request->get('id');
        $this->setWarehouse($id);
    }else{
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'OUT','warehouse_id' => $warehouse['warehouse_id']]);
        
        // return $this->render('@app/modules/inventory/views/stock-order/index', [
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    }

    public function actionStock()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->select(['stock.*',new Expression('SUM(stock.qty) AS total'),]);
        $dataProvider->query->leftJoin('categorise at', 'at.code=stock.asset_item');
        if(isset($searchModel->warehouse_id)){
            $dataProvider->query->where(['warehouse_id' => $searchModel->warehouse_id]);
        }else{
            $dataProvider->query->where(['warehouse_id' => $warehouse['warehouse_id']]);
        }
        
        $dataProvider->query->andWhere(['>', 'stock.qty', 0]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'at.title', $searchModel->q],
        ]);

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_stock', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider
                ])
            ];
        } else {
            return $this->render('list_stock', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * Displays a single StockOut model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {



        $model = StockEvent::findOne($id);
        return $this->render('view', [
            'model' => $model
        ]);
    }


    /**
     * Creates a new StockOut model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $order =  StockEvent::findOne($order_id);
        $type = $this->request->get('type');

        $model = new StockEvent([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'warehouse_id' => $order ? $order->warehouse_id : '',
            'category_id' => $order_id,
            'name' => $name,
            'transaction_type' => $order ? $order->transaction_type : $type,
            'code' => $order ? $order->code : '',
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . (substr(AppHelper::YearBudget(), 2)) . '????');
                }
                $model->order_status = 'await';
                $model->from_warehouse_id = $warehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            'status' => 'success',
                            'container' => '#inventory',
                        ];
                    }
                } else {
                    $model->loadDefaultValues();
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }


     

public function actionShowCart()
{
    if ($this->request->isAJax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('show_cart')
        ];
    } else {
        return $this->render('show_cart');
    }
}
    /**
     * Updates an existing StockOut model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post()) ) {

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
//เพิ่มรายการสินคเาวัสดุ
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

        // $model = StockEvent::findOne($id);
        $warehouse = Yii::$app->session->get('warehouse');

        // สร้างรายการคำสั่งเข้าเข้าคลังที่ขอเบิก
        $model = new StockEvent;
        $model->name = 'order';
        $model->order_status = 'success';
        $model->code = \mdm\autonumber\AutoNumber::generate('OUT-' . (substr(AppHelper::YearBudget(), 2)) . '????');
        $model->from_warehouse_id = $warehouse['warehouse_id'];
        $model->warehouse_id = $warehouse['warehouse_id'];
        $model->transaction_type = 'OUT';
        // $newStockModel->category_id = $model->code;
        
       $model->save(false);
        // จบ
            $cart = \Yii::$app->cart;

        // update Stock
        foreach ($cart->getItems() as $item) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $newStockItem = new StockEvent;
            $newStockItem->name = 'order_item';
            $newStockItem->order_status = 'success';
            $newStockItem->code = $model->code;
            $newStockItem->lot_number = $item->lot_number;
            $newStockItem->asset_item = $item->asset_item;
            $newStockItem->qty = $item->getQuantity();
            $newStockItem->unit_price = $item->unit_price;
            $newStockItem->transaction_type = 'OUT';
            $newStockItem->warehouse_id = $model->from_warehouse_id;
            $newStockItem->category_id = $model->id;
            $newStockItem->save(false);

            // ตัด stock และทำการ update
            $checkStock = Stock::findOne(['id' => $item->id, 'lot_number' => $item->lot_number, 'warehouse_id' => $newStockItem->warehouse_id]);
            $checkStock->qty = $checkStock->qty - $item->getQuantity();
            $checkStock->save(false);
        }

        // $model->order_status = 'success';
        // $model->save(false);
        // End update Stock

        // To New Stock
        \Yii::$app->cart->checkOut(false);
        return $this->redirect(['/inventory/stock-out']);
    }


    //ยืนยันการจ่ายของ
    // public function actionCheckOut($id)
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $order = StockEvent::findOne($id);
    //     // $order = $this->findModel($id);
    //     // return $order->listItems();
    //     $data = [];
    //     foreach($order->listItems() as $item){
    //         // $checkStock = Stock::findOne(['asset_item' => $item->asset_item,'warehouse_id' => $item->from_warehouse_id]);
    //         $checkStock = Stock::findOne(['asset_item' => $item->asset_item,'warehouse_id' => $item->from_warehouse_id]);

    //         if($checkStock){
    //             $stock = $checkStock;
    //         }else{
    //             $stock = new Stock;
    //         }
    //         $stock->asset_item = $item->asset_item;
    //         $stock->warehouse_id = $item->from_warehouse_id;
    //         $stock->qty = $stock->qty + $item->qty;
    //         $data [] = $stock->save(false);
    //     }
    //     return $data;

    // }

    // update รายการ Items ที่บันทึกใหม่ให้เป็น success
    public function actionConfirmOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // $model = StockOut::updateAll(['order_status' => 'success'], ['category_id' => $id]);
        return $this->updateStock($id);
        return $this->redirect(['/inventory/stock-out']);

        if ($model) {
            return [
                'status' => 'success',
                'container' => '#inventory',
            ];
        }
    }


    protected function updateStock($id)
    {

        $order = $this->findModel($id);
        // return $order->listItems();
        foreach ($order->listItems() as $item) {
            $model = StockEvent::findOne(['lot_number' => $item->lot_number]);
            $model->asset_item  = $item->asset_item;
            $model->qty = $model->qty - $item->qty;
            $model->warehouse_id = $item->warehouse_id;
            $model->save(false);

            $stock = Stock::findOne(['asset_item' => $model->asset_item]);
            $stock->qty = $stock->qty - $item->qty;
            $stock->save(false);
        }
    }


    /**
     * Deletes an existing StockOut model.
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


      //เลือกคลังที่จะทำงาน
      protected function setWarehouse($id)
      {
          $model = Warehouse::find()->where(['id' => $id])->One();
          Yii::$app->session->set('warehouse',[
              'id' => $model->id,
              'warehouse_id' => $model->id,
              'warehouse_code' => $model->warehouse_code,
              'warehouse_name' => $model->warehouse_name,
              'warehouse_type' => $model->warehouse_type,
              'category_id' => $model->category_id,
              'checker' => isset($model->data_json['checker']) ?  $model->data_json['checker'] : '',
              'checker_name' => isset($model->data_json['checker_name']) ? $model->data_json['checker_name'] : '',
          ]);
          return $this->redirect(['index']);
          // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
      }
      
    /**
     * Finds the StockOut model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockOut the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockEvent::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
