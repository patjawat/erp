<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveSearch;
use app\modules\leave\models\LeaveType;
<<<<<<< HEAD
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
=======
use app\modules\leave\components\LeaveApprovalService;
use app\modules\leave\components\LeaveTelegramService;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveLevelSetting;
>>>>>>> leave
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * ผู้ตรวจสอบวันลา — แสดงทะเบียนวันลาทั้งหมด
 */
class ApproverController extends Controller
{
    /**
     * ทะเบียนวันลาทั้งหมด
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าผู้ตรวจสอบวันลา');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/default/index']);
        }

        $status = $this->request->get('status');
        $searchModel = new LeaveSearch([
            'status' => 'Checking2_pass'
        ]);
        
        $params = $this->request->queryParams;

        $dataProvider = $searchModel->search($params);
        $query = $dataProvider->query;
        $query->joinWith([
            'employee',
            'leaveType',
            'leaveStatus',
        ]);

        if ($dataProvider->pagination !== false) {
            $dataProvider->pagination->pageSize = 20;
        }

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }

        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }

        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }

        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        // สรุปตามตัวกรอง: ตามสถานะ และตามประเภทการลา (ใช้ query ชุดเดียวกัน)
        $summaryByStatus = (clone $query)->select(['leave.status', 'COUNT(*) as cnt'])->groupBy('leave.status')->asArray()->all();
        $summaryByLeaveType = (clone $query)->select(['leave.leave_type_id', 'COUNT(*) as cnt'])->groupBy('leave.leave_type_id')->asArray()->all();

        $query->with([
            'approves' => function ($q) {
                $q->andWhere(['!=', 'approve.status', 'None'])->orderBy(['level' => SORT_ASC]);
            },
        ]);
        $dataProvider->setSort(['defaultOrder' => [
            'date_start' => SORT_DESC,
        ]]);

        $leaveTypes = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['title' => SORT_ASC])
            ->all();
        $listLeaveType = ArrayHelper::map($leaveTypes, 'code', 'title');
        $allowedColors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
        $leaveTypeColors = [];
        foreach ($leaveTypes as $lt) {
            $data = is_array($lt->data_json) ? $lt->data_json : (is_string($lt->data_json) ? json_decode($lt->data_json, true) : []);
            $c = isset($data['color']) ? $data['color'] : 'info';
            $leaveTypeColors[$lt->code] = in_array($c, $allowedColors, true) ? $c : 'info';
        }
        $listLeaveStatus = (new Leave())->listStatus();

        $levelSettings = ApproveLevelSetting::getLevelsBySystem('leave');

        return $this->render('index', [
            'searchModel'        => $searchModel,
            'dataProvider'       => $dataProvider,
            'listLeaveType'      => $listLeaveType,
            'listLeaveStatus'    => $listLeaveStatus,
            'summaryByStatus'    => $summaryByStatus,
            'summaryByLeaveType' => $summaryByLeaveType,
            'leaveTypeColors'    => $leaveTypeColors,
            'levelSettings'      => $levelSettings,
        ]);
    }

    /**
     * เปลี่ยนผู้อนุมัติ (admin only) — แสดง modal form + บันทึก
     */
    public function actionChangeApprover($id)
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        $me = UserHelper::GetEmployee();
        $leave = Leave::find()
            ->andWhere(['id' => (int) $id])
            ->with(['employee', 'leaveType'])
            ->one();
        if ($leave === null) {
            throw new NotFoundHttpException('ไม่พบรายการวันลา');
        }

