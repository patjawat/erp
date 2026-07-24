<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
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
        ];
    }

    public function actionIndex()
    {
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
        return $this->render('index', compact('dataProvider', 'q', 'status'));
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findRoadmap($id)]);
    }

    public function actionCreate()
    {
        $model = new TrainingRoadmap([
            'roadmap_type' => 'professional', 'version_no' => 1, 'duration_value' => 90,
            'duration_unit' => 'day', 'status' => 'draft',
        ]);
        return $this->saveRoadmap($model);
    }

    public function actionUpdate($id)
    {
        return $this->saveRoadmap($this->findRoadmap($id));
    }

    public function actionPhase($roadmap_id, $id = null)
    {
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

    public function actionAssign($roadmap_id, $emp_id = null)
    {
        $roadmap = $this->findRoadmap($roadmap_id);
        $model = new EmployeeTrainingPlan([
            'roadmap_id' => $roadmap->id, 'emp_id' => $emp_id, 'start_date' => date('Y-m-d'),
            'status' => 'assigned', 'progress_percent' => 0,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->target_end_date = $this->calculateEndDate($model->start_date, $roadmap->duration_value, $roadmap->duration_unit);
            $model->roadmap_snapshot_json = json_encode($this->snapshotRoadmap($roadmap), JSON_UNESCAPED_UNICODE);
            $model->assigned_by = Yii::$app->user->id;
            $model->assigned_at = date('Y-m-d H:i:s');
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                foreach ($roadmap->phases as $phase) {
                    foreach ($phase->activities as $activity) {
                        (new EmployeeTrainingResult([
                            'plan_id' => $model->id, 'activity_id' => $activity->id, 'status' => 'pending',
                        ]))->save(false);
                    }
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            return $this->jsonSuccess('มอบหมาย Training Roadmap เรียบร้อย', '#roadmap-assignments');
        }
        return $this->modalOrPage('_assign_form', [
            'model' => $model, 'roadmap' => $roadmap,
            'employeeItems' => ArrayHelper::map(Employees::find()->orderBy(['fname' => SORT_ASC])->all(), 'id', 'fullname'),
        ], 'มอบหมาย Training Roadmap');
    }

    public function actionEmployee($emp_id)
    {
        $employee = Employees::findOne($emp_id);
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากร');
        }
        $me = UserHelper::GetEmployee();
        if (!$this->canManageRoadmaps() && (!$me || (int) $me->id !== (int) $employee->id)) {
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
        if (!$this->canManageRoadmaps() && (!$me || (int) $me->id !== (int) $model->emp_id)) {
            throw new NotFoundHttpException('ไม่พบแผนพัฒนารายบุคคล');
        }
        return $this->render('plan', ['model' => $model]);
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
}
