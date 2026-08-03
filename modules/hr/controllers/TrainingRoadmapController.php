<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TrainingRoadmap;
use app\modules\hr\models\TrainingRoadmapPhase;
use app\modules\hr\models\TrainingRoadmapActivity;
use app\modules\hr\models\TrainingRoadmapMilestone;
use app\modules\hr\models\EmployeeTrainingPlan;
use app\modules\hr\models\EmployeeTrainingResult;

class TrainingRoadmapController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['bulk-assign' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->assertCanManageRoadmaps();
        $q = trim((string) Yii::$app->request->get('q'));
        $planStatus = trim((string) Yii::$app->request->get('status'));
        $newHireSince = date('Y-m-d', strtotime('-90 days'));

        $newHireQuery = Employees::find()->alias('e')
            ->where(['e.status' => 1])
            ->andWhere(['not', ['e.id' => 1]])
            ->andWhere(['>=', 'e.join_date', $newHireSince]);
        $assignedEmployeeIds = (new Query())
            ->select('emp_id')
            ->from(EmployeeTrainingPlan::tableName())
            ->where(['not in', 'status', ['cancelled']]);
        $unassignedQuery = (clone $newHireQuery)
            ->andWhere(['not in', 'e.id', $assignedEmployeeIds])
            ->with(['empDepartment'])
            ->orderBy(['e.join_date' => SORT_DESC, 'e.id' => SORT_DESC]);

        $planQuery = EmployeeTrainingPlan::find()->alias('p')
            ->with(['employee.empDepartment', 'roadmap', 'mentor', 'assessor']);
        if ($q !== '') {
            $planQuery->innerJoin(['e' => Employees::tableName()], 'e.id = p.emp_id')
                ->andWhere(['or', ['like', 'e.fname', $q], ['like', 'e.lname', $q]]);
        }
        if ($planStatus !== '') {
            $planQuery->andWhere(['p.status' => $planStatus]);
        } else {
            $planQuery->andWhere(['p.status' => ['assigned', 'in_progress', 'assessment', 'paused']]);
        }

        $metrics = [
            'new_hires' => (int) (clone $newHireQuery)->count(),
            'unassigned' => (int) (clone $unassignedQuery)->count(),
            'in_progress' => (int) EmployeeTrainingPlan::find()->where(['status' => ['assigned', 'in_progress', 'assessment']])->count(),
            'overdue' => (int) EmployeeTrainingPlan::find()
                ->where(['status' => ['assigned', 'in_progress', 'assessment', 'paused']])
                ->andWhere(['<', 'target_end_date', date('Y-m-d')])
                ->count(),
        ];

        return $this->render('index', [
            'metrics' => $metrics,
            'newHireSince' => $newHireSince,
            'unassignedProvider' => new ActiveDataProvider([
                'query' => $unassignedQuery,
                'pagination' => ['pageSize' => 10, 'pageParam' => 'new-page'],
            ]),
            'planProvider' => new ActiveDataProvider([
                'query' => $planQuery->orderBy(['p.updated_at' => SORT_DESC, 'p.id' => SORT_DESC]),
                'pagination' => ['pageSize' => 15, 'pageParam' => 'plan-page'],
            ]),
            'roadmapItems' => ArrayHelper::map(
                TrainingRoadmap::find()->where(['not in', 'status', ['retired']])->orderBy(['title' => SORT_ASC])->all(),
                'id',
                static fn(TrainingRoadmap $model) => $model->code . ' · ' . $model->title
            ),
            'q' => $q,
            'planStatus' => $planStatus,
        ]);
    }

    public function actionTemplates()
    {
        $this->assertCanManageRoadmaps();
        $query = TrainingRoadmap::find()->with(['phases.activities']);
        $q = trim((string) Yii::$app->request->get('q'));
        $status = Yii::$app->request->get('status');
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'code', $q], ['like', 'title', $q]]);
        }
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['updated_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('templates', compact('dataProvider', 'q', 'status'));
    }

    public function actionView($id)
    {
        $this->assertCanManageRoadmaps();
        return $this->render('view', ['model' => $this->findRoadmap($id)]);
    }

    public function actionCreate()
    {
        $this->assertCanManageRoadmaps();
        $model = new TrainingRoadmap([
            'roadmap_type' => 'professional', 'version_no' => 1, 'duration_value' => 90,
            'duration_unit' => 'day', 'status' => 'draft',
        ]);
        return $this->saveRoadmap($model);
    }

    public function actionUpdate($id)
    {
        $this->assertCanManageRoadmaps();
        return $this->saveRoadmap($this->findRoadmap($id));
    }

    public function actionPhase($roadmap_id, $id = null)
    {
        $this->assertCanManageRoadmaps();
        $roadmap = $this->findRoadmap($roadmap_id);
        $model = $id ? $this->findPhase($id) : new TrainingRoadmapPhase([
            'roadmap_id' => $roadmap->id,
            'sequence' => ((int) TrainingRoadmapPhase::find()->where(['roadmap_id' => $roadmap->id])->max('sequence')) + 1,
            'start_offset' => 0, 'offset_unit' => 'day', 'color_role' => 'primary',
        ]);
        if ((int) $model->roadmap_id !== (int) $roadmap->id) {
            throw new NotFoundHttpException('ไม่พบระยะพัฒนาของ Roadmap นี้');
        }
        return $this->saveModalModel($model, '_phase_form', 'บันทึกระยะพัฒนาเรียบร้อย', '#roadmap-builder');
    }

    public function actionActivity($phase_id, $id = null)
    {
        $this->assertCanManageRoadmaps();
        $phase = $this->findPhase($phase_id);
        $model = $id ? $this->findActivity($id) : new TrainingRoadmapActivity([
            'phase_id' => $phase->id,
            'sequence' => ((int) TrainingRoadmapActivity::find()->where(['phase_id' => $phase->id])->max('sequence')) + 1,
            'activity_type' => 'practice', 'requirement_type' => 'pass_fail',
            'is_required' => 1, 'evidence_required' => 0,
        ]);
        if ((int) $model->phase_id !== (int) $phase->id) {
            throw new NotFoundHttpException('ไม่พบกิจกรรมของระยะนี้');
        }
        return $this->saveModalModel($model, '_activity_form', 'บันทึกกิจกรรมเรียบร้อย', '#roadmap-builder');
    }

    public function actionMilestone($roadmap_id, $id = null)
    {
        $this->assertCanManageRoadmaps();
        $roadmap = $this->findRoadmap($roadmap_id);
        $model = $id ? $this->findMilestone($id) : new TrainingRoadmapMilestone([
            'roadmap_id' => $roadmap->id,
            'sequence' => ((int) TrainingRoadmapMilestone::find()->where(['roadmap_id' => $roadmap->id])->max('sequence')) + 1,
            'due_offset' => 30, 'offset_unit' => 'day', 'requires_signoff' => 1,
        ]);
        if ((int) $model->roadmap_id !== (int) $roadmap->id) {
            throw new NotFoundHttpException('ไม่พบจุดประเมินของ Roadmap นี้');
        }
        return $this->saveModalModel($model, '_milestone_form', 'บันทึกจุดประเมินเรียบร้อย', '#roadmap-builder');
    }

    public function actionAssign($roadmap_id = null, $emp_id = null)
    {
        $roadmap = $roadmap_id ? $this->findRoadmap($roadmap_id) : null;
        $model = new EmployeeTrainingPlan([
            'roadmap_id' => $roadmap?->id,
            'emp_id' => $emp_id,
            'start_date' => date('Y-m-d'),
            'status' => 'assigned',
            'progress_percent' => 0,
        ]);
        if ($model->load(Yii::$app->request->post())) {
            $roadmap = $this->findRoadmap($model->roadmap_id);
            $employee = Employees::findOne($model->emp_id);
            if (!$employee) throw new NotFoundHttpException('ไม่พบบุคลากร');
            $this->assertCanAssignEmployee($employee);
            if ($this->hasOpenPlan($model->emp_id)) {
                $model->addError('emp_id', 'บุคลากรรายนี้มี Training Roadmap ที่กำลังดำเนินการอยู่แล้ว');
            }
        } elseif ($emp_id) {
            $employee = Employees::findOne($emp_id);
            if (!$employee) throw new NotFoundHttpException('ไม่พบบุคลากร');
            $this->assertCanAssignEmployee($employee);
        } else {
            $this->assertCanManageRoadmaps();
        }
        if (!$model->hasErrors() && Yii::$app->request->isPost && $model->validate()) {
            $this->createEmployeePlan($model, $roadmap);
            if (Yii::$app->request->isAjax) {
                return $this->jsonSuccess('มอบหมาย Training Roadmap เรียบร้อย', '#roadmap-assignments');
            }
            Yii::$app->session->setFlash('success', 'มอบหมาย Training Roadmap เรียบร้อย');
            return $this->redirect(['index']);
        }
        return $this->modalOrPage('_assign_form', [
            'model' => $model,
            'roadmap' => $roadmap,
            'employeeItems' => ArrayHelper::map(Employees::find()->where(['status' => 1])->orderBy(['fname' => SORT_ASC])->all(), 'id', 'fullname'),
            'roadmapItems' => ArrayHelper::map(
                TrainingRoadmap::find()->where(['not in', 'status', ['retired']])->orderBy(['title' => SORT_ASC])->all(),
                'id',
                static fn(TrainingRoadmap $item) => $item->code . ' · ' . $item->title
            ),
        ], 'มอบหมาย Training Roadmap');
    }

    public function actionBulkAssign()
    {
        $this->assertCanManageRoadmaps();
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', (array) Yii::$app->request->post('emp_ids')))));
        $roadmapId = (int) Yii::$app->request->post('roadmap_id');
        $startDate = (string) Yii::$app->request->post('start_date', date('Y-m-d'));
        if (!$employeeIds || !$roadmapId) {
            Yii::$app->session->setFlash('warning', 'กรุณาเลือกบุคลากรและ Training Roadmap');
            return $this->redirect(['index']);
        }
        $roadmap = $this->findRoadmap($roadmapId);
        $assigned = 0;
        $skipped = 0;
        foreach (Employees::find()->where(['id' => $employeeIds, 'status' => 1])->all() as $employee) {
            if ($this->hasOpenPlan($employee->id)) {
                $skipped++;
                continue;
            }
            $this->createEmployeePlan(new EmployeeTrainingPlan([
                'emp_id' => $employee->id,
                'roadmap_id' => $roadmap->id,
                'start_date' => $startDate,
                'status' => 'assigned',
                'progress_percent' => 0,
            ]), $roadmap);
            $assigned++;
        }
        Yii::$app->session->setFlash('success', "มอบหมาย TRM สำเร็จ {$assigned} คน" . ($skipped ? " ข้าม {$skipped} คนที่มีแผนอยู่แล้ว" : ''));
        return $this->redirect(['index']);
    }

    public function actionEmployee($emp_id)
    {
        $employee = Employees::findOne($emp_id);
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากร');
        }
        $me = UserHelper::GetEmployee();
        $isLeader = $me && (int) ($employee->leader()?->id ?? 0) === (int) $me->id;
        if (!$this->canManageRoadmaps() && (!$me || ((int) $me->id !== (int) $employee->id && !$isLeader))) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากร');
        }
        $plans = EmployeeTrainingPlan::find()->where(['emp_id' => $emp_id])
            ->with(['roadmap.phases.activities', 'results'])->orderBy(['id' => SORT_DESC])->all();
        return $this->render('employee', ['employee' => $employee, 'plans' => $plans, 'isSelfProfile' => false]);
    }

    public function actionMy()
    {
        $employee = UserHelper::GetEmployee();
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากรที่เชื่อมกับบัญชีนี้');
        }
        $plans = EmployeeTrainingPlan::find()->where(['emp_id' => $employee->id])
            ->with(['roadmap.phases.activities', 'results'])->orderBy(['id' => SORT_DESC])->all();
        return $this->render('employee', ['employee' => $employee, 'plans' => $plans, 'isSelfProfile' => true]);
    }

    public function actionPlan($id)
    {
        $model = EmployeeTrainingPlan::find()->where(['id' => $id])->with(['employee', 'roadmap.phases.activities', 'roadmap.milestones', 'results.activity'])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบแผนพัฒนารายบุคคล');
        }
        $me = UserHelper::GetEmployee();
        $isLeader = $me && (int) ($model->employee?->leader()?->id ?? 0) === (int) $me->id;
        if (!$this->canManageRoadmaps() && (!$me || ((int) $me->id !== (int) $model->emp_id && !$isLeader))) {
            throw new NotFoundHttpException('ไม่พบแผนพัฒนารายบุคคล');
        }
        return $this->render('plan', ['model' => $model]);
    }

    public function actionPdf($id)
    {
        $model = EmployeeTrainingPlan::find()->where(['id' => (int) $id])->with([
            'employee.empDepartment',
            'roadmap.phases.activities',
            'roadmap.milestones',
            'results.activity',
            'mentor',
            'assessor',
        ])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบแผน Training Roadmap');
        }

        $me = UserHelper::GetEmployee();
        $isLeader = $me && (int) ($model->employee?->leader()?->id ?? 0) === (int) $me->id;
        if (!$this->canManageRoadmaps() && (!$me || ((int) $me->id !== (int) $model->emp_id && !$isLeader))) {
            throw new NotFoundHttpException('ไม่พบแผน Training Roadmap');
        }

        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'fontDir' => array_merge($defaultConfig['fontDir'], [$fontPath]),
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'thsarabunnew' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                    'I' => 'THSarabunNew-Italic.ttf',
                    'BI' => 'THSarabunNew BoldItalic.ttf',
                ],
            ],
            'default_font' => 'thsarabunnew',
            'tempDir' => Yii::getAlias('@runtime/mpdf'),
        ]);
        $mpdf->SetTitle('TRM - ' . $model->employee->fullname);
        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->WriteHTML($this->renderPartial('_pdf_one_page', ['model' => $model]));

        return $mpdf->Output('TRM_' . (int) $model->emp_id . '_' . (int) $model->id . '.pdf', \Mpdf\Output\Destination::INLINE);
    }

    public function actionResult($id)
    {
        $model = EmployeeTrainingResult::find()->where(['id' => $id])->with(['plan', 'activity'])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการประเมิน');
        }
        $me = UserHelper::GetEmployee();
        $canAssess = $me && in_array((int) $me->id, [(int) $model->plan->mentor_emp_id, (int) $model->plan->assessor_emp_id], true);
        if (!$this->canManageRoadmaps() && !$canAssess) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์บันทึกผลการพัฒนารายการนี้');
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (in_array($model->status, ['passed', 'completed'], true)) {
                $model->assessed_by = Yii::$app->user->id;
                $model->assessed_at = date('Y-m-d H:i:s');
                $model->save(false);
            }
            $this->refreshPlanProgress($model->plan_id);
            return $this->jsonSuccess('บันทึกผลการพัฒนาเรียบร้อย', '#employee-roadmap-plan');
        }
        return $this->modalOrPage('_result_form', ['model' => $model], 'บันทึกผลการพัฒนา');
    }

    protected function saveRoadmap(TrainingRoadmap $model)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                return $this->jsonSuccess('บันทึกแม่แบบ Roadmap เรียบร้อย', '#training-roadmap-list');
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->modalOrPage('_form', ['model' => $model], $model->isNewRecord ? 'สร้าง Training Roadmap' : 'แก้ไข Training Roadmap');
    }

    protected function saveModalModel($model, $view, $message, $container)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->jsonSuccess($message, $container);
        }
        return $this->modalOrPage($view, ['model' => $model], Yii::$app->request->get('title', 'บันทึกข้อมูล'));
    }

    protected function modalOrPage($view, $params, $title)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => $title, 'content' => $this->renderAjax($view, $params)];
        }
        return $this->render($view, $params);
    }

    protected function jsonSuccess($message, $container)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'success', 'message' => $message, 'container' => $container];
    }

    protected function snapshotRoadmap(TrainingRoadmap $roadmap)
    {
        $data = $roadmap->toArray();
        $data['phases'] = [];
        foreach ($roadmap->phases as $phase) {
            $phaseData = $phase->toArray();
            $phaseData['activities'] = array_map(static fn($activity) => $activity->toArray(), $phase->activities);
            $data['phases'][] = $phaseData;
        }
        $data['milestones'] = array_map(static fn($milestone) => $milestone->toArray(), $roadmap->milestones);
        return $data;
    }

    protected function calculateEndDate($date, $value, $unit)
    {
        $unitMap = ['day' => 'days', 'week' => 'weeks', 'month' => 'months'];
        return date('Y-m-d', strtotime(sprintf('+%d %s', $value, $unitMap[$unit] ?? 'days'), strtotime($date)));
    }

    protected function refreshPlanProgress($planId)
    {
        $plan = EmployeeTrainingPlan::findOne($planId);
        $total = EmployeeTrainingResult::find()->where(['plan_id' => $planId])->count();
        $done = EmployeeTrainingResult::find()->where(['plan_id' => $planId, 'status' => ['passed', 'completed']])->count();
        $plan->progress_percent = $total ? round(($done / $total) * 100, 2) : 0;
        if ($total && $done === $total) {
            $plan->status = 'completed';
            $plan->completed_at = date('Y-m-d H:i:s');
            $plan->actual_end_date = date('Y-m-d');
        } elseif ($done > 0 && $plan->status === 'assigned') {
            $plan->status = 'in_progress';
        }
        $plan->save(false);
    }

    protected function createEmployeePlan(EmployeeTrainingPlan $model, TrainingRoadmap $roadmap)
    {
        $model->target_end_date = $this->calculateEndDate($model->start_date, $roadmap->duration_value, $roadmap->duration_unit);
        $model->roadmap_snapshot_json = json_encode($this->snapshotRoadmap($roadmap), JSON_UNESCAPED_UNICODE);
        $model->assigned_by = Yii::$app->user->id;
        $model->assigned_at = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                throw new \RuntimeException(implode(', ', $model->getFirstErrors()));
            }
            foreach ($roadmap->phases as $phase) {
                foreach ($phase->activities as $activity) {
                    (new EmployeeTrainingResult([
                        'plan_id' => $model->id,
                        'activity_id' => $activity->id,
                        'status' => 'pending',
                    ]))->save(false);
                }
            }
            $transaction->commit();
            return $model;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function hasOpenPlan($employeeId)
    {
        return EmployeeTrainingPlan::find()
            ->where(['emp_id' => $employeeId])
            ->andWhere(['not in', 'status', ['completed', 'cancelled']])
            ->exists();
    }

    protected function findRoadmap($id) {
        $model = TrainingRoadmap::find()->where(['id' => $id])->with(['phases.activities', 'milestones', 'assignments'])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบ Training Roadmap');
        return $model;
    }
    protected function findPhase($id) {
        $model = TrainingRoadmapPhase::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบระยะพัฒนา');
        return $model;
    }
    protected function findActivity($id) {
        $model = TrainingRoadmapActivity::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบกิจกรรม');
        return $model;
    }
    protected function findMilestone($id) {
        $model = TrainingRoadmapMilestone::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบจุดประเมิน');
        return $model;
    }

    protected function canManageRoadmaps()
    {
        return Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
    }

    protected function assertCanManageRoadmaps()
    {
        if (!$this->canManageRoadmaps()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์จัดการ Training Roadmap');
        }
    }

    protected function assertCanAssignEmployee(Employees $employee)
    {
        if ($this->canManageRoadmaps()) return;
        $me = UserHelper::GetEmployee();
        if (!$me || (int) ($employee->leader()?->id ?? 0) !== (int) $me->id) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์มอบหมาย Training Roadmap ให้บุคลากรรายนี้');
        }
    }
}
