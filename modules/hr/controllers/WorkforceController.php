<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeTrainingPlan;
use app\modules\hr\models\IdpCycle;
use app\modules\hr\models\IdpPlan;
use app\modules\hr\models\TrainingRoadmap;
use app\modules\jd\models\JdEmployee;
use app\modules\jd\models\JdEmployeeAcknowledgement;

class WorkforceController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex($section = 'overview')
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูภาพรวมงานบุคลากร');
        }

        $allowedSections = ['overview', 'jd', 'appraisal', 'health', 'exit'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'overview';
        }

        $employeeCount = (int) Employees::find()->count();
        $activeJdCount = (int) JdEmployee::find()
            ->select('emp_id')
            ->where(['status' => JdEmployee::STATUS_ACTIVE])
            ->distinct()
            ->count();
        $acknowledgedJdCount = (int) JdEmployeeAcknowledgement::find()
            ->select('emp_id')
            ->distinct()
            ->count();
        $activeCycle = IdpCycle::current();
        $idpQuery = IdpPlan::find();
        if ($activeCycle) {
            $idpQuery->andWhere(['cycle_id' => $activeCycle->id]);
        }

        $metrics = [
            'employees' => $employeeCount,
            'jd_active' => $activeJdCount,
            'jd_pending' => max(0, $activeJdCount - $acknowledgedJdCount),
            'idp_total' => (int) (clone $idpQuery)->count(),
            'idp_action' => (int) (clone $idpQuery)->andWhere(['status' => ['draft', 'submitted', 'revision']])->count(),
            'trm_active' => (int) TrainingRoadmap::find()->where(['status' => 'active'])->count(),
            'trm_in_progress' => (int) EmployeeTrainingPlan::find()->where(['status' => ['assigned', 'in_progress', 'assessment']])->count(),
        ];

        return $this->render('index', [
            'section' => $section,
            'metrics' => $metrics,
            'activeCycle' => $activeCycle,
        ]);
    }
}
