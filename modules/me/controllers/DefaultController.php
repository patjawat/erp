<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveSearch;
use app\modules\helpdesk\models\HelpdeskSearch;

/**
 * Default controller for the `me` module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        // clear session คลัง
        foreach (['sub-warehouse', 'main-warehouse', 'asset_type'] as $key) {
            Yii::$app->session->remove($key);
        }

        $model = UserHelper::GetEmployee();

        if ($model === null) {
            return $this->render('warning');
        }

        $info = $model->getInfo();
    //  return $model;
        if (empty(trim($model->position_name ?? ''))) {

            return $this->render('warning');
        }
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        //เช็คจากการตั้งค่าใน Employees ถ้าเป็น รพ.สต.
        if ($model->branch == 'BRANCH') {
            return $this->redirect(['/me/store-v2/dashboard']);
        }

        return $this->render('index', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionWarning()
    {
        return $this->render('warning');
    }

    public function actionV2()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v2', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionV3()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v3', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionTeam()
    {
        return $this->render('team_work', []);
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
