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
use app\modules\attendance\services\RosterAttendance;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $monthStart = date('Y-m-01 00:00:00');
        $locations = [];
        $todayCount = 0;
        $weekCount = 0;
        $monthCount = 0;
        $pendingCount = 0;
        $lastCheckin = null;
        $statsAll = null;
        $recentCheckinsAll = [];
        $isAdminOrHr = Yii::$app->user->can('admin') || Yii::$app->user->can('hr');

        try {
            $locations = CheckinLocation::find()->where(['active' => 1])->all();
            $todayCount = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->andWhere(['>=', 'checkin_at', $todayStart])
                ->andWhere(['<=', 'checkin_at', $todayEnd])
                ->count();
            $weekCount = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->andWhere(['>=', 'checkin_at', $weekStart])
                ->count();
            $monthCount = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->andWhere(['>=', 'checkin_at', $monthStart])
                ->count();
            $pendingCount = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->andWhere(['status' => CheckinRecord::STATUS_PENDING])
                ->count();
            $lastCheckin = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->orderBy(['checkin_at' => SORT_DESC])
                ->one();
            if ($isAdminOrHr) {
                $statsAll = [
                    'todayCount' => CheckinRecord::find()
                        ->andWhere(['>=', 'checkin_at', $todayStart])
                        ->andWhere(['<=', 'checkin_at', $todayEnd])
                        ->count(),
                    'weekCount' => CheckinRecord::find()
                        ->andWhere(['>=', 'checkin_at', $weekStart])
                        ->count(),
                    'monthCount' => CheckinRecord::find()
                        ->andWhere(['>=', 'checkin_at', $monthStart])
                        ->count(),
                    'pendingCount' => CheckinRecord::find()
                        ->andWhere(['status' => CheckinRecord::STATUS_PENDING])
                        ->count(),
                ];
                $recentCheckinsAll = CheckinRecord::find()
                    ->with(['employee'])
                    ->orderBy(['checkin_at' => SORT_DESC])
                    ->limit(10)
                    ->all();
            }
        } catch (\Throwable $e) {
            // ตาราง checkin_record / checkin_location ยังไม่มี (ยังไม่รัน migration)
        }

        return $this->render('index', [
            'employee' => $me,
            'locations' => $locations,
            'todayCount' => $todayCount,
            'weekCount' => $weekCount,
            'monthCount' => $monthCount,
            'pendingCount' => $pendingCount,
            'lastCheckin' => $lastCheckin,
            'isAdminOrHr' => $isAdminOrHr,
            'statsAll' => $statsAll,
            'recentCheckinsAll' => $recentCheckinsAll,
        ]);
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
            $record = AttendanceService::record($me, Yii::$app->request->post());
            return ['success' => true, 'id' => $record->id, 'checkin_at' => $record->checkin_at,
                'message' => 'บันทึกเวลา ' . $record->checkin_at . ' แล้ว รอหัวหน้าหรือผู้มีสิทธิตรวจสอบอนุมัติ',
                'attendance' => RosterAttendance::forRecord($record)];
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
        return ['shifts' => RosterAttendance::candidates((int)$me->id, AttendanceService::now()), 'now' => AttendanceService::now()];
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
        if (!$file || $file->error !== UPLOAD_ERR_OK || $file->size > 5 * 1024 * 1024 || !@getimagesize($file->tempName) || !in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return ['url' => null, 'error' => 'กรุณาเลือกไฟล์รูปภาพ'];
        }
        $dir = Yii::getAlias('@webroot/uploads/checkin');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = date('Ymd_His') . '_' . $me->id . '_' . substr(md5(uniqid('', true)), 0, 8) . '.' . $file->extension;
        $path = $dir . '/' . $name;
        if ($file->saveAs($path)) {
            return ['url' => 'uploads/checkin/' . $name];
        }
        return ['url' => null, 'error' => 'บันทึกไฟล์ไม่สำเร็จ'];
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
