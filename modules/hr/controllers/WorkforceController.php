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
use app\modules\approveV2\models\Approve;
use app\modules\jd\services\JdApprovalService;
use yii\data\ActiveDataProvider;

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
            'jd_approval_pending' => (int) JdEmployee::find()->where(['status' => JdEmployee::STATUS_PENDING])->count(),
            'jd_missing' => max(0, $employeeCount - (int) JdEmployee::find()->select('emp_id')->where(['status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING, JdEmployee::STATUS_ACTIVE]])->distinct()->count()),
            'idp_total' => (int) (clone $idpQuery)->count(),
            'idp_action' => (int) (clone $idpQuery)->andWhere(['status' => ['draft', 'submitted', 'revision']])->count(),
            'trm_active' => (int) TrainingRoadmap::find()->where(['status' => 'active'])->count(),
            'trm_in_progress' => (int) EmployeeTrainingPlan::find()->where(['status' => ['assigned', 'in_progress', 'assessment']])->count(),
        ];

        $jdDataProvider = null;
        $jdByEmployee = [];
        $approvalByJd = [];
        $acknowledgedJdIds = [];
        if ($section === 'jd') {
            $employeeQuery = Employees::find()
                ->with(['empDepartment', 'employeePosition', 'positionLevel'])
                ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC, 'id' => SORT_ASC]);
            $keyword = trim((string) Yii::$app->request->get('jd_q', ''));
            if ($keyword !== '') {
                $employeeQuery->andWhere(['or',
                    ['like', 'fname', $keyword],
                    ['like', 'lname', $keyword],
                    ['like', 'position_name', $keyword],
                ]);
            }

            $jdDataProvider = new ActiveDataProvider([
                'query' => $employeeQuery,
                'pagination' => ['pageSize' => 20, 'pageParam' => 'jd_page'],
                'sort' => false,
            ]);
            $employees = $jdDataProvider->getModels();
            $employeeIds = array_map(static fn(Employees $employee): int => (int) $employee->id, $employees);
            if ($employeeIds !== []) {
                $jdRows = JdEmployee::find()
                    ->where(['emp_id' => $employeeIds])
                    ->andWhere(['status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING, JdEmployee::STATUS_ACTIVE]])
                    ->orderBy(['emp_id' => SORT_ASC, 'revision_no' => SORT_DESC, 'id' => SORT_DESC])
                    ->all();
                foreach ($jdRows as $jdRow) {
                    $jdByEmployee[(int) $jdRow->emp_id] ??= $jdRow;
                }

                $jdIds = array_map(static fn(JdEmployee $jd): int => (int) $jd->id, array_values($jdByEmployee));
                if ($jdIds !== []) {
                    foreach (Approve::find()
                        ->where(['name' => JdApprovalService::APPROVE_NAME, 'from_id' => array_map('strval', $jdIds)])
                        ->orderBy(['level' => SORT_ASC])
                        ->all() as $approval) {
                        $approvalByJd[(int) $approval->from_id][] = $approval;
                    }
                    $acknowledgedJdIds = array_fill_keys(array_map('intval', JdEmployeeAcknowledgement::find()
                        ->select('jd_employee_id')
                        ->where(['jd_employee_id' => $jdIds])
                        ->column()), true);
                }
            }
        }

        return $this->render('index', [
            'section' => $section,
            'metrics' => $metrics,
            'activeCycle' => $activeCycle,
            'jdDataProvider' => $jdDataProvider,
            'jdByEmployee' => $jdByEmployee,
            'approvalByJd' => $approvalByJd,
            'acknowledgedJdIds' => $acknowledgedJdIds,
        ]);
    }
}
