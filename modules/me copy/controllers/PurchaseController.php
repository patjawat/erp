<?php

namespace app\modules\me\controllers;
use app\components\AppHelper;
use app\components\UserHelper;
use app\components\SiteHelper;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use Yii;
class PurchaseController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['created_by' => Yii::$app->user->id]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        $dataProvider->pagination->pageSize = 8;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/purchase/views/order/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'isAjax' => $this->request->isAjax
                ]),
            ];
        } else {

            return $this->render('@app/modules/purchase/views/order/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'isAjax' => true
            ]);
        }
    }

}
