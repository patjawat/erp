<?php

namespace app\modules\purchaseV2\services;

use Yii;
use app\components\SiteHelper;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Order;
use app\modules\approve\models\Approve;
use yii\helpers\ArrayHelper;
use app\modules\approveV2\models\ApproveLevelSetting;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestApproval;
use app\modules\purchaseV2\models\PurchaseRequestLog;

class PurchaseWorkflowService
{
    public static function previewApprovers(PurchaseRequest $request, ?Employees $actor = null): array
    {
        $requester = $request->requesterEmployee();
        $settings = ApproveLevelSetting::getLevelsBySystem('purchase');

        if (empty($settings)) {
            return static::fallbackApprovers($requester, $actor);
        }

        $rows = [];
        foreach ($settings as $setting) {
            $resolved = static::resolveApprover((string) $setting->approver_type, $setting->approver_value, $requester);
            $rows[] = [
                'step_no' => (int) $setting->level,
                'step_type' => PurchaseRequestApproval::STEP_WORKFLOW,
                'role_name' => trim((string) ($setting->title ?: $setting->label)),
                'approver_emp_id' => $resolved['emp_id'],
                'approver_name' => $resolved['fullname'],
                'approver_position' => $resolved['position'],
                'status' => ((int) $setting->level === 1) ? PurchaseRequestApproval::STATUS_PENDING : PurchaseRequestApproval::STATUS_NONE,
                'data_json' => [
                    'source' => 'workflow-setting',
                    'label' => trim((string) ($setting->title ?: $setting->label)),
                    'title' => trim((string) ($setting->title ?: $setting->label)),
                ],
            ];
        }

        return $rows;
    }

    public static function createWorkflow(PurchaseRequest $request, ?Employees $actor = null): array
    {
        $request->refresh();
        PurchaseRequestApproval::deleteAll(['request_id' => $request->id, 'step_type' => PurchaseRequestApproval::STEP_WORKFLOW]);

        $rows = static::previewApprovers($request, $actor);
        if (empty($rows)) {
            $rows = static::fallbackApprovers($request->requesterEmployee(), $actor);
        }

        $saved = [];
        foreach ($rows as $row) {
            $approval = new PurchaseRequestApproval();
            $approval->request_id = $request->id;
            $approval->step_no = (int) ($row['step_no'] ?? 1);
            $approval->step_type = $row['step_type'] ?? PurchaseRequestApproval::STEP_WORKFLOW;
            $approval->role_name = $row['role_name'] ?? '';
            $approval->approver_emp_id = $row['approver_emp_id'] ?? null;
            $approval->approver_name = $row['approver_name'] ?? '';
            $approval->approver_position = $row['approver_position'] ?? '';
            $approval->status = $row['status'] ?? PurchaseRequestApproval::STATUS_NONE;
            $approval->data_json = $row['data_json'] ?? [
                'source' => 'workflow-setting',
                'label' => $approval->role_name,
                'title' => $approval->role_name,
            ];
            $approval->save(false);
            $saved[] = $approval;
        }

        static::writeLog($request, 'workflow_created', 'สร้าง workflow การอนุมัติ', null, PurchaseRequest::STATUS_DRAFT, [
            'steps' => count($saved),
        ], $actor);

        return $saved;
    }

