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
        $userCreate = UserHelper::GetEmployee();
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
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . (substr((date('Y') + 543), 2)) . '????');
                }

                $model->order_status = 'await';
                $model->warehouse_id = $warehouse['warehouse_id'];
                $model->from_warehouse_id = $warehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        $this->saveCartItem($model);
                        
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
        $orderItem = $cart->getItems();
        foreach($orderItem  as $item)
        {
            $item = new StockEvent([
                'name' => 'order_item',
                'transaction_type' => $model->transaction_type,
                'category_id' => $model->id,
                'warehouse_id' => $model->warehouse_id,
                'asset_item' => $item->asset_item,
                'data_json' => [
                    'req_qty' => $item->qty
                ],
                'order_status' => 'await'
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

        
            if (isset($model->lot_number)) {
                $model->lot_number == "" ? $model->addError('lot_number', $requiredName) : null;
            }
            
            $model->qty == "" ? $model->addError('qty', $requiredName) : null;

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

        // สร้างรายการคำสั่งเข้าเข้าคลังที่ขอเบิก
        $newStockModel = new StockEvent;
        $newStockModel->name = 'order';
        $newStockModel->order_status = 'success';
        $newStockModel->code = \mdm\autonumber\AutoNumber::generate('IN-' . (substr((date('Y') + 543), 2)) . '????');
        $newStockModel->from_warehouse_id = $model->warehouse_id;
        $newStockModel->warehouse_id = $model->from_warehouse_id;
        $newStockModel->transaction_type = 'IN';
        $newStockModel->category_id = $model->code;
        
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
        $model->save(false);
        // End update Stock

        // To New Stock

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
