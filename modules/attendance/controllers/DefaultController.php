<?php

namespace app\modules\attendance\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use app\components\UserHelper;
use app\modules\attendance\models\CheckinRecord;
use app\modules\attendance\models\CheckinLocation;
use app\modules\attendance\services\AttendanceService;
use app\modules\attendance\services\AttendanceAccess;
use app\modules\attendance\services\RosterAttendance;

class DefaultController extends Controller
{
    /**
     * ภาพรวมระบบลงเวลา — สำหรับผู้ดูแล (admin/hr/attendance) เท่านั้น
     * ผู้ใช้ทั่วไปลงเวลา/ดูประวัติของตัวเองผ่าน /me → ส่งไปหน้าประวัติของฉัน
     */
    public function actionIndex()
    {
        if (!\app\modules\attendance\services\WorkScheduleService::manager()) {
            return $this->redirect(['/attendance/checkin/index']);
        }
        $today = substr(AttendanceService::now(), 0, 10);
        // date = Y-m-d (ปุ่มเลื่อนวัน), d = วว/ดด/พ.ศ. (DatepickerThai)
        $thai = Yii::$app->request->get('d');
        $date = is_string($thai) && $thai !== ''
            ? (string)\app\components\AppHelper::convertToGregorian($thai)
            : Yii::$app->request->get('date', $today);
        if (!is_string($date)) $date = $today;
        $d = \DateTime::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date || $date > $today) $date = $today;

        $day = \app\modules\attendance\services\AttendanceDashboard::daily($date);
        $exceptions = \app\modules\attendance\services\AttendanceDashboard::exceptions($date);
        $pendingCount = (int)AttendanceAccess::pendingQuery()->count();
        $orphanCount = (int)AttendanceAccess::pendingQuery()
            ->andWhere(['or', ['approve.emp_id' => null], ['approve.emp_id' => 0]])
            ->count();

