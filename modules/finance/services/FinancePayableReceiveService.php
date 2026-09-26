<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinanceInboxReview;
use app\modules\finance\models\FinancePayable;

/**
 * การเงิน "รับเอกสาร" จากกล่องรอรับ — กดครั้งเดียว ไม่ต้องกรอก
 * รับรองรายการในกล่อง + ตั้งเจ้าหนี้เข้าทะเบียนทันที (ออกเลขทะเบียน ไม่มีขั้นร่าง/อนุมัติ)
 * ข้อมูลดึงจากต้นทาง: ผู้ขาย ยอด VAT เครดิต (จากทะเบียนผู้ขาย) และเลขใบแจ้งหนี้ถ้าต้นทางส่งมา
 * ส่วนที่ไม่มี (เลขใบแจ้งหนี้ / ภาษีหัก ณ ที่จ่าย) แก้ภายหลังที่หน้าบิล
 */
class FinancePayableReceiveService
{
    /** @throws \DomainException */
    public function receive(FinanceInbox $inbox): FinancePayable
    {
        if ($inbox->status === FinanceInbox::STATUS_PENDING_REVIEW) {
            if ($inbox->validationMessages()) {
                throw new \DomainException('เอกสาร ' . ($inbox->source_document_no ?: '#' . $inbox->id) . ' ข้อมูลจากต้นทางยังไม่ครบ — ส่งคืนพัสดุให้แก้ไข');
            }
            (new FinanceInboxReviewService())->review($inbox, FinanceInboxReview::DECISION_ACCEPT, 'รับเอกสาร');
            $inbox->refresh();
        }
        if ($inbox->status !== FinanceInbox::STATUS_ACCEPTED) {
            throw new \DomainException('เอกสาร ' . ($inbox->source_document_no ?: '#' . $inbox->id) . ' ไม่อยู่ในสถานะที่รับได้');
        }

        $existing = FinancePayable::findOne(['finance_inbox_id' => $inbox->id]);
        if ($existing) {
            return $existing;
        }

        $model = (new FinancePayableDraftService())->prepare($inbox);
        $model->vendor_id = (int) $model->vendor_id;
        // เลขใบแจ้งหนี้ซ้ำกับบิลเดิมของผู้ขายรายเดียวกัน → เว้นว่างไว้ให้แก้ทีหลัง (ไม่ขวางการรับ)
        $invoiceNo = FinancePayableDraftService::normalizeInvoiceNo((string) $model->invoice_no);
        $duplicate = $invoiceNo !== '' && FinancePayable::find()
            ->where(['vendor_id' => $model->vendor_id, 'invoice_no' => $invoiceNo])->exists();
        $model->invoice_no = ($invoiceNo === '' || $duplicate) ? null : $invoiceNo;

        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $model->status = FinancePayable::STATUS_APPROVED;
        $model->submitted_at = $now;
        $model->submitted_by = $userId;
        $model->approved_at = $now;
        $model->approved_by = $userId;
        if (!$model->save(false)) {
            throw new \RuntimeException('ตั้งเจ้าหนี้ไม่สำเร็จ');
        }
        $model->payable_no = self::payableNo($model);
        $model->save(false, ['payable_no']);
        return $model;
    }

    /** เลขทะเบียนเจ้าหนี้ (รูปแบบเดียวกับที่ใช้อยู่เดิม AP-ปี-ลำดับ) */
    public static function payableNo(FinancePayable $model): string
    {
        return sprintf('AP-%s-%06d', date('Y'), $model->id);
    }
}
