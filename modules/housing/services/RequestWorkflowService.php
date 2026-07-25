<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\RequestStatusLog;
use Yii;
use yii\db\Expression;

final class RequestWorkflowService
{
    private const TRANSITIONS = [
        HousingRequest::STATUS_DRAFT => [HousingRequest::STATUS_SUBMITTED, HousingRequest::STATUS_CANCELLED],
        HousingRequest::STATUS_SUBMITTED => [HousingRequest::STATUS_STAFF_REVIEW, HousingRequest::STATUS_CANCELLED],
        HousingRequest::STATUS_STAFF_REVIEW => [HousingRequest::STATUS_COMMITTEE_REVIEW, HousingRequest::STATUS_REJECTED],
        HousingRequest::STATUS_COMMITTEE_REVIEW => [HousingRequest::STATUS_APPROVED, HousingRequest::STATUS_REJECTED],
        HousingRequest::STATUS_APPROVED => [HousingRequest::STATUS_ALLOCATED, HousingRequest::STATUS_CANCELLED],
        HousingRequest::STATUS_ALLOCATED => [HousingRequest::STATUS_ACTIVE, HousingRequest::STATUS_CANCELLED],
        HousingRequest::STATUS_ACTIVE => [HousingRequest::STATUS_COMPLETED],
    ];

    public function transition(HousingRequest $request, string $toStatus, ?string $comment = null): void
    {
        $fromStatus = (string)$request->status;
        if (!in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
            throw new \DomainException("ไม่สามารถเปลี่ยนสถานะจาก {$fromStatus} เป็น {$toStatus}");
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request->status = $toStatus;
            if ($toStatus === HousingRequest::STATUS_SUBMITTED) {
                $request->submitted_at = new Expression('NOW()');
                $request->requested_at = $request->requested_at ?: new Expression('NOW()');
            }
            if (in_array($toStatus, [HousingRequest::STATUS_COMPLETED, HousingRequest::STATUS_CANCELLED], true)) {
                $request->completed_at = new Expression('NOW()');
            }
            if (!$request->save(false, ['status', 'submitted_at', 'requested_at', 'completed_at', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('บันทึกสถานะคำขอไม่สำเร็จ');
            }
            $log = new RequestStatusLog([
                'request_id' => $request->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'comment' => $comment,
                'acted_at' => new Expression('NOW()'),
                'acted_by' => Yii::$app->user->id ?: null,
            ]);
            if (!$log->save()) {
                throw new \RuntimeException(implode(' ', $log->getFirstErrors()));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
