<?php

namespace app\modules\leave\components;

use yii\helpers\ArrayHelper;
use app\modules\leave\models\Leave;
use app\modules\approveV2\models\Approve;

class LeaveApprovalService
{
    /**
     * แมประดับผู้อนุมัติ → สถานะของใบลา (leave.status)
     * ใช้ร่วมกับ ApproverController::syncLeaveStatus() เพื่อให้สถานะตรงกันเสมอ
     */
    public const LEVEL_STATUS_MAP = [
        1 => ['Pass' => 'Checking1_pass', 'Reject' => 'Checking1_reject'],
        2 => ['Pass' => 'Checking2_pass', 'Reject' => 'Checking2_reject'],
        3 => ['Pass' => 'Checkup_pass',   'Reject' => 'Checkup_reject'],
        4 => ['Pass' => 'Approve',        'Reject' => 'Reject'],
    ];

    public function process(Approve $approve, string $status, ?int $actorEmpId = null, bool $forceStampActorEmpId = false): array
    {
        if (!in_array($status, ['Pass', 'Reject'], true)) {
            return ['ok' => false, 'message' => 'Invalid status'];
        }

        if ($approve->name !== 'leave') {
            return ['ok' => false, 'message' => 'Invalid approve type'];
        }

        if ($approve->status !== 'Pending') {
            return ['ok' => false, 'message' => 'รายการนี้ไม่ได้อยู่ระหว่างรออนุมัติ'];
        }

        $approve->data_json = ArrayHelper::merge(
            (array) $approve->data_json,
            ['approve_date' => date('Y-m-d H:i:s')]
        );
        $approve->status = $status;

        if (!empty($actorEmpId) && ($forceStampActorEmpId || empty($approve->emp_id))) {
            $approve->emp_id = (int) $actorEmpId;
        }

        if (!$approve->save(false)) {
            return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }

        $leave = Leave::findOne((int) $approve->from_id);
        if (!$leave) {
            return ['ok' => true];
        }

        $telegram = new LeaveTelegramService();
        if ($status === 'Reject') {
            $leave->status = 'Reject';
            $leave->save(false);
            $leave->MsgReject();
            $telegram->notifyLeaveResult($leave, 'Reject');

            return ['ok' => true, 'leave' => $leave];
        }

        if ($approve->maxLevel()) {
            $leave->status = 'Approve';
            $leave->save(false);
            $leave->MsgApprove();
            $telegram->notifyLeaveResult($leave, 'Approve');

            return ['ok' => true, 'leave' => $leave];
        }

        if (isset(self::LEVEL_STATUS_MAP[$approve->level][$status])) {
            $leave->status = self::LEVEL_STATUS_MAP[$approve->level][$status];
            $leave->save(false);
        }

        $nextApprove = Approve::findOne([
            'from_id' => $approve->from_id,
            'name' => 'leave',
            'level' => $approve->level + 1,
        ]);

        if ($nextApprove) {
            $nextApprove->status = 'Pending';
            $nextApprove->save(false);
            $telegram->notifyPendingApprove($nextApprove);
        }

        return [
            'ok' => true,
            'leave' => $leave,
            'nextApprove' => $nextApprove,
        ];
    }
}