    public static function importLegacyWorkflow(PurchaseRequest $request, Order $legacyOrder, ?Employees $actor = null): void
    {
        PurchaseRequestApproval::deleteAll(['request_id' => $request->id]);

        $step = 1;
        foreach (Approve::find()->where(['from_id' => $legacyOrder->id, 'name' => 'purchase'])->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])->all() as $legacyApprove) {
            $legacyDataJson = static::normalizeLegacyDataJson($legacyApprove->data_json);
            $approval = new PurchaseRequestApproval();
            $approval->request_id = $request->id;
            $approval->step_no = (int) $legacyApprove->level;
            $approval->step_type = PurchaseRequestApproval::STEP_WORKFLOW;
            $approval->role_name = (string) $legacyApprove->title;
            $approval->approver_emp_id = $legacyApprove->emp_id ?: null;
            $approval->approver_name = static::resolveEmployeeName($legacyApprove->emp_id);
            $approval->approver_position = static::resolveEmployeePosition($legacyApprove->emp_id);
            $approval->status = static::mapLegacyApprovalStatus($legacyApprove->status);
            $approval->comment = $legacyApprove->comment ?? null;
            $approval->action_at = !empty($legacyDataJson['approve_date']) ? $legacyDataJson['approve_date'] : null;
            $approval->legacy_approve_id = $legacyApprove->id;
            $approval->data_json = $legacyDataJson;
            $approval->save(false);
            $step++;
        }

        foreach ([
            'committee' => $legacyOrder->ListCommittee(),
            'committee_detail' => $legacyOrder->ListCommitteeDetail(),
        ] as $stepType => $legacyRows) {
            foreach ($legacyRows as $legacyRow) {
                $legacyDataJson = static::normalizeLegacyDataJson($legacyRow->data_json);
                $approval = new PurchaseRequestApproval();
                $approval->request_id = $request->id;
                $approval->step_no = (int) ($legacyDataJson['committee'] ?? $step);
                $approval->step_type = $stepType === 'committee' ? PurchaseRequestApproval::STEP_COMMITTEE : PurchaseRequestApproval::STEP_COMMITTEE_DETAIL;
                $approval->role_name = (string) ($legacyDataJson['committee_name'] ?? $legacyRow->name);
                $approval->approver_emp_id = $legacyDataJson['employee_id'] ?? null;
                $approval->approver_name = static::resolveEmployeeName($approval->approver_emp_id);
                $approval->approver_position = static::resolveEmployeePosition($approval->approver_emp_id);
                $approval->status = PurchaseRequestApproval::STATUS_INFO;
                $approval->data_json = $legacyDataJson;
                $approval->save(false);
                $step++;
            }
        }

        static::writeLog($request, 'migrated_legacy_workflow', 'ย้ายข้อมูล workflow เดิม', $legacyOrder->status, $request->status, [
            'legacy_approve_count' => (int) Approve::find()->where(['from_id' => $legacyOrder->id, 'name' => 'purchase'])->count(),
        ], $actor);
    }

    public static function submit(PurchaseRequest $request, ?Employees $actor = null): void
    {
        $request->status = PurchaseRequest::STATUS_PENDING_APPROVAL;
        $request->submitted_at = date('Y-m-d H:i:s');
        if (empty($request->pr_number)) {
            $request->pr_number = $request->request_no;
        }
        $request->current_approval_level = 1;
        $request->save(false);

        static::createWorkflow($request, $actor);
        static::writeLog($request, 'submit', 'ส่งคำขอเข้าสู่การอนุมัติ', PurchaseRequest::STATUS_DRAFT, PurchaseRequest::STATUS_PENDING_APPROVAL, [], $actor);
    }

    public static function approve(PurchaseRequestApproval $approval, string $decision, string $comment = '', ?Employees $actor = null): PurchaseRequest
    {
        $request = $approval->request;
        $oldStatus = (int) $request->status;

        $approval->status = $decision === 'reject' ? PurchaseRequestApproval::STATUS_REJECTED : PurchaseRequestApproval::STATUS_APPROVED;
        $approval->comment = $comment;
        $approval->action_at = date('Y-m-d H:i:s');
        $approval->data_json = ArrayHelper::merge((array) $approval->data_json, [
            'approve_date' => $approval->action_at,
            'decision' => $decision,
        ]);
        $approval->save(false);

        if ($decision === 'reject') {
            $request->status = PurchaseRequest::STATUS_CANCELLED;
            $request->cancelled_at = date('Y-m-d H:i:s');
            $request->current_approval_level = (int) $approval->step_no;
            $request->save(false);
            static::writeLog($request, 'reject', 'ไม่อนุมัติคำขอ', $oldStatus, PurchaseRequest::STATUS_CANCELLED, [
                'approval_id' => $approval->id,
                'comment' => $comment,
            ], $actor);
            return $request;
        }

        $nextApproval = PurchaseRequestApproval::find()
            ->where(['request_id' => $request->id, 'step_type' => PurchaseRequestApproval::STEP_WORKFLOW, 'status' => PurchaseRequestApproval::noneStatusValues()])
            ->andWhere(['>', 'step_no', $approval->step_no])
            ->orderBy(['step_no' => SORT_ASC, 'id' => SORT_ASC])
            ->one();

        if ($nextApproval) {
            $nextApproval->status = PurchaseRequestApproval::STATUS_PENDING;
            $nextApproval->save(false);
            $request->current_approval_level = (int) $nextApproval->step_no;
        } else {
            $request->status = PurchaseRequest::STATUS_APPROVED;
            $request->approved_at = date('Y-m-d H:i:s');
            $request->current_approval_level = (int) $approval->step_no;
            if (empty($request->pq_number)) {
                $request->pq_number = 'PQ-' . $request->request_no;
            }
        }

        $request->save(false);
        static::writeLog($request, 'approve', 'อนุมัติขั้นตอน', $oldStatus, $request->status, [
            'approval_id' => $approval->id,
            'comment' => $comment,
        ], $actor);

        return $request;
    }

    public static function markStatus(PurchaseRequest $request, int $status, string $action, string $message, ?Employees $actor = null): void
    {
        $oldStatus = (int) $request->status;
        $request->status = $status;

        $now = date('Y-m-d H:i:s');
        switch ($status) {
            case PurchaseRequest::STATUS_ORDERED:
                $request->ordered_at = $now;
                if (empty($request->po_number)) {
                    $request->po_number = 'PO-' . $request->request_no;
                }
                break;
            case PurchaseRequest::STATUS_RECEIVED:
                $request->received_at = $now;
                if (empty($request->gr_number)) {
                    $request->gr_number = 'GR-' . $request->request_no;
                }
                break;
            case PurchaseRequest::STATUS_STOCKED:
                $request->stocked_at = $now;
                break;
            case PurchaseRequest::STATUS_COMPLETED:
                $request->completed_at = $now;
                break;
            case PurchaseRequest::STATUS_CANCELLED:
                $request->cancelled_at = $now;
                break;
        }

        $request->save(false);
        static::writeLog($request, $action, $message, $oldStatus, $status, [], $actor);
    }

    protected static function fallbackApprovers(?Employees $requester, ?Employees $actor = null): array
    {
        $leaderInfo = $requester ? $requester->leaderUser() : [];
        $director = SiteHelper::viewDirector();
        $procurementOfficer = $actor ?: $requester;

        return [
            [
                'step_no' => 1,
                'step_type' => PurchaseRequestApproval::STEP_WORKFLOW,
                'role_name' => 'หัวหน้าผู้ขอ',
                'approver_emp_id' => $leaderInfo['leader1'] ?? null,
                'approver_name' => $leaderInfo['leader1_fullname'] ?? '',
                'approver_position' => $leaderInfo['leader1_position'] ?? '',
                'status' => PurchaseRequestApproval::STATUS_PENDING,
                'data_json' => [
                    'source' => 'fallback',
                    'label' => 'หัวหน้าผู้ขอ',
                    'title' => 'หัวหน้าผู้ขอ',
                ],
            ],
            [
                'step_no' => 2,
                'step_type' => PurchaseRequestApproval::STEP_WORKFLOW,
                'role_name' => 'เจ้าหน้าที่พัสดุ',
                'approver_emp_id' => $procurementOfficer?->id,
                'approver_name' => $procurementOfficer?->fullname ?? '',
                'approver_position' => $procurementOfficer?->positionName() ?? '',
                'status' => PurchaseRequestApproval::STATUS_NONE,
                'data_json' => [
                    'source' => 'fallback',
                    'label' => 'เจ้าหน้าที่พัสดุ',
                    'title' => 'เจ้าหน้าที่พัสดุ',
                ],
            ],
            [
                'step_no' => 3,
                'step_type' => PurchaseRequestApproval::STEP_WORKFLOW,
                'role_name' => 'ผู้อำนวยการ',
                'approver_emp_id' => $director['id'] ?? null,
                'approver_name' => $director['fullname'] ?? '',
                'approver_position' => $director['position_name'] ?? '',
                'status' => PurchaseRequestApproval::STATUS_NONE,
                'data_json' => [
                    'source' => 'fallback',
                    'label' => 'ผู้อำนวยการ',
                    'title' => 'ผู้อำนวยการ',
                ],
            ],
        ];
    }

    protected static function resolveApprover(string $type, ?string $value, ?Employees $requester): array
    {
        $info = [
            'emp_id' => null,
            'fullname' => '',
            'position' => '',
        ];

        if ($type === ApproveLevelSetting::TYPE_FIXED && !empty($value)) {
            $employee = Employees::findOne((int) $value);
            if ($employee) {
                $info['emp_id'] = $employee->id;
                $info['fullname'] = $employee->fullname;
                $info['position'] = $employee->positionName();
            }
            return $info;
        }

        if ($type === ApproveLevelSetting::TYPE_DIRECTOR) {
            $director = SiteHelper::viewDirector();
            $info['emp_id'] = !empty($director['id']) ? (int) $director['id'] : null;
            $info['fullname'] = $director['fullname'] ?? '';
            $info['position'] = $director['position_name'] ?? '';
            return $info;
        }

        $leaderInfo = $requester ? $requester->leaderUser() : [];
        if ($type === ApproveLevelSetting::TYPE_ORG_LEADER2) {
            $empId = $leaderInfo['leader2'] ?? null;
            if ($empId) {
                $employee = Employees::findOne((int) $empId);
                if ($employee) {
                    $info['emp_id'] = $employee->id;
                    $info['fullname'] = $employee->fullname;
                    $info['position'] = $employee->positionName();
                }
            }
            return $info;
        }

        $empId = $leaderInfo['leader1'] ?? null;
        if ($empId) {
            $employee = Employees::findOne((int) $empId);
            if ($employee) {
                $info['emp_id'] = $employee->id;
                $info['fullname'] = $employee->fullname;
                $info['position'] = $employee->positionName();
            }
        }

        return $info;
    }

    protected static function resolveEmployeeName($empId): string
    {
        if (empty($empId)) {
            return '';
        }
        $employee = Employees::findOne((int) $empId);
        return $employee ? $employee->fullname : '';
    }

    protected static function resolveEmployeePosition($empId): string
    {
        if (empty($empId)) {
            return '';
        }
        $employee = Employees::findOne((int) $empId);
        return $employee ? $employee->positionName() : '';
    }

    protected static function mapLegacyApprovalStatus(?string $legacyStatus): string
    {
        return match (strtolower(trim((string) $legacyStatus))) {
            'pass', 'approved' => PurchaseRequestApproval::STATUS_APPROVED,
            'reject', 'rejected' => PurchaseRequestApproval::STATUS_REJECTED,
            'pending' => PurchaseRequestApproval::STATUS_PENDING,
            'none', 'waiting' => PurchaseRequestApproval::STATUS_NONE,
            default => PurchaseRequestApproval::STATUS_NONE,
        };
    }

    protected static function normalizeLegacyDataJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected static function writeLog(PurchaseRequest $request, string $action, string $message, $fromStatus, $toStatus, array $extra = [], ?Employees $actor = null): void
    {
        $log = new PurchaseRequestLog();
        $log->request_id = $request->id;
        $log->action = $action;
        $log->message = $message;
        $log->from_status = $fromStatus;
        $log->to_status = $toStatus;
        $log->actor_emp_id = $actor?->id;
        $log->actor_user_id = $actor?->user_id;
        $log->data_json = $extra;
        $log->save(false);
    }
}
