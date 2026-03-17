<?php

namespace app\modules\amSurvey\controllers;

use Yii;
use yii\web\Controller;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\AssetSurveyItem;

/**
 * Dashboard and entry for am-survey module.
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(['dashboard']);
    }

    /**
     * Dashboard: widgets for total assets, surveyed, missing, location/department changes.
     */
    public function actionDashboard()
    {
        $surveyId = (int) (Yii::$app->request->get('survey_id') ?? 0);
        $survey = $surveyId ? AssetSurvey::findOne($surveyId) : AssetSurvey::find()->orderBy(['survey_year' => SORT_DESC])->one();

        $stats = [
            'totalItems' => 0,
            'found' => 0,
            'notFound' => 0,
            'newAsset' => 0,
            'locationMismatch' => 0,
            'departmentMismatch' => 0,
        ];

        if ($survey) {
            $query = AssetSurveyItem::find()->where(['survey_id' => $survey->id]);
            $stats['totalItems'] = (int) $query->count();
            $stats['found'] = (int) (clone $query)->andWhere(['found_status' => AssetSurveyItem::FOUND_STATUS_FOUND])->count();
            $stats['notFound'] = (int) (clone $query)->andWhere(['found_status' => AssetSurveyItem::FOUND_STATUS_NOT_FOUND])->count();
            $stats['newAsset'] = (int) (clone $query)->andWhere(['found_status' => AssetSurveyItem::FOUND_STATUS_NEW_ASSET])->count();
            $stats['locationMismatch'] = (int) (clone $query)->andWhere(['location_match' => false])->count();
            $stats['departmentMismatch'] = (int) (clone $query)->andWhere(['department_match' => false])->count();
        }

        $surveys = AssetSurvey::find()->orderBy(['survey_year' => SORT_DESC])->limit(10)->all();

        return $this->render('dashboard', [
            'survey' => $survey,
            'stats' => $stats,
            'surveys' => $surveys,
        ]);
    }
}
