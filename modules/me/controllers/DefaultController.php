<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveSearch;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

/**
 * Default controller for the `me` module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        
        return $this->render('index', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);

    }

    public function actionTeam()
    {
        return $this->render('team_work', [
           
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

    public function actionRepair()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        // $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        $dataProvider->pagination->pageSize = 4;
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('activity', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }

    
}
