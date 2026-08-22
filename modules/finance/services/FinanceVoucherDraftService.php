<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinanceVoucher;

class FinanceVoucherDraftService
{
    public function prepare(FinancePayable $payable): FinanceVoucher
    {
        self::assertEligible((string) $payable->status, $payable->voucher !== null);
        return new FinanceVoucher([
            'finance_payable_id' => $payable->id,
            'vendor_id' => $payable->vendor_id,
            'vendor_code_snapshot' => $payable->vendor_code_snapshot,
            'vendor_name_snapshot' => $payable->vendor_name_snapshot,
            'payable_no_snapshot' => $payable->payable_no,
            'invoice_no_snapshot' => $payable->invoice_no,
            'gross_amount' => $payable->gross_amount,
            'withholding_tax_amount' => $payable->withholding_tax_amount,
            'net_amount' => $payable->net_amount,
            'requested_payment_date' => max(date('Y-m-d'), (string) $payable->due_date),
            'payment_method' => FinanceVoucher::METHOD_CHEQUE,
            'status' => FinanceVoucher::STATUS_DRAFT,
        ]);
    }

    public static function assertEligible(string $status, bool $hasVoucher): void
    {
        if ($status !== FinancePayable::STATUS_APPROVED) {
            throw new \DomainException('สร้างร่างฎีกาได้เฉพาะรายการเจ้าหนี้ที่ฝ่ายบัญชีอนุมัติแล้ว');
        }
        if ($hasVoucher) {
            throw new \DomainException('รายการเจ้าหนี้นี้มีร่างฎีกาแล้ว');
        }
    }

    public function create(FinancePayable $payable, FinanceVoucher $voucher): FinanceVoucher
    {
        if ($payable->status !== FinancePayable::STATUS_APPROVED || $payable->voucher !== null) {
            throw new \DomainException('รายการต้นทางไม่พร้อมสร้างร่างฎีกาหรือถูกดำเนินการแล้ว');
        }
        foreach ($this->prepare($payable)->getAttributes() as $name => $value) {
            if (!in_array($name, ['funding_source', 'requested_payment_date', 'payment_method', 'note'], true)) $voucher->$name = $value;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$voucher->save()) throw new \RuntimeException(implode(' ', $voucher->getFirstErrors()));
            $voucher->voucher_no = sprintf('PV-DRAFT-%s-%06d', date('Y'), $voucher->id);
            if (!$voucher->save(false, ['voucher_no', 'updated_at', 'updated_by'])) throw new \RuntimeException('ไม่สามารถกำหนดเลขที่ร่างฎีกาได้');
            $transaction->commit();
            return $voucher;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
