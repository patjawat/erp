<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

class MedicalController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 3,
             'auth_item' => 'medication'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('@app/modules/helpdesk/views/repair/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'title' => 'ศูนย์เครื่องมืออพทย์',
                    'icon' => '<i class="fa-solid fa-briefcase-medical"></i>',
                ]),
            ];
        } else {
            return $this->render('@app/modules/helpdesk/views/repair/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => 'ศูนย์เครื่องมือแพทย์',
                'icon' => '<i class="fa-solid fa-briefcase-medical"></i>',
            ]);
        }
    }


}
