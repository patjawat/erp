<?php

namespace app\modules\me\controllers;

use Yii;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\db\Expression;
use yii\web\Response;
use yii\helpers\ArrayHelper;

class ApproveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionStock()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        // $dataProvider->pagination = ['pageSize' => 20];
        $dataProvider->query->where(['=', new Expression("JSON_EXTRACT(data_json, '$.checker')"),  (string) Yii::$app->user->id]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('stock', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('stock', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    public function actionStockOrderConfirm($id)
    {
        $model  =  Stock::findOne($id);

        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#me'
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->CreateBy('ผู้ขอเบิก')['avatar'],
                'content' => $this->renderAjax('_form_stock_order_confirm', ['model' => $model])
            ];
        } else {
            return $this->render('_form_stock_order_confirm',['model' => $model]);
        }
    }



    // ตรวจสอบความถูกต้อง
    public function actionStockConfirmValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Stock();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['order_confirm'])) {
                $model->data_json['order_confirm'] == "" ? $model->addError('data_json[order_confirm]', $requiredName) : null;
            }

        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }



    public function actionPurchase()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'pr_number', null]);
        $dataProvider->query->andwhere(['status' => 1]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        $dataProvider->pagination->pageSize = 8;
        $dataProvider->query->where(['=', new Expression("JSON_EXTRACT(data_json, '$.leader1')"),  (string) Yii::$app->user->id]);

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายการขอซื้อ/ขอจ้าง',
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('@app/modules/sm/views/default/list_order', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'container' => 'pr-order',
                    'title' => 'รายการขอซื้อ/ขอจ้าง',
                ])
            ];
        } else {
            return $this->render('@app/modules/sm/views/default/list_order', [
                'title' => 'รายการขอซื้อ/ขอจ้าง',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'container' => 'pr-order'
            ]);
        }
    }
}
