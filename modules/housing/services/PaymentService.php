<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\Invoice;
use app\modules\housing\models\InvoiceItem;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\models\Payment;
use app\modules\housing\models\PaymentAllocation;
use app\modules\housing\models\Receipt;
use Yii;

final class PaymentService
{
    public function invoiceForAccount(MonthlyAccount $account): Invoice
    {
        if (!$account->occupancy_id || !$account->payer_emp_id || (float)$account->total_amount <= 0) {
            throw new \DomainException('รายการนี้ไม่มีผู้รับผิดชอบค่าใช้จ่ายหรือไม่มียอดที่รับชำระได้');
        }
        $existing = Invoice::findOne(['monthly_account_id' => $account->id]);
        if ($existing) {
            return $existing;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $status = (float)$account->balance_amount <= 0
                ? Invoice::PAID
                : ((float)$account->paid_amount > 0 ? Invoice::PARTIAL : Invoice::CONFIRMED);
            $invoice = new Invoice([
                'invoice_no' => (new DocumentNumberService())->temporary('HIN'),
                'monthly_account_id' => $account->id,
                'billing_period_id' => $account->billing_period_id,
                'occupancy_id' => $account->occupancy_id,
                'payer_emp_id' => $account->payer_emp_id,
                'issued_at' => date('Y-m-d H:i:s'),
                'due_date' => $account->period->due_date,
                'subtotal' => $account->total_amount,
                'total_amount' => $account->total_amount,
                'paid_amount' => $account->paid_amount,
                'balance_amount' => $account->balance_amount,
                'status' => $status,
                'confirmed_at' => date('Y-m-d H:i:s'),
                'confirmed_by' => Yii::$app->user->id ?: null,
                'note' => $account->note,
            ]);
            if (!$invoice->save()) {
                throw new \RuntimeException(implode(' ', $invoice->getFirstErrors()));
            }
            foreach ($account->items as $accountItem) {
                $item = new InvoiceItem([
                    'invoice_id' => $invoice->id,
                    'charge_type_id' => $accountItem->charge_type_id,
                    'description' => $accountItem->description,
                    'quantity' => 1,
                    'unit_name' => 'บาท',
                    'unit_price' => $accountItem->amount,
                    'amount' => $accountItem->amount,
                    'calculation_note' => $accountItem->note,
                    'sort_order' => $accountItem->sort_order,
                ]);
                if (!$item->save()) {
                    throw new \RuntimeException(implode(' ', $item->getFirstErrors()));
                }
            }
            $transaction->commit();
            return $invoice;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function receive(Invoice $invoice, float $amount, string $method, ?string $referenceNo = null, ?string $note = null, ?string $paidAt = null): Receipt
    {
        if (!in_array($invoice->status, [Invoice::CONFIRMED, Invoice::PARTIAL, Invoice::OVERDUE], true)) {
            throw new \DomainException('ใบแจ้งนี้ยังไม่พร้อมรับชำระ');
        }
        $amount = round($amount, 2);
        if ($amount <= 0 || $amount > round((float)$invoice->balance_amount, 2)) {
            throw new \DomainException('ยอดรับชำระต้องมากกว่า 0 และไม่เกินยอดคงเหลือ');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $numbers = new DocumentNumberService();
            $payment = new Payment([
                'payment_no' => $numbers->temporary('HPY'),
                'payer_emp_id' => $invoice->payer_emp_id,
                'paid_at' => $paidAt ?: date('Y-m-d H:i:s'),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'note' => $note,
                'received_by' => Yii::$app->user->id ?: null,
            ]);
            if (!$payment->save()) {
                throw new \RuntimeException(implode(' ', $payment->getFirstErrors()));
            }
            $allocation = new PaymentAllocation([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Yii::$app->user->id ?: null,
            ]);
            if (!$allocation->save()) {
                throw new \RuntimeException(implode(' ', $allocation->getFirstErrors()));
            }

            $paid = round((float)$invoice->paid_amount + $amount, 2);
            $balance = max(0, round((float)$invoice->total_amount - $paid, 2));
            $invoice->updateAttributes([
                'paid_amount' => $paid,
                'balance_amount' => $balance,
                'status' => $balance <= 0 ? Invoice::PAID : Invoice::PARTIAL,
            ]);
            $this->syncMonthlyAccount($invoice, $paid, $balance);

            $receipt = new Receipt([
                'receipt_no' => $numbers->temporary('HRC-TMP'),
                'payment_id' => $payment->id,
                'issued_at' => date('Y-m-d H:i:s'),
                'amount' => $amount,
                'verification_code' => Yii::$app->security->generateRandomString(48),
                'issued_by' => Yii::$app->user->id ?: null,
            ]);
            if (!$receipt->save()) {
                throw new \RuntimeException(implode(' ', $receipt->getFirstErrors()));
            }
            $receipt->updateAttributes(['receipt_no' => $numbers->receiptNumber((int)$receipt->id)]);
            $receipt->refresh();
            $transaction->commit();
            return $receipt;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function cancel(Payment $payment, string $reason): void
    {
        $reason = trim($reason);
        if ($payment->status !== 'confirmed' || $reason === '') {
            throw new \DomainException('รายการนี้ยกเลิกไม่ได้หรือยังไม่ได้ระบุเหตุผล');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($payment->allocations as $allocation) {
                $invoice = $allocation->invoice;
                if (!$invoice) {
                    continue;
                }
                $paid = max(0, round((float)$invoice->paid_amount - (float)$allocation->amount, 2));
                $balance = max(0, round((float)$invoice->total_amount - $paid, 2));
                $status = $paid <= 0 ? Invoice::CONFIRMED : ($balance > 0 ? Invoice::PARTIAL : Invoice::PAID);
                $invoice->updateAttributes(['paid_amount' => $paid, 'balance_amount' => $balance, 'status' => $status]);
                $this->syncMonthlyAccount($invoice, $paid, $balance);
            }
            $now = date('Y-m-d H:i:s');
            $payment->updateAttributes([
                'status' => 'cancelled', 'cancelled_at' => $now,
                'cancelled_by' => Yii::$app->user->id ?: null, 'cancel_reason' => $reason,
            ]);
            if ($payment->receipt) {
                $payment->receipt->updateAttributes([
                    'status' => 'cancelled', 'cancelled_at' => $now,
                    'cancelled_by' => Yii::$app->user->id ?: null, 'cancel_reason' => $reason,
                ]);
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function syncMonthlyAccount(Invoice $invoice, float $paid, float $balance): void
    {
        $account = $invoice->monthlyAccount;
        if (!$account) {
            return;
        }
        $paymentStatus = (float)$account->total_amount <= 0 || $balance <= 0
            ? MonthlyAccount::PAYMENT_PAID
            : ($paid > 0 ? MonthlyAccount::PAYMENT_PARTIAL : MonthlyAccount::PAYMENT_UNPAID);
        $account->updateAttributes([
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,
        ]);
    }
}
