<?php

namespace app\modules\inventory\controllers;
use Yii;
use app\components\AppHelper;
use app\modules\inventory\models\StockSummary;
use app\modules\inventory\models\StockSummarySearch;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockSummarySearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
         $dataProvider = $searchModel->search($this->request->queryParams);
         $dataProvider->query->groupBy('type_code');
         
         return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
