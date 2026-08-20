<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Controller;
use app\components\UserHelper;
use app\modules\booking\models\VehicleDetail;

/**
 * แบบประเมินความพึงพอใจการใช้รถ (เปิดจากลิงก์ใน Telegram)
 *
 * เข้าถึงได้โดยไม่ต้องล็อกอิน — สิทธิ์มาจาก token ใน vehicle_detail.data_json.survey_token
 * (ประกาศไว้ใน config/web.php > as access > allowActions)
 */
class VehicleSurveyController extends Controller
{
    public $layout = '@app/views/layouts/blank';

    public function actionIndex($token = null)
    {
        $this->view->title = 'ประเมินความพึงพอใจการใช้รถ';

        $detail = VehicleDetail::findBySurveyToken((string) $token);
        if (!$detail || !$detail->vehicle) {
            return $this->render('invalid', [
                'message' => 'ลิงก์แบบประเมินไม่ถูกต้องหรือหมดอายุแล้ว',
            ]);
        }

        if ($detail->status !== VehicleDetail::STATUS_SUCCESS) {
            return $this->render('invalid', [
                'message' => 'ภารกิจนี้ยังไม่ถูกบันทึกว่าเสร็จสิ้น จึงยังประเมินไม่ได้',
            ]);
        }

        if ($detail->isSurveyed()) {
            return $this->render('done', ['model' => $detail]);
        }

        $error = null;
        if (Yii::$app->request->isPost) {
            $score = (int) Yii::$app->request->post('score', 0);
            $comment = trim((string) Yii::$app->request->post('comment', ''));
            if ($score < 1 || $score > 5) {
                $error = 'กรุณาเลือกระดับความพึงพอใจ 1 ถึง 5 ดาว';
            } else {
                $me = null;
                try {
                    $me = Yii::$app->user->isGuest ? null : UserHelper::GetEmployee();
                } catch (\Throwable $e) {
                    $me = null;
                }
                $byEmpId = $me->id ?? $detail->vehicle->emp_id;
                if ($detail->saveSatisfaction($score, $comment, $byEmpId)) {
                    return $this->render('done', ['model' => $detail]);
                }
                $error = 'บันทึกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง';
            }
        }

        return $this->render('index', [
            'model' => $detail,
            'token' => $detail->surveyToken(),
            'error' => $error,
        ]);
    }

}
