<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Product;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;

class StockV2Controller extends \yii\web\Controller
{
    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('sub-warehouse');
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere(['warehouse_id' =>$warehouse['id'] ?? null]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApprove($id)
    {
        $model = StockEvent::findOne($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('stock_approve', ['model' => $model])
            ];
        }
    }


    public function actionMaterialList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Categorise::find()
            ->select(['id', 'code', 'title'])
            ->andWhere(['name' => 'asset_item', 'group_id' => 'EQUIP'])
            ->andWhere(['like', 'title', $q])
            ->orWhere(['like', 'code', $q])
            ->limit(20)
            ->asArray()
            ->all();

        $results = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'text' => $row['code'] . ' - ' . $row['title'],
                'code' => $row['code'],
                'name' => $row['title'],
                'unit' => 'หน่วย' // สมมติว่า unit คงที่ หรือจะดึงจาก DB ก็ได้
            ];
        }, $query);

        return ['results' => $results];
    }

    public function actionRenderMaterialRow($index)
    {
        // if (!Yii::$app->request->isAjax) {
        //     throw new \yii\web\BadRequestHttpException('Invalid request');
        // }

        return $this->renderPartial('_material_row', [
            'index' => (int)$index,
        ]);
    }



    public function actionCreate()
    {
         $warehouse = Yii::$app->session->get('sub-warehouse');
        $model = new StockEvent([
            'name' => 'order',
            'transaction_type' => 'OUT',
            'warehouse_id' => $warehouse['id'] ?? null,

        ]);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->request->post();

            return [
                'status' => 'success',
                'message' => 'Stock event created successfully',
                'data' => $model->attributes,
                'data2' => $this->request->post()
            ];
        }

        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    // public function actionUpdate($id, $quantity)
    public function actionUpdate($id)
    {

        $model = StockEvent::findOne($id);
        $model->items = $model->listOrderItem();
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            if ($model->save(false)) {
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
