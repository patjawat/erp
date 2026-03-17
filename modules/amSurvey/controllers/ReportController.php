<?php

namespace app\modules\amSurvey\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\AssetSurveyItem;

/**
 * Survey reports: summary, department status, missing assets, relocated.
 */
class ReportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionSummary($survey_id)
    {
        $survey = $this->findSurvey($survey_id);

        $items = AssetSurveyItem::find()->where(['survey_id' => $survey->id])->with('asset')->all();
        $byStatus = [
            AssetSurveyItem::FOUND_STATUS_FOUND => 0,
            AssetSurveyItem::FOUND_STATUS_NOT_FOUND => 0,
            AssetSurveyItem::FOUND_STATUS_NEW_ASSET => 0,
        ];
        $locationMismatch = 0;
        $departmentMismatch = 0;
        foreach ($items as $item) {
            $byStatus[$item->found_status] = ($byStatus[$item->found_status] ?? 0) + 1;
            if ($item->location_match === false) {
                $locationMismatch++;
            }
            if ($item->department_match === false) {
                $departmentMismatch++;
            }
        }

        return $this->render('summary', [
            'survey' => $survey,
            'byStatus' => $byStatus,
            'locationMismatch' => $locationMismatch,
            'departmentMismatch' => $departmentMismatch,
            'total' => count($items),
        ]);
    }

    public function actionMissing($survey_id)
    {
        $survey = $this->findSurvey($survey_id);
        $query = AssetSurveyItem::find()
            ->where(['survey_id' => $survey->id, 'found_status' => AssetSurveyItem::FOUND_STATUS_NOT_FOUND])
            ->orderBy(['scanned_asset_number' => SORT_ASC]);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('missing', ['survey' => $survey, 'dataProvider' => $dataProvider]);
    }

    public function actionRelocated($survey_id)
    {
        $survey = $this->findSurvey($survey_id);
        $query = AssetSurveyItem::find()
            ->where(['survey_id' => $survey->id])
            ->andWhere(['or', ['location_match' => false], ['department_match' => false]])
            ->with('asset', 'surveyDepartment')
            ->orderBy(['scanned_at' => SORT_DESC]);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('relocated', ['survey' => $survey, 'dataProvider' => $dataProvider]);
    }

    public function actionDepartment($survey_id)
    {
        $survey = $this->findSurvey($survey_id);

        $raw = Yii::$app->db->createCommand(
            'SELECT survey_department_id, COUNT(*) as cnt FROM {{%am_asset_survey_items}} WHERE survey_id = :sid GROUP BY survey_department_id',
            [':sid' => $survey->id]
        )->queryAll();

        $departments = [];
        foreach ($raw as $r) {
            $departments[$r['survey_department_id'] ?? 0] = (int) $r['cnt'];
        }

        return $this->render('department', ['survey' => $survey, 'departments' => $departments]);
    }

    private function findSurvey($id)
    {
        $survey = AssetSurvey::findOne($id);
        if (!$survey) {
            throw new NotFoundHttpException('ไม่พบโครงการสำรวจ');
        }
        return $survey;
    }
}
