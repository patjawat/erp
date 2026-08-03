<?php

namespace app\modules\plan\controllers;

use yii\web\Controller;
use app\components\AppHelper;
use app\modules\plan\models\PlanOrderSearch;

/**
 * Default controller for the `plan` module
 */
class DashboardController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
         $searchModel = new PlanOrderSearch([
            'thai_year' =>  \app\modules\plan\components\PlanHelper::currentPlanYear()
         ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['plan_group_id' => 'expenses']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
