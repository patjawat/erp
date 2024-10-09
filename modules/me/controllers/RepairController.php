<?php

namespace app\modules\me\controllers;

use app\modules\helpdesk\models\HelpdeskSearch;
use yii\web\Controller;

class RepairController extends Controller
{
    public function actionIndex()
    {
        $userId = \Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summary' => $dataProvider->getTotalCount(),
        ]);
    }
}
