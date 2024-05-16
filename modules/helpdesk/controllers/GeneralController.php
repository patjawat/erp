<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class GeneralController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['in', 'status', [2,3]]);

        $dataProviderStatus1 = $searchModel->search($this->request->queryParams);
        $dataProviderStatus1->query->andFilterWhere(['name' => 'repair']);
        $dataProviderStatus1->query->andFilterWhere(['status' => 1]);

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProviderStatus1' => $dataProviderStatus1,
                    'dataProvider' => $dataProvider,
                    ])
                ];
            }else{
                return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProviderStatus1' => $dataProviderStatus1,
                    'dataProvider' => $dataProvider,
            ]);
        }
    }

}
