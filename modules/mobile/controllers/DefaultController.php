<?php

namespace app\modules\mobile\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\attendance\models\CheckinLocation;
use app\modules\attendance\models\CheckinRecord;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;
use app\modules\booking\models\Vehicle;
use app\modules\mobile\services\MobileMeetingAdminService;
use app\modules\mobile\services\MobileMeetingService;
use app\modules\mobile\services\MobileVehicleService;
use app\modules\approveV2\models\Approve as ApproveModel;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\leave\components\LeaveApprovalService;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveType;
use app\modules\mobile\services\MobileLeaveService;
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

        $service = new MobileVehicleService();
        $model   = $service->newWithDefaults();
        $saveErrors = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = $service->prepareAndSave($model, $me);

            if ($result['ok']) {
                $message = 'บันทึกคำขอจองรถเรียบร้อย (รหัส ' . $model->code . ')';
                return $this->respondVehicleSave($message, ['/mobile/default/booking-vehicle']);
            }

            if ($result['exception']) {
                return $this->respondVehicleSaveError(
                    'เกิดข้อผิดพลาดในการบันทึก: ' . $result['exception']->getMessage(),
                    $model
                );
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status'  => 'error',
                    'message' => 'ไม่สามารถบันทึกได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
                    'errors'  => \yii\bootstrap5\ActiveForm::validate($model),
                ];
            }

            $service->restoreThaiDates($model);
            $saveErrors = $result['errors'];
        }

        return $this->render('booking-vehicle', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => $me,
            'myBookings'   => $service->findMyBookings((string) $me->id),
            'saveErrors'   => $saveErrors,
        ]);
    }

    /**
     * รายละเอียดคำขอจองรถของผู้ใช้ปัจจุบัน.
     */
    public function actionVehicleView($id)
    {
        $me = UserHelper::GetEmployee();
        $vehicle = $me ? (new MobileVehicleService())->findOwnedById((int) $id, (string) $me->id) : null;
        if (!$vehicle) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองรถ หรือคุณไม่มีสิทธิ์ดูรายการนี้');
            return $this->redirect(['/mobile/default/booking-vehicle']);
        }

        $this->view->title = 'รายละเอียดการจองรถ';
        return $this->render('vehicle-view', [
            'current_page' => 'services',
            'vehicle'      => $vehicle,
            'employee'     => $me,
        ]);
    }

    /**
     * แก้ไขคำขอจองรถ เฉพาะรายการที่ยังรออนุมัติ.
     */
    public function actionVehicleUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $service = new MobileVehicleService();
        $model   = $me ? $service->findOwnedById((int) $id, (string) $me->id) : null;
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองรถ หรือคุณไม่มีสิทธิ์แก้ไขรายการนี้');
            return $this->redirect(['/mobile/default/booking-vehicle']);
        }
        if (!$service->canEdit($model)) {
            Yii::$app->session->setFlash('error', 'แก้ไขได้เฉพาะคำขอที่รออนุมัติเท่านั้น');
            return $this->redirect(['/mobile/default/vehicle-view', 'id' => $model->id]);
        }

        $saveErrors = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = $service->prepareAndSave($model, $me);

            if ($result['ok']) {
                return $this->respondVehicleSave(
                    'บันทึกการแก้ไขคำขอจองรถเรียบร้อย',
                    ['/mobile/default/vehicle-view', 'id' => $model->id]
                );
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status'  => 'error',
                    'message' => 'ไม่สามารถบันทึกได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
                    'errors'  => \yii\bootstrap5\ActiveForm::validate($model),
                ];
            }
            $saveErrors = $result['errors'];
        }

        $service->restoreThaiDates($model);

        $this->view->title = 'แก้ไขการจองรถ';
        return $this->render('booking-vehicle', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => $me,
            'myBookings'   => [],
            'saveErrors'   => $saveErrors,
            'forceMode'    => 'wizard',
            'isEdit'       => true,
        ]);
    }

    /**
     * ยกเลิกคำขอจองรถ เฉพาะผู้ขอและเฉพาะสถานะ Pending.
     */
    public function actionVehicleCancel($id)
    {
        $me = UserHelper::GetEmployee();
        $service = new MobileVehicleService();
        $vehicle = $me ? $service->findOwnedById((int) $id, (string) $me->id) : null;
        if (!$vehicle) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองรถ หรือคุณไม่มีสิทธิ์ยกเลิกรายการนี้');
            return $this->redirect(['/mobile/default/booking-vehicle']);
        }
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/mobile/default/vehicle-view', 'id' => $vehicle->id]);
        }
        if (!$service->canEdit($vehicle)) {
            Yii::$app->session->setFlash('error', 'ยกเลิกได้เฉพาะคำขอที่รออนุมัติเท่านั้น');
            return $this->redirect(['/mobile/default/vehicle-view', 'id' => $vehicle->id]);
        }

        if ($service->cancel($vehicle)) {
            Yii::$app->session->setFlash('success', 'ยกเลิกคำขอจองรถเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถยกเลิกคำขอได้');
        }
        return $this->redirect(['/mobile/default/vehicle-view', 'id' => $vehicle->id]);
    }

    /**
     * แชร์ response shape สำหรับ vehicle save success ระหว่าง AJAX และ non-AJAX
     * (booking + update flows ใช้ shape นี้).
     */
    private function respondVehicleSave(string $message, array $redirectRoute)
    {
        Yii::$app->session->setFlash('success', $message);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status'       => 'success',
                'message'      => $message,
                'redirect_url' => \yii\helpers\Url::to($redirectRoute),
            ];
        }
        return $this->redirect($redirectRoute);
    }

    /**
     * แชร์ response shape สำหรับ vehicle save exception (เช่น DB error).
     */
    private function respondVehicleSaveError(string $message, Vehicle $model)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'error', 'message' => $message];
        }
        Yii::$app->session->setFlash('error', $message);
        (new MobileVehicleService())->restoreThaiDates($model);
        return $this->render('booking-vehicle', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => UserHelper::GetEmployee(),
            'myBookings'   => [],
            'saveErrors'   => $model->getFirstErrors(),
        ]);
    }

    /**
     * จองห้องประชุม (mobile-first: calendar, rooms, ActiveForm + Meeting model).
     */
    public function actionBookingMeeting()
    {
        $this->view->title = 'จองห้องประชุม';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR');
            return $this->redirect(['/mobile/default/index']);
        }

        $service = new MobileMeetingService();
        $model   = $service->newWithDefaults($me);

        $roomCards      = $service->listRoomCards();
        $rooms          = $service->listRooms($roomCards);
        $roomLayouts    = $service->listRoomLayouts($model);
        $urgentOptions  = $service->listUrgentOptions($model);
        $equipmentItems = $service->listEquipmentItems();

        $saveErrors = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = $service->prepareAndSave($model, $me, $urgentOptions);

            if ($result['ok']) {
                $message = 'บันทึกคำจองห้องประชุมเรียบร้อย (รหัส ' . $model->code . ')';
                return $this->respondMeetingSave($message, ['/mobile/default/booking-meeting'], $model);
            }

            if (Yii::$app->request->isAjax) {
                return $this->respondMeetingSaveError($model);
            }

            $service->restoreThaiDates($model);
            $saveErrors = $result['errors'];
        }

        return $this->render('booking-meeting', [
            'current_page'   => 'services',
            'rooms'          => $rooms,
            'roomCards'      => $roomCards,
            'roomLayouts'    => $roomLayouts,
            'urgentOptions'  => $urgentOptions,
            'equipmentItems' => $equipmentItems,
            'employee'       => $me,
            'model'          => $model,
            'myBookings'     => $service->findMyBookings((string) $me->id),
            'saveErrors'     => $saveErrors,
        ]);
    }

    /**
     * API คืนค่าสถานะห้องประชุมตามวันที่และเวลา (สำหรับปุ่มตรวจสอบเวลาว่าง).
     * GET/POST: meeting_date (d/m/Y), meeting_date_end (optional d/m/Y), time_start, time_end
     */
    public function actionMeetingRoomAvailability()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        $meetingDate    = (string) ($req->get('meeting_date') ?: $req->post('meeting_date', ''));
        $meetingDateEnd = (string) ($req->get('meeting_date_end') ?: $req->post('meeting_date_end', $meetingDate));
        $timeStart      = (string) ($req->get('time_start') ?: $req->post('time_start', ''));
        $timeEnd        = (string) ($req->get('time_end') ?: $req->post('time_end', ''));
        $excludeId      = (int) ($req->get('exclude_id') ?: $req->post('exclude_id', 0));

        return (new MobileMeetingService())->checkAvailability(
            $meetingDate,
            $meetingDateEnd ?: $meetingDate,
            $timeStart,
            $timeEnd,
            $excludeId > 0 ? $excludeId : null
        );
    }

    /**
     * แชร์ response shape สำหรับ meeting save success ระหว่าง AJAX และ non-AJAX.
     */
    private function respondMeetingSave(string $message, array $redirectRoute, Meeting $model)
    {
        Yii::$app->session->setFlash('success', $message);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status'       => 'success',
                'message'      => $message,
                'redirect_url' => \yii\helpers\Url::to($redirectRoute),
            ];
        }
        return $this->redirect($redirectRoute);
    }

    /**
     * แชร์ response shape สำหรับ AJAX meeting save error: แมพ error attribute → input id.
     */
    private function respondMeetingSaveError(Meeting $model)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $errors = [];
        foreach ($model->getErrors() as $attribute => $messages) {
            $inputId = $attribute === 'data_json'
                ? \yii\helpers\Html::getInputId($model, 'data_json[phone]')
                : \yii\helpers\Html::getInputId($model, $attribute);
            $errors[$inputId] = $messages;
        }
        return [
            'status'  => 'error',
            'message' => 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
            'errors'  => $errors,
        ];
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

        $service = new MobileLeaveService();
        $draft   = $service->newDraft($me);
        $model   = $draft['model'];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = $service->saveFromPost($model);
            if ($result['ok']) {
                return $this->redirect(['/mobile/default/leave-request-view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกคำขอลาได้ กรุณาตรวจสอบฟิลด์ที่กรอก');
        }

        return $this->render('leave-request', [
            'current_page'         => 'services',
            'model'                => $model,
            'employee'             => $me,
            'draftRef'             => $draft['ref'],
            'stats'                => [],
            // เตรียม view data ที่ก่อนหน้านี้ view query เอง (DB query out of presentation)
            'leaveSendInitAvatar'  => $service->loadWorkSendAvatar(
                $model->data_json['leave_work_send_id'] ?? null,
                !$model->isNewRecord
            ),
            'approveChain'         => $service->loadApproveChain($model->emp_id),
        ]);
    }

    public function actionLeaveRequestView($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $me ? (new MobileLeaveService())->findOwnedById((int) $id, $me->id) : null;
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบคำขอลานี้ หรือคุณไม่มีสิทธิ์ดูคำขอลานี้');
            return $this->redirect(['/mobile/default/leave-request']);
        }

        $this->view->title = 'รายละเอียดคำขอลา';
        return $this->render('leave_request_view', [
            'current_page' => 'profile',
            'model'        => $model,
        ]);
    }

    public function actionApproveLeave($id)
    {
        $this->view->title = 'อนุมัติใบลา';

        $service = new MobileLeaveService();
        $approve = $service->findApproveById((int) $id);
        if ($approve === null) {
            throw new NotFoundHttpException('ไม่พบรายการอนุมัติ');
        }

        $me = UserHelper::GetEmployee();
        if (!$service->canActOnApprove($approve, $me)) {
            Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์อนุมัติรายการนี้');
            return $this->redirect(['/mobile/default/index']);
        }

        $leave = Leave::findOne((int) $approve->from_id);
        if (!$leave) {
            throw new NotFoundHttpException('ไม่พบข้อมูลใบลา');
        }

        return $this->render('leave_approve_view', [
            'current_page' => 'services',
            'approve'      => $approve,
            'model'        => $leave,
        ]);
    }

    public function actionApproveLeaveUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }

        $service = new MobileLeaveService();
        $approve = $service->findApproveById((int) $id);
        if ($approve === null) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการอนุมัติ'];
        }

        $me = UserHelper::GetEmployee();
        if (!$service->canActOnApprove($approve, $me)) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์อนุมัติรายการนี้'];
        }

        $status = (string) Yii::$app->request->post('status');
        $result = (new LeaveApprovalService())->process($approve, $status, $me ? (int) $me->id : null);
        if (!($result['ok'] ?? false)) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
        }

        return [
            'status'   => 'success',
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
            'approvals'    => (new MobileLeaveService())->findPendingApprovals($me),
        ]);
    }

    // Protected helpers — keep ระบบ legacy ของ actionIndex/Services ใช้ได้ต่อ
    // (delegate ไป service เพื่อรวม implementation จุดเดียว)
    protected function findPendingLeaveApprovals(?int $limit = null, $employee = null): array
    {
        $me = $employee ?: UserHelper::GetEmployee();
        return (new MobileLeaveService())->findPendingApprovals($me, $limit);
    }

    protected function findRecentLeaveRequests(?int $limit = null, $employee = null): array
    {
        $me = $employee ?: UserHelper::GetEmployee();
        return (new MobileLeaveService())->findRecentRequests($me, $limit);
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

        $me = UserHelper::GetEmployee();

        // Aggregate page รวมประวัติทั้งหมดของผู้ใช้ (รวม soft-deleted ก็ได้ → withDeleted=true)
        $meetings = $me ? (new MobileMeetingService())->findMyBookings((string) $me->id, 100, true) : [];
        $leaves   = (new MobileLeaveService())->findRecentRequests($me, 100);

        return $this->render('my-requests', [
            'current_page' => 'profile',
            'type'         => $type,
            'meetings'     => $meetings,
            'leaves'       => $leaves,
        ]);
    }

    /**
     * หน้ารายละเอียดคำขอจองห้องประชุม (ภายใต้ mobile เพื่อให้ UX/UI เหมือนแอป).
     * เฉพาะผู้ขอ (emp_id) เท่านั้นที่ดูได้
     */
    public function actionMeetingView($id)
    {
        $me = UserHelper::GetEmployee();
        $meeting = $me ? (new MobileMeetingService())->findOwnedById((int) $id, (string) $me->id) : null;
        if (!$meeting) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองนี้ หรือคุณไม่มีสิทธิ์ดูรายการนี้');
            return $this->redirect(['/mobile/default/my-requests', 'type' => 'meeting']);
        }

        $this->view->title = 'รายละเอียดการจองห้องประชุม';
        return $this->render('meeting-view', [
            'current_page' => 'services',
            'meeting'      => $meeting,
        ]);
    }

    /**
     * แก้ไขการจองห้องประชุม (เฉพาะผู้ขอ และเฉพาะสถานะ Pending).
     */
    public function actionMeetingUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงานที่ล็อกอิน กรุณาติดต่อ HR');
            return $this->redirect(['/mobile/default/index']);
        }

        $service = new MobileMeetingService();
        $meeting = $service->findOwnedById((int) $id, (string) $me->id);
        if (!$meeting) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองนี้ หรือคุณไม่มีสิทธิ์แก้ไขรายการนี้');
            return $this->redirect(['/mobile/default/my-requests', 'type' => 'meeting']);
        }
        if (!$service->canEdit($meeting)) {
            Yii::$app->session->setFlash('error', 'แก้ไขได้เฉพาะคำขอที่รอการอนุมัติเท่านั้น');
            return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
        }

        $roomCards      = $service->listRoomCards();
        $rooms          = $service->listRooms($roomCards);
        $roomLayouts    = $service->listRoomLayouts($meeting);
        $urgentOptions  = $service->listUrgentOptions($meeting);
        $equipmentItems = $service->listEquipmentItems();

        $saveErrors = [];

        if (Yii::$app->request->isPost && $meeting->load(Yii::$app->request->post())) {
            $result = $service->prepareAndSave($meeting, $me, $urgentOptions);

            if ($result['ok']) {
                return $this->respondMeetingSave(
                    'บันทึกการแก้ไขคำขอจองห้องประชุมเรียบร้อย',
                    ['/mobile/default/meeting-view', 'id' => $meeting->id],
                    $meeting
                );
            }

            if (Yii::$app->request->isAjax) {
                return $this->respondMeetingSaveError($meeting);
            }

            $service->restoreThaiDates($meeting);
            $saveErrors = $result['errors'];
        } else {
            $service->convertDbDatesToThai($meeting);
        }

        $this->view->title = 'แก้ไขการจองห้องประชุม';
        return $this->render('booking-meeting', [
            'current_page'   => 'services',
            'rooms'          => $rooms,
            'roomCards'      => $roomCards,
            'roomLayouts'    => $roomLayouts,
            'urgentOptions'  => $urgentOptions,
            'equipmentItems' => $equipmentItems,
            'employee'       => $me,
            'model'          => $meeting,
            'myBookings'     => [],
            'saveErrors'     => $saveErrors,
            'isEdit'         => true,
            'forceMode'      => 'wizard',
        ]);
    }

    /**
     * ยกเลิกคำขอจองห้องประชุม เฉพาะผู้ขอและเฉพาะสถานะ Pending.
     */
    public function actionMeetingCancel($id)
    {
        $me = UserHelper::GetEmployee();
        $service = new MobileMeetingService();
        $meeting = $me ? $service->findOwnedById((int) $id, (string) $me->id) : null;
        if (!$meeting) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการจองห้องประชุม หรือคุณไม่มีสิทธิ์ยกเลิกรายการนี้');
            return $this->redirect(['/mobile/default/booking-meeting']);
        }
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
        }
        if (!$service->canEdit($meeting)) {
            Yii::$app->session->setFlash('error', 'ยกเลิกได้เฉพาะคำขอที่รออนุมัติเท่านั้น');
            return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
        }

        if ($service->cancel($meeting)) {
            Yii::$app->session->setFlash('success', 'ยกเลิกคำขอจองห้องประชุมเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถยกเลิกคำขอได้');
        }
        return $this->redirect(['/mobile/default/meeting-view', 'id' => $meeting->id]);
    }

    /**
     * ผู้ดูแลห้องประชุม — รายการจองห้องที่ผู้ใช้เป็นผู้ดูแล (room.data_json.owner).
     * GET filter: status (pending|passed|cancelled|all), room (room code).
     */
    public function actionRoomManage()
    {
        $this->view->title = 'จัดการห้องประชุม';

        $me = UserHelper::GetEmployee();
        $service = new MobileMeetingAdminService();

        $owned    = $me ? $service->findOwnedRoomsForUser((string) $me->id) : ['codes' => [], 'titles' => []];
        $meetings = $service->findMeetingsForOwnedRooms($owned['codes']);
        $statsCount = $service->bucketCountsForRoomManage($meetings);

        return $this->render('room-manage', [
            'current_page'   => 'profile',
            'meetings'       => $meetings,
            'ownedRoomCodes' => $owned['codes'],
            'roomTitles'     => $owned['titles'],
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

        $me = UserHelper::GetEmployee();
        $service = new MobileMeetingAdminService();
        $room = $service->getRoomFromMeeting($meeting);

        // Authorization: ถ้ามีห้องและไม่ใช่เจ้าของ → ห้ามดู
        if ($room && (!$me || !$service->canManageRoomMeeting($meeting, (string) $me->id))) {
            return [
                'title'   => 'ไม่อนุญาต',
                'content' => '<div class="p-4 text-center text-danger">คุณไม่มีสิทธิ์ดูรายการจองห้องนี้</div>',
                'footer'  => '',
            ];
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

        $id     = (int) Yii::$app->request->post('id');
        $status = (string) Yii::$app->request->post('status');

        $meeting = Meeting::findOne($id);
        if (!$meeting) {
            return ['ok' => false, 'message' => 'ไม่พบรายการจอง'];
        }

        $me = UserHelper::GetEmployee();
        $service = new MobileMeetingAdminService();
        if (!$service->getRoomFromMeeting($meeting)) {
            return ['ok' => false, 'message' => 'ไม่พบข้อมูลห้อง'];
        }
        if (!$me || !$service->canManageRoomMeeting($meeting, (string) $me->id)) {
            return ['ok' => false, 'message' => 'คุณไม่มีสิทธิ์จัดการห้องนี้'];
        }

        return $service->confirmMeeting($meeting, $status);
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
