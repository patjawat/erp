<?php

namespace app\modules\me\controllers;

use app\modules\inventory\models\Product;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\db\Expression;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;

class StoreController extends \yii\web\Controller
{
    // public function actionIndex()
    // {
    //     $searchModel = new StockEventSearch();
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->leftJoin('warehouses w', 'w.id=stock_events.warehouse_id');
    //     $dataProvider->query->andFilterWhere([
    //         'warehouse_type' => 'SUB',
    //         'stock_events.name' => 'order',
    //         'transaction_type' => 'OUT',
    //         'stock_events.created_by' =>  Yii::$app->user->id]);

    //     return $this->render('index',[
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //     ]);
    // }

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



    public function actionView($id)
    {
        $model = StockEvent::findOne($id);
        return $this->render('view',['model' => $model]);
    }
    //รายการขอเบิกวัสดุรอตรวจสอบ
    public function actionChecker()
    {
        $searchModel = new StockOutSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order','checker' => Yii::$app->user->id]);
        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('@app/modules/inventory/views/warehouse/list_order_request', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('@app/modules/inventory/views/warehouse/list_order_request', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    public function actionProduct()
    {
        $warehouse = Yii::$app->session->get('search_warehouse_id');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['>=','stock.qty',1]);
        $dataProvider->query->leftJoin('categorise at', 'at.code=stock.asset_item');
        $dataProvider->query->leftJoin('warehouses w', 'w.id=stock.warehouse_id');
        $dataProvider->query->andFilterWhere(['warehouse_type' => 'SUB']);
        $dataProvider->query->andFilterWhere(['stock.warehouse_id' => ($warehouse ? $warehouse  : $searchModel->warehouse_id)]);
        $dataProvider->query->andFilterWhere(['like','at.title',$searchModel->q]);
        $dataProvider->query->groupBy('asset_item,warehouse_id,lot_number');
        // $dataProvider->pagination->pageSize = 4;

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('product', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('product', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    public function actionAddToCart($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $stock = Stock::findOne($id);
        $model = Stock::findOne($id);
        $model->qty = 1;
        if ($model) {
            \Yii::$app->cart->create($model, 1);
            // return  $this->redirect(['/inventory/store']);
            $this->SetWarehouse($model->warehouse_id);
            return [
                'container' => '#me',
                'status' => 'success',
                'data' => $model
            ];
        }
        throw new NotFoundHttpException();
    }


    public function SetWarehouse($id)
    {
        $warehouse = Yii::$app->session->get('search_warehouse_id');
        if(!$warehouse){
            Yii::$app->session->set('search_warehouse_id',$id);
           return $this->redirect(['/me/store/product']);
        }
    }

    public function actionRemoveWarehouse()
    {
     
            Yii::$app->session->remove('search_warehouse_id');

    }


    public function actionFormCheckout(){
        $model = new StockEvent([
            // 'name' => $this->request->get('name'),
            // 'movement_type' => $this->request->get('name'),
            // 'po_number' => $this->request->get('category_id'),
            // 'receive_type' => $this->request->get('receive_type')
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $thaiYear = substr(AppHelper::YearBudget(), 2);
                if ($model->rq_number == '') {
                    $model->rq_number = \mdm\autonumber\AutoNumber::generate('RQ-' . $thaiYear . '????');
                }
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
                'content' => $this->renderAjax('_form_checkout', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_checkout', [
                'model' => $model,
            ]);
        }

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

     // public function actionUpdate($id, $quantity)
     public function actionUpdateItem($id, $quantity)
     {
         Yii::$app->response->format = Response::FORMAT_JSON;
         $model = Product::findOne($id);
         if ($model) {
              \Yii::$app->cart->update($model,$quantity);
 
             return [
                 'container' => '#me',
                 'status' => 'success'
             ];
         }
     }


    public function actionDelete($id)
    {
        $product = Product::findOne($id);
        if ($product) {
            \Yii::$app->cart->delete($product);
            //ถ้าไม่มีรายดารให้ clear warehouse
            $cart = \Yii::$app->cart;
            if($cart->getCount() == 0){
                Yii::$app->session->remove('search_warehouse_id');
                $this->redirect(['/me/store/product']);
            }

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

        $stock = new StockEvent([
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
        $this->redirect(['index']);
    }



}
