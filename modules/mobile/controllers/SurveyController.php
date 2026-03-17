<?php

namespace app\modules\mobile\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\AssetSurveyItem;
use app\modules\amSurvey\services\SurveyCompareService;

/**
 * API for mobile QR survey scan.
 * POST /mobile/survey/scan
 * Body/params: asset_number, survey_id, location_id (optional), department_id (optional)
 */
class SurveyController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => ['application/json' => Response::FORMAT_JSON],
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => ['scan' => ['post']],
            ],
        ];
    }

    /**
     * POST /mobile/survey/scan
     * Input: asset_number, survey_id, location_id (optional), department_id (optional)
     */
    public function actionScan()
    {
        $assetNumber = trim((string) (Yii::$app->request->post('asset_number') ?? Yii::$app->request->get('asset_number') ?? ''));
        $surveyId = (int) (Yii::$app->request->post('survey_id') ?? Yii::$app->request->get('survey_id') ?? 0);
        $departmentId = Yii::$app->request->post('department_id') ?? Yii::$app->request->get('department_id');
        $departmentId = $departmentId !== null && $departmentId !== '' ? (int) $departmentId : null;
        $locationText = trim((string) (Yii::$app->request->post('location_id') ?? Yii::$app->request->post('survey_location') ?? ''));

        if ($assetNumber === '') {
            return ['success' => false, 'message' => 'กรุณาระบุหมายเลขครุภัณฑ์'];
        }
        if (!$surveyId) {
            return ['success' => false, 'message' => 'กรุณาระบุ survey_id'];
        }

        $survey = AssetSurvey::findOne($surveyId);
        if (!$survey) {
            return ['success' => false, 'message' => 'ไม่พบโครงการสำรวจ'];
        }
        if ($survey->status !== AssetSurvey::STATUS_ACTIVE) {
            return ['success' => false, 'message' => 'โครงการสำรวจไม่เปิดรับข้อมูล'];
        }

        $compare = SurveyCompareService::compare($assetNumber, $departmentId, $locationText !== '' ? $locationText : null);
        $item = SurveyCompareService::createSurveyItem(
            $surveyId,
            $assetNumber,
            $compare,
            AssetSurveyItem::METHOD_QRCODE,
            $departmentId,
            null,
            $locationText !== '' ? $locationText : null,
            null,
            Yii::$app->user->isGuest ? null : Yii::$app->user->id
        );

        return [
            'success' => true,
            'message' => 'บันทึกผลสำรวจเรียบร้อย',
            'item_id' => $item->id,
            'found_status' => $item->found_status,
            'location_match' => $item->location_match,
            'department_match' => $item->department_match,
        ];
    }
}