        return $this->render('index', compact('date', 'today', 'day', 'exceptions', 'pendingCount', 'orphanCount'));
    }

    /**
     * หน้ากดลงเวลา (QR / ถ่ายรูป / Manual) + บันทึกพิกัด
     */
    public function actionCheckin()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }
        $locations = [];
        $geofences = [];
        try {
            $locations = CheckinLocation::find()->where(['active' => 1])->all();
            $geofences = CheckinLocation::findActiveGeofenced();
        } catch (\Throwable $e) {
            // ตาราง checkin_location ยังไม่มี (ยังไม่รัน migration)
        }
        return $this->render('checkin', [
            'employee' => $me,
            'locations' => $locations,
            'geofences' => $geofences,
        ]);
    }

    /**
     * เนื้อหาลงเวลาสำหรับเปิดใน modal (.open-modal จาก /me) — คืน JSON {status,title,content,footer}
     * ตาม convention ของ web/js/erp.js
     */
    public function actionCheckinModal($check_type = 'in')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงาน'];
        }
        $checkType = in_array($check_type, [CheckinRecord::CHECK_TYPE_IN, CheckinRecord::CHECK_TYPE_OUT], true)
            ? $check_type : CheckinRecord::CHECK_TYPE_IN;
        $geofences = [];
        try {
            $geofences = CheckinLocation::findActiveGeofenced();
        } catch (\Throwable $e) {
            // ตาราง checkin_location ยังไม่มี (ยังไม่รัน migration)
        }
        $content = $this->renderAjax('_checkin_modal', [
            'geofences' => $geofences,
            'checkType' => $checkType,
            'saveUrl' => \yii\helpers\Url::to(['/attendance/default/save']),
        ]);
        return [
            'status' => 'success',
            'title' => '<i class="bi bi-clock-history me-1"></i> ลงเวลาเข้า-ออก',
            'content' => $content,
            'footer' => '',
        ];
    }

    /**
     * API: บันทึกการลงเวลา (เรียกจากฟอร์มหรือ AJAX) — หัวหน้าอนุมัติภายหลัง
     * POST: method, check_type (in|out), qr_token?, lat?, lng?, photo_path?
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) return ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน'];
        try {
            $input = Yii::$app->request->post();
            unset($input['check_type'], $input['roster_item_id']);
            $record = AttendanceService::record($me, $input, true);
            return ['success' => true, 'id' => $record->id, 'checkin_at' => $record->checkin_at,
                'duplicate' => $record->wasDuplicate, 'status' => $record->status,
                'location' => $record->locationSummary(),
                'message' => ($record->wasDuplicate ? 'บันทึกไว้แล้ว ไม่สร้างรายการซ้ำ: ' : 'บันทึกเวลาสำเร็จ: ') . $record->checkin_at . ' · ' . $record->getStatusLabel() . (RosterAttendance::forRecord($record)['shift'] ? '' : ' · รอตรวจสอบเวลางาน/ตารางเวร'),
                'attendance' => RosterAttendance::forRecord($record), 'day_summary'=>$this->daySummary((int)$me->id)];
        } catch (\DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ กรุณาลองใหม่หรือติดต่อผู้ดูแลระบบ'];
        }
    }

    public function actionShifts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) throw new \yii\web\ForbiddenHttpException('ไม่พบข้อมูลพนักงาน');
        $latest = CheckinRecord::find()->where(['emp_id'=>$me->id])->orderBy(['checkin_at'=>SORT_DESC,'id'=>SORT_DESC])->one();
        return ['shifts' => RosterAttendance::candidates((int)$me->id, AttendanceService::now()), 'now' => AttendanceService::now(), 'latest'=>$latest ? ['at'=>$latest->checkin_at,'status'=>$latest->getStatusLabel(),'status_code'=>$latest->status] : null, 'day_summary'=>$this->daySummary((int)$me->id)];
    }

    /** Calendar-day summary: never infer direction for an unmatched raw scan. */
    private function daySummary(int $employeeId): array
    {
        $date = substr(AttendanceService::now(), 0, 10);
        $next = (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
        $query = CheckinRecord::find()->where(['emp_id'=>$employeeId])
            ->andWhere(['status'=>['pending','approved']])
            ->andWhere(['>=','checkin_at',$date.' 00:00:00'])->andWhere(['<','checkin_at',$next.' 00:00:00']);
        $in = (clone $query)->andWhere(['check_type'=>'in'])->orderBy(['checkin_at'=>SORT_ASC,'id'=>SORT_ASC])->one();
        $out = (clone $query)->andWhere(['check_type'=>'out'])->orderBy(['checkin_at'=>SORT_DESC,'id'=>SORT_DESC])->one();
        // Show the first unresolved scan as a provisional entry receipt, without changing its classification.
        $provisional = false;
        if (!$in) {
            $in = (clone $query)->andWhere(['check_type'=>'scan','status'=>'pending'])->orderBy(['checkin_at'=>SORT_ASC,'id'=>SORT_ASC])->one();
            $provisional = $in !== null;
        }
        return ['date'=>$date, 'in'=>$in->checkin_at ?? null, 'out'=>$out->checkin_at ?? null,
            'in_status'=>$in->status ?? null, 'out_status'=>$out->status ?? null, 'in_provisional'=>$provisional];
    }

    public function actionPosition()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $input = Yii::$app->request->post();
        $token = $input['qr_token'] ?? '';
        if (!is_string($token)) return ['success'=>false,'message'=>'QR ไม่ถูกต้อง'];
        $result = CheckinLocation::validateClockIn($input['lat']??null, $input['lng']??null, $token, 'ตรวจตำแหน่งก่อนบันทึก');
        $name = $result['location']->name ?? null;
        $inside = $result['inside'] ?? false;
        // นอกพื้นที่: location คือ "จุดใกล้สุด" ที่ใช้วัดระยะ — แสดงเป็นระยะห่าง ไม่ใช่ชื่อจุดที่ลงเวลา
        $label = $inside || !$name ? $name : 'ห่างจาก' . $name . ' ' . \app\modules\attendance\models\CheckinRecord::distanceText((float)($result['meta']['distance_m'] ?? 0));
        return ['success'=>$result['ok'],'inside'=>$inside,'message'=>$result['message']??'', 'location'=>$label];
    }

    /**
     * อัปโหลดรูปถ่ายลงเวลา (คืน path สำหรับส่งไป actionSave)
     */
    public function actionUploadPhoto()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['url' => null, 'error' => 'ไม่พบข้อมูลพนักงาน'];
        }
        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            return ['url' => null, 'error' => 'กรุณาถ่ายรูปก่อนลงเวลา'];
        }
        try {
            // ย่อ 640px + ประทับเวลา + เก็บนอก webroot — 'url' คือ path สัมพัทธ์ที่ส่งกลับมากับ save
            return ['url' => \app\modules\attendance\services\AttendancePhoto::store($file, (int)$me->id)];
        } catch (\DomainException $e) {
            return ['url' => null, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return ['url' => null, 'error' => 'บันทึกรูปไม่สำเร็จ กรุณาลองใหม่'];
        }
    }

    /**
     * คืนค่า QR token ที่จุด (สำหรับสร้าง QR ไปแปะที่จุด)
     */
    public function actionQrCode($id)
    {
        try {
            $loc = CheckinLocation::findOne((int)$id);
        } catch (\Throwable $e) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['token' => null];
        }
        if (!$loc || !$loc->qr_token) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['token' => null];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['token' => $loc->qr_token, 'name' => $loc->name];
    }
}
