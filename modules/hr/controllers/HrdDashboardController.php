<?php

namespace app\modules\hr\controllers;

use app\components\AppHelper;
use app\modules\hr\services\HrdMetricsService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * HRD Dashboard V2 — ภาพรวมการพัฒนาบุคลากร (ทักษะ / อบรม / IDP / ผู้สืบทอด)
 *
 * แยกจาก DefaultController::actionDashboard (ที่เน้นประชากรกำลังคน) เพราะเป็นคนละมุมมอง
 * route: hr/hrd-dashboard/* — whitelist ใน config/web.php allowActions + guard ภายในที่นี่
 */
class HrdDashboardController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ]);
    }

    /**
     * เฉพาะ HR / ผู้บริหาร ดูภาพรวมได้ (สอดคล้องกับ dashboard เดิม)
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $user = Yii::$app->user;
        if (!($user->can('hr') || $user->can('admin') || $user->can('director') || $user->can('hrViewDashboard'))) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงภาพรวมการพัฒนาบุคลากร');
        }
        return true;
    }

    public function actionIndex()
    {
        $currentFy = (int) AppHelper::YearBudget();
        $req = $this->request->get();
        $fy = isset($req['fy']) && ctype_digit((string) $req['fy']) ? (int) $req['fy'] : $currentFy;
        // จำกัดช่วงปีที่เลือกได้ (ปีนี้ย้อนหลัง 4 ปี)
        $fyOptions = range($currentFy, $currentFy - 4);
        if (!in_array($fy, $fyOptions, true)) {
            $fy = $currentFy;
        }

        $metrics = new HrdMetricsService($fy);
        $kpis = $metrics->kpis();
        $trend = $metrics->developmentTrend();

        return $this->render('index', [
            'fy' => $fy,
            'currentFy' => $currentFy,
            'fyOptions' => $fyOptions,
            'kpis' => $kpis,
            'trend' => $trend,
            'coverageThreshold' => HrdMetricsService::COVERAGE_THRESHOLD,
        ]);
    }
}
