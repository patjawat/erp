<?php

namespace app\modules\mobile\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\attendance\models\CheckinLocation;
use app\modules\attendance\models\CheckinRecord;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;
use app\modules\approveV2\models\Approve as ApproveModel;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\leave\components\LeaveApprovalService;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveType;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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
                    $ref = Yii::$app->request->url;
                    Yii::$app->user->setReturnUrl($ref);
                    return Yii::$app->response->redirect(['/mobile/auth/login', 'ref' => $ref]);
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
        $officialUnreadCount = $this->countOfficialDocuments(true);
        return $this->render('index', [
            'current_page' => 'home',
            'pendingLeaveApprovals' => $this->findPendingLeaveApprovals(3),
            'recentLeaveRequests' => $this->findRecentLeaveRequests(3),
            'recentMeetings' => $this->findRecentMeetings(3),
            'officialDocumentsPreview' => $this->findOfficialDocuments(true, 3),
            'officialUnreadCount' => $officialUnreadCount,
        ]);
    }

    /**
     * หนังสือราชการที่ส่งมาถึงผู้ใช้ปัจจุบัน
     */
    public function actionNews()
    {
        $filter = trim((string) Yii::$app->request->get('filter', 'all'));
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        $this->view->title = 'หนังสือราชการ';
        return $this->render('news', [
            'current_page' => 'news',
            'filter' => $filter,
            'dataProvider' => $this->buildOfficialDocumentsProvider($filter),
            'officialUnreadCount' => $this->countOfficialDocuments('unread'),
            'officialTotalCount' => $this->countOfficialDocuments('all'),
        ]);
    }

    /**
     * ดูรายละเอียดหนังสือราชการ
     */
    public function actionNewsView($id)
    {
        $detail = $this->findOfficialDocumentDetail((int) $id);
        if (!$detail) {
            throw new NotFoundHttpException('ไม่พบหนังสือราชการที่ร้องขอ');
        }

        $model = $detail->document;
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบข้อมูลเอกสาร');
        }

        $this->markOfficialDocumentAsRead($detail);

        $this->view->title = $model->topic ?: 'หนังสือราชการ';
        $this->view->params['current_page'] = 'news';
        $this->view->params['mobileTitle'] = 'หนังสือราชการ';
        $this->view->params['mobileSubtitle'] = $model->topic ?: 'รายละเอียดหนังสือ';

        return $this->render('news-view', [
            'current_page' => 'news',
            'model' => $model,
            'detail' => $detail,
        ]);
    }

    /**
     * สร้าง query หนังสือราชการที่ส่งถึงผู้ใช้ปัจจุบัน
     *
     * @param string|bool $filter 'all' | 'unread' | 'read' (bool true == 'unread' for legacy callers)
     * @param bool $forCount      true เพื่อ skip select/groupBy ที่ไม่จำเป็นสำหรับ count
     */
    protected function buildOfficialDocumentsQuery($filter = 'all', bool $forCount = false)
    {
        // Legacy callers pass `true` to mean "unread only".
        if (is_bool($filter)) {
            $filter = $filter ? 'unread' : 'all';
        }
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return Documents::find()->where('0=1');
        }

        $empId = (int) $me->id;
        $depId = (int) ($me->department ?? 0);

        $query = Documents::find();
        // Match the dms/me index logic — include `comment_emp` so employee comments
        // count as a "delivered to me" reference, and `comment_dept` likewise for
        // department-level comments.
        $query->leftJoin('documents_detail te', "
            te.document_id = documents.id
            AND te.name IN ('comment_emp', 'tags', 'employee_tag', 'employee', 'req_approve')
            AND te.to_id = :empId
        ", [':empId' => $empId]);
        $query->leftJoin('documents_detail td', "
            td.document_id = documents.id
            AND td.name IN ('comment_dept', 'department')
            AND td.to_id = :depId
        ", [':depId' => $depId]);
        $query->leftJoin('documents_detail tr', "
            tr.document_id = documents.id
            AND tr.name = 'read'
            AND tr.to_id = :empId
        ", [':empId' => $empId]);

        $query->andWhere(['documents.document_group' => 'receive']);
        $query->andWhere([
            'or',
            ['not', ['te.id' => null]],
            ['not', ['td.id' => null]],
        ]);

        if ($filter === 'unread') {
            $query->andWhere(['tr.id' => null]);
        } elseif ($filter === 'read') {
            $query->andWhere(['not', ['tr.id' => null]]);
        }

        if ($forCount) {
            return $query;
        }

        $query->select([
            'documents.*',
            new Expression('COALESCE(MIN(te.id), MIN(td.id)) AS detail_id'),
            new Expression('MIN(tr.doc_read) AS doc_read'),
        ]);
        $query->groupBy('documents.id');
        $query->with(['documentOrg']);
        $query->orderBy(new Expression('CASE WHEN MIN(tr.id) IS NULL THEN 0 ELSE 1 END ASC, documents.doc_transactions_date DESC, documents.doc_regis_number DESC, documents.id DESC'));

        return $query;
    }

    /**
     * DataProvider สำหรับรายการหนังสือราชการ
     */
    protected function buildOfficialDocumentsProvider($filter = 'all'): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $this->buildOfficialDocumentsQuery($filter, false),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => false,
        ]);
    }

    /**
     * ดึงรายการหนังสือราชการจาก query
     *
     * @return array<int, Documents>
     */
    protected function findOfficialDocuments($filter = 'all', ?int $limit = null): array
    {
        $query = $this->buildOfficialDocumentsQuery($filter, false);
        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->all();
    }

    /**
     * นับจำนวนหนังสือราชการตาม filter ('all' | 'unread' | 'read')
     */
    protected function countOfficialDocuments($filter = 'all'): int
    {
        $query = $this->buildOfficialDocumentsQuery($filter, true);
        return (int) $query->count('DISTINCT documents.id');
    }

    /**
     * ตรวจสอบสิทธิ์และดึงรายละเอียดหนังสือจาก documents_detail
     */
    protected function findOfficialDocumentDetail(int $detailId): ?DocumentsDetail
    {
        $detail = DocumentsDetail::findOne($detailId);
        if (!$detail) {
            return null;
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงาน');
        }

        $empId = (int) $me->id;
        $depId = (int) ($me->department ?? 0);
        $toId = (int) ($detail->to_id ?? 0);

        // Whitelist must match buildOfficialDocumentsQuery so any document the
        // user can SEE in the list, they can also OPEN. Adds comment_emp +
        // comment_dept (canonical pattern from me/DocumentsController).
        $isAllowed = false;
        if (in_array($detail->name, ['comment_emp', 'tags', 'employee_tag', 'employee', 'req_approve'], true) && $toId === $empId) {
            $isAllowed = true;
        }
        if (in_array($detail->name, ['comment_dept', 'department'], true) && $toId === $depId) {
            $isAllowed = true;
        }

        if (!$isAllowed) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูหนังสือฉบับนี้');
        }

        return $detail;
    }

    /**
     * บันทึกสถานะอ่านของหนังสือราชการในมุมมองมือถือ
     */
    protected function markOfficialDocumentAsRead(DocumentsDetail $detail): void
    {
        $me = UserHelper::GetEmployee();
        if (!$me || !$detail->document_id) {
            return;
        }

        $reading = DocumentsDetail::find()
            ->where([
                'document_id' => $detail->document_id,
                'name' => 'read',
                'to_id' => $me->id,
                'from_id' => $detail->id,
            ])
            ->one();

        if (!$reading) {
            $reading = new DocumentsDetail();
            $reading->document_id = $detail->document_id;
            $reading->name = 'read';
            $reading->to_id = $me->id;
            $reading->from_id = $detail->id;
            $reading->doc_read = date('Y-m-d H:i:s');
            $reading->save(false);
            return;
        }

        if (empty($reading->doc_read)) {
            $reading->doc_read = date('Y-m-d H:i:s');
            $reading->save(false);
        }
    }

    /**
     * รายการการแจ้งเตือนทั้งหมด.
     */
    public function actionNotifications()
    {
        $this->view->title = 'การแจ้งเตือน';
        return $this->render('notifications', [
            'current_page' => 'home',
            'pendingLeaveApprovals' => $this->findPendingLeaveApprovals(),
            'recentLeaveRequests' => $this->findRecentLeaveRequests(10),
        ]);
    }

    /**
     * 3x3 grid menu for tools.
     */
    public function actionServices()
    {
        $this->view->title = 'บริการ';

        // RBAC gate for the meeting-room admin tool. The Yii auth manager
        // returns false for guests, so this is safe pre-login as well.
        $canManageMeeting = Yii::$app->user->can('meeting');

        return $this->render('services', [
            'current_page'     => 'services',
            'canManageMeeting' => $canManageMeeting,
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
     * จองรถราชการ (mobile-first: ActiveForm + Vehicle model + transaction).
     */
    public function actionBookingVehicle()
    {
        $this->view->title = 'จองรถราชการ';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR');
            return $this->redirect(['/mobile/default/index']);
        }

        $model = new \app\modules\booking\models\Vehicle();
        // Sensible defaults so the form renders pre-filled fields the user can tweak.
        $model->date_start = date('d/m/Y');
        $model->date_end   = date('d/m/Y');
        $model->time_start = '08:00';
        $model->time_end   = '17:00';
        $model->go_type    = 1; // ไปกลับวันเดียวกัน
        // Defaults set programmatically (not user-facing fields).
        $model->vehicle_type_id = 'general';
        $model->urgent          = 'ปกติ';
        $model->status          = 'Pending';

        $saveErrors = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $isAjax = Yii::$app->request->isAjax;

            // Convert Thai dates to Gregorian. For one-day trips (go_type=1) mirror end onto start.
            $startGreg = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
            $endGreg   = $model->date_end   ? AppHelper::convertToGregorian($model->date_end)   : $startGreg;
            if ((int) $model->go_type === 1) {
                $endGreg = $startGreg;
            }
            $model->date_start = $startGreg;
            $model->date_end   = $endGreg;
            $model->time_start = substr((string) $model->time_start, 0, 5);
            $model->time_end   = substr((string) $model->time_end, 0, 5);

            // System-managed fields.
            $model->emp_id    = (string) $me->id;
            $model->leader_id = (string) ($me->head_id ?? $me->id);
            $model->thai_year = (int) AppHelper::YearBudget($startGreg);
            $model->status    = 'Pending';
            if (empty($model->vehicle_type_id)) $model->vehicle_type_id = 'general';
            if (empty($model->urgent))          $model->urgent          = 'ปกติ';

            try {
                $model->code = \mdm\autonumber\AutoNumber::generate('VEH' . date('ymd') . '-???');
            } catch (\Throwable $e) {
                $model->code = 'VEH' . date('Ymd') . '-' . substr(uniqid(), -4);
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $tx->commit();
                    $message = 'บันทึกคำขอจองรถเรียบร้อย (รหัส ' . $model->code . ')';
                    if ($isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        Yii::$app->session->setFlash('success', $message);
                        return [
                            'status'       => 'success',
                            'message'      => $message,
                            'redirect_url' => \yii\helpers\Url::to(['/mobile/default/booking-vehicle']),
                        ];
                    }
                    Yii::$app->session->setFlash('success', $message);
                    return $this->redirect(['booking-vehicle']);
                }
                $tx->rollBack();
            } catch (\Throwable $e) {
                $tx->rollBack();
                if ($isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()];
                }
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }

            // Validation / save failure path
            if ($isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status'  => 'error',
                    'message' => 'ไม่สามารถบันทึกได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
                    'errors'  => \yii\bootstrap5\ActiveForm::validate($model),
                ];
            }

            // Restore Thai date form for re-display.
            if ($startGreg) {
                $ts = strtotime($startGreg); if ($ts) $model->date_start = date('d/m/Y', $ts);
            }
            if ($endGreg) {
                $ts = strtotime($endGreg); if ($ts) $model->date_end = date('d/m/Y', $ts);
            }
            $saveErrors = $model->getFirstErrors();
        }

        return $this->render('booking-vehicle', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => $me,
            'saveErrors'   => $saveErrors,
        ]);
    }

    /**
     * จองห้องประชุม (mobile-first: calendar, rooms, ActiveForm + Meeting model).
     */
    public function actionBookingMeeting()
    {
        $this->view->title = 'จองห้องประชุม';

        $model = new Meeting();
        $model->time_start = '09:00';
        $model->time_end   = '10:00';
        $model->emp_number = 1;
        // Default to today's date in d/m/Y form so the Thai datepicker renders it.
        $model->date_start = date('d/m/Y');

        $rooms = [];
        $empId = null;
        $saveErrors = [];

        try {
            $rooms = $model->listRooms();
            if (!empty(Yii::$app->user->identity->employee)) {
                $empId = Yii::$app->user->identity->employee->id;
            }
        } catch (\Throwable $e) {
            $rooms = [];
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $isAjax = Yii::$app->request->isAjax;

            if (!$empId) {
                if ($isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR'];
                }
                Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR');
                return $this->redirect(['booking-meeting']);
            }

            // Convert Thai date → Gregorian and mirror onto date_end (same-day meeting).
            $dateGregorian = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
            $model->date_start = $dateGregorian;
            $model->date_end   = $dateGregorian;
            $model->time_start = substr((string) $model->time_start, 0, 5);
            $model->time_end   = substr((string) $model->time_end, 0, 5);
            $model->emp_id     = (string) $empId;
            $model->thai_year  = (int) AppHelper::YearBudget($dateGregorian);
            $model->status     = 'Pending';

            $urgentList = $model->listUrgent();
            $model->urgent = !empty($urgentList) ? (string) array_key_first($urgentList) : 'ปกติ';

            try {
                $model->code = \mdm\autonumber\AutoNumber::generate('MTG' . date('ymd') . '-???');
            } catch (\Throwable $e) {
                $model->code = 'MTG' . date('Ymd') . '-' . substr(uniqid(), -4);
            }

            if ($model->save()) {
                $message = 'บันทึกคำจองห้องประชุมเรียบร้อย (รหัส ' . $model->code . ')';
                if ($isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    Yii::$app->session->setFlash('success', $message);
                    return [
                        'status'       => 'success',
                        'message'      => $message,
                        'redirect_url' => \yii\helpers\Url::to(['/mobile/default/booking-meeting']),
                    ];
                }
                Yii::$app->session->setFlash('success', $message);
                return $this->redirect(['booking-meeting']);
            }

            // Validation / save failed.
            if ($isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status'  => 'error',
                    'message' => 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
                    'errors'  => \yii\bootstrap5\ActiveForm::validate($model),
                ];
            }

            // Non-AJAX fallback: restore Thai-format date and re-render with errors.
            if ($dateGregorian) {
                $ts = strtotime($dateGregorian);
                if ($ts) {
                    $model->date_start = date('d/m/Y', $ts);
                }
            }
            $saveErrors = $model->getFirstErrors();
        }

        return $this->render('booking-meeting', [
            'current_page' => 'services',
            'rooms'        => $rooms,
            'model'        => $model,
            'saveErrors'   => $saveErrors,
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

        // Annual-leave balance: dedicated mobile endpoint not yet available, so the
        // form skips the LT4 "remaining days" client-side warning (controller stays
        // unchanged below). When a stats source becomes available pass it via `stats`.
        return $this->render('leave-request', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => $me,
            'draftRef'     => $ref,
            'stats'        => [],
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
            'current_page' => 'profile',
            'model' => $model,
        ]);
    }

    public function actionApproveLeave($id)
    {
        $this->view->title = 'อนุมัติใบลา';
        $approve = ApproveModel::find()
            ->andWhere(['id' => (int) $id, 'name' => 'leave'])
            ->one();
        if ($approve === null) {
            throw new NotFoundHttpException('ไม่พบรายการอนุมัติ');
        }

        $me = UserHelper::GetEmployee();
        $userIsChecker = Yii::$app->user->can('leave');
        $userIsOwner = $me && (int) $approve->emp_id === (int) $me->id;
        $canApprove = $approve->status === 'Pending' && ($userIsOwner || (empty($approve->emp_id) && $userIsChecker));
        if (!$canApprove) {
            Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์อนุมัติรายการนี้');
            return $this->redirect(['/mobile/default/index']);
        }

        $leave = Leave::findOne((int) $approve->from_id);
        if (!$leave) {
            throw new NotFoundHttpException('ไม่พบข้อมูลใบลา');
        }

        return $this->render('leave_approve_view', [
            'current_page' => 'services',
            'approve' => $approve,
            'model' => $leave,
        ]);
    }

    public function actionApproveLeaveUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }

        $approve = ApproveModel::find()
            ->andWhere(['id' => (int) $id, 'name' => 'leave'])
            ->one();
        if ($approve === null) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการอนุมัติ'];
        }

        $me = UserHelper::GetEmployee();
        $userIsChecker = Yii::$app->user->can('leave');
        $userIsOwner = $me && (int) $approve->emp_id === (int) $me->id;
        $canApprove = $approve->status === 'Pending' && ($userIsOwner || (empty($approve->emp_id) && $userIsChecker));
        if (!$canApprove) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์อนุมัติรายการนี้'];
        }

        $status = (string) Yii::$app->request->post('status');
        $result = (new LeaveApprovalService())->process($approve, $status, $me ? (int) $me->id : null);
        if (!($result['ok'] ?? false)) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
        }

        return [
            'status' => 'success',
            'redirect' => \yii\helpers\Url::to(['/mobile/default/leave-approvals']),
        ];
    }

    public function actionLeaveApprovals()
    {
        $this->view->title = 'รายการอนุมัติใบลา';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/mobile/default/index']);
        }

        return $this->render('leave-approvals', [
            'current_page' => 'services',
            'approvals' => $this->findPendingLeaveApprovals(null, $me),
        ]);
    }

    protected function findPendingLeaveApprovals(?int $limit = null, $employee = null): array
    {
        $me = $employee ?: UserHelper::GetEmployee();
        if (!$me) {
            return [];
        }

        $query = ApproveModel::find()
            ->with(['leave.leaveType', 'leave.employee', 'employee'])
            ->andWhere(['name' => 'leave', 'status' => 'Pending'])
            ->orderBy(['id' => SORT_DESC]);

        if (Yii::$app->user->can('leave')) {
            $query->andWhere([
                'or',
                ['approve.emp_id' => (int) $me->id],
                ['approve.emp_id' => null],
            ]);
        } else {
            $query->andWhere(['approve.emp_id' => (int) $me->id]);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return array_values(array_filter($query->all(), static function (ApproveModel $approve) {
            return $approve->leave !== null;
        }));
    }

    protected function findRecentLeaveRequests(?int $limit = null, $employee = null): array
    {
        $me = $employee ?: UserHelper::GetEmployee();
        if (!$me) {
            return [];
        }

        $query = Leave::find()
            ->with(['leaveType', 'leaveStatus'])
            ->where(['emp_id' => (int) $me->id])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->all();
    }

    protected function findRecentMeetings(?int $limit = null, $employee = null): array
    {
        $me = $employee ?: UserHelper::GetEmployee();
        if (!$me) {
            return [];
        }

        $query = Meeting::find()
            ->with(['room'])
            ->where(['emp_id' => (string) $me->id])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->all();
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
        $leaves = [];
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
            try {
                $leaves = Leave::find()
                    ->with(['leaveType', 'leaveStatus'])
                    ->where(['emp_id' => $empId])
                    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                    ->limit(100)
                    ->all();
            } catch (\Throwable $e) {
                $leaves = [];
            }
        }
        return $this->render('my-requests', [
            'current_page' => 'profile',
            'type' => $type,
            'meetings' => $meetings,
            'leaves' => $leaves,
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
     * ผู้ดูแลห้องประชุม — รายการจองห้องที่ผู้ใช้เป็นผู้ดูแล (room.data_json.owner).
     * GET filter: status (pending|passed|cancelled|all), room (room code).
     */
    public function actionRoomManage()
    {
        $this->view->title = 'จัดการห้องประชุม';
        $empId          = null;
        $ownedRoomCodes = [];
        $roomTitles     = []; // code => title (สำหรับ filter dropdown)
        $meetings       = [];
        $statsCount     = ['pending' => 0, 'passed' => 0, 'cancelled' => 0, 'total' => 0];

        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee)) {
            $empId = (string) Yii::$app->user->identity->employee->id;
            $allRooms = Room::find()->where(['name' => 'meeting_room'])->all();
            foreach ($allRooms as $r) {
                $data = is_array($r->data_json)
                    ? $r->data_json
                    : (is_string($r->data_json) ? (json_decode($r->data_json, true) ?: []) : []);
                if (!empty($data['owner']) && (string) $data['owner'] === $empId) {
                    $ownedRoomCodes[]  = $r->code;
                    $roomTitles[$r->code] = $r->title;
                }
            }

            if (!empty($ownedRoomCodes)) {
                $meetings = Meeting::find()
                    ->where(['room_id' => $ownedRoomCodes])
                    ->orderBy(['date_start' => SORT_DESC, 'time_start' => SORT_DESC])
                    ->limit(200)
                    ->all();

                // Count by bucket for the header stats / filter chips.
                foreach ($meetings as $m) {
                    $status = (string) $m->status;
                    $statsCount['total']++;
                    if ($status === 'Pending') {
                        $statsCount['pending']++;
                    } elseif (in_array($status, ['Pass', 'Approve'], true)) {
                        $statsCount['passed']++;
                    } elseif (in_array($status, ['Cancel', 'Reject'], true)) {
                        $statsCount['cancelled']++;
                    }
                }
            }
        }

        return $this->render('room-manage', [
            'current_page'   => 'profile',
            'meetings'       => $meetings,
            'ownedRoomCodes' => $ownedRoomCodes,
            'roomTitles'     => $roomTitles,
            'statsCount'     => $statsCount,
            'filterStatus'   => Yii::$app->request->get('status', 'pending'),
            'filterRoom'     => Yii::$app->request->get('room', ''),
        ]);
    }

    /**
     * รายละเอียดการจอง — payload JSON สำหรับ .open-modal pattern (erp.js).
     * Returns { title, content, footer } where content + footer are pre-rendered HTML.
     */
    public function actionMeetingDetail($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $meeting = Meeting::findOne((int) $id);
        if (!$meeting) {
            return [
                'title'   => 'ไม่พบรายการจอง',
                'content' => '<div class="p-4 text-center text-danger">ไม่พบรายการจองนี้ในระบบ</div>',
                'footer'  => '',
            ];
        }

        // Authorisation: only the room owner can view (mirrors actionMeetingConfirm).
        $empId = (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->employee))
            ? (string) Yii::$app->user->identity->employee->id
            : null;
        $room  = $meeting->room_id ? Room::findOne(['name' => 'meeting_room', 'code' => $meeting->room_id]) : null;
        if ($room) {
            $data    = is_array($room->data_json) ? $room->data_json : (is_string($room->data_json) ? (json_decode($room->data_json, true) ?: []) : []);
            $ownerId = isset($data['owner']) ? (string) $data['owner'] : null;
            if ($ownerId !== $empId) {
                return [
                    'title'   => 'ไม่อนุญาต',
                    'content' => '<div class="p-4 text-center text-danger">คุณไม่มีสิทธิ์ดูรายการจองห้องนี้</div>',
                    'footer'  => '',
                ];
            }
        }

        $isPending = (string) $meeting->status === 'Pending';

        $content = $this->renderPartial('_meeting-detail', [
            'meeting' => $meeting,
            'room'    => $room,
        ]);

        $footer = '';
        if ($isPending) {
            $footer  = '<button type="button" class="btn btn-outline-danger rm-action flex-grow-1" '
                . 'data-id="' . (int) $meeting->id . '" data-status="Cancel" '
                . 'data-confirm-title="ยกเลิกการจอง?" data-confirm-text="การยกเลิกไม่สามารถกู้คืนได้">'
                . '<i data-lucide="x" class="mi-sm mi-baseline me-1"></i> ยกเลิก</button>';
            $footer .= '<button type="button" class="btn btn-success rm-action flex-grow-1" '
                . 'data-id="' . (int) $meeting->id . '" data-status="Pass" '
                . 'data-confirm-title="อนุมัติการจอง?" data-confirm-text="แจ้ง Telegram ผู้ขอเมื่อบันทึก">'
                . '<i data-lucide="check" class="mi-sm mi-baseline me-1"></i> อนุมัติ</button>';
        }

        return [
            'title'        => 'รายละเอียดการจอง',
            'content'      => $content,
            'footer'       => $footer,
            'initCallback' => 'lucideRefresh',
        ];
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
            if ($status === 'Pass') {
                $meeting->notifyBookerMeetingApprovedTelegram();
            }
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
