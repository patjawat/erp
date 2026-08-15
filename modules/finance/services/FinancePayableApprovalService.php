<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableReview;

class FinancePayableApprovalService
{
    /** @throws \DomainException|\RuntimeException */
    public function decide(FinancePayable $payable, string $decision, ?string $note = null): FinancePayableReview
    {
        $decision = trim($decision);
        $note = trim((string) $note);
        $toStatus = self::targetStatus((string) $payable->status, $decision);
        if ($decision === FinancePayableReview::DECISION_REQUEST_REVISION && $note === '') {
            throw new \DomainException('กรุณาระบุสิ่งที่ต้องแก้ไขก่อนส่งกลับ');
        }

        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($decision === FinancePayableReview::DECISION_APPROVE && $userId !== null && (int) $payable->created_by === (int) $userId) {
            throw new \DomainException('ผู้จัดทำรายการไม่สามารถอนุมัติรายการของตนเองได้');
        }

        $now = date('Y-m-d H:i:s');
        $attributes = [
            'status' => $toStatus,
            'updated_at' => $now,
            'updated_by' => $userId,
        ];
        if ($decision === FinancePayableReview::DECISION_SUBMIT) {
            $attributes['submitted_at'] = $now;
            $attributes['submitted_by'] = $userId;
        } elseif ($decision === FinancePayableReview::DECISION_APPROVE) {
            $attributes['approved_at'] = $now;
            $attributes['approved_by'] = $userId;
            $attributes['payable_no'] = sprintf('AP-%s-%06d', date('Y'), $payable->id);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $fromStatus = (string) $payable->status;
            $updated = FinancePayable::updateAll($attributes, ['id' => $payable->id, 'status' => $fromStatus]);
            if ($updated !== 1) {
                throw new \DomainException('รายการถูกดำเนินการแล้ว กรุณาโหลดข้อมูลใหม่');
            }

            $review = new FinancePayableReview([
                'finance_payable_id' => $payable->id,
                'decision' => $decision,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note ?: null,
                'metadata_json' => [
                    'payable_no' => $payable->payable_no,
                    'invoice_no' => $payable->invoice_no,
                    'net_amount' => $payable->net_amount,
                ],
            ]);
            if (!$review->save()) {
                throw new \RuntimeException('บันทึกประวัติการตรวจอนุมัติไม่สำเร็จ: ' . implode(' ', $review->getFirstErrors()));
            }
            $transaction->commit();
            $payable->refresh();
            return $review;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function targetStatus(string $fromStatus, string $decision): string
    {
        $transitions = [
            FinancePayable::STATUS_DRAFT => [
                FinancePayableReview::DECISION_SUBMIT => FinancePayable::STATUS_PENDING_APPROVAL,
            ],
            FinancePayable::STATUS_NEEDS_REVISION => [
                FinancePayableReview::DECISION_SUBMIT => FinancePayable::STATUS_PENDING_APPROVAL,
            ],
            FinancePayable::STATUS_PENDING_APPROVAL => [
                FinancePayableReview::DECISION_APPROVE => FinancePayable::STATUS_APPROVED,
                FinancePayableReview::DECISION_REQUEST_REVISION => FinancePayable::STATUS_NEEDS_REVISION,
            ],
        ];
        if (!isset($transitions[$fromStatus][$decision])) {
            throw new \DomainException('ไม่สามารถเปลี่ยนสถานะรายการตามคำสั่งนี้ได้');
        }
        return $transitions[$fromStatus][$decision];
    }
}
