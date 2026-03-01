<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use app\components\UserHelper;
use app\modules\approveV3\models\Approve as ApproveModel;
use app\components\AppHelper;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveType;
use app\modules\leave\models\LeaveCreateForm;
use app\components\SiteHelper;
use app\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\components\LeaveHelper;

/**
 * สร้างใบลา (2 ขั้น: กรอกรายละเอียด → ตรวจสอบ + ลงลายมือชื่อ)
 */
class LeaveController extends Controller
{
    const SESSION_KEY = 'leave_create_draft';

    /**
     * แสดงรายละเอียดใบลา (view ภายในโมดูล leave)
     * เปิดแบบ .open-modal: คืน JSON { title, content, footer } ตาม erp.js
     * @param int $id
     * @return string|array
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = Leave::find()
            ->andWhere(['id' => (int) $id])
            ->with(['employee', 'leaveType', 'leaveStatus'])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = $this->request->get('title', $model->employee ? $model->employee->getAvatar(false) : 'รายละเอียดการลา');
            return [
                'title' => $title,
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * AJAX validation สำหรับฟอร์มสร้างใบลา (ใช้กับ Kartik ActiveForm enableAjaxValidation)
     */
    public function actionValidation()
    {
        $model = new LeaveCreateForm();
        $me = UserHelper::GetEmployee();
        if ($me) {
            $model->emp_id = $me->id;
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
        return $this->redirect(['create']);
    }

    /**
     * อนุมัติ/ไม่อนุมัติขั้นตอนการลา (ใช้ตาราง approve + approveV3)
     * แทนที่ /approve-v2/leave/update เพื่อไม่พึ่ง approveV2
     */
    public function actionApproveUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = ApproveModel::find()
            ->andWhere(['id' => (int) $id, 'name' => 'leave'])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการอนุมัติ');
        }
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }
        $me = UserHelper::GetEmployee();
        $status = (string) Yii::$app->request->post('status');
        if (!in_array($status, ['Pass', 'Reject'], true)) {
            return ['status' => 'error', 'message' => 'Invalid status'];
        }
        $model->data_json = ArrayHelper::merge(
            (array) $model->data_json,
            ['approve_date' => date('Y-m-d H:i:s')]
        );
        $model->status = $status;
        if (empty($model->emp_id)) {
            $model->emp_id = $me->id;
        }
        if (!$model->save(false)) {
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ'];
        }
        $leave = Leave::findOne((int) $model->from_id);
        if (!$leave) {
            return ['status' => 'success'];
        }
        if ($status === 'Reject') {
            $leave->status = 'Reject';
            $leave->save(false);
            $leave->MsgReject();
            return ['status' => 'success'];
        }
        if ($model->maxLevel() && $status === 'Pass') {
            $leave->status = 'Approve';
            $leave->save(false);
            $leave->MsgApprove();
            return ['status' => 'success'];
        }
        $statusMap = [
            1 => ['Pass' => 'Checking1_pass', 'Reject' => 'Checking1_reject'],
            2 => ['Pass' => 'Checking2_pass', 'Reject' => 'Checking2_reject'],
            3 => ['Pass' => 'Checkup_pass', 'Reject' => 'Checkup_reject'],
            4 => ['Pass' => 'Approve', 'Reject' => 'Reject'],
        ];
        if (isset($statusMap[$model->level][$status])) {
            $leave->status = $statusMap[$model->level][$status];
            $leave->save(false);
        }
        $nextApprove = ApproveModel::findOne([
            'from_id' => $model->from_id,
            'name' => 'leave',
            'level' => $model->level + 1,
        ]);
        if ($nextApprove && $status === 'Pass') {
            $nextApprove->status = 'Pending';
            $nextApprove->save(false);
        }
        return ['status' => 'success'];
    }

    /**
     * คืนวันลา (ผู้อนุมัติยืนยันการยกเลิก) — แทนที่ /hr/leave/cancel
     */
    public function actionCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }
        $me = UserHelper::GetEmployee();
        $leave = Leave::findOne((int) $id);
        if ($leave === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        if ($leave->status !== 'ReqCancel') {
            return ['status' => 'error', 'message' => 'สถานะไม่ถูกต้อง'];
        }
        $leave->status = 'Cancel';
        $leave->data_json = ArrayHelper::merge((array) $leave->data_json, [
            'cancel_date' => date('Y-m-d H:i:s'),
            'cancel_user_id' => Yii::$app->user->id,
            'cancel_emp_id' => $me->id,
            'cancel_fullname' => $me->fullname,
        ]);
        $leave->save(false);
        if ($leave->employee && $leave->employee->user && !empty($leave->employee->user->line_id)) {
            $message = 'ขอยกเลิกวัน' . ($leave->leaveType ? $leave->leaveType->title : '') . ' วันที่ ' . \Yii::$app->formatter->asDate($leave->date_start, 'long') . ' ถึง ' . \Yii::$app->formatter->asDate($leave->date_end, 'long') . ' ได้รับการอนุมัติแล้ว';
            \app\components\LineMsg::sendMsg($leave->employee->user->line_id, $message);
        }
        return ['status' => 'success'];
    }

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/default/index']);
        }

        $thaiYear = (int) AppHelper::YearBudget();
        $budgetRange = AppHelper::BudgetYearRange($thaiYear);
        $roundLabel = 'รอบที่ 1 (1 ม.ค. ' . substr($thaiYear - 1, 2) . ' - 31 มี.ค. ' . substr($thaiYear, 2) . ')';

        $typesQuery = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
        if (isset($me->gender) && $me->gender === 'ชาย') {
            $typesQuery->andWhere(['not in', 'code', ['LT2']]);
        } elseif (isset($me->gender) && $me->gender === 'หญิง') {
            $typesQuery->andWhere(['not in', 'code', ['LT8', 'LT7']]);
        }
        $types = $typesQuery->all();

        $hiddenCodes = [];
        if (isset($me->gender) && $me->gender === 'ชาย') {
            $hiddenCodes = ['LT2'];
        } elseif (isset($me->gender) && $me->gender === 'หญิง') {
            $hiddenCodes = ['LT8', 'LT7'];
        }
        $stats = $this->getStatsForCreate($me->id, $thaiYear, $me->gender ?? null);
        $model = new LeaveCreateForm();
        $model->emp_id = $me->id;
        $model->contact_phone = $me->phone ?? '';
        $model->address = $me->fulladdress ?? '';

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->emp_id = $me->id;
            if ($model->validate()) {
                $leaveTypeId = $model->leave_type_id;
                $dateStart = trim((string) $model->date_start);
                $dateEnd = trim((string) $model->date_end);
                $reason = trim((string) $model->reason);
                $address = trim((string) $model->address);
                $contactPhone = trim((string) $model->contact_phone);
                $placeGo = trim((string) $model->place_go);
                $leaveTimeType = (float) $model->leave_time_type;
                if ($leaveTimeType !== 1.0 && $leaveTimeType !== 0.5) {
                    $leaveTimeType = 1.0;
                }

                $dateStartGregorian = AppHelper::convertToGregorian($dateStart);
                $dateEndGregorian = AppHelper::convertToGregorian($dateEnd);
            $daySummary = $this->getDaySummary($dateStartGregorian, $dateEndGregorian, $me->id);
            $allDays = (int) ($daySummary['allDays'] ?? 0);
            $satsunDays = (int) ($daySummary['satsunDays'] ?? 0);
            $holidayDays = (int) ($daySummary['holiday'] ?? 0);
            $isShift8 = (isset($daySummary['shift']) && $daySummary['shift'] === 'shift');
            $calendarDays = $isShift8 ? $allDays : max(0, $allDays - $satsunDays - $holidayDays);
            $totalDays = round($calendarDays * $leaveTimeType, 2);
            $attachmentInfo = [];
            $files = UploadedFile::getInstancesByName('leave_attachments');
            if (!empty($files)) {
                $tempDir = Yii::getAlias('@runtime/leave_draft_uploads');
                FileHelper::createDirectory($tempDir);
                foreach ($files as $file) {
                    if ($file->error !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->name);
                    $tempName = uniqid('leave_', true) . '_' . $safeName;
                    $tempPath = $tempDir . '/' . $tempName;
                    if ($file->saveAs($tempPath)) {
                        $attachmentInfo[] = ['file_name' => $file->name, 'temp_path' => $tempPath];
                    }
                }
            }
            $draft = [
                'leave_type_id' => $leaveTypeId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'date_start_g' => $dateStartGregorian,
                'date_end_g' => $dateEndGregorian,
                'leave_time_type' => $leaveTimeType,
                'total_days' => $totalDays,
                'summary_calendar_days' => (int) ($daySummary['allDays'] ?? $calendarDays),
                'summary_sat_sun' => (int) ($daySummary['satsunDays'] ?? 0),
                'summary_holiday' => (int) ($daySummary['holiday'] ?? 0),
                'reason' => $reason,
                'address' => $address,
                'contact_phone' => $contactPhone,
                'place_go' => $placeGo,
                'attachment_info' => $attachmentInfo,
            ];
                Yii::$app->session->set(self::SESSION_KEY, $draft);
                return $this->redirect(['confirm']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'employee' => $me,
            'types' => $types,
            'stats' => $stats,
            'roundLabel' => $roundLabel,
            'thaiYear' => $thaiYear,
        ]);
    }

    public function actionConfirm()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return $this->redirect(['/leave/default/index']);
        }
        $draft = Yii::$app->session->get(self::SESSION_KEY);
        if (!$draft) {
            Yii::$app->session->setFlash('warning', 'ไม่พบข้อมูลกรอกใบลา กรุณากรอกใหม่');
            return $this->redirect(['create']);
        }

        $leaveType = LeaveType::find()->where(['name' => 'leave_type', 'code' => $draft['leave_type_id']])->one();

        $signatureSystemUrl = null;
        if ($me && method_exists($me, 'SignatureShow')) {
            $signatureSystemUrl = $me->SignatureShow();
        }

        return $this->render('confirm', [
            'employee' => $me,
            'draft' => $draft,
            'leaveType' => $leaveType,
            'signatureSystemUrl' => $signatureSystemUrl,
        ]);
    }

    public function actionSave()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน'];
            }
            return $this->redirect(['/leave/default/index']);
        }

        $draft = Yii::$app->session->get(self::SESSION_KEY);
        if (!$draft) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'หมดอายุ กรุณากรอกใบลาใหม่'];
            }
            return $this->redirect(['create']);
        }

        $signatureType = trim((string) Yii::$app->request->post('signature_type', 'canvas'));
        $signatureData = Yii::$app->request->post('signature_data'); // base64 image or null

        if ($signatureType === 'system' && $me && method_exists($me, 'signature')) {
            $signaturePath = $me->signature();
            if ($signaturePath && is_file($signaturePath)) {
                $mime = 'image/png';
                if (preg_match('/\.(jpe?g|gif|webp)$/i', $signaturePath)) {
                    $mime = 'image/jpeg';
                    if (preg_match('/\.gif$/i', $signaturePath)) $mime = 'image/gif';
                    if (preg_match('/\.webp$/i', $signaturePath)) $mime = 'image/webp';
                }
                $signatureData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($signaturePath));
            }
        }

        $model = new Leave([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $draft['leave_type_id'],
            'date_start' => $draft['date_start_g'],
            'date_end' => $draft['date_end_g'],
            'leave_time_type' => (float) ($draft['leave_time_type'] ?? 1),
            'total_days' => (float) $draft['total_days'],
            'emp_id' => $me->id,
            'on_holidays' => 0,
            'thai_year' => (int) AppHelper::YearBudget($draft['date_start_g']),
        ]);

        $approve = $model->Approve();
        $model->data_json = [
            'reason' => $draft['reason'] ?? '',
            'address' => $draft['address'] ?? '',
            'work_shift' => $me->work_shift ?? null,
            'approve_1' => $approve['approve_1']['id'] ?? null,
            'approve_2' => $approve['approve_2']['id'] ?? null,
            'leave_contact_phone' => $draft['contact_phone'] ?? $me->phone ?? '',
            'place_go' => $draft['place_go'] ?? '',
            'director' => SiteHelper::viewDirector()['id'] ?? null,
            'director_fullname' => SiteHelper::viewDirector()['fullname'] ?? '',
            'signature_data' => $signatureData,
            'signature_type' => ($signatureType === 'system' ? 'system' : 'canvas'),
            'summary_calendar_days' => (int) ($draft['summary_calendar_days'] ?? 0),
            'summary_sat_sun' => (int) ($draft['summary_sat_sun'] ?? 0),
            'summary_holiday' => (int) ($draft['summary_holiday'] ?? 0),
        ];

        $model->status = $me->isDirector() ? 'Approve' : 'Pending';
        if ($model->save(false)) {
            $this->saveDraftAttachments($model, $draft['attachment_info'] ?? []);
            if (!$me->isDirector()) {
                $model->createApprove();
            }
            Yii::$app->session->remove(self::SESSION_KEY);
            Yii::$app->session->setFlash('success', 'สร้างใบลาสำเร็จ');
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true, 'redirect' => Url::to(['/leave/default/index'])];
            }
            return $this->redirect(['/leave/default/index']);
        }

        Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }
        return $this->redirect(['confirm']);
    }

    protected function getStatsForCreate($empId, $thaiYear, $gender = null)
    {
        $typesQuery = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['id' => SORT_ASC]);
        if ($gender === 'ชาย') {
            $typesQuery->andWhere(['not in', 'code', ['LT2']]);
        } elseif ($gender === 'หญิง') {
            $typesQuery->andWhere(['not in', 'code', ['LT8', 'LT7']]);
        }
        $types = $typesQuery->all();
        $rows = [];
        foreach ($types as $t) {
            $used = Leave::find()
                ->where([
                    'emp_id' => $empId,
                    'thai_year' => $thaiYear,
                    'leave_type_id' => $t->code,
                    'status' => 'Approve',
                ])
                ->select(['days' => 'SUM(total_days)', 'times' => 'COUNT(id)'])
                ->asArray()
                ->one();
            $rows[] = [
                'code' => $t->code,
                'title' => $t->title,
                'used_days' => (float) ($used['days'] ?? 0),
                'used_times' => (int) ($used['times'] ?? 0),
            ];
        }
        return $rows;
    }

    protected function calDays($start, $end)
    {
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if ($endTs < $startTs) {
            return 0;
        }
        $days = (($endTs - $startTs) / 86400) + 1;
        return round($days, 2);
    }

    /**
     * สรุปวันลา: จำนวนวันตามปฏิทิน, วันเสาร์-อาทิตย์, วันหยุดนักขัตฤกษ์
     * @param string $dateStartGregorian Y-m-d
     * @param string $dateEndGregorian Y-m-d
     * @param int|null $empId
     * @return array allDays, satsunDays, holiday
     */
    protected function getDaySummary($dateStartGregorian, $dateEndGregorian, $empId = null)
    {
        if (!$dateStartGregorian || !$dateEndGregorian || strtotime($dateEndGregorian) < strtotime($dateStartGregorian)) {
            return ['allDays' => 0, 'satsunDays' => 0, 'holiday' => 0, 'shift' => null];
        }
        return LeaveHelper::CalDay($dateStartGregorian, $dateEndGregorian, $empId);
    }

    /**
     * AJAX: คืนค่าสรุปวันลา (จำนวนวัน, เสาร์-อาทิตย์, วันหยุด) ตามช่วงวันที่
     */
    public function actionSummaryDays()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $dateStartTh = trim((string) Yii::$app->request->get('date_start', ''));
        $dateEndTh = trim((string) Yii::$app->request->get('date_end', ''));
        $leaveTimeType = (float) Yii::$app->request->get('leave_time_type', 1);
        if ($dateStartTh === '' || $dateEndTh === '') {
            return ['calendar_days' => 0, 'sat_sun_days' => 0, 'holiday_days' => 0, 'total_leave_days' => 0];
        }
        $dateStartG = AppHelper::convertToGregorian($dateStartTh);
        $dateEndG = AppHelper::convertToGregorian($dateEndTh);
        if (!$dateStartG || !$dateEndG || strtotime($dateEndG) < strtotime($dateStartG)) {
            return ['calendar_days' => 0, 'sat_sun_days' => 0, 'holiday_days' => 0, 'total_leave_days' => 0];
        }
        $summary = $this->getDaySummary($dateStartG, $dateEndG, $me ? $me->id : null);
        $allDays = (int) ($summary['allDays'] ?? 0);
        $satsunDays = (int) ($summary['satsunDays'] ?? 0);
        $holidayDays = (int) ($summary['holiday'] ?? 0);
        $isShift8 = (isset($summary['shift']) && $summary['shift'] === 'shift');
        // เวร 8: เอาวันหยุดมาคิด (ใช้จำนวนวันตามปฏิทินทั้งหมด)
        // เวรปกติ: หักวันหยุดและเสาร์-อาทิตย์ออก
        $calendarDays = $isShift8 ? $allDays : max(0, $allDays - $satsunDays - $holidayDays);
        $totalLeaveDays = round($calendarDays * $leaveTimeType, 2);
        return [
            'calendar_days' => $allDays,
            'sat_sun_days' => $satsunDays,
            'holiday_days' => $holidayDays,
            'total_leave_days' => $totalLeaveDays,
        ];
    }

    /**
     * เงื่อนไข validation การสร้างใบลา
     * @return string[] รายการข้อความ error ถ้ามี
     */
    protected function validateCreateInput($empId, $leaveTypeId, $dateStart, $dateEnd, $reason, $address)
    {
        $errors = [];

        if ($leaveTypeId === '' || $leaveTypeId === null) {
            $errors[] = 'กรุณาเลือกประเภทการลา';
        }
        if ($dateStart === '') {
            $errors[] = 'กรุณาระบุวันที่เริ่มต้น';
        }
        if ($dateEnd === '') {
            $errors[] = 'กรุณาระบุวันที่สิ้นสุด';
        }
        if ($reason === '') {
            $errors[] = 'กรุณาระบุเหตุผลการลา';
        }
        if ($address === '') {
            $errors[] = 'กรุณาระบุที่อยู่ที่ติดต่อได้';
        }

        $dateStartGregorian = $dateStart !== '' ? AppHelper::convertToGregorian($dateStart) : '';
        $dateEndGregorian = $dateEnd !== '' ? AppHelper::convertToGregorian($dateEnd) : '';

        if ($dateStartGregorian !== '' && $dateEndGregorian !== '' && strtotime($dateStartGregorian) > strtotime($dateEndGregorian)) {
            $errors[] = 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น';
        }

        // ตรวจสอบวันลาซ้ำ (ช่วงวันที่ทับกับรายการเดิม)
        if ($dateStartGregorian !== '' && $dateEndGregorian !== '' && empty($errors)) {
            $exists = Leave::find()
                ->where(['emp_id' => $empId])
                ->andWhere(['<=', 'date_start', $dateEndGregorian])
                ->andWhere(['>=', 'date_end', $dateStartGregorian])
                ->andWhere(['NOT IN', 'status', ['Cancel']])
                ->exists();
            if ($exists) {
                $errors[] = 'คุณลาในวันนี้แล้ว (ช่วงวันที่ซ้ำกับรายการเดิม)';
            }
        }

        return $errors;
    }

    /**
     * บันทึกไฟล์แนบจาก draft ไปยัง Uploads (ref = leave->ref, name = leave_file)
     */
    protected function saveDraftAttachments(Leave $model, array $attachmentInfo)
    {
        if (empty($attachmentInfo)) {
            return;
        }
        FileManagerHelper::CreateDir($model->ref);
        $basePath = FileManagerHelper::getUploadPath() . $model->ref . '/';
        foreach ($attachmentInfo as $item) {
            $tempPath = $item['temp_path'] ?? '';
            $fileName = $item['file_name'] ?? 'file';
            if (!is_file($tempPath)) {
                continue;
            }
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $realFileName = md5(uniqid($fileName, true)) . '.' . $ext;
            $destPath = $basePath . $realFileName;
            if (copy($tempPath, $destPath)) {
                $upload = new Uploads();
                $upload->ref = $model->ref;
                $upload->file_name = $fileName;
                $upload->real_filename = $realFileName;
                $upload->name = 'leave_file';
                $upload->save(false);
            }
            @unlink($tempPath);
        }
    }
}
