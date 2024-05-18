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
        // $dataProvider->query->andFilterWhere(['repair_group' => 1]);
        $dataProvider->query->andFilterWhere(['in', 'status', [2,3]]);
        
        $dataProviderStatus1 = $searchModel->search($this->request->queryParams);
        $dataProviderStatus1->query->andFilterWhere(['name' => 'repair']);
        $dataProviderStatus1->query->andFilterWhere(['status' => 1]);
        $dataProviderStatus1->query->andFilterWhere(['repair_group' => 1]);

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

    public function actionSummary()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $sql = "SELECT c.code,c.title,count(h.id) as total FROM helpdesk h
        LEFT JOIN categorise c ON c.code = h.status AND c.name = 'repair_status'
        where JSON_EXTRACT(h.data_json,'$.send_type') = 'general'
        GROUP BY c.id";

        $query = Yii::$app->db->createCommand($sql)->queryAll();
        return $query;
    }

}
