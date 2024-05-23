<?php

namespace app\controllers;

use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;
use app\modules\hr\models\Employees;
use yii\web\Response;
use Yii;

class MeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        return $this->render('index', [
            'model' => $model ? $model : new Employees()
        ]);
    }

    public function actionRepairMe()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('repair_me', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }
}
