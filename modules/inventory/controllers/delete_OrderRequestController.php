<?php

namespace app\modules\inventory\controllers;

use Yii;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\Warehouse;
use app\modules\sm\models\Product;
use app\components\AppHelper;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\ArrayHelper;


class OrderRequestController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('warehouse');
    if(!$warehouse){
        $id = $this->request->get('id');
        $this->setWarehouse($id);
    }else{
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'OUT','warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', 'thai_year', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $searchModel->q],
        ]);
        // return $this->render('@app/modules/inventory/views/stock-order/index', [
      

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'ajax' => true
            ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'ajax' => false
            ]);
        }

    }
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
      
          

}