        $approves = Approve::find()
            ->where(['name' => 'leave', 'from_id' => (string) $leave->id,'level' => 4])
            ->orderBy(['level' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post('approves', []);
            $errors = [];
            $approvalResult = [];
            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($data as $approveId => $fields) {
                    $approveId = (int) $approveId;
                    if ($approveId <= 0) {
                        continue;
                    }

                    $record = Approve::findOne(['id' => $approveId, 'name' => 'leave', 'from_id' => (string) $leave->id]);
                    if (!$record) {
                        $errors[] = "ไม่พบรายการอนุมัติ #{$approveId}";
                        continue;
                    }

                    $newEmpId = isset($fields['emp_id']) ? (int) $fields['emp_id'] : 0;
                    if ($newEmpId > 0) {
                        $record->emp_id = $newEmpId;
                    }

                    if (!$record->save(false)) {
                        $errors[] = "บันทึกรายการ #{$approveId} ไม่สำเร็จ";
                    }
                }

                if (!empty($errors)) {
                    throw new \RuntimeException(implode('<br>', $errors));
                }

                $approvalResult = ['ok' => true, 'leave' => $leave];
                if ($leave->status === 'Checking2_pass') {
                    $level3Approve = Approve::findOne([
                        'name'    => 'leave',
                        'from_id' => (string) $leave->id,
                        'level'   => 3,
                        'status'  => 'Pending',
                    ]);
                    if ($level3Approve === null) {
                        throw new \RuntimeException('ไม่พบรายการอนุมัติระดับ 3 ที่รอดำเนินการ');
                    }

                    $approvalResult = (new LeaveApprovalService())->process(
                        $level3Approve,
                        'Pass',
                        $me ? (int) $me->id : null,
                        true
                    );
                    if (!($approvalResult['ok'] ?? false)) {
                        throw new \RuntimeException($approvalResult['message'] ?? 'บันทึกไม่สำเร็จ');
                    }
                } elseif ($leave->status !== 'Checkup_pass') {
                    throw new \RuntimeException('สถานะใบลาไม่รองรับการเปลี่ยนผู้อนุมัติ');
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                return ['status' => 'error', 'message' => $e->getMessage()];
            }

            $leaveStatus = $approvalResult['leave']->status ?? $leave->status;
            $leaveStatusLabels = [
                'Pending'        => 'ใบลาอยู่ระหว่างรอการอนุมัติ',
                'Checking1_pass' => 'ใบลาอยู่ระหว่างการอนุมัติชั้นที่ 1',
                'Checking2_pass' => 'ใบลาอยู่ระหว่างการอนุมัติชั้นที่ 2',
                'Checkup_pass'   => 'ใบลาผ่านชั้นที่ 3 แล้ว',
                'Approve'        => 'ใบลาได้รับการอนุมัติแล้ว',
                'Reject'         => 'ใบลาถูกปฏิเสธ',
                'Cancel'         => 'ใบลาถูกยกเลิก',
            ];

            return [
                'status'             => 'success',
                'leave_status'       => $leaveStatus,
                'leave_status_label' => 'สถานะใบลา: ' . ($leaveStatusLabels[$leaveStatus] ?? $leaveStatus),
            ];
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = Yii::$app->request->get('title', '<i class="bi bi-person-gear me-1"></i> เปลี่ยนผู้อนุมัติ');
            return [
                'title'   => $title,
                'content' => $this->renderAjax('_change_approver', [
                    'leave'    => $leave,
                    'approves' => $approves,
                ]),
                'footer'  => '',
            ];
        }

        return $this->render('_change_approver', [
            'leave'    => $leave,
            'approves' => $approves,
        ]);
    }

    /**
     * เปิด modal สำหรับเปลี่ยนผู้อนุมัติแบบหลายรายการจากรายการที่เลือก
     */
    public function actionBulkChangeApprover()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        $isAjax = Yii::$app->request->isAjax;
        if ($isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        $title = Yii::$app->request->get('title', '<i class="bi bi-person-gear me-1"></i> เปลี่ยนผู้อนุมัติ (หลายรายการ)');
        $leaveIds = array_values(array_filter(array_map('intval', (array) Yii::$app->request->get('leave_ids', []))));

        if (empty($leaveIds)) {
            $content = '<div class="alert alert-warning border-0 rounded-3 mb-0">กรุณาเลือกรายการอย่างน้อย 1 รายการ</div>';

            if ($isAjax) {
                return [
                    'title'   => $title,
                    'content' => $content,
                    'footer'  => '',
                ];
            }

            Yii::$app->session->setFlash('warning', 'กรุณาเลือกรายการอย่างน้อย 1 รายการ');
            return $this->redirect(['index']);
        }

        $leaveMap = Leave::find()
            ->where(['in', 'id', $leaveIds])
            ->with(['employee', 'leaveType'])
            ->indexBy('id')
            ->all();

        $leaves = [];
        $missingIds = [];
        foreach ($leaveIds as $leaveId) {
            if (isset($leaveMap[$leaveId])) {
                $leaves[] = $leaveMap[$leaveId];
            } else {
                $missingIds[] = $leaveId;
            }
        }

        if (!empty($missingIds)) {
            $missingText = implode(', #', $missingIds);
            $content = '<div class="alert alert-danger border-0 rounded-3 mb-0">ไม่พบรายการวันลา #' . $missingText . '</div>';

            if ($isAjax) {
                return [
                    'title'   => $title,
                    'content' => $content,
                    'footer'  => '',
                ];
            }

            Yii::$app->session->setFlash('error', 'ไม่พบรายการวันลา #' . $missingText);
            return $this->redirect(['index']);
        }

        if (count($leaves) > 0) {
            $title .= ' <span class="badge bg-primary ms-1">' . count($leaves) . '</span>';
        }

        if ($isAjax) {
            return [
                'title'   => $title,
                'content' => $this->renderAjax('_bulk_change_approver', [
                    'leaves' => $leaves,
                ]),
                'footer'  => '',
            ];
        }

        return $this->render('_bulk_change_approver', [
            'leaves' => $leaves,
        ]);
    }

