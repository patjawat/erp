<?php

namespace app\modules\lm\controllers;
use yii\web\Controller;
use app\components\AppHelper;
use app\modules\lm\models\LeaveSummary;
use app\modules\lm\models\LeaveSummarySearch;

/**
 * Default controller for the `leave` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveSummarySearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->groupBy('code');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
