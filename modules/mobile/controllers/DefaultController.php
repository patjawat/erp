<?php

namespace app\modules\mobile\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\attendance\models\CheckinLocation;
use app\modules\attendance\models\CheckinRecord;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveType;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `mobile` module.
 * Actions: index (dashboard), news, services, scan, profile.
 * ต้องล็อกอินก่อนเข้าหน้าใดๆ
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->user->setReturnUrl(Yii::$app->request->url);
                    return Yii::$app->response->redirect(['/mobile/auth/login']);
                },
            ],
        ];
    }

    /**
     * Dashboard with summary cards.
     */
    public function actionIndex()
    {
        $this->view->title = 'บริการออนไลน์';
        return $this->render('index', [
            'current_page' => 'home',
        ]);
    }

    /**
     * Vertical news feed (ข่าวสารและประกาศ).
     */
    public function actionNews()
    {
        $this->view->title = 'ข่าวสาร';
        return $this->render('news', [
            'current_page' => 'news',
        ]);
    }

    /**
     * ดูรายละเอียดข่าวสาร (ตัวอย่าง).
     */
    public function actionNewsView($id)
    {
        $this->view->title = 'รายละเอียดข่าว';
        return $this->render('news-view', [
            'current_page' => 'news',
            'id' => (int) $id,
        ]);
    }

    /**
     * รายการการแจ้งเตือนทั้งหมด.
     */
    public function actionNotifications()
    {
        $this->view->title = 'การแจ้งเตือน';
        return $this->render('notifications', [
            'current_page' => 'home',
        ]);
    }

    /**
     * 3x3 grid menu for tools.
     */
    public function actionServices()
    {
        $this->view->title = 'บริการ';
        return $this->render('services', [
            'current_page' => 'services',
        ]);
    }

    /**
     * ลงเวลาเข้า-ออกงาน (เรียกบันทึกผ่าน attendance/default/save เหมือนเว็บหลัก).
     */
    public function actionAttendance()
    {
        $this->view->title = 'ลงเวลาเข้า-ออก';
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน กรุณาติดต่อ HR');
            return $this->redirect(['/mobile/default/index']);
        }
        $geofences = [];
        $lastCheckin = null;
        try {
            $geofences = CheckinLocation::findActiveGeofenced();
            $lastCheckin = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->orderBy(['checkin_at' => SORT_DESC])
                ->one();
        } catch (\Throwable $e) {
            $geofences = [];
            $lastCheckin = null;
        }
        return $this->render('attendance', [
            'current_page' => 'attendance',
            'employee' => $me,
            'geofences' => $geofences,
            'lastCheckin' => $lastCheckin,
        ]);
    }

    /**
     * Scan page (camera-UI mockup).
     */
    public function actionScan()
    {
        $this->view->title = 'สแกน';
        return $this->render('scan', [
            'current_page' => 'scan',
        ]);
    }

    /**
     * User profile and settings list.
     */
    public function actionProfile()
    {
        $this->view->title = 'ส่วนตัว';
        $isRoomOwner = false;
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
            $allRooms = Room::find()->where(['name' => 'meeting_room'])->all();
            foreach ($allRooms as $r) {
                $data = is_array($r->data_json) ? $r->data_json : (is_string($r->data_json) ? json_decode($r->data_json, true) : []);
                if (!empty($data['owner']) && (string) $data['owner'] === $empId) {
                    $isRoomOwner = true;
                    break;
                }
            }
        }
        return $this->render('profile', [
            'current_page' => 'profile',
            'isRoomOwner' => $isRoomOwner,
        ]);
    }

    /**
     * จองรถราชการ (mobile-first form).
     */
    public function actionBookingVehicle()
    {
        $this->view->title = 'จองรถราชการ';
        return $this->render('booking-vehicle', [
            'current_page' => 'services',
        ]);
    }

    /**
     * จองห้องประชุม (mobile-first: calendar, rooms, form). บันทึกลง modules/booking Meeting.
     */
    public function actionBookingMeeting()
    {
        $this->view->title = 'จองห้องประชุม';
        $rooms = [];
        $empId = null;
        $saveSuccess = false;
        $saveErrors = [];

        try {
            $meeting = new Meeting();
            $rooms = $meeting->listRooms();
            if (!empty(Yii::$app->user->identity->employee)) {
                $empId = Yii::$app->user->identity->employee->id;
            }
        } catch (\Throwable $e) {
            $rooms = [];
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('action') === 'submit') {
            if (!$empId) {
                Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR');
                return $this->redirect(['booking-meeting']);
            }
            $roomId = Yii::$app->request->post('room_id');
            $meetingDate = Yii::$app->request->post('meeting_date');
            $timeStart = Yii::$app->request->post('time_start');
            $timeEnd = Yii::$app->request->post('time_end');
            $title = Yii::$app->request->post('meeting_title');
            $attendees = (int) Yii::$app->request->post('attendees', 1);
            $dateGregorian = $meetingDate ? AppHelper::convertToGregorian($meetingDate) : null;
            if (!$roomId || !$dateGregorian || !$timeStart || !$timeEnd || !$title) {
                Yii::$app->session->setFlash('error', 'กรุณากรอกห้อง วันที่ เวลา และหัวข้อประชุมให้ครบ');
            } else {
                $model = new Meeting();
                $model->room_id = $roomId;
                $model->date_start = $dateGregorian;
                $model->date_end = $dateGregorian;
                $model->time_start = substr($timeStart, 0, 5);
                $model->time_end = substr($timeEnd, 0, 5);
                $model->title = $title;
                $model->emp_number = $attendees > 0 ? $attendees : 1;
                $model->emp_id = (string) $empId;
                $model->thai_year = (int) AppHelper::YearBudget($dateGregorian);
                $model->status = 'Pending';
                $urgentList = $model->listUrgent();
                $model->urgent = !empty($urgentList) ? (string) array_key_first($urgentList) : 'ปกติ';
                try {
                    $model->code = \mdm\autonumber\AutoNumber::generate('MTG' . date('ymd') . '-???');
                } catch (\Throwable $e) {
                    $model->code = 'MTG' . date('Ymd') . '-' . substr(uniqid(), -4);
                }
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'บันทึกคำจองห้องประชุมเรียบร้อย (รหัส ' . $model->code . ')');
                    return $this->redirect(['booking-meeting']);
                }
                $saveErrors = $model->getFirstErrors();
            }
        }

        return $this->render('booking-meeting', [
            'current_page' => 'services',
            'rooms' => $rooms,
            'saveSuccess' => $saveSuccess,
            'saveErrors' => $saveErrors,
        ]);
    }

    /**
     * API คืนค่าสถานะห้องประชุมตามวันที่และเวลา (สำหรับปุ่มตรวจสอบเวลาว่าง).
     * GET/POST: meeting_date (d/m/Y), time_start, time_end
     */
    public function actionMeetingRoomAvailability()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $meetingDate = Yii::$app->request->get('meeting_date') ?: Yii::$app->request->post('meeting_date');
        $timeStart = Yii::$app->request->get('time_start') ?: Yii::$app->request->post('time_start');
        $timeEnd = Yii::$app->request->get('time_end') ?: Yii::$app->request->post('time_end');
        if (!$meetingDate || !$timeStart || !$timeEnd) {
            return ['ok' => false, 'message' => 'กรุณาระบุวันที่และเวลา', 'rooms' => []];
        }
        $dateGregorian = AppHelper::convertToGregorian($meetingDate);
        if (!$dateGregorian) {
            return ['ok' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง', 'rooms' => []];
        }
        $timeStart = substr($timeStart, 0, 5);
        $timeEnd = substr($timeEnd, 0, 5);
        $excludeId = (int) (Yii::$app->request->get('exclude_id') ?: Yii::$app->request->post('exclude_id'));
        $rooms = [];
        try {
            $roomModels = Room::find()->where(['name' => 'meeting_room'])->orderBy(['title' => SORT_ASC])->all();
            foreach ($roomModels as $room) {
                $code = $room->code;
                $title = $room->title;
                $capacity = isset($room->data_json['seat_capacity']) ? (int) $room->data_json['seat_capacity'] : null;
                $overlapQuery = Meeting::find()
                    ->andWhere(['room_id' => $code])
                    ->andWhere(['<=', 'date_start', $dateGregorian])
                    ->andWhere(['>=', 'date_end', $dateGregorian])
                    ->andWhere(['<', 'time_start', $timeEnd])
                    ->andWhere(['>', 'time_end', $timeStart]);
                if ($excludeId > 0) {
                    $overlapQuery->andWhere(['!=', 'id', $excludeId]);
                }
                $hasOverlap = $overlapQuery->exists();
                $rooms[] = [
                    'code' => $code,
                    'title' => $title,
                    'capacity' => $capacity,
                    'available' => !$hasOverlap,
                ];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage(), 'rooms' => []];
        }
        return ['ok' => true, 'rooms' => $rooms];
    }

    /**
     * ขอลาออนไลน์ (mobile-first: balance, workflow, form with attachment).
     */
    public function actionLeaveRequest()
    {
        $this->view->title = 'ขอลาออนไลน์';
       $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/default/index']);
        }

        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 0, 22);

        $model = new Leave();
        $model->ref = $ref;
        $model->emp_id = $me->id;
        $model->thai_year = (int) AppHelper::YearBudget();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);
            $model->status = 'Pending';
            $model->save();
            $model->createApprove();
            return $this->redirect(['/mobile/default/leave-request-view', 'id' => $model->id]);
           
        }

        return $this->render('leave-request', [
            'current_page' => 'services',
            'model' => $model,
        ]);
    }

    public function actionLeaveRequestView($id)
    {
        $this->view->title = 'รายละเอียดคำขอลา';
        $model = Leave::findOne((int) $id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบคำขอลานี้');
            return $this->redirect(['/mobile/default/leave-request']);
        }
        if ($model->emp_id !== Yii::$app->user->identity->employee->id) {
            Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์ดูคำขอลานี้');
            return $this->redirect(['/mobile/default/leave-request']);
        }
        return $this->render('leave_request_view', [
            'current_page' => 'services',
            'model' => $model,
        ]);
    }

    /**
     * แจ้งซ่อม (mobile-first: type, location, description, camera/gallery upload, QR asset).
     */
    public function actionMaintenanceRequest()
    {
        $this->view->title = 'แจ้งซ่อม';
        return $this->render('maintenance-request', [
            'current_page' => 'services',
        ]);
    }

    /**
     * คำขอของฉัน — ดูคำขอทั้งหมด (จองห้อง, ขอลา, จองรถ, แจ้งซ่อม).
     */
    public function actionMyRequests($type = 'all')
    {
        $this->view->title = 'คำขอของฉัน';
        $empId = null;
        $meetings = [];
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = Yii::$app->user->identity->employee->id;
            try {
                $meetings = Meeting::find()
                    ->where(['emp_id' => (string) $empId])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->limit(100)
                    ->all();
            } catch (\Throwable $e) {
                $meetings = [];
            }
        }
        return $this->render('my-requests', [
            'current_page' => 'profile',
            'type' => $type,
            'meetings' => $meetings,
        ]);
    }

    /**
     * หน้ารายละเอียดคำขอจองห้องประชุม (ภายใต้ mobile เพื่อให้ UX/UI เหมือนแอป).
     * เฉพาะผู้ขอ (emp_id) เท่านั้นที่ดูได้
     */
    public function actionMeetingView($id)
    {
        $id = (int) $id;
        $meeting = Meeting::findOne($id);
        if (!$meeting) {
            \Yii::$app->session->setFlash('error', 'ไม่พบรายการจองนี้');
            return $this->redirect(['/mobile/default/my-requests', 'type' => 'meeting']);
        }
        $empId = null;
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
        }
        if ($meeting->emp_id !== $empId) {
            \Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์ดูรายการนี้');
            return $this->redirect(['/mobile/default/my-requests']);
        }
        $this->view->title = 'รายละเอียดการจองห้องประชุม';
        return $this->render('meeting-view', [
            'current_page' => 'profile',
            'meeting' => $meeting,
        ]);
    }

    /**
     * แก้ไขการจองห้องประชุม (เฉพาะผู้ขอ และเฉพาะสถานะ Pending).
     */
    public function actionMeetingUpdate($id)
    {
        $id = (int) $id;
        $meeting = Meeting::findOne($id);
        if (!$meeting) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองนี้');
            return $this->redirect(['/mobile/default/my-requests', 'type' => 'meeting']);
        }
        $empId = null;
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
        }
        if ($meeting->emp_id !== $empId) {
            Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์แก้ไขรายการนี้');
            return $this->redirect(['/mobile/default/my-requests']);
        }
        if ((string) $meeting->status !== 'Pending') {
            Yii::$app->session->setFlash('error', 'แก้ไขได้เฉพาะคำขอที่รอการอนุมัติเท่านั้น');
            return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
        }

        $rooms = [];
        try {
            $m = new Meeting();
            $rooms = $m->listRooms();
        } catch (\Throwable $e) {
            $rooms = [];
        }
        $saveErrors = [];

        if (Yii::$app->request->isPost && Yii::$app->request->post('action') === 'submit') {
            $roomId = Yii::$app->request->post('room_id');
            $meetingDate = Yii::$app->request->post('meeting_date');
            $timeStart = Yii::$app->request->post('time_start');
            $timeEnd = Yii::$app->request->post('time_end');
            $title = Yii::$app->request->post('meeting_title');
            $attendees = (int) Yii::$app->request->post('attendees', 1);
            $dateGregorian = $meetingDate ? AppHelper::convertToGregorian($meetingDate) : null;
            if (!$roomId || !$dateGregorian || !$timeStart || !$timeEnd || !$title) {
                $saveErrors['form'] = 'กรุณากรอกห้อง วันที่ เวลา และหัวข้อประชุมให้ครบ';
            } else {
                $meeting->room_id = $roomId;
                $meeting->date_start = $dateGregorian;
                $meeting->date_end = $dateGregorian;
                $meeting->time_start = substr($timeStart, 0, 5);
                $meeting->time_end = substr($timeEnd, 0, 5);
                $meeting->title = $title;
                $meeting->emp_number = $attendees > 0 ? $attendees : 1;
                $meeting->thai_year = (int) AppHelper::YearBudget($dateGregorian);
                if ($meeting->save(false)) {
                    Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขคำจองห้องประชุมเรียบร้อย');
                    return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
                }
                $saveErrors = $meeting->getFirstErrors();
            }
        }

        $this->view->title = 'แก้ไขการจองห้องประชุม';
        return $this->render('meeting-update', [
            'current_page' => 'profile',
            'meeting' => $meeting,
            'rooms' => $rooms,
            'saveErrors' => $saveErrors,
        ]);
    }

    /**
     * ผู้ดูแลห้องประชุม — รายการจองห้องที่ผู้ใช้เป็นผู้ดูแล (room.data_json.owner)
     */
    public function actionRoomManage()
    {
        $this->view->title = 'จัดการห้องประชุม';
        $empId = null;
        $ownedRoomCodes = [];
        $meetings = [];
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
            $allRooms = Room::find()->where(['name' => 'meeting_room'])->all();
            foreach ($allRooms as $r) {
                $data = is_array($r->data_json) ? $r->data_json : (is_string($r->data_json) ? json_decode($r->data_json, true) : []);
                if (!empty($data['owner']) && (string) $data['owner'] === $empId) {
                    $ownedRoomCodes[] = $r->code;
                }
            }
            if (!empty($ownedRoomCodes)) {
                $meetings = Meeting::find()
                    ->where(['room_id' => $ownedRoomCodes])
                    ->orderBy(['date_start' => SORT_DESC, 'time_start' => SORT_DESC])
                    ->limit(100)
                    ->all();
            }
        }
        return $this->render('room-manage', [
            'current_page' => 'profile',
            'meetings' => $meetings,
            'ownedRoomCodes' => $ownedRoomCodes,
        ]);
    }

    /**
     * อนุมัติ/ยกเลิกการจอง (ผู้ดูแลห้องเท่านั้น). POST: id, status (Pass|Cancel)
     */
    public function actionMeetingConfirm()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['ok' => false, 'message' => 'Invalid request'];
        }
        $id = (int) Yii::$app->request->post('id');
        $status = (string) Yii::$app->request->post('status');
        if (!in_array($status, ['Pass', 'Cancel'], true)) {
            return ['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'];
        }
        $meeting = Meeting::findOne($id);
        if (!$meeting) {
            return ['ok' => false, 'message' => 'ไม่พบรายการจอง'];
        }
        $empId = null;
        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
        }
        $room = $meeting->room_id ? Room::findOne(['name' => 'meeting_room', 'code' => $meeting->room_id]) : null;
        if (!$room) {
            return ['ok' => false, 'message' => 'ไม่พบข้อมูลห้อง'];
        }
        $data = is_array($room->data_json) ? $room->data_json : (is_string($room->data_json) ? json_decode($room->data_json, true) : []);
        $ownerId = isset($data['owner']) ? (string) $data['owner'] : null;
        if ($ownerId !== $empId) {
            return ['ok' => false, 'message' => 'คุณไม่มีสิทธิ์จัดการห้องนี้'];
        }
        $meeting->status = $status;
        if ($meeting->save(false)) {
            return ['ok' => true, 'message' => $status === 'Pass' ? 'อนุมัติการจองแล้ว' : 'ยกเลิกการจองแล้ว'];
        }
        return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    /**
     * ดูข้อมูลครุภัณฑ์ (จาก QR สแกนได้รหัส หรือเปิดโดย id).
     * รองรับ: ?id=123 หรือ ?code=AM-001 (รหัสครุภัณฑ์จาก QR)
     */
    public function actionAsset($id = null, $code = null)
    {
        $this->view->title = 'ข้อมูลครุภัณฑ์';
        $asset = null;
        if ($id && is_numeric($id)) {
            $asset = Asset::findOne(['id' => (int) $id]);
        }
        if ($asset === null && $code !== null && $code !== '') {
            $asset = Asset::findOne(['code' => trim((string) $code)]);
        }
        return $this->render('asset', [
            'current_page' => 'services',
            'id' => $asset ? $asset->id : $id,
            'code' => $code,
            'asset' => $asset,
        ]);
    }
}
