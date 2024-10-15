<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\Warehouse;
use yii\web\Response;
use Yii;
class SubStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }



    public function actionAddToCart($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = \Yii::$app->cartSub;
        $itemsCount = $cart->getCount();

        $model = Stock::findOne($id);

        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');
        if (!$getWarehouse) {
            $warehouse = Warehouse::find()->where(['id' => $model->warehouse_id])->One();
            \Yii::$app->session->set('selectMainWarehouse', [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->warehouse_name,
            ]);
        }
        $cart->create($model, 1);

        return [
            'status' => 'success',
            'container' => '#inventory',
        ];
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
                'container' => '#inventory',
               ];
           }else{
            \Yii::$app->cartSub->update($model,$quantity);
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
            \Yii::$app->cartSub->delete($product);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'container' => '#inventory',
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
            'warehouse_id' => $warehouse['warehouse_id'],
            'name' => 'order',
            'transaction_type' => 'OUT'
        ]);
        $model->save();
        foreach($items as $item){
            $item = new StockEvent([
                'name' => 'order_item',
                'transaction_type' => 'OUT',
                'warehouse_id' => $warehouse['warehouse_id'],
                'qty' => $item->getQuantity(),
                'unit_price' => $item->price,
                'lot_number' => $item->lot_number,
            ]);
        }
        
    }



}
