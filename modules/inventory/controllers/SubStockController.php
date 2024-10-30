<?php

namespace app\modules\inventory\controllers;
use Yii;
use yii\web\Response;
use app\components\AppHelper;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
class SubStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }





    //เลือกเบิก lot จาก stock 
    public function actionSelectLot($id)
    {      
        $model= Stock::findOne($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เลือกเบิก'.$model->product->title,
                'content' => $this->renderAjax('select_lot',['model' => $model])
            ];
        } else {
            return $this->render('select_lot',['model' => $model]);
        }  
    }



    public function actionAddToCart($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = \Yii::$app->cartSub;
        $itemsCount = $cart->getCount();
        $model = Stock::findOne($id);
        // return $model->getLotQtyOut();

        // $getWarehouse = \Yii::$app->session->get('selectSubWarehouse');
        // if (!$getWarehouse) {
        //     $warehouse = Warehouse::find()->where(['id' => $model->warehouse_id])->One();
        //     \Yii::$app->session->set('selectSubWarehouse', [
        //         'warehouse_id' => $warehouse->id,
        //         'warehouse_name' => $warehouse->warehouse_name,
        //     ]);
        // }

        $checkStock = Stock::findOne(['lot_number' => $model->lot_number,'id' => $model->id]);

        if($model->qty > $checkStock->qty){
            return [
                'status' => 'error',
                'container' => '#inventory-container',
               ];
           }else{
               $cart->create($model, 1);
                if(!$cart->getItems($model->lot_number)){
                }

        return [
            'status' => 'success',
            'totalCount' => $cart->getCount(),
            'container' => '#inventory-container',
        ];
    }
    }


    public function actionShowCart()
    {
        $cart = \Yii::$app->cartSub;
        $warehouseSelect = \Yii::$app->session->get('select-warehouse');
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<h6><i class="bi bi-ui-checks"></i> ขอเบิก <span class="badge rounded-pill text-bg-primary countMainItem">'.$cart->getCount().' </span> รายการ</h6>',
                'content' => $this->renderAjax('show_cart'),
                'countItem' => $cart->getCount(),
            ];
        } else {
            return $this->render('show_cart');
        }
    }

    public function actionViewCart()
    {
        $cart = Yii::$app->cartSub;
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
                'container' => '#inventory-container',
               ];
           }else{
            \Yii::$app->cartSub->update($model,$quantity);
            return [
                'container' => '#inventory-container',
                'status' => 'success'
            ];
           }
    
    }


 //กำหนดจำนวนที่จ่ายให้
 public function actionUpdateQty()
 {
     Yii::$app->response->format = Response::FORMAT_JSON;
     $id = $this->request->get('id');
     $qty = $this->request->get('qty');
     $checkStock = Stock::findOne($id);
     if($qty > $checkStock->qty){
         return [
             'status' => 'error',
             'container' => '#inventory-container',
            ];
        }else{
            //  $model->qty = $qty;
            \Yii::$app->cartSub->update($checkStock,$qty);
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'status' => 'success',
                 'container' => '#inventory-container',
             ];

     }
         
 }

    public function actionDeleteItem($id)
    {
        $product = Stock::findOne($id);
        if ($product) {
            \Yii::$app->cartSub->delete($product);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'container' => '#inventory-container',
                'status' => 'success'
            ];
        }
    }
    
    //บันทึกเบิก
    public function actionCheckOut()
    {
        $cart = \Yii::$app->cartSub;
        $items = $cart->getItems();
        $warehouse = \Yii::$app->session->get('warehouse');
        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
           
        ]);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            
            $error = 0;

            // foreach ($cart->getItems() as $item) {
            //     $stock = Stock::findOne($item->id);
            //     if($item->getQuantity() > $stock->qty){
            //         $error+=1;
            //     }
            // }

            // if($error>0){
            //     return ['status' => 'error', 'message' =>'ไม่พอจ่าย'];
            // }

            $transaction = \Yii::$app->db->beginTransaction();
            try {

            // Save Order
            $model->code = \mdm\autonumber\AutoNumber::generate('OUT-'.substr(AppHelper::YearBudget(), 2).'????');
            $model->warehouse_id = $warehouse['warehouse_id'];
            $model->name = 'order';
            $model->transaction_type = 'OUT';
            $model->order_status = 'success';
            $model->thai_year = AppHelper::YearBudget();

            if (!$model->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ได้');
            }
            // ถ้า Save Order เสร็จ ให้ save Items
            foreach ($cart->getItems() as $item) {
                $newItem = new StockEvent([
                    'name' => 'order_item',
                    'thai_year' => AppHelper::YearBudget(),
                    'transaction_type' => $model->transaction_type,
                    'category_id' => $model->id,
                    'warehouse_id' => $model->warehouse_id,
                    'asset_item' => $item->asset_item,
                    'lot_number' => $item->lot_number,
                    'unit_price' => $item->unit_price,
                    'qty' => $item->getQuantity(),
                    'order_status' => 'success',
                    'data_json' => [
                        'req_qty' => $item->getQuantity(),
                    ],
                ]);
                if (!$newItem->save(false)) {
                    throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ITems ได้');
                }
                    //ถ้า save icon เสร็จให้ update stock
                
                        // $stock = Stock::findOne(['warehouse_id' => $item->warehouse_id,'asset_item' => $item->asset_item,'lot_number' => $item->lot_number]);
                        $stock = Stock::findOne($item->id);
                        if($stock){
                            $stock->qty =  ($stock->qty - $newItem->qty);
                           if (!$stock->save(false)) {
                            throw new \Exception('ไม่สามารถบันทึกข้อมูล Stock ได้');
                        }
                        }

                }
                $cart->checkOut(false);
                $transaction->commit();
                return [
                    'container' => '#inventory-container',
                    'status' => 'success'
                ];

        } catch (\Throwable $e) {
            $transaction->rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

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


     // ตรวจสอบความถูกต้อง
     public function actionCreateValidator()
     {
         Yii::$app->response->format = Response::FORMAT_JSON;
         $model = new StockEvent();
 
         $requiredName = "ต้องระบุ";
         if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['note'])) {
                $model->data_json['note'] == '' ? $model->addError('data_json[note]', $requiredName) : null;
            }
 
         }
         foreach ($model->getErrors() as $attribute => $errors) {
             $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
         }
         if (!empty($result)) {
             return $this->asJson($result);
         }
     }





}
