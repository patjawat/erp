<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockOutSearch;
use app\modules\inventory\models\StockEventSearch;
use app\modules\sm\models\Product;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\inventory\models\Warehouse;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * StockOutController implements the CRUD actions for StockOut model.
 */
class StockOrderController extends Controller
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
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'OUT','from_warehouse_id' => $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRequest()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'OUT','from_warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['<>','warehouse_id', $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        $formWarehouse = Yii::$app->session->get('warehouse');
        $toWarehouse = Yii::$app->session->get('select-warehouse');
        $userCreate = UserHelper::GetEmployee();
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $order =  StockEvent::findOne($order_id);
        $type = $this->request->get('type');

        $model = new StockEvent([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'warehouse_id' => $toWarehouse['warehouse_id'],
            'category_id' => $order_id,
            'name' => $name,
            'transaction_type' => $order ? $order->transaction_type : $type,
            'code' => $order ? $order->code : '',
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . (substr(AppHelper::YearBudget(), 2)) . '????');
                }

                $model->order_status = 'pending';
                $model->warehouse_id = $toWarehouse['warehouse_id'];
                $model->from_warehouse_id = $formWarehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                         $this->saveCartItem($model);
                        \Yii::$app->cart->checkOut(false);

                        return $this->redirect(['/inventory/stock-order/request']);
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

    protected function saveCartItem($model)
    {
        $cart = \Yii::$app->cart;

        foreach($cart->getItems()  as $item)
        {
            $item = new StockEvent([
                'name' => 'order_item',
                'transaction_type' => $model->transaction_type,
                'category_id' => $model->id,
                'warehouse_id' => $model->warehouse_id,
                'asset_item' => $item->asset_item,
                'lot_number' => $item->lot_number,
                'unit_price' => $item->unit_price,
                'qty' => $item->getQuantity(),
                'data_json' => [
                    'req_qty' => $item->getQuantity()
                ],
                'order_status' => 'pending'
            ]);
            $item->save(false);
        }
        return true;

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


    //กำหนดจำนวนที่จ่ายให้
    public function actionUpdateQty($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $qty = $this->request->get('qty');
        $model = StockEvent::findOne($id);
        $checkStock = Stock::findOne(['lot_number' => $model->lot_number,'warehouse_id' => $model->warehouse_id]);
        if($qty > $checkStock->qty){
            return [
                'status' => 'error',
                'container' => '#inventory',
            ];
        }else{
            $model->qty = $qty;
            if($model->save(false)){
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            }
        }
            
    }

    public function actionUpdateLot($id)
    {
        $model = StockEvent::findOne($id);
        $model->qty = $model->data_json['req_qty'];
        if ($this->request->isPost && $model->load($this->request->post())) {
            $lot = Stock::findOne(['lot_number'=> $model->lot_number]);
            $model->unit_price = $lot->unit_price;
            $model->total_price = ($lot->unit_price* $model->qty);
            $model->save(false);

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'container' => '#inventory',
            ];
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->product->Avatar(),
                'content' => $this->renderAjax('_form_update_lot', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_update_lot', [
                'model' => $model,
            ]);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionUpdateLotValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();

        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {
            $checkStock = Stock::findOne(['lot_number' => $model->lot_number]);
            
            if (isset($model->lot_number)) {
                $model->lot_number == "" ? $model->addError('lot_number', $requiredName) : null;
            }
            
            $model->qty == "" ? $model->addError('qty', $requiredName) : null;
            if($model->qty > $checkStock->qty){
                $model->addError('qty', 'วัสดุไม่พอจ่าย');
            }

        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }




    public function actionSaveOrder($id)
    {
        $model = StockEvent::findOne($id);
        $model->order_status = 'pending';
        $model->save(false);

        foreach ($model->getItems() as $item) {
            $item->order_status = 'pending';
            $item->save(false);
        }

        return $this->redirect(['/inventory/stock-order']);
    }

    public function actionCheckOut($id)
    {

        $model = StockEvent::findOne($id);

        $userCreate = UserHelper::GetEmployee();
        $jsonDate =[
            'player' => $userCreate->id,
            'player_date' => date('Y-m-d H:i:s')
        ];

        // สร้างรายการคำสั่งเข้าเข้าคลังที่ขอเบิก
        $newStockModel = new StockEvent;
        $newStockModel->name = 'order';
        $newStockModel->order_status = 'success';
        $newStockModel->code = \mdm\autonumber\AutoNumber::generate('IN-' . (substr(AppHelper::YearBudget(), 2)) . '????');
        $newStockModel->from_warehouse_id = $model->warehouse_id;
        $newStockModel->warehouse_id = $model->from_warehouse_id;
        $newStockModel->transaction_type = 'IN';
        $newStockModel->category_id = $model->code;
        $newStockModel->data_json = $jsonDate;
        
        $newStockModel->save(false);
        // จบ

        // update Stock
        foreach ($model->getItems() as $item) {
            $item->order_status = 'success';
            $item->save(false);
            Yii::$app->response->format = Response::FORMAT_JSON;

            $newStockItem = new StockEvent;
            $newStockItem->name = 'order_item';
            $newStockItem->code = $newStockModel->code;
            $newStockItem->asset_item = $item->asset_item;
            $newStockItem->lot_number = $item->lot_number;
            $newStockItem->qty = $item->qty;
            $newStockItem->unit_price = $item->unit_price;
            $newStockItem->transaction_type = 'IN';
            $newStockItem->warehouse_id = $model->from_warehouse_id;
            $newStockItem->category_id = $newStockModel->id;
            $newStockItem->save(false);

            // UpdateNewStock ที่ขอเบิก
            $checkToStock = Stock::findOne(['asset_item' => $item->asset_item, 'warehouse_id' => $newStockItem->warehouse_id]);
            if ($checkToStock) {
                $toStock = $checkToStock;
            } else {
                $toStock = new Stock;
            }
            $toStock->asset_item = $item->asset_item;
            $toStock->lot_number = $item->lot_number;
            $toStock->warehouse_id = $newStockItem->warehouse_id;
            $toStock->unit_price = $item->unit_price;
            $toStock->qty = $toStock->qty + $newStockItem->qty;
            $toStock->save(false);

            // ตัด stock และทำการ update
            $checkStock = Stock::findOne(['asset_item' => $item->asset_item, 'lot_number' => $item->lot_number, 'warehouse_id' => $model->warehouse_id]);
            $checkStock->qty = $checkStock->qty - $item->qty;
            $checkStock->save(false);
        }

        $model->order_status = 'success';
        $oldObj = $model->data_json;
        $model->data_json =  ArrayHelper::merge($oldObj,$model->data_json,$jsonDate);
        $model->save(false);
        // End update Stock

        // To New Stock

        return $this->redirect(['/inventory/order-request']);
    }

 public function actionAddNewItem($id)
 {
    // Yii::$app->response->format = Response::FORMAT_JSON;
    $order = $this->findModel($id);
    $model = new StockEvent;

    if ($this->request->isPost && $model->load($this->request->post())) 
    {
        $item = Stock::findOne(['lot_number' => $model->lot_number]);
        $model->category_id = $order->id;
        $model->name = 'order_item';
        $model->asset_item = $item->asset_item;
        $model->unit_price = $item->unit_price;
        $model->transaction_type = 'OUT';
        $model->warehouse_id =  $order->warehouse_id;

        Yii::$app->response->format = Response::FORMAT_JSON;
        $checkStock = Stock::findOne(['lot_number' => $model->lot_number]);

            if($model->qty > $checkStock->qty){
                return [
                    'status' => 'error'
                ];
            }

            $model->save(false);
            return [
                'status' => 'success',
                'container' => '#inventory'
            ];

            
    }
 

    if ($this->request->isAJax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'เพิ่มรายการใหม่',
            'content' => $this->renderAjax('_add_new_item', [
                'model' => $model,
                'order' => $order
            ])
        ];
    } else {
        return $this->render('_add_new_item', [
            'model' => $model,
             'order' => $order
        ]);
    }


   
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


    public function actionCopyItem()
    {
        $lot_number = $this->request->get('lot_number');
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Stock::findOne(['lot_number' => $lot_number]);
        $sql = "SELECT o.data_json->>'$.receive_date' as receive_date,i.lot_number,IFNULL(s.qty,0) as qty FROM `stock_events` i
                LEFT JOIN stock_events o ON i.code = o.code AND o.name ='order'
                LEFT JOIN stock s ON s.lot_number = i.lot_number
                WHERE i.asset_item = '01-00011'
                AND IFNULL(s.qty,0) > 0
                AND JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.receive_date')) > '2024-08-01'
                GROUP BY s.lot_number
                ORDER BY JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.receive_date')) ASC limit 1;";
        $query  = Yii::$app->db->createCommand($sql,[':asset_item' => $model->asset_item,':qty' => $model->SumQty()])
        ->queryOne();
        return $query;
        
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
        $container = $this->request->get('container');
        $url = $this->request->get('url');
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model->delete();
        return [
            'status' => 'success',
            'container' => '#inventory',
            'url' => $url
        ];
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
