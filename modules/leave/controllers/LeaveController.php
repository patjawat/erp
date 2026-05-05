<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\modules\pdfTemplate\services\PdfTemplateService;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\approveV2\models\Approve as ApproveModel;
use app\components\AppHelper;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveType;
use app\modules\leave\models\LeaveCreateForm;
use app\modules\leave\components\LeaveApprovalService;
use app\components\SiteHelper;
use app\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\leave\components\LeaveHelper;

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
     * คืน URL สำหรับพิมพ์แบบเดิม (ไฟล์ leave_templates) — ใช้เฉพาะ actionPrint เมื่อไม่ได้ผ่าน actionPdf
     */
    protected function getPreviewPdfUrlForLeave(Leave $model): ?string
    {
        $baseDir = Yii::getAlias('@app') . '/modules/filemanager/fileupload/leave_templates';
        $ltCode = $model->leaveType->code ?? null;
        $safe = $ltCode ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $ltCode) : '';
        $hasPerType = $safe !== '' && is_file($baseDir . '/' . $safe . '/template.pdf');
        $hasDefault = is_file($baseDir . '/default/template.pdf');
        if (!$hasPerType && !$hasDefault) {
            return null;
        }
        return Url::to(['/leave/leave/print', 'id' => $model->id]);
    }

    /**
     * พิมพ์ใบลา (PDF) — แสดงผลแบบเดียวกับ /hr/development/print
     * ถ้ามีเทมเพลต PDF ส่ง PDF โดยตรง (inline) ไม่ redirect
     * ถ้าไม่มีเทมเพลต แสดงแบบ HTML สำหรับพิมพ์
     */
    public function actionPrint($id)
    {
        $model = Leave::find()
            ->andWhere(['id' => (int) $id])
            ->with(['employee', 'leaveType', 'leaveStatus'])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        if ($this->getPreviewPdfUrlForLeave($model) !== null) {
            return Yii::$app->runAction('leave/setting/leave-pdf', ['id' => (int) $id]);
        }
        return $this->render('print', [
            'model' => $model,
            'pdfUrl' => null,
        ]);
    }

    /**
     * พิมพ์ใบลา (PDF) — ลองใช้เทมเพลตจาก /pdf-template/template ก่อน หากยังไม่ตั้งค่าจะใช้แบบฟอร์มใบลาเดิม (leave_templates) หรือหน้า HTML
     */
    public function actionPdf($id)
    {
        $id = (int) $id;
        if (Yii::$app->hasModule('pdf-template')) {
            try {
                $binary = (new PdfTemplateService())->generateLeavePdfBinary($id);
                Yii::$app->response->format = Response::FORMAT_RAW;
                Yii::$app->response->content = $binary;
                Yii::$app->response->headers->set('Content-Type', 'application/pdf');
                Yii::$app->response->headers->set(
                    'Content-Disposition',
                    'inline; filename="' . addslashes('leave-' . $id . '.pdf') . '"'
                );
                return Yii::$app->response;
            } catch (NotFoundHttpException $e) {
                // ไม่มีเทมเพลต pdf-template หรือข้อมูลไม่พร้อม — fallback
            }
        }
        return $this->actionPrint($id);
    }

    /**
     * แก้ไขใบลา — ฟอร์มและบันทึกอยู่ภายในโมดูล leave (/leave/leave/update)
     * เฉพาะเจ้าของใบลา และยังไม่มีผู้อนุมัติ/ไม่อนุมัติ (สถานะรอ หน.เห็นชอบ = Pending ยังแก้ไขได้)
     */
    public function actionUpdate($id)
    {
        $model = Leave::find()->andWhere(['id' => (int) $id])->with(['employee', 'leaveType'])->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }

        $me = UserHelper::GetEmployee();
        // 1. ตรวจสอบว่าเป็นผู้ดูแลระบบหรือไม่
        $isAdmin = Yii::$app->user->can('leave'); 

        // 2. เช็คสิทธิ์เจ้าของใบลา (ถ้าไม่ใช่ Admin และไม่ใช่เจ้าของ -> ปฏิเสธ)
        if (!$isAdmin && (!$me || $me->id != $model->emp_id)) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['title' => 'แก้ไข', 'content' => '<p class="text-center text-muted mb-0">ไม่ใช่เจ้าของใบลาและไม่มีสิทธิ์ผู้ดูแลระบบ</p>', 'footer' => ''];
            }
            Yii::$app->session->setFlash('error', 'ไม่ใช่เจ้าของใบลาและไม่มีสิทธิ์ผู้ดูแลระบบ');
            return $this->redirect(['/leave/default/index']);
        }

        // 3. เช็คสถานะการอนุมัติ (ถ้าไม่ใช่ Admin และมีการอนุมัติแล้ว -> ปฏิเสธ)
        if (!$isAdmin && $model->hasApprovalDecision()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['title' => 'แก้ไข', 'content' => '<p class="text-center text-muted mb-0">ใบลานี้มีการอนุมัติ/ไม่อนุมัติแล้ว ไม่สามารถแก้ไขได้</p>', 'footer' => ''];
            }
            Yii::$app->session->setFlash('error', 'ใบลานี้มีการอนุมัติ/ไม่อนุมัติแล้ว ไม่สามารถแก้ไขได้');
            return $this->redirect(['/leave/default/index']);
        }

        // --- ส่วนที่เหลือคงเดิม ---
        
        // แปลงวันที่เป็น พ.ศ. สำหรับแสดงในฟอร์ม
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end   = AppHelper::convertToThai($model->date_end);

        $leaveWorkSendInitText = '';
        try {
            $emp = $model->getLeaveWorkSend();
            if ($emp) {
                $leaveWorkSendInitText = $emp->getAvatar(false);
            }
        } catch (\Throwable $e) {
        }

        $thaiYear = (int) ($model->thai_year ?? AppHelper::YearBudget());
        $roundLabel = 'รอบที่ 1 (1 ม.ค. ' . substr($thaiYear - 1, 2) . ' - 31 มี.ค. ' . substr($thaiYear, 2) . ')';
        $stats = $this->getStatsForCreate($model->emp_id, $thaiYear, $model->employee->gender ?? null);

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end   = AppHelper::convertToGregorian($model->date_end);
            
            $oldJson = $model->getOldAttribute('data_json');
            if (is_string($oldJson)) {
                $oldJson = json_decode($oldJson, true) ?: [];
            }
            $model->data_json = array_merge((array) $oldJson, (array) $model->data_json);
            
            if ($model->save(false)) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'redirect' => Url::to(['/leave/approver/index'])];
                }
                return $this->redirect(['/leave/approver/index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = Yii::$app->request->get('title', 'แก้ไข');
            return [
                'title'   => $title,
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                    'leaveWorkSendInitText' => $leaveWorkSendInitText,
                    'stats' => $stats,
                    'roundLabel' => $roundLabel,
                ]),
                'footer'  => '',
            ];
        }
        return $this->render('update', [
            'model' => $model,
            'leaveWorkSendInitText' => $leaveWorkSendInitText,
            'stats' => $stats,
            'roundLabel' => $roundLabel,
        ]);
    }

    /**
     * Ajax validation สำหรับฟอร์มแก้ไขใบลา
     */
    public function actionUpdateValidation($id)
    {
        $model = Leave::findOne((int) $id);
        if ($model === null) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [];
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end   = AppHelper::convertToGregorian($model->date_end);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [];
    }

    /**
     * แสดง/ดาวน์โหลดไฟล์แนบใบรับรองแพทย์ (ส่งด้วย sendFile เพื่อไม่ให้ binary ไปปนกับ HTML)
     * ตรวจสิทธิ์: ต้องเป็นเจ้าของใบลาหรือมีสิทธิ์ดูใบลานั้น
     */
    public function actionShowFile($id)
    {
        $upload = Uploads::findOne((int) $id);
        if ($upload === null || $upload->ref === null || $upload->ref === '') {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        $leave = Leave::find()->andWhere(['ref' => $upload->ref])->one();
        if ($leave === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $me = UserHelper::GetEmployee();
        $canAdmin = Yii::$app->user->can('admin');
        $canLeave = Yii::$app->user->can('leave');
        $isApprover = false;
        if ($me) {
            $isApprover = ApproveModel::find()
                ->where([
                    'name' => 'leave',
                    'from_id' => (string) $leave->id,
                    'emp_id' => $me->id,
                ])
                ->exists();
        }
        if (!$me || ($me->id != $leave->emp_id && !$canAdmin && !$canLeave && !$isApprover)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ดูไฟล์นี้');
        }
        $basePath = FileManagerHelper::getUploadPath() . $upload->ref . '/';
        $filePath = $basePath . $upload->real_filename;
        if (!is_file($filePath)) {
            $filePath = $basePath . 'thumbnail/' . $upload->real_filename;
        }
        if (!is_file($filePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        $mimeTypes = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $ext = strtolower(pathinfo($upload->file_name ?? $upload->real_filename, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        $fileName = $upload->file_name ?: $upload->real_filename;
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', $mime);
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . addslashes($fileName) . '"');
        Yii::$app->response->data = file_get_contents($filePath);
        return Yii::$app->response;
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
     * อนุมัติ/ไม่อนุมัติขั้นตอนการลา (ใช้ตาราง approve + approveV2)
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
        $userIsChecker = Yii::$app->user->can('leave');
        $userIsOwner = $me && (int) $model->emp_id === (int) $me->id;
        $canApprove = $model->status === 'Pending' && ($userIsOwner || (empty($model->emp_id) && $userIsChecker));
        if (!$canApprove) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์อนุมัติรายการนี้'];
        }

        $result = (new LeaveApprovalService())->process($model, $status, $me ? (int) $me->id : null);
        if (!($result['ok'] ?? false)) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
        }

        return ['status' => 'success'];
    }

    /**
     * ขอยกเลิกวันลา (เจ้าของใบลา) — แทนที่ /me/leave/req-cancel ให้ link อยู่ภายในโมดูล leave
     */
    public function actionReqCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $model = Leave::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        if (Yii::$app->request->isPost && $me && $me->id == $model->emp_id) {
            $model->status = 'ReqCancel';
            if ($model->save(false)) {
                return ['status' => 'success'];
            }
        }
        return ['status' => 'error'];
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
        $roundLabel = 'รอบที่ 1 (1 ม.ค. ' . substr($thaiYear - 1, 2) . ' - 31 มี.ค. ' . substr($thaiYear, 2) . ')';

        $types = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $stats = $this->getStatsForCreate($me->id, $thaiYear, $me->gender ?? null);
        $model = new LeaveCreateForm();
        $model->emp_id = $me->id;
        $model->contact_phone = $me->phone ?? '';
        $model->address = $me->fulladdress ?? '';
        $model->work_shift = $me->work_shift ?? 'normal';

        // คลิกจากปฏิทินการลา: เซ็ต date_start และ date_end จาก GET date (Y-m-d)
        $dateParam = trim((string) Yii::$app->request->get('date', ''));
        if ($dateParam !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
            $dateThai = AppHelper::convertToThai($dateParam);
            if ($dateThai !== '' && $dateThai !== null) {
                $model->date_start = $dateThai;
                $model->date_end   = $dateThai;
            }
        }

        // สร้าง draft ref ล่วงหน้าสำหรับ FileInput AJAX upload
        $existingDraft = Yii::$app->session->get(self::SESSION_KEY);
        $draftRef = $existingDraft['ref'] ?? substr(Yii::$app->getSecurity()->generateRandomString(), 0, 22);
        if (!isset($existingDraft['ref'])) {
            Yii::$app->session->set(self::SESSION_KEY, array_merge((array)$existingDraft, ['ref' => $draftRef]));
            FileManagerHelper::CreateDir($draftRef);
        }

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

                $dateStartGregorian = AppHelper::convertToGregorian($dateStart);
                $dateEndGregorian = AppHelper::convertToGregorian($dateEnd);
                // ใช้ค่าจากฟอร์ม (date_start_type / date_end_type) ให้ตรงกับ JS + actionCalDays — ไม่ใช้ leave_time_type เพราะฟอร์มปัจจุบันไม่ส่งฟิลด์นั้น
                $dateStartType = (trim((string) $model->date_start_type) === '0.5') ? '0.5' : '0';
                $dateEndType = (trim((string) $model->date_end_type) === '0.5') ? '0.5' : '0';
                if ($dateStartGregorian && $dateEndGregorian && $dateStartGregorian === $dateEndGregorian) {
                    $dateEndType = '0';
                }
                $leaveTimeType = ($dateStartGregorian && $dateEndGregorian && $dateStartGregorian === $dateEndGregorian && $dateStartType === '0.5')
                    ? 0.5
                    : 1.0;
                $workShiftForm = $me->work_shift ?? 'normal';
                $daySummary = $this->getDaySummary($dateStartGregorian, $dateEndGregorian, $me->id);
                $allDays = (float) ($daySummary['allDays'] ?? 0);
                $satsunDays = (float) ($daySummary['satsunDays'] ?? 0);
                $holidayDays = (float) ($daySummary['holiday'] ?? 0);
                $empShift = (string) ($daySummary['shift'] ?? 'normal');
                $effectiveShift = $workShiftForm ?: $empShift;
                $dstF = (float) $dateStartType;
                $detF = (float) $dateEndType;
                if ($leaveTypeId === 'LT2') {
                    $totalDays = $allDays;
                } elseif ($effectiveShift === 'normal') {
                    $totalDays = $allDays - ($dstF + $detF) - $satsunDays - $holidayDays;
                } else {
                    $totalDays = $allDays - ($dstF + $detF);
                }
                $totalDays = max(0, round($totalDays, 2));

                $draft = [
                    'ref'                  => $draftRef,
                    'leave_type_id'        => $leaveTypeId,
                    'date_start'           => $dateStart,
                    'date_end'             => $dateEnd,
                    'date_start_g'         => $dateStartGregorian,
                    'date_end_g'           => $dateEndGregorian,
                    'date_start_type'      => $dateStartType,
                    'date_end_type'        => $dateEndType,
                    'leave_time_type'      => $leaveTimeType,
                    'total_days'           => $totalDays,
                    'summary_calendar_days'=> $allDays,
                    'summary_sat_sun'      => $satsunDays,
                    'summary_holiday'      => $holidayDays,
                    'work_shift'           => $effectiveShift,
                    'leave_work_send_id'   => null,
                    'leave_work_send_name' => '',
                    'reason'               => $reason,
                    'address'              => $address,
                    'contact_phone'        => $contactPhone,
                    'place_go'             => $placeGo,
                ];
                Yii::$app->session->set(self::SESSION_KEY, $draft);
                return $this->redirect(['confirm']);
            }
        }

        return $this->render('create', [
            'model'      => $model,
            'employee'   => $me,
            'types'      => $types,
            'stats'      => $stats,
            'roundLabel' => $roundLabel,
            'thaiYear'   => $thaiYear,
            'draftRef'   => $draftRef,
        ]);
    }

    public function actionCreateV2()
    {

        $model = new Leave();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_v2', ['model' => $model]),
            ];
        }else{
            return $this->render('_form_v2', ['model' => $model]);
        }

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

        // ใช้ ref จาก draft (ที่สร้างไว้ตั้งแต่ขั้น create เพื่อให้ FileInput AJAX ใช้ ref เดิม)
        $draftRef = $draft['ref'] ?? substr(Yii::$app->getSecurity()->generateRandomString(), 0, 22);
        $model = new Leave([
            'ref' => $draftRef,
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

        // เก็บ approve_N id ทุกระดับ (dynamic ตาม settings) — ใช้จาก draft ถ้ามี (ฟอร์มสร้างเลือกผู้อนุมัติ)
        $approveIds = [];
        foreach ($approve as $key => $info) {
            $approveIds[$key] = isset($draft[$key]) ? $draft[$key] : ($info['id'] ?? null);
        }

        $model->data_json = array_merge([
            'reason'               => $draft['reason'] ?? '',
            'address'              => $draft['address'] ?? '',
            'work_shift'           => $draft['work_shift'] ?? $me->work_shift ?? null,
            'date_start_type'      => $draft['date_start_type'] ?? '0',
            'date_end_type'        => $draft['date_end_type'] ?? '0',
            'leave_work_send_id'   => $draft['leave_work_send_id'] ?? null,
            'leave_work_send_name' => $draft['leave_work_send_name'] ?? '',
            'leave_contact_phone'  => $draft['contact_phone'] ?? $me->phone ?? '',
            'place_go'             => $draft['place_go'] ?? '',
            'director'             => SiteHelper::viewDirector()['id'] ?? null,
            'director_fullname'    => SiteHelper::viewDirector()['fullname'] ?? '',
            'signature_data'       => $signatureData,
            'signature_type'       => ($signatureType === 'system' ? 'system' : 'canvas'),
            'summary_calendar_days'=> (int) ($draft['summary_calendar_days'] ?? 0),
            'summary_sat_sun'      => (int) ($draft['summary_sat_sun'] ?? 0),
            'summary_holiday'      => (int) ($draft['summary_holiday'] ?? 0),
        ], $approveIds);

        // ผอ. ตามตั้งค่าองค์กร ให้สถานะ Approve; createApprove() จะสร้างทุกระดับเป็นผอ. และ Pass (อนุมัติตัวเองได้เลย)
        $model->status = \app\components\SiteHelper::isDirectorFromSettings($me->id) ? 'Approve' : 'Pending';
        if ($model->save(false)) {
            $model->createApprove();
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
        $types = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
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
        $dateStartTh   = trim((string) Yii::$app->request->get('date_start', ''));
        $dateEndTh     = trim((string) Yii::$app->request->get('date_end', ''));
        $dateStartType = trim((string) Yii::$app->request->get('date_start_type', '0'));
        $dateEndType   = trim((string) Yii::$app->request->get('date_end_type', '0'));
        $workShiftReq  = trim((string) Yii::$app->request->get('work_shift', ''));

        $empty = ['calendar_days' => 0, 'sat_sun_days' => 0, 'holiday_days' => 0, 'total_leave_days' => 0, 'work_shift' => ''];
        if ($dateStartTh === '' || $dateEndTh === '') return $empty;

        $dateStartG = AppHelper::convertToGregorian($dateStartTh);
        $dateEndG   = AppHelper::convertToGregorian($dateEndTh);
        if (!$dateStartG || !$dateEndG || strtotime($dateEndG) < strtotime($dateStartG)) return $empty;

        $summary    = $this->getDaySummary($dateStartG, $dateEndG, $me ? $me->id : null);
        $allDays    = (int) ($summary['allDays'] ?? 0);
        $satsunDays = (int) ($summary['satsunDays'] ?? 0);
        $holidayDays= (int) ($summary['holiday'] ?? 0);

        // work_shift: ใช้ค่าจาก request ถ้ามี ไม่งั้นใช้จากข้อมูลพนักงาน
        $empShift = $summary['shift'] ?? '';
        $effectiveShift = ($workShiftReq !== '') ? $workShiftReq : $empShift;
        $isShift8 = ($effectiveShift === 'shift');

        // เวร 8: ไม่หักวันหยุดและเสาร์-อาทิตย์
        $workDays = $isShift8 ? $allDays : max(0, $allDays - $satsunDays - $holidayDays);

        // ปรับตาม date_start_type และ date_end_type
        // วันแรก = 0.5 → หัก 0.5 วัน (ถ้า workDays >= 1)
        // วันสุดท้าย = 0.5 → หัก 0.5 วัน (ถ้าไม่ใช่วันเดียวกัน)
        $adjustment = 0;
        if ($workDays >= 1) {
            if ($dateStartType === '0.5') $adjustment += 0.5;
            if ($dateEndType === '0.5' && $dateStartG !== $dateEndG) $adjustment += 0.5;
            if ($dateStartType === '0.5' && $dateStartG === $dateEndG) {
                // วันเดียวกัน ใช้ date_start_type เท่านั้น
                $adjustment = 0.5;
            }
        }
        $totalLeaveDays = max(0, round($workDays - $adjustment, 2));

        return [
            'calendar_days'    => $allDays,
            'sat_sun_days'     => $satsunDays,
            'holiday_days'     => $holidayDays,
            'total_leave_days' => $totalLeaveDays,
            'work_shift'       => $effectiveShift,
        ];
    }

    /**
     * ค้นหาพนักงานสำหรับ Select2 AJAX — copy logic จาก /depdrop/employee-by-id
     * ใช้ raw SQL เพื่อไม่ต้องพึ่ง Employees model ที่อยู่นอก modules/leave
     */
    public function actionSearchEmployee($q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) ($q ?? Yii::$app->request->get('q', '')));
        $params = [':status' => 1];
        $where = 'status = :status AND user_id IS NOT NULL AND user_id <> 0';
        if ($q !== '') {
            $where .= ' AND (fname LIKE :q1 OR lname LIKE :q2 OR CONCAT(fname," ",lname) LIKE :q3)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        $rows = Yii::$app->db->createCommand(
            "SELECT id, fname, lname FROM `employees` WHERE {$where} ORDER BY fname, lname LIMIT 30",
            $params
        )->queryAll();
        $data = [];
        foreach ($rows as $row) {
            $fullname = trim($row['fname'] . ' ' . $row['lname']);
            $data[] = ['id' => $row['id'], 'text' => $fullname, 'fullname' => $fullname];
        }
        return ['results' => $data];
    }

    /**
     * คำนวณวันลา — copy logic จาก /hr/leave/cal-days ไว้ใน modules/leave
     * รับ: date_start, date_end (พ.ศ.), date_start_type, date_end_type, leave_type_id, work_shift (override)
     */
    public function actionCalDays()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $empId = $me ? (int) $me->id : 0;

        $dateStartType    = (float) Yii::$app->request->get('date_start_type', 0);
        $dateEndType      = (float) Yii::$app->request->get('date_end_type', 0);
        $leaveTypeId      = trim((string) Yii::$app->request->get('leave_type_id', ''));
        $workShiftOverride = trim((string) Yii::$app->request->get('work_shift', ''));
        $dateStartRaw     = trim((string) Yii::$app->request->get('date_start', ''));
        $dateEndRaw       = trim((string) Yii::$app->request->get('date_end', ''));

        $empty = ['status' => 'ok', 'allDays' => 0, 'satsunDays' => 0, 'holiday' => 0, 'total' => 0, 'shift' => '', 'shift_name' => ''];
        if ($dateStartRaw === '' || $dateEndRaw === '') return $empty;

        $dateStart = preg_replace('/\D/', '', $dateStartRaw) === '' ? '' : AppHelper::convertToGregorian($dateStartRaw);
        $dateEnd   = preg_replace('/\D/', '', $dateEndRaw)   === '' ? '' : AppHelper::convertToGregorian($dateEndRaw);
        if (!$dateStart || !$dateEnd) return $empty;
        if (strtotime($dateEnd) < strtotime($dateStart)) {
            return ['status' => 'error', 'message' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น'];
        }

        // ตรวจสอบปีงบประมาณ (raw SQL แทน model ที่อยู่นอก modules/leave)
        $checkYear = AppHelper::YearBudget($dateEnd);
        $entitleCount = (int) Yii::$app->db->createCommand(
            'SELECT COUNT(id) FROM `leave_entitlements` WHERE thai_year = :yr',
            [':yr' => $checkYear]
        )->queryScalar();
        if ($entitleCount === 0) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลสิทธิ์การลาในปี ' . $checkYear . ' กรุณาติดต่อเจ้าหน้าที่'];
        }

        $result = LeaveHelper::CalDay($dateStart, $dateEnd, $empId);
        $allDays    = (float) ($result['allDays'] ?? 0);
        $satsunDays = (float) ($result['satsunDays'] ?? 0);
        $holiday    = (float) ($result['holiday'] ?? 0);
        // ถ้า work_shift ส่งมาให้ใช้ override ก่อน ไม่งั้นใช้จาก DB
        $shift = ($workShiftOverride !== '' && in_array($workShiftOverride, ['normal', 'shift'], true))
            ? $workShiftOverride
            : (string) ($result['shift'] ?? 'normal');

        if ($leaveTypeId === 'LT2') {
            // ลาคลอดบุตร: ไม่หักวันใดทั้งนั้น
            $total = $allDays;
        } elseif ($shift === 'normal') {
            $total = $allDays - ($dateStartType + $dateEndType) - $satsunDays - $holiday;
        } else {
            // เวร 8: ไม่หักวันเสาร์-อาทิตย์และวันหยุด
            $total = $allDays - ($dateStartType + $dateEndType);
        }
        $total = max(0, round($total, 2));

        return [
            'status'     => 'ok',
            'allDays'    => $allDays,
            'satsunDays' => $satsunDays,
            'holiday'    => $holiday,
            'shift'      => $shift,
            'shift_name' => $shift === 'normal' ? 'เวรเช้า' : 'เวร 8',
            'type_days'  => round($dateStartType + $dateEndType, 2),
            'total'      => $total,
        ];
    }

    /**
     * อัปเดต work_shift ของพนักงาน — copy logic จาก /hr/work-shift/update-shift
     * ใช้ raw SQL เพื่อไม่ต้องพึ่ง Employees model ที่อยู่นอก modules/leave
     */
    public function actionUpdateWorkShift()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['success' => false];
        }
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['success' => false];
        }
        $workShift = trim((string) Yii::$app->request->post('work_shift', ''));
        if (!in_array($workShift, ['normal', 'shift'], true)) {
            return ['success' => false, 'message' => 'ค่าไม่ถูกต้อง'];
        }
        $rows = Yii::$app->db->createCommand(
            'UPDATE `employees` SET `work_shift` = :ws WHERE `id` = :id',
            [':ws' => $workShift, ':id' => (int) $me->id]
        )->execute();
        return ['success' => $rows > 0];
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
