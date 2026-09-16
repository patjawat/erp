<?php

namespace app\modules\complaint\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\complaint\models\Complaint;
use app\modules\complaint\models\ComplaintAction;
use app\modules\complaint\models\ComplaintAttachment;
use app\modules\complaint\models\ComplaintMaster;
use app\modules\complaint\models\ComplaintSurvey;
use app\modules\complaint\services\ComplaintFileService;
use app\modules\complaint\services\ComplaintService;
use app\modules\hr\models\Employees;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * ทะเบียนเรื่องร้องเรียน + workflow 5 ขั้น (แจ้ง/รับ/ประเมิน/ดำเนิน/ปิด)
 *
 * สิทธิ์: controller ปล่อยผู้ล็อกอิน (roles ['@']) แล้ว guard จริงผ่าน ComplaintService
 * (ทีมศูนย์ฯ = admin/role complaint เห็นทุกเรื่อง ; ผู้ใช้ทั่วไป = เฉพาะสายหน่วยตนเอง/ที่ตนสร้าง)
 */
class ComplaintController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'intake' => ['POST'],
                    'assess' => ['POST'],
                    'add-action' => ['POST'],
                    'delete-action' => ['POST'],
                    'close' => ['POST'],
                    'reopen' => ['POST'],
                    'save-survey' => ['POST'],
                    'upload-files' => ['POST'],
                    'delete-file' => ['POST'],
                ],
            ],
        ]);
    }

    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    private function meContext(): array
    {
        $me = UserHelper::GetEmployee();
        return [
            'empUnitId' => $me ? (int) $me->department : null,
            'empId' => $me ? (int) $me->id : null,
            'userId' => Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null,
        ];
    }

    /** ทะเบียนเรื่องร้องเรียน: ตาราง + ตัวกรอง (ปีงบ/สถานะ/ประเภท/ระดับ/หน่วยงาน/คำค้น) */
    public function actionIndex()
    {
        $ctx = $this->meContext();
        $req = Yii::$app->request;
        $fiscalYear = $this->fiscalYear();
        $status = (string) $req->get('status');
        $typeId = (int) $req->get('type_id') ?: null;
        $level = (int) $req->get('level') ?: null;
        $unitId = (int) $req->get('unit_id') ?: null;
        $q = trim((string) $req->get('q'));

        $query = Complaint::find()
            ->with(['type', 'channel', 'assignedUnit', 'assignee'])
            ->where(['fiscal_year' => $fiscalYear]);

        // ขอบเขตการมองเห็น (ไม่ใช่ทีมศูนย์ฯ → เฉพาะสายหน่วยตนเอง/ที่ตนสร้าง)
        if (!ComplaintService::isManager()) {
            $scope = ComplaintService::unitScopeIds($ctx['empUnitId']) ?: [-1];
            $query->andWhere([
                'or',
                ['assigned_unit_id' => $scope],
                ['created_by' => $ctx['userId'] ?? -1],
            ]);
        }

        if ($status !== '' && array_key_exists($status, Complaint::statusLabels())) {
            $query->andWhere(['status' => $status]);
        }
        if ($typeId) {
            $query->andWhere(['type_id' => $typeId]);
        }
        if ($level) {
            $query->andWhere(['severity_level' => $level]);
        }
        if ($unitId) {
            $query->andWhere(['assigned_unit_id' => $unitId]);
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'detail', $q],
                ['like', 'complaint_no', $q],
                ['like', 'tracking_code', $q],
                ['like', 'reporter_name', $q],
            ]);
        }

        $count = (int) $query->count();
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => 25]);
        $rows = $query
            ->orderBy(['complaint_date' => SORT_DESC, 'id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        // สรุปนับตามสถานะ (ทั้งปีงบ ภายในขอบเขตเดียวกับตัวกรอง)
        $countQuery = clone $query;
        $countQuery->orderBy([])->limit(-1)->offset(-1);

        return $this->render('index', [
            'rows' => $rows,
            'pages' => $pages,
            'count' => $count,
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'types' => ComplaintMaster::options('type'),
            'units' => ComplaintService::orderedUnits(),
            'filters' => ['status' => $status, 'type_id' => $typeId, 'level' => $level, 'unit_id' => $unitId, 'q' => $q],
        ]);
    }

    public function actionCreate()
    {
        $ctx = $this->meContext();
        $model = new Complaint();
        $model->fiscal_year = $this->fiscalYear();
        $model->status = Complaint::STATUS_REPORTED;
        $model->complaint_date = date('Y-m-d');
        $model->assigned_unit_id = $ctx['empUnitId'] ?: null;

        if (Yii::$app->request->isPost) {
            if ($this->saveStep1($model, $ctx)) {
                Yii::$app->session->setFlash('success', 'บันทึกเรื่องร้องเรียนแล้ว — เลขที่ ' . $model->complaint_no);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('form', $this->formData($model, $ctx));
    }

    public function actionUpdate($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);

        if (Yii::$app->request->isPost) {
            if ($this->saveStep1($model, $ctx)) {
                Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('form', $this->formData($model, $ctx));
    }

    public function actionView($id)
    {
        $model = Complaint::find()
            ->with(['channel', 'type', 'reporterRelation', 'patientRight', 'rejectReason',
                'assignedUnit', 'assignee', 'actions', 'actions.actor', 'attachments', 'surveys', 'logs'])
            ->where(['id' => (int) $id])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเรื่องร้องเรียนที่ต้องการ');
        }
        $ctx = $this->meContext();
        if (!ComplaintService::canView($model, $ctx['userId'], $ctx['empUnitId'])) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูเรื่องนี้');
        }

        return $this->render('view', [
            'model' => $model,
            'canManage' => ComplaintService::canManage($model, $ctx['userId'], $ctx['empUnitId']),
            'employees' => $this->employeeMap(),
            'units' => ComplaintService::selectableUnits($ctx['empUnitId']),
            'masters' => [
                'patient_right' => ComplaintMaster::options('patient_right'),
                'reject_reason' => ComplaintMaster::options('reject_reason'),
                'action_kind' => ComplaintMaster::options('action_kind'),
            ],
        ]);
    }

    // --- ขั้น 2 รับเรื่อง ---------------------------------------------------
    public function actionIntake($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);
        $req = Yii::$app->request;

        $post = $req->post('Complaint', []);
        $model->setAttributes($post);
        $model->intake_date = AppHelper::normalizeDateToDb($req->post('intake_date_thai')) ?: $model->intake_date;
        $model->intake_by = $model->intake_by ?: ($ctx['empId'] ?: null);
        // checklist มาเป็น array ของ key ที่ติ๊ก → เก็บ JSON
        $checklist = $req->post('intake_checklist', []);
        $model->intake_checklist = is_array($checklist) ? json_encode(array_values($checklist), JSON_UNESCAPED_UNICODE) : null;

        $from = $model->status;
        if ($model->status === Complaint::STATUS_REPORTED) {
            $model->status = Complaint::STATUS_INTAKE;
        }
        if ($model->save()) {
            if ($from !== $model->status) {
                ComplaintService::log($model->id, 'status', $from, $model->status, 'รับเรื่อง');
            }
            Yii::$app->session->setFlash('success', 'บันทึกการรับเรื่องแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-intake']);
    }

    // --- ขั้น 3 ประเมิน ----------------------------------------------------
    public function actionAssess($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);
        $req = Yii::$app->request;

        $post = $req->post('Complaint', []);
        $model->setAttributes($post);
        $model->assess_date = AppHelper::normalizeDateToDb($req->post('assess_date_thai')) ?: date('Y-m-d');
        $model->assess_by = $model->assess_by ?: ($ctx['empId'] ?: null);
        $model->need_rca = (int) $req->post('Complaint')['need_rca'] ?? 0;

        // คำนวณกำหนด SLA จากระดับ + วันร้องเรียน (ตั้งต้น) — ใช้ complaint_date ถ้ามี ไม่งั้น assess_date
        $base = $model->complaint_date ?: $model->assess_date;
        $sla = ComplaintService::slaDates($model->severity_level ? (int) $model->severity_level : null, $base);
        $model->setAttributes($sla, false);

        $from = $model->status;
        if ($model->assess_decision === Complaint::DECISION_REJECT) {
            $model->status = Complaint::STATUS_REJECTED;
        } elseif (Complaint::STATUS_STEP[$model->status] < Complaint::STATUS_STEP[Complaint::STATUS_ASSESSED]) {
            $model->status = Complaint::STATUS_ASSESSED;
        }

        if ($model->save()) {
            if ($from !== $model->status) {
                ComplaintService::log($model->id, 'status', $from, $model->status, 'ประเมิน');
            }
            Yii::$app->session->setFlash('success', 'บันทึกการประเมินแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-assess']);
    }

    // --- ขั้น 4 ดำเนินงาน (timeline) --------------------------------------
    public function actionAddAction($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);
        $req = Yii::$app->request;

        $action = new ComplaintAction([
            'complaint_id' => $model->id,
            'action_date' => AppHelper::normalizeDateToDb($req->post('action_date_thai')) ?: date('Y-m-d'),
            'action_level' => (int) $req->post('action_level') ?: null,
            'action_kind' => trim((string) $req->post('action_kind')) ?: null,
            'title' => trim((string) $req->post('action_title')) ?: null,
            'detail' => trim((string) $req->post('action_detail')) ?: null,
            'action_by' => $ctx['empId'] ?: null,
        ]);

        if ($action->save()) {
            $from = $model->status;
            if (Complaint::STATUS_STEP[$model->status] < Complaint::STATUS_STEP[Complaint::STATUS_IN_PROGRESS]
                && !$model->isFinal()) {
                $model->status = Complaint::STATUS_IN_PROGRESS;
                $model->save(false, ['status']);
                ComplaintService::log($model->id, 'status', $from, $model->status, 'เริ่มดำเนินงาน');
            }
            Yii::$app->session->setFlash('success', 'บันทึกการดำเนินงานแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $action->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-action']);
    }

    public function actionDeleteAction($id)
    {
        $action = ComplaintAction::findOne((int) $id);
        if (!$action) {
            throw new NotFoundHttpException('ไม่พบรายการดำเนินงาน');
        }
        $model = $this->findComplaint($action->complaint_id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);

        $action->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการดำเนินงานแล้ว');
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-action']);
    }

    // --- ขั้น 5 ปิดเคส -----------------------------------------------------
    public function actionClose($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);
        $req = Yii::$app->request;

        $post = $req->post('Complaint', []);
        $model->setAttributes($post);
        $model->close_date = AppHelper::normalizeDateToDb($req->post('close_date_thai')) ?: date('Y-m-d');
        $model->close_by = $model->close_by ?: ($ctx['empId'] ?: null);

        $from = $model->status;
        $model->status = Complaint::STATUS_CLOSED;
        if ($model->save()) {
            ComplaintService::log($model->id, 'status', $from, $model->status, 'ปิดเคส');
            Yii::$app->session->setFlash('success', 'ปิดเคสเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'ปิดเคสไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-close']);
    }

    public function actionReopen($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);

        $from = $model->status;
        $model->status = Complaint::STATUS_IN_PROGRESS;
        $model->save(false, ['status']);
        ComplaintService::log($model->id, 'status', $from, $model->status, 'เปิดเคสใหม่');
        Yii::$app->session->setFlash('success', 'เปิดเคสกลับมาดำเนินงานแล้ว');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    // --- แบบสำรวจความพึงพอใจ ----------------------------------------------
    public function actionSaveSurvey($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);
        $req = Yii::$app->request;

        $survey = new ComplaintSurvey(['complaint_id' => $model->id]);
        $survey->setAttributes($req->post('ComplaintSurvey', []));
        $survey->survey_date = AppHelper::normalizeDateToDb($req->post('survey_date_thai')) ?: date('Y-m-d');
        if ($survey->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกแบบสำรวจความพึงพอใจแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $survey->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'step-close']);
    }

    // --- ไฟล์แนบ -----------------------------------------------------------
    public function actionUploadFiles($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);

        $category = (string) Yii::$app->request->post('category', 'general');
        $files = \yii\web\UploadedFile::getInstancesByName('files');
        if (!$files) {
            Yii::$app->session->setFlash('error', 'ยังไม่ได้เลือกไฟล์');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $sort = (int) ComplaintAttachment::find()->where(['complaint_id' => $model->id])->max('sort');
        $ok = 0;
        $errors = [];
        foreach ($files as $file) {
            $res = ComplaintFileService::store($model, $file, $category, ++$sort);
            if ($res instanceof ComplaintAttachment) {
                $ok++;
            } else {
                $errors[] = (string) $res;
            }
        }
        if ($ok) {
            Yii::$app->session->setFlash('success', "แนบไฟล์แล้ว $ok ไฟล์");
        }
        if ($errors) {
            Yii::$app->session->setFlash('error', implode(' / ', array_slice($errors, 0, 5)));
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'attachments']);
    }

    public function actionDeleteFile($id)
    {
        $att = ComplaintAttachment::findOne((int) $id);
        if (!$att) {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        $model = $this->findComplaint($att->complaint_id);
        $ctx = $this->meContext();
        $this->assertManage($model, $ctx);

        ComplaintFileService::delete($att);
        Yii::$app->session->setFlash('success', 'ลบไฟล์แล้ว');
        return $this->redirect(['view', 'id' => $model->id, '#' => 'attachments']);
    }

    /** เสิร์ฟไฟล์แนบ (นอก webroot) พร้อมตรวจสิทธิ์ */
    public function actionFile($id, $thumb = 0)
    {
        $att = ComplaintAttachment::findOne((int) $id);
        if (!$att) {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        $model = $this->findComplaint($att->complaint_id);
        $ctx = $this->meContext();
        if (!ComplaintService::canView($model, $ctx['userId'], $ctx['empUnitId'])) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูไฟล์นี้');
        }
        $rel = ((int) $thumb === 1 && $att->thumbnail_path) ? $att->thumbnail_path : $att->file_path;
        $abs = ComplaintFileService::absolutePath($rel);
        if (!is_file($abs)) {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        return Yii::$app->response->sendFile($abs, $att->file_name ?: basename($abs), [
            'inline' => true,
            'mimeType' => $att->mime ?: null,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findComplaint($id);
        $ctx = $this->meContext();
        // ลบทั้งเรื่อง: เฉพาะทีมศูนย์ฯ
        if (!ComplaintService::isManager()) {
            throw new ForbiddenHttpException('ลบเรื่องได้เฉพาะทีมศูนย์รับเรื่องร้องเรียน');
        }
        // ลบไฟล์จริงในดิสก์ด้วย
        foreach ($model->attachments as $att) {
            ComplaintFileService::delete($att);
        }
        $fy = $model->fiscal_year;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบเรื่องร้องเรียนแล้ว');
        return $this->redirect(['index', 'fy' => $fy]);
    }

    // --- helpers -----------------------------------------------------------

    /** บันทึกขั้น 1 (แจ้งเรื่อง) + ออกเลขที่/รหัสติดตามตอนสร้าง */
    private function saveStep1(Complaint $model, array $ctx): bool
    {
        $req = Yii::$app->request;
        $model->setAttributes($req->post('Complaint', []));
        $model->complaint_date = AppHelper::normalizeDateToDb($req->post('complaint_date_thai')) ?: $model->complaint_date;
        $model->complaint_time = $model->complaint_time ?: null;
        $model->is_anonymous = (int) $req->post('Complaint')['is_anonymous'] ?? 0;

        // กันเลือกหน่วยงานนอกสิทธิ์ (ยกเว้นทีมศูนย์ฯ)
        if ($model->assigned_unit_id && !ComplaintService::isManager()) {
            $allowed = array_map(static fn ($o) => (int) $o['id'], ComplaintService::selectableUnits($ctx['empUnitId']));
            if (!in_array((int) $model->assigned_unit_id, $allowed, true)) {
                $model->assigned_unit_id = $ctx['empUnitId'] ?: null;
            }
        }

        $isNew = $model->isNewRecord;
        if ($isNew) {
            $model->tracking_code = ComplaintService::generateTrackingCode();
            $model->complaint_no = ComplaintService::nextComplaintNo((int) $model->fiscal_year);
            $model->status = Complaint::STATUS_REPORTED;
        }

        if (!$model->save()) {
            return false;
        }
        if ($isNew) {
            ComplaintService::log($model->id, 'create', null, $model->status, 'แจ้งเรื่อง');
        }
        return true;
    }

    private function formData(Complaint $model, array $ctx): array
    {
        return [
            'model' => $model,
            'channels' => ComplaintMaster::options('channel'),
            'types' => ComplaintMaster::options('type'),
            'relations' => ComplaintMaster::options('relationship'),
            'units' => ComplaintService::selectableUnits($ctx['empUnitId']),
            'employees' => $this->employeeMap(),
            'years' => range((int) AppHelper::YearBudget() + 1, (int) AppHelper::YearBudget() - 3),
        ];
    }

    /** @return array<int,string> employees.id => ชื่อ-สกุล (เรียงชื่อ) */
    private function employeeMap(): array
    {
        $rows = Employees::find()
            ->select(['id', 'prefix', 'fname', 'lname'])
            ->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->asArray()
            ->all();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['id']] = trim(($r['prefix'] ?? '') . $r['fname'] . ' ' . $r['lname']);
        }
        return $map;
    }

    private function findComplaint($id): Complaint
    {
        $model = Complaint::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเรื่องร้องเรียนที่ต้องการ');
        }
        return $model;
    }

    private function assertManage(Complaint $model, array $ctx): void
    {
        if (!ComplaintService::canManage($model, $ctx['userId'], $ctx['empUnitId'])) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์จัดการเรื่องนี้');
        }
    }
}
