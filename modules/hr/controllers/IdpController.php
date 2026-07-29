<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\IdpActivity;
use app\modules\hr\models\IdpCycle;
use app\modules\hr\models\IdpGoal;
use app\modules\hr\models\IdpPlan;
use app\modules\hr\services\IdpTelegramService;

class IdpController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'start' => ['POST'],
                    'submit' => ['POST'],
                    'approve' => ['POST'],
                    'return' => ['POST'],
                    'open' => ['POST'],
                    'close' => ['POST'],
                    'delete-goal' => ['POST'],
                    'delete-activity' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->assertCanManage();
        $cycleId = (int) Yii::$app->request->get('cycle_id');
        $status = trim((string) Yii::$app->request->get('status'));
        $q = trim((string) Yii::$app->request->get('q'));
        $cycle = $cycleId ? IdpCycle::findOne($cycleId) : IdpCycle::current();
        $query = IdpPlan::find()->alias('p')->with(['employee', 'cycle', 'goals.activities']);
        if ($cycle) $query->andWhere(['p.cycle_id' => $cycle->id]);
        if ($status !== '') $query->andWhere(['p.status' => $status]);
        if ($q !== '') {
            $query->innerJoin('{{%employees}} e', 'e.id = p.emp_id')
                ->andWhere(['or', ['like', 'e.fname', $q], ['like', 'e.lname', $q], ['like', 'e.id', $q]]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['p.updated_at' => SORT_DESC, 'p.id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        $counts = [];
        foreach (array_keys(IdpPlan::statusOptions()) as $key) {
            $countQuery = IdpPlan::find()->where(['status' => $key]);
            if ($cycle) $countQuery->andWhere(['cycle_id' => $cycle->id]);
            $counts[$key] = (int) $countQuery->count();
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'cycle' => $cycle,
            'cycles' => IdpCycle::find()->orderBy(['start_date' => SORT_DESC])->all(),
            'counts' => $counts,
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function actionCycle($id = null)
    {
        $this->assertCanManage();
        $model = $id ? IdpCycle::findOne($id) : new IdpCycle([
            'title' => 'รอบ IDP ปีงบประมาณ ' . (date('Y') + 543),
            'fiscal_year' => date('Y') + 543,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-09-30', strtotime('+1 year')),
            'status' => 'draft',
        ]);
        if (!$model) throw new NotFoundHttpException('ไม่พบรอบ IDP');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->status === 'active') {
                IdpCycle::updateAll(['status' => 'closed'], ['and', ['status' => 'active'], ['<>', 'id', $model->id]]);
            }
            return $this->jsonSuccess('บันทึกรอบ IDP เรียบร้อย', '#idp-management');
        }
        return $this->modalOrPage('_cycle_form', compact('model'), $model->isNewRecord ? 'สร้างรอบ IDP' : 'แก้ไขรอบ IDP');
    }

    public function actionEmployee($emp_id)
    {
        $employee = Employees::findOne($emp_id);
        if (!$employee) throw new NotFoundHttpException('ไม่พบบุคลากร');
        $this->assertCanViewEmployee($employee);
        $cycle = IdpCycle::current();
        $plan = $cycle ? IdpPlan::find()->where(['cycle_id' => $cycle->id, 'emp_id' => $employee->id])
            ->with(['cycle', 'employee', 'supervisor', 'goals.activities'])->one() : null;
        return $this->render('employee', compact('employee', 'cycle', 'plan'));
    }

    public function actionStart()
    {
        $employee = UserHelper::GetEmployee();
        if (!$employee) throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากรของบัญชีนี้');
        $cycle = IdpCycle::current();
        if (!$cycle) {
            Yii::$app->session->setFlash('warning', 'ขณะนี้ยังไม่มีรอบ IDP ที่เปิดใช้งาน');
            return $this->redirect(['/profile', 'name' => 'idp']);
        }
        $plan = IdpPlan::findOne(['cycle_id' => $cycle->id, 'emp_id' => $employee->id]);
        if (!$plan) {
            $plan = new IdpPlan([
                'cycle_id' => $cycle->id,
                'emp_id' => $employee->id,
                'supervisor_emp_id' => $employee->supervisorEmpId(),
                'status' => 'draft',
                'progress_percent' => 0,
            ]);
            if (!$plan->save()) throw new \RuntimeException(implode(', ', $plan->getFirstErrors()));
        }
        return $this->redirect(['/profile', 'name' => 'idp']);
    }

    public function actionGoal($plan_id, $id = null)
    {
        $plan = $this->findPlan($plan_id);
        $this->assertCanEditPlan($plan);
        $model = $id ? IdpGoal::findOne(['id' => $id, 'plan_id' => $plan->id]) : new IdpGoal([
            'plan_id' => $plan->id,
            'sequence' => ((int) IdpGoal::find()->where(['plan_id' => $plan->id])->max('sequence')) + 1,
            'source_type' => 'employee',
            'weight_percent' => 100,
            'progress_percent' => 0,
            'status' => 'not_started',
        ]);
        if (!$model) throw new NotFoundHttpException('ไม่พบเป้าหมาย IDP');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->refreshPlanProgress($plan->id);
            return $this->jsonSuccess('บันทึกเป้าหมายการพัฒนาแล้ว', '#idp-employee-panel');
        }
        return $this->modalOrPage('_goal_form', compact('model', 'plan'), $model->isNewRecord ? 'เพิ่มเป้าหมายการพัฒนา' : 'แก้ไขเป้าหมายการพัฒนา');
    }

    public function actionActivity($goal_id, $id = null)
    {
        $goal = IdpGoal::find()->with('plan')->where(['id' => $goal_id])->one();
        if (!$goal) throw new NotFoundHttpException('ไม่พบเป้าหมาย IDP');
        $this->assertCanEditPlan($goal->plan, true);
        $model = $id ? IdpActivity::findOne(['id' => $id, 'goal_id' => $goal->id]) : new IdpActivity([
            'goal_id' => $goal->id,
            'sequence' => ((int) IdpActivity::find()->where(['goal_id' => $goal->id])->max('sequence')) + 1,
            'method_type' => 'on_the_job',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);
        if (!$model) throw new NotFoundHttpException('ไม่พบกิจกรรม IDP');
        if ($model->load(Yii::$app->request->post())) {
            if ((float) $model->progress_percent >= 100) {
                $model->status = 'completed';
                $model->completed_at = date('Y-m-d H:i:s');
            } elseif ((float) $model->progress_percent > 0) {
                $model->status = 'in_progress';
            }
            if ($model->save()) {
                $this->refreshGoalProgress($goal->id);
                return $this->jsonSuccess('บันทึกกิจกรรมพัฒนาแล้ว', '#idp-employee-panel');
            }
        }
        return $this->modalOrPage('_activity_form', compact('model', 'goal'), $model->isNewRecord ? 'เพิ่มกิจกรรมพัฒนา' : 'บันทึกความก้าวหน้า');
    }

    public function actionSubmit($id)
    {
        $plan = $this->findPlan($id);
        $this->assertOwner($plan);
        $emptyGoals = [];
        foreach ($plan->goals as $goal) {
            if (!$goal->activities) $emptyGoals[] = $goal->title;
        }
        if (!$plan->canEdit() || !$plan->goals) {
            Yii::$app->session->setFlash('warning', 'กรุณาเพิ่มเป้าหมายอย่างน้อย 1 รายการก่อนส่งแผน');
        } elseif ($emptyGoals) {
            Yii::$app->session->setFlash('warning', 'กรุณาเพิ่มกิจกรรมพัฒนาอย่างน้อย 1 รายการในทุกเป้าหมายก่อนส่งแผน (ยังไม่มีกิจกรรม: ' . implode(', ', $emptyGoals) . ')');
        } else {
            $plan->status = 'submitted';
            $plan->submitted_at = date('Y-m-d H:i:s');
            $plan->supervisor_emp_id = $plan->employee?->supervisorEmpId() ?: $plan->supervisor_emp_id;
            $plan->save(false);
            IdpTelegramService::notifySubmitted($plan);
            $target = $plan->supervisor_emp_id ? 'หัวหน้าพิจารณา' : 'HR ตรวจสอบ (ยังไม่พบหัวหน้าในระบบ)';
            Yii::$app->session->setFlash('success', 'ส่ง IDP ให้' . $target . 'แล้ว');
        }
        return $this->redirect(['/profile', 'name' => 'idp']);
    }

    public function actionApprove($id)
    {
        $plan = $this->findPlan($id);
        $this->assertCanReview($plan);
        $plan->status = 'approved';
        $plan->supervisor_comment = trim((string) Yii::$app->request->post('supervisor_comment'));
        $plan->reviewed_at = date('Y-m-d H:i:s');
        $plan->save(false);
        IdpTelegramService::notifyApproved($plan);
        Yii::$app->session->setFlash('success', 'หัวหน้าเห็นชอบแผน IDP แล้ว รอ HR ตรวจสอบและเปิดให้บันทึกผล');
        return $this->redirect(['employee', 'emp_id' => $plan->emp_id]);
    }

    public function actionOpen($id)
    {
        $plan = $this->findPlan($id);
        $this->assertCanManage();
        if ($plan->status !== 'approved') {
            Yii::$app->session->setFlash('warning', 'เปิดให้บันทึกผลได้เฉพาะแผนที่หัวหน้าเห็นชอบแล้ว');
        } else {
            $plan->status = 'in_progress';
            $plan->reviewed_at = date('Y-m-d H:i:s');
            $plan->save(false);
            IdpTelegramService::notifyOpened($plan);
            Yii::$app->session->setFlash('success', 'เปิดให้เจ้าหน้าที่บันทึกผลการดำเนินการแล้ว');
        }
        return $this->redirect(['employee', 'emp_id' => $plan->emp_id]);
    }

    public function actionClose($id)
    {
        $plan = $this->findPlan($id);
        $this->assertCanManage();
        if (!in_array($plan->status, ['in_progress', 'assessment'], true)) {
            Yii::$app->session->setFlash('warning', 'ปิดรอบได้เฉพาะแผนที่กำลังดำเนินการหรือรอปิดรอบ');
        } else {
            $summary = trim((string) Yii::$app->request->post('employee_summary'));
            if ($summary !== '') $plan->employee_summary = $summary;
            $plan->status = 'completed';
            $plan->completed_at = date('Y-m-d H:i:s');
            $plan->save(false);
            IdpTelegramService::notifyClosed($plan);
            Yii::$app->session->setFlash('success', 'ปิด IDP ประจำปีเรียบร้อยแล้ว');
        }
        return $this->redirect(['employee', 'emp_id' => $plan->emp_id]);
    }

    public function actionReturn($id)
    {
        $plan = $this->findPlan($id);
        $this->assertCanReview($plan);
        $comment = trim((string) Yii::$app->request->post('supervisor_comment'));
        if ($comment === '') {
            Yii::$app->session->setFlash('warning', 'กรุณาระบุสิ่งที่ต้องการให้ปรับปรุง');
        } else {
            $plan->status = 'revision';
            $plan->supervisor_comment = $comment;
            $plan->reviewed_at = date('Y-m-d H:i:s');
            $plan->save(false);
            IdpTelegramService::notifyReturned($plan);
            Yii::$app->session->setFlash('success', 'ส่งแผนกลับให้พนักงานปรับปรุงแล้ว');
        }
        return $this->redirect(['employee', 'emp_id' => $plan->emp_id]);
    }

    protected function refreshGoalProgress($goalId)
    {
        $goal = IdpGoal::findOne($goalId);
        $avg = IdpActivity::find()->where(['goal_id' => $goalId])->average('progress_percent');
        $goal->progress_percent = $avg === null ? 0 : round((float) $avg, 2);
        $goal->status = $goal->progress_percent >= 100 ? 'completed' : ($goal->progress_percent > 0 ? 'in_progress' : 'not_started');
        $goal->save(false);
        $this->refreshPlanProgress($goal->plan_id);
    }

    protected function refreshPlanProgress($planId)
    {
        $plan = IdpPlan::findOne($planId);
        $avg = IdpGoal::find()->where(['plan_id' => $planId])->average('progress_percent');
        $plan->progress_percent = $avg === null ? 0 : round((float) $avg, 2);
        if ($plan->progress_percent >= 100) $plan->status = 'assessment';
        $plan->save(false);
    }

    protected function findPlan($id)
    {
        $model = IdpPlan::find()->with(['cycle', 'employee', 'supervisor', 'goals.activities'])->where(['id' => $id])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบแผน IDP');
        return $model;
    }

    protected function assertCanManage()
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์จัดการ IDP');
    }

    protected function assertOwner(IdpPlan $plan)
    {
        $me = UserHelper::GetEmployee();
        if (!$me || (int) $me->id !== (int) $plan->emp_id) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์แก้ไขแผนนี้');
    }

    protected function assertCanEditPlan(IdpPlan $plan, $allowProgress = false)
    {
        if ($this->canManage()) return;
        $this->assertOwner($plan);
        $editable = $plan->canEdit() || ($allowProgress && in_array($plan->status, ['in_progress', 'assessment'], true));
        if (!$editable) throw new ForbiddenHttpException('แผนนี้ไม่อยู่ในสถานะที่แก้ไขได้');
    }

    protected function assertCanReview(IdpPlan $plan)
    {
        if ($this->canManage()) return;
        $me = UserHelper::GetEmployee();
        if (!$me || (int) $me->id !== (int) $plan->supervisor_emp_id) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์พิจารณาแผนนี้');
    }

    protected function assertCanViewEmployee(Employees $employee)
    {
        if ($this->canManage()) return;
        $me = UserHelper::GetEmployee();
        if (!$me || ((int) $me->id !== (int) $employee->id && (int) $me->id !== (int) ($employee->supervisorEmpId() ?? 0))) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดู IDP นี้');
        }
    }

    protected function canManage()
    {
        return Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
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
}
