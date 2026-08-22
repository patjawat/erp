<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinanceInboxReview;

class FinanceInboxReviewService
{
    /**
     * @throws \DomainException for an invalid transition or missing reason
     * @throws \RuntimeException when persistence fails
     */
    public function review(FinanceInbox $inbox, string $decision, ?string $note = null): FinanceInboxReview
    {
        $decision = trim($decision);
        $note = trim((string) $note);
        $toStatus = self::targetStatus($inbox->status, $decision);

        if (in_array($decision, [
            FinanceInboxReview::DECISION_REQUEST_INFORMATION,
            FinanceInboxReview::DECISION_REJECT,
        ], true) && $note === '') {
            throw new \DomainException('กรุณาระบุเหตุผลประกอบการตัดสินใจ');
        }
        if ($decision === FinanceInboxReview::DECISION_ACCEPT && $inbox->validationMessages()) {
            throw new \DomainException('ยังรับรองไม่ได้ เนื่องจากข้อมูลขั้นต่ำยังไม่ครบ');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $fromStatus = $inbox->status;
            $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
            $updated = FinanceInbox::updateAll([
                'status' => $toStatus,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'reviewed_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId,
            ], [
                'id' => $inbox->id,
                'status' => FinanceInbox::STATUS_PENDING_REVIEW,
            ]);
            if ($updated !== 1) {
                throw new \DomainException('รายการนี้ถูกดำเนินการแล้ว กรุณาโหลดข้อมูลใหม่');
            }

            $review = new FinanceInboxReview([
                'finance_inbox_id' => $inbox->id,
                'decision' => $decision,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note ?: null,
                'metadata_json' => [
                    'source_system' => $inbox->source_system,
                    'source_type' => $inbox->source_type,
                    'source_id' => $inbox->source_id,
                    'source_version' => $inbox->source_version,
                ],
            ]);
            if (!$review->save()) {
                throw new \RuntimeException('บันทึกประวัติการตรวจสอบไม่สำเร็จ: ' . implode(' ', $review->getFirstErrors()));
            }

            $transaction->commit();
            $inbox->refresh();
            return $review;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function targetStatus(string $currentStatus, string $decision): string
    {
        if ($currentStatus !== FinanceInbox::STATUS_PENDING_REVIEW) {
            throw new \DomainException('ดำเนินการได้เฉพาะรายการที่อยู่ระหว่างรอตรวจสอบ');
        }

        $map = [
            FinanceInboxReview::DECISION_ACCEPT => FinanceInbox::STATUS_ACCEPTED,
            FinanceInboxReview::DECISION_REQUEST_INFORMATION => FinanceInbox::STATUS_NEEDS_INFORMATION,
            FinanceInboxReview::DECISION_REJECT => FinanceInbox::STATUS_REJECTED,
        ];
        if (!isset($map[$decision])) {
            throw new \DomainException('คำสั่งตรวจสอบไม่ถูกต้อง');
        }
        return $map[$decision];
    }
}
