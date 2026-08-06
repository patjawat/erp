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
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeeType;
use app\modules\kpi\models\KpiCycle;
use app\modules\kpi\models\KpiItem;
use app\modules\kpi\services\KpiService;
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

        if ($section === 'health') {
            return $this->redirect(['/health/default/index']);
        }

        $allowedSections = ['overview', 'jd', 'kpi', 'appraisal', 'exit'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'overview';
        }

        // ค่าเริ่มต้น: นับ/แสดงเฉพาะบุคลากรที่ยังปฏิบัติงาน (status=1) — ติ๊ก "แสดงทั้งหมด" เพื่อรวมทุกคน
        $showAll = (int) Yii::$app->request->get('show_all') === 1;
        $activeEmpIds = Employees::find()->select('id')->where(['status' => Employees::STATUS_WORKING])->column();
        $activeEmpIds = array_map('intval', $activeEmpIds);
        $employeeCount = count($activeEmpIds); // เฉพาะที่ปฏิบัติงาน

        $activeJdCount = (int) JdEmployee::find()
            ->select('emp_id')
            ->where(['status' => JdEmployee::STATUS_ACTIVE, 'emp_id' => $activeEmpIds])
            ->distinct()
            ->count();
        $acknowledgedJdCount = (int) JdEmployeeAcknowledgement::find()
            ->select('emp_id')
            ->where(['emp_id' => $activeEmpIds])
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
            'jd_approval_pending' => (int) JdEmployee::find()->where(['status' => JdEmployee::STATUS_PENDING, 'emp_id' => $activeEmpIds])->count(),
            'jd_missing' => max(0, $employeeCount - (int) JdEmployee::find()->select('emp_id')->where(['status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING, JdEmployee::STATUS_ACTIVE], 'emp_id' => $activeEmpIds])->distinct()->count()),
            'idp_total' => (int) (clone $idpQuery)->count(),
            'idp_action' => (int) (clone $idpQuery)->andWhere(['status' => ['draft', 'submitted', 'revision']])->count(),
            'trm_active' => (int) TrainingRoadmap::find()->where(['status' => 'active'])->count(),
            'trm_in_progress' => (int) EmployeeTrainingPlan::find()->where(['status' => ['assigned', 'in_progress', 'assessment']])->count(),
        ];

        $jdDataProvider = null;
        $jdByEmployee = [];
        $approvalByJd = [];
        $acknowledgedJdIds = [];
        $jdDepartments = [];
        $jdPositions = [];
        $jdEmployeeTypes = [];
        if ($section === 'jd') {
            $jdDepartments = Organization::find()
                ->where(['>=', 'lvl', 1])
                ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
                ->all();
            $jdPositions = EmployeePosition::listItems();
            $jdEmployeeTypes = EmployeeType::listItems();

            $employeeQuery = Employees::find()
                ->with(['empDepartment', 'employeePosition', 'positionLevel'])
                ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC, 'id' => SORT_ASC]);
            if (!$showAll) {
                $employeeQuery->andWhere(['status' => Employees::STATUS_WORKING]);
            }
            $keyword = trim((string) Yii::$app->request->get('jd_q', ''));
            if ($keyword !== '') {
                $employeeQuery->andWhere(['or',
                    ['like', 'fname', $keyword],
                    ['like', 'lname', $keyword],
                    ['like', 'position_name', $keyword],
                ]);
            }

            $departmentId = (int) Yii::$app->request->get('jd_department');
            if ($departmentId > 0) {
                $department = Organization::findOne($departmentId);
                if ($department) {
                    $departmentIds = Organization::find()
                        ->select('id')
                        ->where(['root' => $department->root])
                        ->andWhere(['>=', 'lft', $department->lft])
                        ->andWhere(['<=', 'rgt', $department->rgt])
                        ->column();
                    $employeeQuery->andWhere(['department' => $departmentIds ?: [$departmentId]]);
                }
            }

            $positionId = (int) Yii::$app->request->get('jd_position');
            if ($positionId > 0) {
                $employeeQuery->andWhere(['employee_position_id' => $positionId]);
            }

            $employeeTypeId = (int) Yii::$app->request->get('jd_employee_type');
            if ($employeeTypeId > 0) {
                $employeeQuery->andWhere(['employee_type_id' => $employeeTypeId]);
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

        // ===== KPI (section = kpi) =====
        $kpiDataProvider = null;
        $kpiByEmployee = [];   // emp_id => KpiCycle (ปีล่าสุด)
        $kpiItemCounts = [];   // cycle_id => จำนวน KPI (active)
        $departments = [];
        $currentFy = KpiService::currentFiscalYear();
        if ($section === 'kpi') {
            $departments = Organization::find()
                ->where(['>=', 'lvl', 1])
                ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
                ->all();

            $employeeQuery = Employees::find()
                ->with(['empDepartment', 'employeePosition', 'positionLevel'])
                ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC, 'id' => SORT_ASC]);
            if (!$showAll) {
                $employeeQuery->andWhere(['status' => Employees::STATUS_WORKING]);
            }

            $keyword = trim((string) Yii::$app->request->get('kpi_q', ''));
            if ($keyword !== '') {
                $employeeQuery->andWhere(['or',
                    ['like', 'fname', $keyword],
                    ['like', 'lname', $keyword],
                    ['like', 'position_name', $keyword],
                ]);
            }

            $depId = (int) Yii::$app->request->get('kpi_dep');
            if ($depId > 0) {
                $node = Organization::findOne($depId);
                if ($node) {
                    $subtreeIds = Organization::find()
                        ->select('id')
                        ->where(['root' => $node->root])
                        ->andWhere(['>=', 'lft', $node->lft])
                        ->andWhere(['<=', 'rgt', $node->rgt])
                        ->column();
                    $employeeQuery->andWhere(['department' => $subtreeIds ?: [$depId]]);
                }
            }

            $kpiDataProvider = new ActiveDataProvider([
                'query' => $employeeQuery,
                'pagination' => ['pageSize' => 20, 'pageParam' => 'kpi_page'],
                'sort' => false,
            ]);
            $employees = $kpiDataProvider->getModels();
            $employeeIds = array_map(static fn(Employees $e): int => (int) $e->id, $employees);
            if ($employeeIds !== []) {
                foreach (KpiCycle::find()
                    ->where(['emp_id' => $employeeIds])
                    ->orderBy(['fiscal_year' => SORT_DESC, 'id' => SORT_DESC])
                    ->all() as $cycle) {
                    $kpiByEmployee[(int) $cycle->emp_id] ??= $cycle;
                }
                $cycleIds = array_map(static fn(KpiCycle $c): int => (int) $c->id, array_values($kpiByEmployee));
                if ($cycleIds !== []) {
                    foreach (KpiItem::find()
                        ->select(['cycle_id', 'cnt' => 'COUNT(*)'])
                        ->where(['cycle_id' => $cycleIds, 'status' => KpiItem::STATUS_ACTIVE])
                        ->groupBy('cycle_id')
                        ->asArray()
                        ->all() as $row) {
                        $kpiItemCounts[(int) $row['cycle_id']] = (int) $row['cnt'];
                    }
                }
            }

            $metrics['kpi_current'] = (int) KpiCycle::find()->where(['fiscal_year' => $currentFy, 'emp_id' => $activeEmpIds])->count();
            $metrics['kpi_active'] = (int) KpiCycle::find()->where(['fiscal_year' => $currentFy, 'status' => KpiCycle::STATUS_ACTIVE, 'emp_id' => $activeEmpIds])->count();
            $metrics['kpi_pending'] = (int) KpiCycle::find()->where(['fiscal_year' => $currentFy, 'status' => [KpiCycle::STATUS_DRAFT, KpiCycle::STATUS_PENDING], 'emp_id' => $activeEmpIds])->count();
            $metrics['kpi_missing'] = max(0, $employeeCount - $metrics['kpi_current']);
        }

        return $this->render('index', [
            'section' => $section,
            'metrics' => $metrics,
            'activeCycle' => $activeCycle,
            'jdDataProvider' => $jdDataProvider,
            'jdByEmployee' => $jdByEmployee,
            'approvalByJd' => $approvalByJd,
            'acknowledgedJdIds' => $acknowledgedJdIds,
            'jdDepartments' => $jdDepartments,
            'jdPositions' => $jdPositions,
            'jdEmployeeTypes' => $jdEmployeeTypes,
            'kpiDataProvider' => $kpiDataProvider,
            'kpiByEmployee' => $kpiByEmployee,
            'kpiItemCounts' => $kpiItemCounts,
            'departments' => $departments,
            'currentFy' => $currentFy,
            'showAll' => $showAll,
        ]);
    }
}