    /**
     * เปลี่ยนผู้อนุมัติจากรายการที่เลือกใน checkbox
     */
    public function actionChangeApproverOnChange()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $leaveIds = array_values(array_filter(array_map('intval', (array) Yii::$app->request->post('leave_ids', []))));
        $newEmpId = (int) Yii::$app->request->post('emp_id', 0);

        if (empty($leaveIds)) {
            return ['status' => 'error', 'message' => 'ไม่ได้เลือกรายการ'];
        }

        if ($newEmpId <= 0) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกผู้อนุมัติใหม่'];
        }

        $leaves = Leave::find()
            ->where(['in', 'id', $leaveIds])
            ->with(['employee', 'leaveType'])
            ->all();

        if (count($leaves) !== count(array_unique($leaveIds))) {
            $foundIds = array_map(static function (Leave $leave) {
                return (int) $leave->id;
            }, $leaves);
            $missingIds = array_values(array_diff($leaveIds, $foundIds));
            return [
                'status'  => 'error',
                'message' => 'ไม่พบรายการวันลา #' . implode(', #', $missingIds),
            ];
        }

        $me = UserHelper::GetEmployee();
        $actorEmpId = $me ? (int) $me->id : null;
        $transaction = Yii::$app->db->beginTransaction();
        $updated = 0;

        try {
            foreach ($leaves as $leave) {
                $this->applyChangeApproverFlowForLeave($leave, $newEmpId, $actorEmpId);
                $updated++;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return [
            'status'  => 'success',
            'message' => 'เปลี่ยนผู้อนุมัติสำเร็จ ' . $updated . ' รายการ',
        ];
    }

    /**
     * ดำเนินการแบบ bulk กับใบลาที่เลือก
     * action: approve | reject | change-approver
     */
    public function actionBulkAction()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $action   = Yii::$app->request->post('action', '');
        $leaveIds = array_filter(array_map('intval', (array) Yii::$app->request->post('leave_ids', [])));

        if (empty($leaveIds)) {
            return ['status' => 'error', 'message' => 'ไม่ได้เลือกรายการ'];
        }

        $leaves = Leave::find()->where(['in', 'id', $leaveIds])->all();
        if (empty($leaves)) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการที่เลือก'];
        }

        $me = UserHelper::GetEmployee();
        $actorEmpId = $me ? (int) $me->id : null;

        // ── อนุมัติทั้งหมด ────────────────────────────────────────
        if ($action === 'approve') {
            return $this->bulkProcessLeaveApprovals($leaves, 'Pass', $actorEmpId, 'อนุมัติ');
        }

        // ── ไม่อนุมัติทั้งหมด ────────────────────────────────────
        if ($action === 'reject') {
            return $this->bulkProcessLeaveApprovals($leaves, 'Reject', $actorEmpId, 'ไม่อนุมัติ');
        }

        // ── เปลี่ยนผู้อนุมัติ ────────────────────────────────────
        if ($action === 'change-approver') {
            $level    = (int) Yii::$app->request->post('level', 0);
            $newEmpId = (int) Yii::$app->request->post('emp_id', 0);
            $newStatus = Yii::$app->request->post('status', '');
            $allowedStatuses = ['Pending', 'Pass', 'Reject', 'None'];

            if ($level <= 0) {
                return ['status' => 'error', 'message' => 'กรุณาระบุลำดับผู้อนุมัติ'];
            }
            if ($newEmpId <= 0 && ($newStatus === '' || !in_array($newStatus, $allowedStatuses, true))) {
                return ['status' => 'error', 'message' => 'กรุณาระบุผู้อนุมัติใหม่หรือสถานะอย่างใดอย่างหนึ่ง'];
            }

            $updated = 0;
            $skipped = 0;
            foreach ($leaves as $leave) {
                $record = Approve::find()
                    ->where(['name' => 'leave', 'from_id' => (string) $leave->id, 'level' => $level])
                    ->one();
                if (!$record) { $skipped++; continue; }

                if ($newEmpId > 0) $record->emp_id = $newEmpId;
                if ($newStatus !== '' && in_array($newStatus, $allowedStatuses, true)) {
                    $record->status = $newStatus;
                }

                if ($record->save(false)) {
                    $updated++;
                    $this->syncLeaveStatus($leave);
                    if ($record->status === 'Pending') {
                        try {
                            (new LeaveTelegramService())->notifyPendingApprove($record);
                        } catch (\Throwable $e) {
                            Yii::warning('Telegram notify failed: ' . $e->getMessage(), __METHOD__);
                        }
                    }
                }
            }

            $msg = "เปลี่ยนผู้อนุมัติสำเร็จ {$updated} รายการ";
            if ($skipped > 0) $msg .= " (ข้าม {$skipped} รายการที่ไม่มี level {$level})";
            return ['status' => 'success', 'message' => $msg];
        }

        return ['status' => 'error', 'message' => 'ไม่รู้จัก action นี้'];
    }

    /**
     * ใช้ flow เดียวกับ change-approver รายใบ เพื่ออัปเดต level 4 และผ่าน level 3
     */
    protected function applyChangeApproverFlowForLeave(Leave $leave, int $newEmpId, ?int $actorEmpId): array
    {
        $level4Records = Approve::find()
            ->where(['name' => 'leave', 'from_id' => (string) $leave->id, 'level' => 4])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (empty($level4Records)) {
            throw new \RuntimeException('ไม่พบรายการอนุมัติระดับ 4 ของใบลา #' . $leave->id);
        }

        foreach ($level4Records as $record) {
            $record->emp_id = $newEmpId;
            if (!$record->save(false)) {
                throw new \RuntimeException("บันทึกรายการอนุมัติ #{$record->id} ไม่สำเร็จ");
            }

            if ($record->status === 'Pending') {
                try {
                    (new LeaveTelegramService())->notifyPendingApprove($record);
                } catch (\Throwable $e) {
                    Yii::warning('Telegram notify failed: ' . $e->getMessage(), __METHOD__);
                }
            }
        }

        if ($leave->status === 'Checking2_pass') {
            $level3Approve = Approve::findOne([
                'name'    => 'leave',
                'from_id' => (string) $leave->id,
                'level'   => 3,
                'status'  => 'Pending',
            ]);
            if ($level3Approve === null) {
                throw new \RuntimeException('ไม่พบรายการอนุมัติระดับ 3 ที่รอดำเนินการของใบลา #' . $leave->id);
            }

            $approvalResult = (new LeaveApprovalService())->process(
                $level3Approve,
                'Pass',
                $actorEmpId,
                true
            );
            if (!($approvalResult['ok'] ?? false)) {
                throw new \RuntimeException($approvalResult['message'] ?? 'บันทึกไม่สำเร็จ');
            }
        } elseif ($leave->status === 'Checkup_pass') {
            $approvalResult = ['ok' => true, 'leave' => $leave];
        } else {
            throw new \RuntimeException('สถานะใบลาไม่รองรับการเปลี่ยนผู้อนุมัติ');
        }

        return $approvalResult;
    }

    /**
     * ดำเนินการอนุมัติ/ไม่อนุมัติแบบ bulk ตาม approve ที่ยัง Pending อยู่จริง
     */
    protected function bulkProcessLeaveApprovals(array $leaves, string $status, ?int $actorEmpId, string $label): array
    {
        $updated = 0;
        $invalid = [];

        foreach ($leaves as $leave) {
            $pending = Approve::find()
                ->where([
                    'name'    => 'leave',
                    'from_id' => (string) $leave->id,
                    'status'  => 'Pending',
                ])
                ->orderBy(['level' => SORT_ASC])
                ->one();

            if ($pending === null) {
                $invalid[] = $leave;
                continue;
            }

            try {
                $result = (new LeaveApprovalService())->process($pending, $status, $actorEmpId);
                if (!($result['ok'] ?? false)) {
                    $invalid[] = $leave;
                    continue;
                }
                $updated++;
            } catch (\Throwable $e) {
                Yii::warning('Bulk leave approval failed: ' . $e->getMessage(), __METHOD__);
                $invalid[] = $leave;
            }
        }

        if (empty($invalid)) {
            return ['status' => 'success', 'message' => "{$label}สำเร็จ {$updated} รายการ"];
        }

        $names = implode(', ', array_map(static function (Leave $leave) {
            return $leave->employee ? $leave->employee->fullname : "#{$leave->id}";
        }, $invalid));

        if ($updated === 0) {
            return [
                'status'  => 'warning',
                'message' => "ไม่มีรายการที่อยู่ในขั้นตอนอนุมัติ: {$names}",
            ];
        }

        return [
            'status'  => 'partial',
            'message' => "{$label}สำเร็จ {$updated} รายการ — รายการที่ยังไม่อยู่ในขั้นตอนอนุมัติ: {$names}",
        ];
    }

    /**
     * แยก leave[] เป็น [valid[], invalid[]]
     * เงื่อนไข: leave.status === 'Checking2_pass' AND approve level 3 status === 'Pass'
     *
     * @param Leave[] $leaves
     * @return array{0: Leave[], 1: Leave[]}
     */
    protected function partitionByApproverCondition(array $leaves): array
    {
        $valid   = [];
        $invalid = [];
        foreach ($leaves as $leave) {
            $passCondition = $leave->status === 'Checking2_pass';
            if ($passCondition) {
                $ap3 = Approve::findOne([
                    'name'    => 'leave',
                    'from_id' => (string) $leave->id,
                    'level'   => 3,
                ]);
                $passCondition = $ap3 && $ap3->status === 'Pass';
            }
            if ($passCondition) {
                $valid[] = $leave;
            } else {
                $invalid[] = $leave;
            }
        }
        return [$valid, $invalid];
    }

    /**
     * คำนวณและอัพเดต leave.status ตามสถานะของผู้อนุมัติทั้งหมด
     * Reject ใด ๆ → Reject | ทั้งหมด Pass → Approve | มี Pending → Pending
     */
    protected function syncLeaveStatus(Leave $leave): string
    {
        $activeApproves = Approve::find()
            ->where(['name' => 'leave', 'from_id' => (string) $leave->id])
            ->andWhere(['!=', 'status', 'None'])
            ->all();

        if (empty($activeApproves)) {
            return $leave->status;
        }

        $hasReject  = false;
        $hasPending = false;
        foreach ($activeApproves as $ap) {
            if ($ap->status === 'Reject')  { $hasReject  = true; break; }
            if ($ap->status === 'Pending') { $hasPending = true; }
        }

        if ($hasReject) {
            $newStatus = 'Reject';
        } elseif ($hasPending) {
            $newStatus = 'Pending';
        } else {
            $newStatus = 'Approve';
        }

        if ($leave->status !== $newStatus) {
            $leave->status = $newStatus;
            $leave->save(false);
        }

        return $newStatus;
    }

    /**
     * คืนค่า emp_id ที่อยู่ในหน่วยงานที่กำหนด (ถ้ามี inject จาก config)
     */
    protected function getEmpIdsByDepartment($q_department)
    {
        if (empty($q_department)) {
            return null;
        }

        if (isset(Yii::$app->params['leave.empIdsByDepartment']) && is_callable(Yii::$app->params['leave.empIdsByDepartment'])) {
            $empIds = call_user_func(Yii::$app->params['leave.empIdsByDepartment'], $q_department);
            if ($empIds !== null) {
                return is_array($empIds) ? array_values(array_filter($empIds)) : [$empIds];
            }
        }

        $org = Organization::findOne($q_department);
        if (!$org) {
            return [];
        }

        $departmentIds = [$org->id];
        if ((int) $org->lvl === 1) {
            $query = Organization::find()
                ->select('id')
                ->where(['root' => $org->root])
                ->andWhere(['>', 'lft', $org->lft])
                ->andWhere(['<', 'rgt', $org->rgt])
                ->andWhere(['lvl' => $org->lvl + 1]);

            $childDepartmentIds = $query->column();
            if (!empty($childDepartmentIds)) {
                $departmentIds = $childDepartmentIds;
            }
        }

        return Employees::find()
            ->select('id')
            ->where(['department' => $departmentIds])
            ->column();
    }

    /**
     * ส่งออก Excel ทะเบียนวันลา (ใช้ตัวกรองเดียวกับ actionIndex)
     */
    public function actionExport()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ส่งออกข้อมูล');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/approver/index']);
        }

        $status = $this->request->get('status');
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $query->joinWith(['employee', 'leaveType', 'leaveStatus']);
        $dataProvider->pagination = false;

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }
        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }
        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }
        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        $query->orderBy(['leave.date_start' => SORT_DESC]);

        return $this->exportToExcelLeave($dataProvider);
    }

    /**
     * พิมพ์ทะเบียนวันลาเป็น PDF เปิดในแท็บใหม่ (ใช้ตัวกรองเดียวกับ actionIndex)
     */
    public function actionPrint()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์พิมพ์ข้อมูล');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/approver/index']);
        }

        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $query->joinWith(['employee', 'leaveType', 'leaveStatus']);
        $dataProvider->pagination = false;

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }
        $status = $this->request->get('status');
        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }
        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }
        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        $query->orderBy(['leave.date_start' => SORT_DESC]);
        $models = $dataProvider->getModels();

        $html = $this->renderPartial('print-pdf', [
            'models' => $models,
            'searchModel' => $searchModel,
        ]);

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ];
        $fontPathTh = Yii::getAlias('@webroot') . '/fonts';
        $ttfR = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew.ttf';
        if (is_dir($fontPathTh) && file_exists($ttfR)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $defaultFont = (new \Mpdf\Config\FontVariables())->getDefaults();
            $config['fontDir'] = array_merge($defaultConfig['fontDir'], [$fontPathTh]);
            $ttfB = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew Bold.ttf';
            $ttfBAlt = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew-Bold.ttf';
            $fontdata = [
                'R' => 'THSarabunNew.ttf',
                'B' => file_exists($ttfB) ? 'THSarabunNew Bold.ttf' : (file_exists($ttfBAlt) ? 'THSarabunNew-Bold.ttf' : 'THSarabunNew.ttf'),
            ];
            $config['fontdata'] = array_merge($defaultFont['fontdata'], [
                'thsarabun' => $fontdata,
            ]);
            $config['default_font'] = 'thsarabun';
        }

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('ทะเบียนวันลา');
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $filename = 'ทะเบียนวันลา_' . date('Y-m-d_His') . '.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
        return Yii::$app->response;
    }

    /**
     * ส่งออก DataProvider (Leave) เป็นไฟล์ Excel
     */
    protected function exportToExcelLeave($dataProvider)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ทะเบียนวันลา');

        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'ผู้ขออนุมัติ',
            'C1' => 'ประเภทการลา',
            'D1' => 'จำนวนวัน',
            'E1' => 'เหตุผลการลา',
            'F1' => 'วันที่เริ่ม',
            'G1' => 'วันที่สิ้นสุด',
            'H1' => 'หน่วยงาน',
            'I1' => 'สถานะใบลา',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $colWidths = ['A' => 8, 'B' => 28, 'C' => 18, 'D' => 10, 'E' => 36, 'F' => 14, 'G' => 14, 'H' => 24, 'I' => 16];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $row = 2;
        foreach ($dataProvider->getModels() as $index => $item) {
            $emp = $item->employee ?? null;
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $emp ? $emp->fullname : '-');
            $sheet->setCellValue('C' . $row, $item->leaveType->title ?? '-');
            $sheet->setCellValue('D' . $row, (float) $item->total_days);
            $dataJson = is_array($item->data_json) ? $item->data_json : (is_string($item->data_json) ? json_decode($item->data_json, true) : []);
            $sheet->setCellValue('E' . $row, isset($dataJson['reason']) ? $dataJson['reason'] : '-');
            $sheet->setCellValue('F' . $row, $item->date_start ? ThaiDateHelper::formatThaiDate($item->date_start) : '-');
            $sheet->setCellValue('G' . $row, $item->date_end ? ThaiDateHelper::formatThaiDate($item->date_end) : '-');
            $sheet->setCellValue('H' . $row, $emp ? $emp->departmentName() : '-');
            $sheet->setCellValue('I' . $row, $item->leaveStatus ? $item->leaveStatus->title : $item->status);
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A1:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ทะเบียนวันลา_' . date('Y-m-d_His') . '.xlsx';
        $filePath = Yii::getAlias('@runtime') . '/' . $filename;
        $writer->save($filePath);

        return Yii::$app->response->sendFile($filePath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ]);
    }
}
