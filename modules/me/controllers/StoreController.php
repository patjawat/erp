<?php

namespace app\modules\me\controllers;

use app\modules\inventory\models\Product;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockOutSearch;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;

class StoreController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);

        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=stock.asset_item');
        // $dataProvider->query->andFilterWhere(['stock.name' => 'order_item']);
        $dataProvider->query->andFilterWhere(['like','at.title',$searchModel->q]);
        $dataProvider->query->groupBy('asset_item');
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

        $model = Product::findOne(['code' => $id]);
        $model->qty = 1;
        if ($model) {
            \Yii::$app->cart->create($model, 1);
            // return  $this->redirect(['/inventory/store']);
            return [
                'container' => '#me',
                'status' => 'success',
                'data' => $model
            ];
        }
        throw new NotFoundHttpException();
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
                $thaiYear = substr((date('Y') + 543), 2);
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
     public function actionUpdate($id, $quantity)
     {
         Yii::$app->response->format = Response::FORMAT_JSON;
         $model = Product::findOne($id);
         // return $model->qty;
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
        $thaiYear = date('dm') . substr((date('Y') + 543), 2);

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
