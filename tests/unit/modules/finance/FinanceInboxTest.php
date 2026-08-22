<?php

namespace tests\unit\modules\finance;

use app\modules\finance\models\FinanceInbox;
use app\modules\finance\services\FinanceInboxService;
use app\modules\finance\services\PurchaseFinanceSnapshotBuilder;
use app\modules\finance\services\FinanceInboxReviewService;
use app\modules\finance\models\FinanceInboxReview;
use app\modules\finance\services\FinancePayableDraftService;
use app\modules\finance\services\FinancePayableApprovalService;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableReview;
use app\modules\finance\models\FinanceVoucher;
use app\modules\finance\services\FinanceVoucherDraftService;
use Codeception\Test\Unit;

class FinanceInboxTest extends Unit
{
    public function testStatusVocabularyIsStable(): void
    {
        $this->assertSame('รอตรวจสอบ', FinanceInbox::statusOptions()[FinanceInbox::STATUS_PENDING_REVIEW]);
        $this->assertArrayHasKey(FinanceInbox::STATUS_ACCEPTED, FinanceInbox::statusOptions());
        $this->assertArrayHasKey(FinanceInbox::STATUS_REJECTED, FinanceInbox::statusOptions());
    }

    public function testPayloadValidationListsMissingRequiredFields(): void
    {
        $messages = (new FinanceInboxService())->validatePayload(['document_no' => 'GR-001']);

        $this->assertCount(4, $messages);
        $this->assertContains('ไม่พบข้อมูลผู้แทนจำหน่าย', $messages);
        $this->assertContains('ไม่พบรายการที่ตรวจรับ', $messages);
    }

    public function testCompletePayloadHasNoValidationMessages(): void
    {
        $messages = (new FinanceInboxService())->validatePayload([
            'document_no' => 'GR-001',
            'document_date' => '2026-08-15',
            'vendor' => ['id' => 10, 'name' => 'ผู้ขายตัวอย่าง'],
            'items' => [['code' => 'ITEM-1', 'quantity' => 1]],
            'amount' => 1000,
        ]);

        $this->assertSame([], $messages);
    }

    public function testPurchaseDestinationClassification(): void
    {
        $this->assertSame(
            PurchaseFinanceSnapshotBuilder::TYPE_SERVICE,
            PurchaseFinanceSnapshotBuilder::classify('M25', '4')
        );
        $this->assertSame(
            PurchaseFinanceSnapshotBuilder::TYPE_ASSET,
            PurchaseFinanceSnapshotBuilder::classify('OTHER', '3')
        );
        $this->assertSame(
            PurchaseFinanceSnapshotBuilder::TYPE_INVENTORY,
            PurchaseFinanceSnapshotBuilder::classify('OTHER', '4')
        );
    }

    public function testReviewTransitionRules(): void
    {
        $this->assertSame(
            FinanceInbox::STATUS_ACCEPTED,
            FinanceInboxReviewService::targetStatus(
                FinanceInbox::STATUS_PENDING_REVIEW,
                FinanceInboxReview::DECISION_ACCEPT
            )
        );
        $this->assertSame(
            FinanceInbox::STATUS_NEEDS_INFORMATION,
            FinanceInboxReviewService::targetStatus(
                FinanceInbox::STATUS_PENDING_REVIEW,
                FinanceInboxReview::DECISION_REQUEST_INFORMATION
            )
        );
        $this->expectException(\DomainException::class);
        FinanceInboxReviewService::targetStatus(
            FinanceInbox::STATUS_ACCEPTED,
            FinanceInboxReview::DECISION_REJECT
        );
    }

    public function testPayableDueDateAndInvoiceNormalization(): void
    {
        $this->assertSame('2026-09-14', FinancePayableDraftService::calculateDueDate('2026-08-15', 30));
        $this->assertSame('INV-001', FinancePayableDraftService::normalizeInvoiceNo(' inv- 001 '));
    }

    public function testPayableApprovalTransitionRules(): void
    {
        $this->assertSame(
            FinancePayable::STATUS_PENDING_APPROVAL,
            FinancePayableApprovalService::targetStatus(FinancePayable::STATUS_DRAFT, FinancePayableReview::DECISION_SUBMIT)
        );
        $this->assertSame(
            FinancePayable::STATUS_APPROVED,
            FinancePayableApprovalService::targetStatus(FinancePayable::STATUS_PENDING_APPROVAL, FinancePayableReview::DECISION_APPROVE)
        );
        $this->assertSame(
            FinancePayable::STATUS_NEEDS_REVISION,
            FinancePayableApprovalService::targetStatus(FinancePayable::STATUS_PENDING_APPROVAL, FinancePayableReview::DECISION_REQUEST_REVISION)
        );
        $this->expectException(\DomainException::class);
        FinancePayableApprovalService::targetStatus(FinancePayable::STATUS_APPROVED, FinancePayableReview::DECISION_SUBMIT);
    }

    public function testVoucherDraftVocabularyIsStable(): void
    {
        $this->assertSame('เช็ค', FinanceVoucher::paymentMethodOptions()[FinanceVoucher::METHOD_CHEQUE]);
        $this->assertSame('โอนเงิน', FinanceVoucher::paymentMethodOptions()[FinanceVoucher::METHOD_BANK_TRANSFER]);
        $this->assertSame('draft', FinanceVoucher::STATUS_DRAFT);
    }

    public function testVoucherDraftRejectsUnapprovedPayable(): void
    {
        $this->expectException(\DomainException::class);
        FinanceVoucherDraftService::assertEligible(FinancePayable::STATUS_PENDING_APPROVAL, false);
    }

    public function testVoucherDraftRejectsDuplicatePayable(): void
    {
        $this->expectException(\DomainException::class);
        FinanceVoucherDraftService::assertEligible(FinancePayable::STATUS_APPROVED, true);
    }

}
