<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Product;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;

class StockV2Controller extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere(['created_by' => Yii::$app->user->id]);

        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApprove($id)
    {
        $model = StockEvent::findOne($id);
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('stock_approve',['model' => $model])
            ];
        }
    }

    public function actionCreate(){
        $model = new StockEvent;
        $warehouse = Yii::$app->session->get('search_warehouse_id');
        $cart = \Yii::$app->cart;
        $items = $cart->getItems();

        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->name = 'order';
                $model->order_status = 'pending';
                $model->transaction_type = 'OUT';
                $model->warehouse_id = $warehouse;
                $model->from_warehouse_id = $warehouse;
                $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . (substr(AppHelper::YearBudget(), 2)) . '????');
                $model->save(false);
                
                // update Stock
                foreach ($items as $item) {
                    
                    $newStockItem = new StockEvent;
                    $newStockItem->code = $model->code;
                    $newStockItem->name = 'order_item';
                    $newStockItem->order_status = 'pending';
            $newStockItem->lot_number = $item['lot_number'];
            $newStockItem->asset_item = $item['asset_item'];
            $newStockItem->qty = $item->getQuantity();
            $newStockItem->data_json = [
                'req_qty' =>  $item->getQuantity()
            ];
            $newStockItem->transaction_type = 'OUT';
            $newStockItem->warehouse_id = $item['warehouse_id'];
            $newStockItem->from_warehouse_id = $item['warehouse_id'];
            $newStockItem->category_id = $model->id;
            $newStockItem->save(false);

           
            // ตัด stock และทำการ update
            // $checkStock = Stock::findOne(['asset_item' => $item->asset_item, 'lot_number' => $item->lot_number, 'warehouse_id' => $model->warehouse_id]);
            // $checkStock->qty = $checkStock->qty - $item->qty;
            // $checkStock->save(false);
        }
        \Yii::$app->cart->checkOut(false);
                return $this->redirect(['/me/store']);
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
    
     // public function actionUpdate($id, $quantity)
     public function actionUpdate($id)
     {

         $model = StockEvent::findOne($id);
         $oldObj = $model->data_json;
            if ($this->request->isPost && $model->load($this->request->post())) {

                $model->data_json = ArrayHelper::merge($oldObj,$model->data_json);
                if($model->save(false)){
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'container' => '#me',
                        'status' => 'success'
                    ];
                    
                }
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


    public function actionDelete($id)
    {
        $product = Product::findOne($id);
        if ($product) {
            \Yii::$app->cart->delete($product);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'container' => '#me',
                'status' => 'success'
            ];
        }
    }

    public function actionCheckout()
    {

        $cart = \Yii::$app->cart;
        $items = $cart->getItems();

        $stock = new Stock([
            'name' => 'order',
            'movement_type' => 'OUT',
        ]);
        $thaiYear = date('dm') . substr(AppHelper::YearBudget(), 2);

        $stock->rq_number  = \mdm\autonumber\AutoNumber::generate('RQ' . $thaiYear . '-?????');
        $stock->save(false);
        foreach ($items as $item) {
           $model = new Stock([
                'name' => 'order_item',
                'category_id' => $stock->id,
                'rq_number' => $stock->rq_number,
                'asset_item' => $item->code,
                'movement_type' => 'OUT'
           ]);
           $model->save(false);
        }


        \Yii::$app->cart->checkOut(false);
        Yii::$app->session->remove('search_warehouse_id');
        $this->redirect(['index']);
    }



}
