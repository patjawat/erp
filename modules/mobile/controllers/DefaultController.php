<?php

namespace app\modules\mobile\controllers;

use app\components\AppHelper;
use app\components\ApproveHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetCategory;
use app\modules\am\models\AssetCondition;
use app\modules\am\models\AssetStatus;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use app\modules\attendance\models\CheckinLocation;
use app\modules\attendance\models\CheckinRecord;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;
use app\modules\booking\models\Vehicle;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\mobile\services\MobileApprovalService;
use app\modules\mobile\services\MobileMaintenanceService;
use app\modules\mobile\services\MobileRepairService;
use app\modules\mobile\services\MobileMeetingAdminService;
use app\modules\mobile\services\MobileMeetingService;
use app\modules\mobile\services\MobileVehicleService;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\mobile\services\MobileLeaveService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

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
     * รายการปีงบประมาณสำหรับ mobile list filters.
     *
     * @return array<int,string>
     */
    protected function mobileFiscalYears(int $back = 10): array
    {
        $current = (int) AppHelper::YearBudget();
        $years = [];
        for ($y = $current; $y > $current - $back; $y--) {
            $years[$y] = 'พ.ศ. ' . $y;
        }
        return $years;
    }

    protected function resolveMobileFiscalYear(array $fiscalYears): int
    {
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = (int) Yii::$app->request->get('year', $currentYear);
        return isset($fiscalYears[$filterYear]) ? $filterYear : $currentYear;
    }

    /**
     * Dashboard with summary cards.
     */
    public function actionIndex()
    {
        $this->view->title = 'บริการออนไลน์';
        $officialUnreadCount = $this->countOfficialDocuments(true);
        $canManageMeeting = !Yii::$app->user->isGuest && Yii::$app->user->can('meeting');

        return $this->render('index', [
            'current_page' => 'home',
            'approvalInfo' => ApproveHelper::Info(),
            'pendingLeaveApprovals' => $this->findPendingLeaveApprovals(3),
            'recentLeaveRequests' => $this->findRecentLeaveRequests(3),
            'recentMeetings' => $this->findRecentMeetings(3),
            'officialDocumentsPreview' => $this->findOfficialDocuments(true, 3),
            'officialUnreadCount' => $officialUnreadCount,
            'canManageMeeting' => $canManageMeeting,
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

        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);

        $this->view->title = 'หนังสือราชการ';
        return $this->render('news', [
            'current_page' => 'news',
            'filter' => $filter,
            'dataProvider' => $this->buildOfficialDocumentsProvider($filter, $filterYear),
            'officialUnreadCount' => $this->countOfficialDocuments('unread', $filterYear),
            'officialTotalCount' => $this->countOfficialDocuments('all', $filterYear),
            'fiscalYears' => $fiscalYears,
            'filterYear' => $filterYear,
            'currentYear' => $currentYear,
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
    protected function buildOfficialDocumentsQuery($filter = 'all', bool $forCount = false, ?int $thaiYear = null)
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
        if ($thaiYear !== null) {
            $query->andWhere(['documents.thai_year' => (string) $thaiYear]);
        }
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
    protected function buildOfficialDocumentsProvider($filter = 'all', ?int $thaiYear = null): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $this->buildOfficialDocumentsQuery($filter, false, $thaiYear),
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
    protected function findOfficialDocuments($filter = 'all', ?int $limit = null, ?int $thaiYear = null): array
    {
        $query = $this->buildOfficialDocumentsQuery($filter, false, $thaiYear);
        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->all();
    }

    /**
     * นับจำนวนหนังสือราชการตาม filter ('all' | 'unread' | 'read')
     */
    protected function countOfficialDocuments($filter = 'all', ?int $thaiYear = null): int
    {
        $query = $this->buildOfficialDocumentsQuery($filter, true, $thaiYear);
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
        $me = UserHelper::GetEmployee();
        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);
        $leaveService = new MobileLeaveService();

        return $this->render('notifications', [
            'current_page' => 'home',
            'pendingLeaveApprovals' => $leaveService->findPendingApprovals($me, null, $filterYear),
            'recentLeaveRequests' => $leaveService->findRecentRequests($me, 10, $filterYear),
            'fiscalYears' => $fiscalYears,
            'filterYear' => $filterYear,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * 3x3 grid menu for tools.
     */
    public function actionServices()
    {
        $this->view->title = 'บริการ';

        $me = UserHelper::GetEmployee();
        $isDriver = !Yii::$app->user->isGuest && Yii::$app->user->can('driver');
        $canManageMeeting = !Yii::$app->user->isGuest && Yii::$app->user->can('meeting');
        $currentYear = (int) AppHelper::YearBudget();
        $driverMissionCount = 0;
        $managedMeetingPendingCount = 0;

        if ($me && $isDriver) {
            $driverMissionCount = (new MobileVehicleService())->countOpenDriverMissions((string) $me->id, $currentYear);
        }

        if ($me && $canManageMeeting) {
            $meetingAdminService = new MobileMeetingAdminService();
            $ownedRooms = $meetingAdminService->findOwnedRoomsForUser((string) $me->id);
            $roomMeetings = $meetingAdminService->findMeetingsForOwnedRooms($ownedRooms['codes'], 200, $currentYear);
            $roomCounts = $meetingAdminService->bucketCountsForRoomManage($roomMeetings);
            $managedMeetingPendingCount = (int) $roomCounts['pending'];
        }

        return $this->render('services', [
            'current_page'               => 'services',
            'isDriver'                   => $isDriver,
            'driverMissionCount'         => $driverMissionCount,
            'canManageMeeting'           => $canManageMeeting,
            'managedMeetingPendingCount' => $managedMeetingPendingCount,
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
        $history = [];
        try {
            $geofences = CheckinLocation::findActiveGeofenced();
            $lastCheckin = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->orderBy(['checkin_at' => SORT_DESC])
                ->one();
            // ดึงประวัติย้อนหลัง 14 วัน (เฉพาะของผู้ใช้ปัจจุบัน)
            $fromDate = date('Y-m-d 00:00:00', strtotime('-14 days'));
            $history = CheckinRecord::find()
                ->andWhere(['emp_id' => $me->id])
                ->andWhere(['>=', 'checkin_at', $fromDate])
                ->orderBy(['checkin_at' => SORT_DESC])
                ->limit(60)
                ->all();
        } catch (\Throwable $e) {
            $geofences = [];
            $lastCheckin = null;
            $history = [];
        }
        return $this->render('attendance', [
            'current_page' => 'attendance',
            'employee' => $me,
            'geofences' => $geofences,
            'lastCheckin' => $lastCheckin,
            'history' => $history,
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
     * อัปโหลด/เปลี่ยนรูปโปรไฟล์ของผู้ใช้ปัจจุบัน — บังคับ ref = employee.ref, name = 'avatar'
     * เพื่อกันการปลอม ref ไปแก้ avatar ของผู้อื่นผ่าน endpoint filemanager โดยตรง
     */
    public function actionAvatarUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะ POST'];
        }

        $me = UserHelper::GetEmployee();
        if (!$me || empty($me->ref)) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงาน'];
        }

        $file = UploadedFile::getInstanceByName('avatar');
        if (!$file) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกไฟล์รูปภาพ'];
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower((string) $file->extension);
        if (!in_array($ext, $allowed, true)) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะไฟล์รูปภาพ (jpg, png, webp, gif)'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if ($file->size > $maxBytes) {
            return ['status' => 'error', 'message' => 'ไฟล์มีขนาดเกิน 5MB'];
        }

        $ref  = (string) $me->ref;
        $name = 'avatar';

        try {
            FileManagerHelper::CreateDir($ref);
            $realFileName = md5($file->baseName . microtime(true)) . '.' . $ext;
            $savePath = FileManagerHelper::getUploadPath() . $ref . '/' . $realFileName;
            if (!$file->saveAs($savePath)) {
                return ['status' => 'error', 'message' => 'บันทึกไฟล์ไม่สำเร็จ'];
            }

            if (FileManagerHelper::isImage($savePath)) {
                FileManagerHelper::createThumbnail($ref, $realFileName);
            }

            $old = Uploads::find()->where(['ref' => $ref, 'name' => $name])->one();
            if ($old) {
                FileManagerHelper::Deletefile($old->id);
            }

            $upload = new Uploads();
            $upload->ref = $ref;
            $upload->name = $name;
            $upload->file_name = $file->baseName . '.' . $ext;
            $upload->real_filename = $realFileName;
            $upload->type = FileManagerHelper::checkFileType($ext);
            $upload->save(false);

            return [
                'status'     => 'success',
                'message'    => 'เปลี่ยนรูปโปรไฟล์เรียบร้อย',
                'avatar_url' => \yii\helpers\Url::to(['/filemanager/uploads/get-image', 'id' => $upload->id, 'v' => time()]),
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
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
        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);

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
            'myBookings'   => $service->findMyBookings((string) $me->id, 50, $filterYear),
            'fiscalYears'  => $fiscalYears,
            'filterYear'   => $filterYear,
            'currentYear'  => $currentYear,
            'cars'         => $service->listCars(),
            'drivers'      => $service->listDrivers(),
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
            'cars'         => $service->listCars(),
            'drivers'      => $service->listDrivers(),
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
     * ภารกิจขับรถที่พนักงานขับรถได้รับมอบหมาย.
     */
    public function actionDriverMissions()
    {
        $this->view->title = 'ภารกิจขับรถ';

        $me = UserHelper::GetEmployee();
        if (!$me || !Yii::$app->user->can('driver')) {
            Yii::$app->session->setFlash('error', 'เมนูนี้สำหรับพนักงานขับรถเท่านั้น');
            return $this->redirect(['/mobile/default/index']);
        }

        $service = new MobileVehicleService();
        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);
        return $this->render('driver-missions', [
            'current_page' => 'services',
            'missions' => $service->findDriverMissions((string) $me->id, 100, $filterYear),
            'openCount' => $service->countOpenDriverMissions((string) $me->id, $filterYear),
            'fiscalYears' => $fiscalYears,
            'filterYear' => $filterYear,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * บันทึกผลภารกิจขับรถแบบ mobile.
     */
    public function actionDriverMission($id)
    {
        $this->view->title = 'บันทึกภารกิจขับรถ';

        $me = UserHelper::GetEmployee();
        $service = new MobileVehicleService();
        $mission = $me && Yii::$app->user->can('driver')
            ? $service->findDriverMissionById((int) $id, (string) $me->id)
            : null;

        if (!$mission) {
            Yii::$app->session->setFlash('error', 'ไม่พบภารกิจขับรถ หรือคุณไม่มีสิทธิ์บันทึกรายการนี้');
            return $this->redirect(['/mobile/default/driver-missions']);
        }

        $service->ensureDriverMissionRef($mission);
        $saveErrors = [];
        if (Yii::$app->request->isPost && $mission->load(Yii::$app->request->post())) {
            $result = $service->prepareAndSaveDriverMission($mission);
            if ($result['ok']) {
                Yii::$app->session->setFlash('success', 'บันทึกภารกิจขับรถเรียบร้อย');
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกภารกิจขับรถเรียบร้อย',
                        'redirect_url' => \yii\helpers\Url::to(['/mobile/default/driver-missions']),
                    ];
                }
                return $this->redirect(['/mobile/default/driver-missions']);
            }
            $saveErrors = $result['errors'];
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $message = reset($saveErrors) ?: 'ไม่สามารถบันทึกภารกิจได้ กรุณาตรวจสอบข้อมูลอีกครั้ง';
                if ($result['exception']) {
                    $message = 'เกิดข้อผิดพลาดในการบันทึก: ' . $result['exception']->getMessage();
                }
                return [
                    'status' => 'error',
                    'message' => $message,
                    'errors' => $mission->getErrors(),
                ];
            }
            if ($result['exception']) {
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $result['exception']->getMessage());
            }
        }

        $service->restoreThaiDatesForDetail($mission);
        return $this->render('driver-mission', [
            'current_page' => 'services',
            'mission' => $mission,
            'saveErrors' => $saveErrors,
        ]);
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
        $service = new MobileVehicleService();
        $service->restoreThaiDates($model);
        return $this->render('booking-vehicle', [
            'current_page' => 'services',
            'model'        => $model,
            'employee'     => UserHelper::GetEmployee(),
            'myBookings'   => [],
            'cars'         => $service->listCars(),
            'drivers'      => $service->listDrivers(),
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
        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);

        $roomCards      = $service->listRoomCards();
        $rooms          = $service->listRooms($roomCards);
        $roomLayouts       = $service->listRoomLayouts($model);
        $roomLayoutImages  = $service->listRoomLayoutImages();
        $urgentOptions     = $service->listUrgentOptions($model);
        $equipmentItems    = $service->listEquipmentItems();

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
            'roomLayouts'      => $roomLayouts,
            'roomLayoutImages' => $roomLayoutImages,
            'urgentOptions'    => $urgentOptions,
            'equipmentItems' => $equipmentItems,
            'employee'       => $me,
            'model'          => $model,
            'myBookings'     => $service->findMyBookings((string) $me->id, 50, false, $filterYear),
            'fiscalYears'    => $fiscalYears,
            'filterYear'     => $filterYear,
            'currentYear'    => $currentYear,
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
                $savedModel = $result['model'] ?? $model;
                return $this->redirect(['/mobile/default/leave-request-view', 'id' => $savedModel->id]);
            }
            Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกคำขอลาได้ กรุณาตรวจสอบฟิลด์ที่กรอก');
        }

        // List mode data — filter by fiscal year (default = ปีปัจจุบัน)
        $fiscalYears  = $service->listFiscalYears();
        $currentYear  = (int) \app\components\AppHelper::YearBudget();
        $filterYear   = (int) (Yii::$app->request->get('year', $currentYear));
        if (!isset($fiscalYears[$filterYear])) $filterYear = $currentYear;
        $myLeaves     = $service->findRequestsByYear($me, $filterYear);

        return $this->render('leave-request', [
            'current_page'         => 'services',
            'model'                => $model,
            'employee'             => $me,
            'draftRef'             => $draft['ref'],
            'stats'                => [],
            'leaveSendInitAvatar'  => $service->loadWorkSendAvatar(
                $model->data_json['leave_work_send_id'] ?? null,
                !$model->isNewRecord
            ),
            'approveChain'         => $service->loadApproveChain($model->emp_id),
            // List mode params (orchestrator ตรวจ ?action=new → wizard, ไม่งั้น = list)
            'myLeaves'             => $myLeaves,
            'fiscalYears'          => $fiscalYears,
            'filterYear'           => $filterYear,
            'currentYear'          => $currentYear,
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

    /**
     * แก้ไขคำขอลา (เฉพาะเจ้าของ + สถานะที่ยังแก้ไขได้)
     * Additive — ไม่กระทบ actionLeaveRequest เดิม
     */
    public function actionLeaveRequestEdit($id)
    {
        $this->view->title = 'แก้ไขคำขอลา';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/mobile/default/leave-request']);
        }

        $service = new MobileLeaveService();
        $model   = $service->findOwnedById((int) $id, $me->id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบคำขอลานี้ หรือคุณไม่มีสิทธิ์แก้ไขคำขอลานี้');
            return $this->redirect(['/mobile/default/leave-request']);
        }

        // สถานะที่ยังให้แก้ไขได้: ก่อนเข้ากระบวนการอนุมัติเสร็จสมบูรณ์
        $editableStatuses = ['Pending', 'Save', 'Draft'];
        if (!in_array((string) $model->status, $editableStatuses, true)) {
            Yii::$app->session->setFlash('error', 'คำขอลานี้อยู่ในสถานะที่ไม่สามารถแก้ไขได้แล้ว');
            return $this->redirect(['/mobile/default/leave-request-view', 'id' => $model->id]);
        }

        $fiscalYears = $service->listFiscalYears();
        $currentYear = (int) \app\components\AppHelper::YearBudget();
        $filterYear  = (int) (Yii::$app->request->get('year', $currentYear));
        if (!isset($fiscalYears[$filterYear])) $filterYear = $currentYear;

        return $this->render('leave-request', [
            'current_page'         => 'services',
            'model'                => $model,
            'employee'             => $me,
            'draftRef'             => $model->ref,
            'stats'                => [],
            'leaveSendInitAvatar'  => $service->loadWorkSendAvatar(
                $model->data_json['leave_work_send_id'] ?? null,
                true
            ),
            'approveChain'         => $service->loadApproveChain($model->emp_id),
            'myLeaves'             => [],
            'fiscalYears'          => $fiscalYears,
            'filterYear'           => $filterYear,
            'currentYear'          => $currentYear,
        ]);
    }

    /**
     * งานอนุมัติบน mobile ที่เปิดใช้ตอนนี้ (ขอลา / ขออบรม-ประชุม-ดูงาน)
     * Dashboard + รายการในหน้าเดียว — กรอง bucket / ปีงบประมาณ
     */
    public function actionApprovals()
    {
        $this->view->title = 'งานอนุมัติ';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/mobile/default/index']);
        }

        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear  = $this->resolveMobileFiscalYear($fiscalYears);

        // เหลือเฉพาะ 3 สถานะตาม spec UI — รออนุมัติ / อนุมัติแล้ว / ไม่อนุมัติ
        $allowedBuckets = ['pending', 'approved', 'rejected'];
        $bucket = (string) Yii::$app->request->get('bucket', 'pending');
        if (!in_array($bucket, $allowedBuckets, true)) {
            $bucket = 'pending';
        }

        // กรองตามประเภทงานที่เปิดใช้บน mobile (leave / development / all)
        $allowedTypes = array_merge(['all'], array_keys(MobileApprovalService::typeMeta()));
        $type = (string) Yii::$app->request->get('type', 'all');
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }
        $typeFilter = $type === 'all' ? null : $type;

        $service = new MobileApprovalService();
        // "รออนุมัติ" คือคิวรอทำ ไม่ใช่ history — ไม่กรองปีงบ (ตรงกับ ApproveHelper::Info ที่ home ใช้)
        // history buckets (approved/rejected) ใช้ปีงบตามปกติ
        $yearForList = ($bucket === 'pending') ? null : $filterYear;
        $approvals   = $service->findForEmployee($me, $bucket, $yearForList, 200, $typeFilter);

        // นับจำนวน: pending ข้ามปี, history ใช้ปีงบที่เลือก
        $historyCounts = $service->bucketCounts($me, $filterYear, $typeFilter);
        $pendingTotal  = count($service->findForEmployee($me, 'pending', null, 500, $typeFilter));
        $counts        = [
            'pending'  => $pendingTotal,
            'approved' => (int) ($historyCounts['approved'] ?? 0),
            'rejected' => (int) ($historyCounts['rejected'] ?? 0),
        ];

        return $this->render('approvals', [
            'current_page' => 'services',
            'approvals'    => $approvals,
            'service'      => $service,
            'counts'       => $counts,
            'bucket'       => $bucket,
            'type'         => $type,
            'fiscalYears'  => $fiscalYears,
            'filterYear'   => $filterYear,
            'currentYear'  => $currentYear,
        ]);
    }

    /**
     * รายละเอียดงานอนุมัติ + timeline ลำดับการอนุมัติ
     */
    public function actionApprovalView($id)
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/mobile/default/index']);
        }

        $service = new MobileApprovalService();
        $approve = $service->findByIdForEmployee((int) $id, $me);
        if ($approve === null) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการอนุมัติ หรือคุณไม่มีสิทธิ์ดูรายการนี้');
            return $this->redirect(['/mobile/default/approvals']);
        }

        $parent   = $service->loadParent($approve);
        $meta     = $service->buildMeta($approve, $parent);
        $timeline = $service->loadTimeline($approve);

        $this->view->title = 'อนุมัติ ' . $service->typeLabel((string) $approve->name);

        return $this->render('approval-view', [
            'current_page' => 'services',
            'approve'      => $approve,
            'parent'       => $parent,
            'meta'         => $meta,
            'timeline'     => $timeline,
            'service'      => $service,
        ]);
    }

    /**
     * บันทึกผลการอนุมัติ — POST AJAX (status: Pass|Reject|SendBack, comment optional)
     * ส่งต่อให้ MobileApprovalService::process ซึ่ง delegate workflow ให้ service เดิม
     * ของ leave (Telegram + status map) — ไม่ duplicate business logic
     */
    public function actionApprovalUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะ POST'];
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงาน'];
        }

        $service = new MobileApprovalService();
        $approve = $service->findByIdForEmployee((int) $id, $me);
        if ($approve === null) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการอนุมัติ หรือคุณไม่มีสิทธิ์'];
        }

        $status  = (string) Yii::$app->request->post('status', '');
        $comment = trim((string) Yii::$app->request->post('comment', ''));

        $result = $service->process($approve, $status, $comment, (int) $me->id);
        if (!($result['ok'] ?? false)) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
        }

        return [
            'status'   => 'success',
            'message'  => $result['message'] ?? 'บันทึกเรียบร้อย',
            'redirect' => \yii\helpers\Url::to(['/mobile/default/approvals', 'bucket' => 'pending']),
        ];
    }

    /**
     * Backward-compat aliases — Telegram links เก่าอาจ hardcode URL เหล่านี้
     * ทั้งหมด redirect ไปหน้าใหม่ ไม่ render view เก่าอีกแล้ว
     */
    public function actionLeaveApprovals()
    {
        return $this->redirect(['/mobile/default/approvals',
            'bucket' => 'pending',
            'year'   => (int) Yii::$app->request->get('year', AppHelper::YearBudget()),
        ]);
    }

    public function actionApproveLeave($id)
    {
        return $this->redirect(['/mobile/default/approval-view', 'id' => (int) $id]);
    }

    public function actionApproveLeaveUpdate($id)
    {
        // เก่า: ใช้ POST status เท่านั้น — แมพไปยัง endpoint ใหม่ ผ่าน MobileApprovalService
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะ POST'];
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงาน'];
        }

        $service = new MobileApprovalService();
        $approve = $service->findByIdForEmployee((int) $id, $me);
        if ($approve === null) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการอนุมัติ หรือคุณไม่มีสิทธิ์'];
        }

        $status = (string) Yii::$app->request->post('status');
        $result = $service->process($approve, $status, '', (int) $me->id);
        if (!($result['ok'] ?? false)) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
        }

        return [
            'status'   => 'success',
            'redirect' => \yii\helpers\Url::to(['/mobile/default/approvals', 'bucket' => 'pending']),
        ];
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
    /**
     * รายการประวัติแจ้งซ่อมของผู้ใช้ (LIST/HISTORY only)
     * Wizard form ส่งซ่อมถูกย้ายไป /mobile/default/repair-request แล้ว
     * (ก่อนหน้านี้ action นี้รวม wizard+list อยู่ในตัวเอง ตอนนี้เหลือเฉพาะ list)
     *
     * Backward compat:
     *  - ?asset=... หรือ ?action=new → redirect ไป wizard ใหม่
     *  - ?id=X (asset edit เดิม) → redirect ไป wizard ใหม่พร้อม id
     */
    public function actionMaintenanceRequest()
    {
        $me = UserHelper::GetEmployee();

        // Backward compat: เก่ามี ?action=new / ?asset=... → ส่งต่อไป wizard ใหม่
        $actionParam = (string) (Yii::$app->request->get('action') ?? '');
        $assetParam  = trim((string) Yii::$app->request->get('asset', ''));
        $assetCodeQs = trim((string) Yii::$app->request->get('asset_code', Yii::$app->request->get('code', '')));
        if ($actionParam === 'new' || $assetParam !== '' || $assetCodeQs !== '') {
            $code = $assetCodeQs;
            if ($code === '' && $assetParam !== '') {
                $asset = ctype_digit($assetParam)
                    ? Asset::findOne(['id' => (int) $assetParam])
                    : Asset::findOne(['code' => $assetParam]);
                if ($asset) $code = (string) $asset->code;
            }
            $params = ['/mobile/default/repair-request', 'send_type' => 'asset'];
            if ($code !== '') $params['asset_number'] = $code;
            return $this->redirect($params);
        }

        $this->view->title = 'งานแจ้งซ่อมของฉัน';

        $service     = new MobileMaintenanceService();
        $fiscalYears = $service->listFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear  = (int) (Yii::$app->request->get('year', $currentYear));
        if (!isset($fiscalYears[$filterYear])) $filterYear = $currentYear;

        $allowedBuckets = ['all', 'waiting', 'in_progress', 'done', 'cancelled'];
        $bucket = (string) Yii::$app->request->get('bucket', 'all');
        if (!in_array($bucket, $allowedBuckets, true)) {
            $bucket = 'all';
        }

        $allRequests = $service->findRequestsByYear($me, $filterYear);
        $kpi         = $service->kpiCounts($allRequests);
        $myRequests  = $service->filterBySubBucket($allRequests, $bucket);

        return $this->render('maintenance-request', [
            'current_page' => 'services',
            'service'      => $service,
            'myRequests'   => $myRequests,
            'fiscalYears'  => $fiscalYears,
            'filterYear'   => $filterYear,
            'currentYear'  => $currentYear,
            'bucket'       => $bucket,
            'kpi'          => $kpi,
        ]);
    }

    /**
     * รายละเอียดงานแจ้งซ่อม (เฉพาะเจ้าของ) — view + status tracker + rating gate.
     */
    public function actionMaintenanceView($id)
    {
        $me = UserHelper::GetEmployee();
        $service = new MobileMaintenanceService();
        $model = $me ? $service->findOwnedById((int) $id, $me->id) : null;
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบงานแจ้งซ่อมนี้ หรือคุณไม่มีสิทธิ์ดูรายการนี้');
            return $this->redirect(['/mobile/default/maintenance-request']);
        }

        $this->view->title = 'รายละเอียดงานแจ้งซ่อม';
        return $this->render('maintenance_view', [
            'current_page' => 'services',
            'model'        => $model,
            'service'      => $service,
        ]);
    }

    /**
     * ลงคะแนนความพึงพอใจ — เปิดเฉพาะงานที่ "ซ่อมเสร็จแล้ว" และยังไม่เคยให้คะแนน.
     */
    public function actionMaintenanceRate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }
        $me = UserHelper::GetEmployee();
        $service = new MobileMaintenanceService();
        $model = $me ? $service->findOwnedById((int) $id, $me->id) : null;
        if (!$model) {
            return ['status' => 'error', 'message' => 'ไม่พบงานแจ้งซ่อมนี้ หรือคุณไม่มีสิทธิ์'];
        }
        if (!$service->canRate($model)) {
            return ['status' => 'error', 'message' => 'งานนี้ยังลงคะแนนไม่ได้ (อาจซ่อมยังไม่เสร็จ หรือเคยลงคะแนนแล้ว)'];
        }
        $score = (int) Yii::$app->request->post('score', 0);
        if ($score < 1 || $score > 5) {
            return ['status' => 'error', 'message' => 'กรุณาให้คะแนน 1 ถึง 5 ดาว'];
        }
        $comment = trim((string) Yii::$app->request->post('comment', ''));
        $model->rating = (string) $score;
        $dataJson = is_array($model->data_json) ? $model->data_json : [];
        $dataJson['rating_score'] = $score;
        $dataJson['rating_comment'] = $comment;
        $dataJson['rating_at'] = date('Y-m-d H:i:s');
        $dataJson['rating_by'] = $me->id ?? null;
        $model->data_json = $dataJson;
        if (!$model->save(false)) {
            return ['status' => 'error', 'message' => 'บันทึกคะแนนไม่สำเร็จ กรุณาลองใหม่'];
        }
        return ['status' => 'success', 'message' => 'บันทึกคะแนนเรียบร้อย ขอบคุณสำหรับ feedback'];
    }

    /**
     * ขอยกเลิกงานแจ้งซ่อม (เจ้าของยกเลิกได้ก่อนเข้าซ่อม).
     */
    public function actionMaintenanceCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }
        $me = UserHelper::GetEmployee();
        $service = new MobileMaintenanceService();
        $model = $me ? $service->findOwnedById((int) $id, $me->id) : null;
        if (!$model) {
            return ['status' => 'error', 'message' => 'ไม่พบงานนี้ หรือคุณไม่มีสิทธิ์'];
        }
        if (!$service->canCancel($model)) {
            return ['status' => 'error', 'message' => 'งานนี้อยู่ในสถานะที่ยกเลิกไม่ได้แล้ว'];
        }
        $model->status = '5'; // ยกเลิก ตาม taxonomy repair_status
        $dataJson = is_array($model->data_json) ? $model->data_json : [];
        $dataJson['cancel_at'] = date('Y-m-d H:i:s');
        $dataJson['cancel_by'] = $me->id ?? null;
        $model->data_json = $dataJson;
        if (!$model->save(false)) {
            return ['status' => 'error', 'message' => 'ยกเลิกไม่สำเร็จ กรุณาลองใหม่'];
        }
        return ['status' => 'success', 'message' => 'ยกเลิกงานแจ้งซ่อมเรียบร้อย'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ส่งซ่อม (Wizard mobile-first) — สร้างใหม่ คู่ขนานกับ /me/repair-v2/create
    // ใช้ Helpdesk model เดิม + reuse workflow side effects (asset_status, Telegram)
    // ผ่าน MobileRepairService — ไม่แตะ RepairV2Controller
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Wizard แจ้งส่งซ่อม
     *   GET  /mobile/default/repair-request[?asset_number=...&send_type=asset]  → form
     *   POST /mobile/default/repair-request                                     → save
     */
    public function actionRepairRequest()
    {
        $this->view->title = 'แจ้งส่งซ่อม';

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน กรุณาติดต่อ HR');
            return $this->redirect(['/mobile/default/services']);
        }

        $service = new MobileRepairService();
        $assetNumber = trim((string) Yii::$app->request->get('asset_number', ''));
        $sendType    = (string) Yii::$app->request->get('send_type', '');

        $model = $service->newDraft($me, $assetNumber !== '' ? $assetNumber : null);
        $assetInfo = $assetNumber !== '' ? $service->lookupAsset($assetNumber) : null;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = $service->save($model);
            if ($result['ok']) {
                // อัปโหลดรูปแนบ (ไม่บังคับ)
                $service->savePhotos($model);

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status'       => 'success',
                        'message'      => 'ส่งคำขอซ่อมเรียบร้อย',
                        'redirect_url' => \yii\helpers\Url::to(['/mobile/default/repair-success', 'id' => $model->id]),
                    ];
                }
                return $this->redirect(['/mobile/default/repair-success', 'id' => $model->id]);
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status'  => 'error',
                    'message' => 'ไม่สามารถบันทึกได้ กรุณาตรวจสอบฟิลด์ที่กรอก',
                    'errors'  => $result['errors'],
                ];
            }
            Yii::$app->session->setFlash('error', reset($result['errors']) ?: 'ไม่สามารถบันทึกได้');
        }

        return $this->render('repair-request', [
            'current_page'  => 'services',
            'model'         => $model,
            'employee'      => $me,
            'assetInfo'     => $assetInfo,
            'sendType'      => $sendType,
            'deviceTypes'   => $service->getDeviceTypes(),
            'urgencyOpts'   => $service->getUrgencyOptions(),
            'repairGroups'  => $service->getRepairGroups(),
        ]);
    }

    /**
     * หน้า success หลังบันทึกแจ้งซ่อมสำเร็จ (เฉพาะเจ้าของรายการ)
     */
    public function actionRepairSuccess($id)
    {
        $me = UserHelper::GetEmployee();
        $service = new MobileRepairService();
        $model = $me ? $service->findOwnedById((int) $id, $me->id) : null;
        if (!$model) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการแจ้งซ่อมนี้');
            return $this->redirect(['/mobile/default/services']);
        }
        $this->view->title = 'ส่งคำขอซ่อมเรียบร้อย';
        return $this->render('repair_success', [
            'current_page' => 'services',
            'model'        => $model,
        ]);
    }

    /**
     * AJAX endpoint คืน repair_group code จาก asset_number (ใช้ใน wizard step 1)
     */
    public function actionRepairGetGroup($asset_number)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $group = (new MobileRepairService())->resolveRepairGroupByAsset((string) $asset_number);
        return ['status' => 'success', 'repair_group' => $group];
    }

    /**
     * คำขอของฉัน — ดูคำขอทั้งหมด (จองห้อง, ขอลา, จองรถ, แจ้งซ่อม).
     */
    public function actionMyRequests($type = 'all')
    {
        $this->view->title = 'คำขอของฉัน';

        $me = UserHelper::GetEmployee();
        $fiscalYears = $this->mobileFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = $this->resolveMobileFiscalYear($fiscalYears);

        // Aggregate page รวมประวัติทั้งหมดของผู้ใช้ (รวม soft-deleted ก็ได้ → withDeleted=true)
        $meetings = $me ? (new MobileMeetingService())->findMyBookings((string) $me->id, 100, true, $filterYear) : [];
        $leaves   = (new MobileLeaveService())->findRecentRequests($me, 100, $filterYear);
        $vehicles = $me ? (new MobileVehicleService())->findMyBookings((string) $me->id, 100, $filterYear) : [];

        return $this->render('my-requests', [
            'current_page' => 'profile',
            'type'         => $type,
            'meetings'     => $meetings,
            'leaves'       => $leaves,
            'vehicles'     => $vehicles,
            'fiscalYears'  => $fiscalYears,
            'filterYear'   => $filterYear,
            'currentYear'  => $currentYear,
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
        $roomLayouts       = $service->listRoomLayouts($meeting);
        $roomLayoutImages  = $service->listRoomLayoutImages();
        $urgentOptions     = $service->listUrgentOptions($meeting);
        $equipmentItems    = $service->listEquipmentItems();

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
            'roomLayouts'      => $roomLayouts,
            'roomLayoutImages' => $roomLayoutImages,
            'urgentOptions'    => $urgentOptions,
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
     * GET filter: year (fiscal year), status (pending|passed|cancelled|all), room (room code).
     */
    public function actionRoomManage()
    {
        $this->view->title = 'จัดการห้องประชุม';

        $me = UserHelper::GetEmployee();
        $service = new MobileMeetingAdminService();
        $fiscalYears = $service->listFiscalYears();
        $currentYear = (int) AppHelper::YearBudget();
        $filterYear = (int) Yii::$app->request->get('year', $currentYear);
        if (!isset($fiscalYears[$filterYear])) {
            $filterYear = $currentYear;
        }

        $owned    = $me ? $service->findOwnedRoomsForUser((string) $me->id) : ['codes' => [], 'titles' => []];
        $meetings = $service->findMeetingsForOwnedRooms($owned['codes'], 200, $filterYear);
        $statsCount = $service->bucketCountsForRoomManage($meetings);

        return $this->render('room-manage', [
            'current_page'   => 'profile',
            'meetings'       => $meetings,
            'ownedRoomCodes' => $owned['codes'],
            'roomTitles'     => $owned['titles'],
            'statsCount'     => $statsCount,
            'fiscalYears'    => $fiscalYears,
            'filterYear'     => $filterYear,
            'currentYear'    => $currentYear,
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

    /**
     * Hub ภาพรวม — เลือกแดชบอร์ดบุคลากร / ทรัพย์สิน
     */
    public function actionOverview()
    {
        $this->view->title = 'ภาพรวม';
        return $this->render('overview', [
            'current_page' => 'home',
            'hrTotal'    => $this->overviewHrTotal(),
            'assetTotal' => $this->overviewAssetTotal(),
        ]);
    }

    /**
     * แดชบอร์ดบุคลากร — KPI, สัดส่วนประเภท, ตำแหน่งยอดนิยม, รายการล่าสุด
     */
    public function actionOverviewHr()
    {
        $this->view->title = 'ภาพรวมบุคลากร';
        $total = $this->overviewHrTotal();
        return $this->render('overview-hr', [
            'current_page'   => 'home',
            'total'          => $total,
            'typeBreakdown'  => $this->overviewHrTypeBreakdown(),
            'groupBreakdown' => $this->overviewHrGroupBreakdown(8),
            'topPositions'   => $this->overviewHrTopPositions(6),
            'recent'         => $this->overviewHrRecent(5),
        ]);
    }

    /**
     * แดชบอร์ดทรัพย์สิน — status KPI, donut, หมวด, รายการล่าสุด
     */
    public function actionOverviewAsset()
    {
        $this->view->title = 'ภาพรวมทรัพย์สิน';
        $statusBreakdown = $this->overviewAssetStatusBreakdown();
        $total = 0;
        foreach ($statusBreakdown as $row) { $total += (int) $row['count']; }
        return $this->render('overview-asset', [
            'current_page'        => 'home',
            'total'               => $total,
            'statusBreakdown'     => $statusBreakdown,
            'conditionBreakdown'  => $this->overviewAssetConditionBreakdown(),
            'riskBreakdown'       => $this->overviewAssetRiskBreakdown(),
            'categoryBreakdown'   => $this->overviewAssetCategoryBreakdown(6),
            'budgetTypeBreakdown' => $this->overviewAssetMoneyBreakdown('budget_type', 'budget_type'),
            'methodGetBreakdown'  => $this->overviewAssetMoneyBreakdown('method_get', 'method_get'),
            'purchaseBreakdown'   => $this->overviewAssetMoneyBreakdown('purchase', 'purchase'),
            'totalAmount'         => $this->overviewAssetTotalAmount(),
            'recent'              => $this->overviewAssetRecent(5),
        ]);
    }

    /** @return int รวมบุคลากร (active เท่านั้น) */
    protected function overviewHrTotal(): int
    {
        try {
            $q = Employees::find();
            if (Yii::$app->db->getTableSchema(Employees::tableName(), true)
                && Yii::$app->db->getTableSchema(Employees::tableName())->getColumn('active')) {
                $q->andWhere(['or', ['active' => 1], ['active' => null]]);
            }
            return (int) $q->count();
        } catch (\Throwable $e) { return 0; }
    }

    /** @return array<int,array{label:string,count:int}> สัดส่วนตาม EmployeeType */
    protected function overviewHrTypeBreakdown(): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(EmployeeType::tableName(), true);
            if (!$schema) return [];
            $types = EmployeeType::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();
            $out = [];
            foreach ($types as $t) {
                $cnt = (int) Employees::find()->andWhere(['employee_type_id' => $t->id])->count();
                if ($cnt > 0) $out[] = ['label' => (string) $t->title, 'count' => $cnt];
            }
            usort($out, static fn($a,$b) => $b['count'] <=> $a['count']);
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /** @return array<int,array{label:string,count:int}> สัดส่วนตาม EmployeePositionGroup */
    protected function overviewHrGroupBreakdown(int $limit = 8): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(EmployeePositionGroup::tableName(), true);
            if (!$schema) return [];
            $rows = (new \yii\db\Query())
                ->select(['gid' => 'employee_position_group_id', 'cnt' => 'COUNT(*)'])
                ->from(Employees::tableName())
                ->andWhere(['not', ['employee_position_group_id' => null]])
                ->groupBy(['employee_position_group_id'])
                ->orderBy(['cnt' => SORT_DESC])
                ->limit($limit)
                ->all();
            if (!$rows) return [];
            $ids = array_column($rows, 'gid');
            $titles = EmployeePositionGroup::find()
                ->select(['id','title'])->where(['id' => $ids])->asArray()->all();
            $titleMap = array_column($titles, 'title', 'id');
            $out = [];
            foreach ($rows as $r) {
                $label = (string) ($titleMap[(int) $r['gid']] ?? 'ไม่ระบุ');
                $out[] = ['label' => $label, 'count' => (int) $r['cnt']];
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /** @return array<int,array{label:string,count:int}> ตำแหน่ง top */
    protected function overviewHrTopPositions(int $limit = 6): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(EmployeePosition::tableName(), true);
            if (!$schema) return [];
            $rows = (new \yii\db\Query())
                ->select(['pid' => 'employee_position_id', 'cnt' => 'COUNT(*)'])
                ->from(Employees::tableName())
                ->andWhere(['not', ['employee_position_id' => null]])
                ->groupBy(['employee_position_id'])
                ->orderBy(['cnt' => SORT_DESC])
                ->limit($limit)
                ->all();
            if (!$rows) return [];
            $ids = array_column($rows, 'pid');
            $titles = EmployeePosition::find()
                ->select(['id','title'])->where(['id' => $ids])->asArray()->all();
            $titleMap = array_column($titles, 'title', 'id');
            $out = [];
            foreach ($rows as $r) {
                $label = (string) ($titleMap[(int) $r['pid']] ?? 'ไม่ระบุ');
                $out[] = ['label' => $label, 'count' => (int) $r['cnt']];
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /** @return Employees[] รายการล่าสุด */
    protected function overviewHrRecent(int $limit = 5): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Employees::tableName(), true);
            if (!$schema) return [];
            $order = $schema->getColumn('created_at') ? ['created_at' => SORT_DESC] : ['id' => SORT_DESC];
            return Employees::find()->orderBy($order)->limit($limit)->all();
        } catch (\Throwable $e) { return []; }
    }

    /**
     * แสดงรายการครุภัณฑ์ตามสถานะ (drill-down จาก donut legend)
     */
    public function actionOverviewAssetByStatus(string $id = '')
    {
        $id = trim($id);
        if ($id === '') {
            return $this->redirect(['/mobile/default/overview-asset']);
        }

        // หา meta ของ status นี้จาก breakdown
        $breakdown = $this->overviewAssetStatusBreakdown();
        $meta = null;
        foreach ($breakdown as $row) {
            if ((string) $row['id'] === $id) { $meta = $row; break; }
        }
        if (!$meta) {
            // fallback meta — ไม่อยู่ใน master แล้ว
            $meta = ['id' => $id, 'label' => 'สถานะ ' . $id, 'tone' => 'secondary', 'icon' => 'circle-dot', 'count' => 0];
        }

        $this->view->title = 'ครุภัณฑ์สถานะ ' . $meta['label'];
        $assets = $this->overviewAssetByStatus($id, 200);
        return $this->render('overview-asset-by-status', [
            'current_page' => 'home',
            'statusId'     => $id,
            'meta'         => $meta,
            'assets'       => $assets,
        ]);
    }

    /**
     * คืนค่าครุภัณฑ์ตาม asset_status id
     *
     * @return Asset[]
     */
    protected function overviewAssetByStatus(string $id, int $limit = 200): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('asset_status')) return [];
            $q = Asset::find()->andWhere(['asset_status' => $id]);
            if ($schema->getColumn('deleted_at')) {
                $q->andWhere(['deleted_at' => null]);
            }
            $order = $schema->getColumn('updated_at')
                ? ['updated_at' => SORT_DESC]
                : ($schema->getColumn('created_at') ? ['created_at' => SORT_DESC] : ['id' => SORT_DESC]);
            return $q->orderBy($order)->limit($limit)->all();
        } catch (\Throwable $e) { return []; }
    }

    /**
     * แสดงรายการครุภัณฑ์ตามระดับความเสี่ยง (drill-down จาก KPI tile)
     * รองรับ: H, M, L, unassessed (null/empty)
     */
    public function actionOverviewAssetByRisk(string $level = 'H')
    {
        $level = strtoupper(trim($level));
        $meta = [
            'H' => ['label' => 'สูง',  'tone' => 'danger',    'icon' => 'octagon-alert',  'desc' => 'ต้องเฝ้าระวัง'],
            'M' => ['label' => 'กลาง', 'tone' => 'warning',   'icon' => 'alert-triangle', 'desc' => 'ระดับปานกลาง'],
            'L' => ['label' => 'ต่ำ',  'tone' => 'success',   'icon' => 'shield-check',   'desc' => 'อยู่ในเกณฑ์'],
        ];
        $isUnassessed = !isset($meta[$level]);
        if ($isUnassessed) {
            $level = 'UNASSESSED';
            $current = ['label' => 'ยังไม่ประเมิน', 'tone' => 'secondary', 'icon' => 'circle-help', 'desc' => 'รอประเมิน'];
        } else {
            $current = $meta[$level];
        }

        $this->view->title = 'ครุภัณฑ์ความเสี่ยง' . $current['label'];

        $assets = $this->overviewAssetByRisk($level, 200);
        return $this->render('overview-asset-by-risk', [
            'current_page' => 'home',
            'level'        => $level,
            'meta'         => $current,
            'assets'       => $assets,
        ]);
    }

    /**
     * คืนค่าครุภัณฑ์กรองตามระดับความเสี่ยง
     * level: 'H' | 'M' | 'L' | 'UNASSESSED'
     *
     * @return Asset[]
     */
    protected function overviewAssetByRisk(string $level, int $limit = 200): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('risk_level')) return [];
            $q = Asset::find();
            $this->applyAssetOverviewFilter($q, $schema);
            if ($level === 'UNASSESSED') {
                $q->andWhere([
                    'or',
                    ['risk_level' => null],
                    ['risk_level' => ''],
                    ['not in', 'risk_level', ['H', 'M', 'L', 'h', 'm', 'l']],
                ]);
            } else {
                $q->andWhere(['risk_level' => $level]);
            }
            $order = $schema->getColumn('updated_at')
                ? ['updated_at' => SORT_DESC]
                : ($schema->getColumn('created_at') ? ['created_at' => SORT_DESC] : ['id' => SORT_DESC]);
            return $q->orderBy($order)->limit($limit)->all();
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Cached ids ของสถานะ "จำหน่ายแล้ว" (asset_status เป็นได้ทั้ง asset_status table หรือ categorise)
     * @var array<int,string>|null
     */
    private ?array $_disposedAssetStatusIds = null;

    /**
     * คืน id ของสถานะที่หมายถึง "จำหน่ายแล้ว" — เพื่อตัดออกจากภาพรวม
     * รวมจาก asset_status table + categorise(name=asset_status)
     *
     * @return array<int,string>
     */
    protected function getDisposedAssetStatusIds(): array
    {
        if ($this->_disposedAssetStatusIds !== null) {
            return $this->_disposedAssetStatusIds;
        }
        $ids = [];
        try {
            $schema = Yii::$app->db->getTableSchema(AssetStatus::tableName(), true);
            if ($schema) {
                $rows = AssetStatus::find()
                    ->select(['id'])
                    ->andWhere(['name' => 'จำหน่ายแล้ว'])
                    ->asArray()->all();
                foreach ($rows as $r) {
                    $v = (string) ($r['id'] ?? '');
                    if ($v !== '') $ids[] = $v;
                }
            }
        } catch (\Throwable $e) { /* ignore */ }
        try {
            $rows = (new \yii\db\Query())
                ->select(['code'])
                ->from(AssetCategory::tableName())
                ->where(['name' => 'asset_status', 'title' => 'จำหน่ายแล้ว'])
                ->all();
            foreach ($rows as $r) {
                $v = (string) ($r['code'] ?? '');
                if ($v !== '') $ids[] = $v;
            }
        } catch (\Throwable $e) { /* ignore */ }

        $this->_disposedAssetStatusIds = array_values(array_unique($ids));
        return $this->_disposedAssetStatusIds;
    }

    /**
     * เพิ่มเงื่อนไข "ครุภัณฑ์ที่มองเห็นในภาพรวม" — soft-delete safe + ตัดสถานะจำหน่าย
     * ใช้ได้ทั้ง \yii\db\Query กับ \yii\db\ActiveQuery
     */
    protected function applyAssetOverviewFilter($query, ?\yii\db\TableSchema $schema = null): void
    {
        $schema = $schema ?? Yii::$app->db->getTableSchema(Asset::tableName(), true);
        if (!$schema) return;

        if ($schema->getColumn('deleted_at')) {
            $query->andWhere(['deleted_at' => null]);
        }
        if ($schema->getColumn('asset_status')) {
            $disposed = $this->getDisposedAssetStatusIds();
            if ($disposed) {
                $query->andWhere(['not in', 'asset_status', $disposed]);
            }
        }
        if ($schema->getColumn('lifecycle_status')) {
            $query->andWhere(['or',
                ['lifecycle_status' => null],
                ['<>', 'lifecycle_status', Asset::LIFECYCLE_DISPOSED],
            ]);
        }
    }

    /** @return int รวมครุภัณฑ์ที่มองเห็นในภาพรวม (ตัด soft-delete + สถานะจำหน่าย) */
    protected function overviewAssetTotal(): int
    {
        try {
            $q = Asset::find();
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            $this->applyAssetOverviewFilter($q, $schema);
            return (int) $q->count();
        } catch (\Throwable $e) { return 0; }
    }

    /**
     * นับครุภัณฑ์ตามสถานะ (asset_status) — แสดงสถานะทั้งหมดจาก master
     * รวมรายการที่ count = 0 ด้วย, เรียงตาม sort_order ของ master
     * tone/icon mapping ตาม Asset::viewStatus() (1=ปกติ, 2=จำหน่าย, 3=รอจำหน่าย, 4=ถูกยืม, 5=เตือน)
     *
     * @return array<int,array{id:string,label:string,count:int,tone:string,icon:string}>
     */
    protected function overviewAssetStatusBreakdown(): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('asset_status')) return [];

            // นับครุภัณฑ์ตามสถานะ (ตัดจำหน่ายผ่าน applyAssetOverviewFilter)
            $countQ = (new \yii\db\Query())
                ->select(['s' => 'asset_status', 'cnt' => 'COUNT(*)'])
                ->from(Asset::tableName())
                ->andWhere(['not', ['asset_status' => null]])
                ->andWhere(['<>', 'asset_status', '']);
            $this->applyAssetOverviewFilter($countQ, $schema);
            $countRows = $countQ->groupBy(['asset_status'])->all();
            $countMap = [];
            foreach ($countRows as $r) {
                $countMap[(string) $r['s']] = (int) $r['cnt'];
            }

            // master statuses — asset_status table ก่อน
            $masters = [];
            try {
                $assetStatusSchema = Yii::$app->db->getTableSchema(AssetStatus::tableName(), true);
                if ($assetStatusSchema) {
                    $q = AssetStatus::find()->select(['id', 'name']);
                    $orderBy = [];
                    if ($assetStatusSchema->getColumn('sort_order')) $orderBy['sort_order'] = SORT_ASC;
                    $orderBy['id'] = SORT_ASC;
                    if ($assetStatusSchema->getColumn('is_active')) {
                        $q->andWhere(['or', ['is_active' => 1], ['is_active' => null]]);
                    }
                    $rows = $q->orderBy($orderBy)->asArray()->all();
                    foreach ($rows as $r) {
                        $id   = (string) ($r['id'] ?? '');
                        $name = trim((string) ($r['name'] ?? ''));
                        if ($id === '') continue;
                        $masters[$id] = $name !== '' ? $name : ('สถานะ ' . $id);
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // fallback / supplement จาก categorise(name=asset_status)
            try {
                $cats = (new \yii\db\Query())
                    ->select(['code', 'title'])
                    ->from(AssetCategory::tableName())
                    ->where(['name' => 'asset_status'])
                    ->andFilterWhere(['active' => 1])
                    ->orderBy(['code' => SORT_ASC])
                    ->all();
                foreach ($cats as $c) {
                    $code  = (string) ($c['code'] ?? '');
                    $title = trim((string) ($c['title'] ?? ''));
                    if ($code === '') continue;
                    if (!isset($masters[$code])) {
                        $masters[$code] = $title !== '' ? $title : ('สถานะ ' . $code);
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // เพิ่ม id ที่อยู่ใน asset แต่ไม่ได้อยู่ใน master (orphan) ท้ายลิสต์
            foreach ($countMap as $id => $_cnt) {
                if (!isset($masters[$id])) {
                    $masters[$id] = $id !== '' ? ('สถานะ ' . $id) : 'ไม่ระบุ';
                }
            }

            if (!$masters) return [];

            $toneMap = [
                '1' => ['tone' => 'success',   'icon' => 'check-circle'],
                '2' => ['tone' => 'secondary', 'icon' => 'archive'],
                '3' => ['tone' => 'danger',    'icon' => 'pause-circle'],
                '4' => ['tone' => 'primary',   'icon' => 'refresh-cw'],
                '5' => ['tone' => 'warning',   'icon' => 'alert-triangle'],
            ];
            $fallbackPalette = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];

            $out = [];
            $idx = 0;
            foreach ($masters as $id => $label) {
                $tone = $toneMap[$id]['tone'] ?? $fallbackPalette[$idx % count($fallbackPalette)];
                $icon = $toneMap[$id]['icon'] ?? 'circle-dot';
                $out[] = [
                    'id'    => (string) $id,
                    'label' => $label,
                    'count' => (int) ($countMap[$id] ?? 0),
                    'tone'  => $tone,
                    'icon'  => $icon,
                ];
                $idx++;
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /**
     * นับครุภัณฑ์ตามสภาพ (asset_condition) — แสดงทุกสภาพจาก master เรียงตาม sort_order
     * tone/icon mapping ตาม Asset::getConditionBadge()
     *
     * @return array<int,array{id:string,label:string,count:int,tone:string,icon:string}>
     */
    protected function overviewAssetConditionBreakdown(): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('asset_condition')) return [];

            $countQ = (new \yii\db\Query())
                ->select(['s' => 'asset_condition', 'cnt' => 'COUNT(*)'])
                ->from(Asset::tableName())
                ->andWhere(['not', ['asset_condition' => null]])
                ->andWhere(['<>', 'asset_condition', '']);
            $this->applyAssetOverviewFilter($countQ, $schema);
            $countRows = $countQ->groupBy(['asset_condition'])->all();
            $countMap = [];
            foreach ($countRows as $r) {
                $countMap[(string) $r['s']] = (int) $r['cnt'];
            }

            $masters = [];
            try {
                $cs = Yii::$app->db->getTableSchema(AssetCondition::tableName(), true);
                if ($cs) {
                    $q = AssetCondition::find()->select(['id', 'name']);
                    $orderBy = [];
                    if ($cs->getColumn('sort_order')) $orderBy['sort_order'] = SORT_ASC;
                    $orderBy['id'] = SORT_ASC;
                    if ($cs->getColumn('is_active')) {
                        $q->andWhere(['or', ['is_active' => 1], ['is_active' => null]]);
                    }
                    foreach ($q->orderBy($orderBy)->asArray()->all() as $r) {
                        $id = (string) ($r['id'] ?? '');
                        if ($id === '') continue;
                        $masters[$id] = trim((string) ($r['name'] ?? '')) ?: $id;
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            foreach ($countMap as $id => $_cnt) {
                if (!isset($masters[$id])) $masters[$id] = $id;
            }
            if (!$masters) return [];

            // mapping ตาม Asset::getConditionBadge()
            $toneMap = [
                'good'    => ['tone' => 'success', 'icon' => 'thumbs-up'],
                'fair'    => ['tone' => 'info',    'icon' => 'circle-dot'],
                'worn'    => ['tone' => 'warning', 'icon' => 'alert-triangle'],
                'damaged' => ['tone' => 'danger',  'icon' => 'wrench'],
            ];
            $fallbackPalette = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];

            $out = [];
            $idx = 0;
            foreach ($masters as $id => $label) {
                $tone = $toneMap[$id]['tone'] ?? $fallbackPalette[$idx % count($fallbackPalette)];
                $icon = $toneMap[$id]['icon'] ?? 'circle-dot';
                $out[] = [
                    'id'    => $id,
                    'label' => $label,
                    'count' => (int) ($countMap[$id] ?? 0),
                    'tone'  => $tone,
                    'icon'  => $icon,
                ];
                $idx++;
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /**
     * นับครุภัณฑ์ตามระดับความเสี่ยง (risk_level) — H/M/L + ยังไม่ประเมิน
     *
     * @return array<int,array{id:string,label:string,count:int,tone:string,icon:string,desc:string}>
     */
    protected function overviewAssetRiskBreakdown(): array
    {
        $blueprint = [
            ['id' => 'H', 'label' => 'สูง',  'tone' => 'danger',    'icon' => 'octagon-alert',  'desc' => 'ต้องเฝ้าระวัง'],
            ['id' => 'M', 'label' => 'กลาง', 'tone' => 'warning',   'icon' => 'alert-triangle', 'desc' => 'ระดับปานกลาง'],
            ['id' => 'L', 'label' => 'ต่ำ',  'tone' => 'success',   'icon' => 'shield-check',   'desc' => 'อยู่ในเกณฑ์'],
            ['id' => '-', 'label' => 'ยังไม่ประเมิน', 'tone' => 'secondary', 'icon' => 'circle-help', 'desc' => 'รอประเมิน'],
        ];

        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('risk_level')) {
                return array_map(static fn($b) => $b + ['count' => 0], $blueprint);
            }

            $base = (new \yii\db\Query())->from(Asset::tableName());
            $this->applyAssetOverviewFilter($base, $schema);

            $countMap = ['H' => 0, 'M' => 0, 'L' => 0, '-' => 0];
            $rows = (clone $base)
                ->select(['lvl' => 'risk_level', 'cnt' => 'COUNT(*)'])
                ->groupBy(['risk_level'])
                ->all();
            foreach ($rows as $r) {
                $lvl = strtoupper(trim((string) ($r['lvl'] ?? '')));
                $cnt = (int) $r['cnt'];
                if ($lvl === 'H' || $lvl === 'M' || $lvl === 'L') {
                    $countMap[$lvl] += $cnt;
                } else {
                    $countMap['-'] += $cnt;
                }
            }

            $out = [];
            foreach ($blueprint as $b) {
                $out[] = $b + ['count' => (int) ($countMap[$b['id']] ?? 0)];
            }
            return $out;
        } catch (\Throwable $e) {
            return array_map(static fn($b) => $b + ['count' => 0], $blueprint);
        }
    }

    /** @return array<int,array{label:string,count:int}> หมวดครุภัณฑ์ top */
    protected function overviewAssetCategoryBreakdown(int $limit = 6): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('asset_category_id')) return [];
            $q = (new \yii\db\Query())
                ->select(['code' => 'asset_category_id', 'cnt' => 'COUNT(*)'])
                ->from(Asset::tableName())
                ->andWhere(['not', ['asset_category_id' => null]])
                ->andWhere(['<>', 'asset_category_id', '']);
            $this->applyAssetOverviewFilter($q, $schema);
            $rows = $q->groupBy(['asset_category_id'])
                ->orderBy(['cnt' => SORT_DESC])
                ->limit($limit)
                ->all();
            if (!$rows) return [];

            // asset.asset_category_id เก็บ `code` (string) ไม่ใช่ id
            // เชื่อมกับ categorise.code WHERE name='asset_category'
            $codes = array_values(array_filter(array_map(static fn($r) => (string) $r['code'], $rows), static fn($v) => $v !== ''));
            $titleMap = [];
            if ($codes) {
                $cats = AssetCategory::find()
                    ->select(['code', 'title', 'name'])
                    ->where(['name' => 'asset_category', 'code' => $codes])
                    ->asArray()->all();
                foreach ($cats as $c) {
                    $titleMap[(string) $c['code']] = trim((string) ($c['title'] ?? '')) !== ''
                        ? (string) $c['title']
                        : (string) ($c['name'] ?? '');
                }
            }
            $out = [];
            foreach ($rows as $r) {
                $code  = (string) $r['code'];
                $label = $titleMap[$code] ?? '';
                if ($label === '') $label = $code !== '' ? $code : 'ไม่ระบุ';
                $out[] = ['label' => $label, 'count' => (int) $r['cnt']];
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /**
     * รวมจำนวนเงินครุภัณฑ์ทั้งหมด (sum ของ price) — ตัดสถานะจำหน่ายแล้ว
     */
    protected function overviewAssetTotalAmount(): float
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn('price')) return 0.0;
            $q = (new \yii\db\Query())
                ->select(['s' => 'COALESCE(SUM(price), 0)'])
                ->from(Asset::tableName());
            $this->applyAssetOverviewFilter($q, $schema);
            $row = $q->one();
            return (float) ($row['s'] ?? 0);
        } catch (\Throwable $e) { return 0.0; }
    }

    /**
     * นับจำนวน + รวมเงินครุภัณฑ์ตาม lookup column ใด ๆ ที่เชื่อมกับ categorise(name=$masterName)
     * เช่น: budget_type / method_get / purchase
     *
     * @return array<int,array{code:string,label:string,count:int,amount:float}>
     */
    protected function overviewAssetMoneyBreakdown(string $assetColumn, string $masterName): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema || !$schema->getColumn($assetColumn)) return [];

            $hasPrice = (bool) $schema->getColumn('price');

            $q = (new \yii\db\Query())
                ->select([
                    'code'   => $assetColumn,
                    'cnt'    => 'COUNT(*)',
                    'amount' => $hasPrice ? 'COALESCE(SUM(price), 0)' : '0',
                ])
                ->from(Asset::tableName())
                ->andWhere(['not', [$assetColumn => null]])
                ->andWhere(['<>', $assetColumn, '']);
            $this->applyAssetOverviewFilter($q, $schema);
            $rows = $q->groupBy([$assetColumn])
                ->orderBy(['cnt' => SORT_DESC])
                ->all();
            if (!$rows) return [];

            $codes = array_values(array_filter(array_map(static fn($r) => (string) $r['code'], $rows), static fn($v) => $v !== ''));
            $titleMap = [];
            if ($codes) {
                $cats = (new \yii\db\Query())
                    ->select(['code', 'title'])
                    ->from(AssetCategory::tableName())
                    ->where(['name' => $masterName, 'code' => $codes])
                    ->all();
                foreach ($cats as $c) {
                    $title = trim((string) ($c['title'] ?? ''));
                    if ($title !== '') $titleMap[(string) $c['code']] = $title;
                }
            }

            $out = [];
            foreach ($rows as $r) {
                $code  = (string) $r['code'];
                $label = $titleMap[$code] ?? ($code !== '' ? $code : 'ไม่ระบุ');
                $out[] = [
                    'code'   => $code,
                    'label'  => $label,
                    'count'  => (int) $r['cnt'],
                    'amount' => (float) ($r['amount'] ?? 0),
                ];
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }

    /** @return Asset[] ครุภัณฑ์ล่าสุด (ตัดสถานะจำหน่าย) */
    protected function overviewAssetRecent(int $limit = 5): array
    {
        try {
            $schema = Yii::$app->db->getTableSchema(Asset::tableName(), true);
            if (!$schema) return [];
            $q = Asset::find();
            $this->applyAssetOverviewFilter($q, $schema);
            $order = $schema->getColumn('created_at') ? ['created_at' => SORT_DESC] : ['id' => SORT_DESC];
            return $q->orderBy($order)->limit($limit)->all();
        } catch (\Throwable $e) { return []; }
    }
}
