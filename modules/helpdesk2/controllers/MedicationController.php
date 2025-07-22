<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\db\Expression;
use app\components\AppHelper;
use app\components\DateFilterHelper;
use app\modules\helpdesk\models\HelpdeskSearch;

class MedicationController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 3,
            'date_filter' => 'this_month',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', 'title', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
    if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        $dataProvider->query->andFilterWhere(['between', new \yii\db\Expression('DATE(created_at)'), AppHelper::convertToGregorian($searchModel->date_start),AppHelper::convertToGregorian($searchModel->date_end)]);

 
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

            return $this->render('index', [
                'title' => 'ศูนย์งานซ่อมบำรุง',
                'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
            ]);

    }

    
}
