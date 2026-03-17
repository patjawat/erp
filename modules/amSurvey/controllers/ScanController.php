<?php

namespace app\modules\amSurvey\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\AssetSurveyItem;
use app\modules\amSurvey\services\SurveyCompareService;
use app\modules\hr\models\Organization;

/**
 * Web survey: select survey, search asset, confirm location/department, save.
 */
class ScanController extends Controller
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

    /**
     * Page: /am-survey/scan
     * Select survey, search by asset number, confirm and save.
     */
    public function actionIndex()
    {
        $surveyId = (int) Yii::$app->request->get('survey_id');
        $survey = $surveyId ? AssetSurvey::findOne($surveyId) : null;
        $surveys = AssetSurvey::find()->andWhere(['status' => AssetSurvey::STATUS_ACTIVE])->orderBy(['survey_year' => SORT_DESC])->all();

        return $this->render('index', [
            'survey' => $survey,
            'surveys' => $surveys,
        ]);
    }

    /**
     * AJAX: search asset by number and return compare result for form.
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $assetNumber = trim((string) Yii::$app->request->get('q'));
        if ($assetNumber === '') {
            return ['ok' => false, 'message' => 'กรุณาระบุหมายเลขครุภัณฑ์'];
        }

        $compare = SurveyCompareService::compare($assetNumber, null, null);
        $asset = $compare['asset'];

        $data = [
            'ok' => true,
            'found' => $asset !== null,
            'found_status' => $compare['found_status'],
            'asset_id' => $asset ? $asset->id : null,
            'code' => $asset ? $asset->code : null,
            'asset_name' => $asset ? ($asset->asset_name ?? $asset->code) : null,
            'current_department_id' => $asset ? $asset->department : null,
            'current_department_name' => $asset ? $asset->departmentName() : null,
            'current_location' => $asset && isset($asset->data_json['location']) ? $asset->data_json['location'] : null,
        ];

        return $data;
    }

    /**
     * POST: save one survey record (web method).
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $surveyId = (int) Yii::$app->request->post('survey_id');
        $assetNumber = trim((string) Yii::$app->request->post('scanned_asset_number'));
        $surveyDepartmentId = Yii::$app->request->post('survey_department_id') ? (int) Yii::$app->request->post('survey_department_id') : null;
        $surveyLocation = trim((string) (Yii::$app->request->post('survey_location') ?? ''));

        if (!$surveyId || $assetNumber === '') {
            return ['ok' => false, 'message' => 'กรุณาเลือกโครงการสำรวจและระบุหมายเลขครุภัณฑ์'];
        }

        $survey = AssetSurvey::findOne($surveyId);
        if (!$survey) {
            return ['ok' => false, 'message' => 'ไม่พบโครงการสำรวจ'];
        }

        $compare = SurveyCompareService::compare($assetNumber, $surveyDepartmentId, $surveyLocation !== '' ? $surveyLocation : null);
        $item = SurveyCompareService::createSurveyItem(
            $surveyId,
            $assetNumber,
            $compare,
            AssetSurveyItem::METHOD_WEB,
            $surveyDepartmentId,
            null,
            $surveyLocation !== '' ? $surveyLocation : null,
            Yii::$app->request->post('remark'),
            Yii::$app->user->isGuest ? null : Yii::$app->user->id
        );

        return ['ok' => true, 'message' => 'บันทึกผลสำรวจเรียบร้อย', 'item_id' => $item->id];
    }

    /**
     * Return department list for dropdown (tree).
     */
    public function actionDepartments()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $list = Organization::find()->select(['id', 'name'])->orderBy('lft')->asArray()->all();
        return array_map(function ($r) {
            return ['id' => (int) $r['id'], 'name' => $r['name']];
        }, $list);
    }
}
