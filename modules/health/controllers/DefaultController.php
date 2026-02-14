<?php

namespace app\modules\health\controllers;

use app\components\AppHelper;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use app\modules\hr\models\EmployeeDetailSearch;
use yii\web\Controller;

/**
 * Default controller for the `health` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
     public function actionIndex()
    {
        $searchModel = new HealthScreenSearch(['thai_year' => AppHelper::YearBudget(date('Y-m-d'))]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        $bmiData = $searchModel->getBmiChartData();
        $stats = $searchModel->getDeptExamStats();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
           'bmiData' => $bmiData,
           'stats' => $stats
        //    'deptCategories' => $stats['categories'],
        //     'deptSuccess'    => $stats['success'],
        //     'deptPending'    => $stats['pending'],
        ]);
    }

         public function actionList()
    {
        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
