<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\ProbationCase;
use app\modules\hr\models\ProbationEvaluation;
use app\modules\hr\models\ProbationTemplate;
use app\modules\hr\services\ProbationAppraisalService;

class ProbationAppraisalController extends Controller
{
    private ProbationAppraisalService $service;
    public function __construct($id, $module, $config = []) { $this->service = new ProbationAppraisalService(); parent::__construct($id, $module, $config); }
    public function behaviors() { return [
        'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        'verbs' => ['class' => VerbFilter::class, 'actions' => ['evaluate' => ['GET', 'POST'], 'decision' => ['GET', 'POST'], 'acknowledge' => ['POST'], 'reopen' => ['GET', 'POST']]],
    ]; }
    public function actionIndex()
    {
        $employee = UserHelper::GetEmployee(); $isHr = $this->isHr();
        $query = ProbationCase::find()->alias('c')->with(['employee.empDepartment', 'employee.employeePositionGroup', 'template', 'rounds.evaluations', 'decision']);
        if (!$isHr) {
            if (!$employee) throw new ForbiddenHttpException('ไม่พบบัญชีบุคลากร');
            $query->andWhere(['or', ['c.employee_id' => $employee->id], ['c.supervisor_employee_id' => $employee->id], ['c.group_head_employee_id' => $employee->id], ['c.director_employee_id' => $employee->id]]);
        }
        $q = trim((string) Yii::$app->request->get('q')); $status = trim((string) Yii::$app->request->get('status'));
        if ($q !== '') $query->innerJoin('{{%employees}} e', 'e.id=c.employee_id')->andWhere(['or', ['like', 'e.fname', $q], ['like', 'e.lname', $q], ['like', 'e.id', $q]]);
        if ($status !== '') $query->andWhere(['c.status' => $status]);
        return $this->render('index', ['dataProvider' => new ActiveDataProvider(['query' => $query->orderBy(['c.updated_at' => SORT_DESC]), 'pagination' => ['pageSize' => 20]]), 'q' => $q, 'status' => $status, 'isHr' => $isHr]);
    }
    public function actionAssign()
    {
        $this->assertHr(); $model = new ProbationCase(['start_date' => date('Y-m-d'), 'status' => 'assigned']);
        if ($model->load(Yii::$app->request->post()) && $this->service->createCase($model)) { Yii::$app->session->setFlash('success', 'สร้างและมอบหมายการประเมินแล้ว'); return $this->redirect(['view', 'id' => $model->id]); }
        $employees = Employees::find()->where(['status' => Employees::STATUS_WORKING])->orderBy(['fname' => SORT_ASC])->all();
        return $this->render('assign', ['model' => $model, 'employees' => $employees, 'templates' => ProbationTemplate::find()->with('positionGroup')->where(['status' => 'active'])->orderBy(['name' => SORT_ASC])->all()]);
    }
    public function actionView($id) { $model = $this->findCase($id); $this->assertCanView($model); return $this->render('view', ['model' => $model, 'currentEmployee' => UserHelper::GetEmployee(), 'isHr' => $this->isHr()]); }
    public function actionEvaluate($id)
    {
        $evaluation = ProbationEvaluation::find()->with(['round.case.template.items', 'round.evaluations.evaluator', 'scores'])->where(['id' => $id])->one();
        if (!$evaluation) throw new NotFoundHttpException('ไม่พบแบบประเมิน');
        $employee = UserHelper::GetEmployee(); if (!$employee || (int) $evaluation->evaluator_employee_id !== (int) $employee->id) throw new ForbiddenHttpException('ไม่ใช่แบบประเมินที่มอบหมายให้คุณ');
        if (Yii::$app->request->isPost) {
            try { $this->service->submitEvaluation($evaluation, (array) Yii::$app->request->post('scores', [])); Yii::$app->session->setFlash('success', 'ส่งผลประเมินเรียบร้อย'); return $this->redirect(['view', 'id' => $evaluation->round->case_id]); }
            catch (\Throwable $e) { Yii::$app->session->setFlash('danger', $e->getMessage()); }
        }
        return $this->render('evaluate', compact('evaluation'));
    }
    public function actionDecision($id)
    {
        $case = $this->findCase($id); $employee = UserHelper::GetEmployee();
        if (!$employee || (int) $case->final_recommender_employee_id !== (int) $employee->id) throw new ForbiddenHttpException('ไม่ได้รับมอบหมายให้สรุปผล');
        if (Yii::$app->request->isPost) {
            try { $this->service->saveDecision($case, (string) Yii::$app->request->post('recommendation'), (string) Yii::$app->request->post('summary_comment'), (int) $employee->id); Yii::$app->session->setFlash('success', 'บันทึกข้อเสนอและส่งให้ ผอ.รับทราบแล้ว'); return $this->redirect(['view', 'id' => $case->id]); }
            catch (\Throwable $e) { Yii::$app->session->setFlash('danger', $e->getMessage()); }
        }
        return $this->render('decision', ['model' => $case]);
    }
    public function actionAcknowledge($id)
    {
        $case = $this->findCase($id); $employee = UserHelper::GetEmployee(); if (!$employee) throw new ForbiddenHttpException();
        try { $this->service->acknowledge($case, (int) $employee->id); Yii::$app->session->setFlash('success', 'รับทราบผลการประเมินแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('danger', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $case->id]);
    }
    public function actionReopen($id)
    {
        $this->assertHr();
        $evaluation = ProbationEvaluation::find()->with(['round.case', 'round.evaluations', 'evaluator'])->where(['id' => $id])->one();
        if (!$evaluation) throw new NotFoundHttpException('ไม่พบผลประเมิน');
        if ($evaluation->status !== 'submitted') throw new ForbiddenHttpException('เปิดกลับได้เฉพาะผลที่ส่งแล้ว');
        $roles = ['self', 'supervisor', 'group_head']; $index = array_search($evaluation->role, $roles, true);
        foreach ($evaluation->round->evaluations as $other) if (array_search($other->role, $roles, true) > $index && $other->status === 'submitted') throw new ForbiddenHttpException('ผู้ประเมินลำดับถัดไปส่งผลแล้ว ไม่สามารถเปิดเฉพาะขั้นตอนนี้ได้');
        if (Yii::$app->request->isPost) {
            $reason = trim((string) Yii::$app->request->post('reason'));
            if ($reason === '') Yii::$app->session->setFlash('danger', 'กรุณาระบุเหตุผล');
            else {
                $evaluation->status = 'open'; $evaluation->reopened_at = date('Y-m-d H:i:s'); $evaluation->reopen_reason = $reason; $evaluation->save(false);
                $evaluation->round->status = $evaluation->role === 'self' ? 'waiting_self' : ($evaluation->role === 'supervisor' ? 'waiting_supervisor' : 'waiting_group_head');
                $evaluation->round->completed_at = null; $evaluation->round->save(false);
                $evaluation->round->case->status = 'in_progress'; $evaluation->round->case->save(false);
                Yii::$app->session->setFlash('success', 'เปิดแบบประเมินกลับให้แก้ไขแล้ว');
                return $this->redirect(['view', 'id' => $evaluation->round->case_id]);
            }
        }
        return $this->render('reopen', compact('evaluation'));
    }
    private function findCase($id): ProbationCase { $model = ProbationCase::find()->with(['employee.empDepartment', 'employee.employeePosition', 'template.items', 'supervisor', 'groupHead', 'director', 'finalRecommender', 'rounds.evaluations.evaluator', 'decision', 'acknowledgement'])->where(['id' => $id])->one(); if (!$model) throw new NotFoundHttpException('ไม่พบแฟ้มทดลองงาน'); return $model; }
    private function isHr(): bool { return Yii::$app->user->can('hr') || Yii::$app->user->can('admin'); }
    private function assertHr(): void { if (!$this->isHr()) throw new ForbiddenHttpException('สำหรับ HR เท่านั้น'); }
    private function assertCanView(ProbationCase $case): void { if ($this->isHr()) return; $employee = UserHelper::GetEmployee(); if (!$employee || !in_array((int) $employee->id, [(int) $case->employee_id, (int) $case->supervisor_employee_id, (int) $case->group_head_employee_id, (int) $case->director_employee_id], true)) throw new ForbiddenHttpException('ไม่มีสิทธิ์ดูแฟ้มนี้'); }
}
