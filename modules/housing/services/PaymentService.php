<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\Invoice;
use app\modules\housing\models\Payment;
use app\modules\housing\models\PaymentAllocation;
use app\modules\housing\models\Receipt;
use Yii;

final class PaymentService
{
    public function receive(Invoice $invoice, float $amount, string $method, ?string $referenceNo = null, ?string $note = null): Receipt
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
                'paid_at' => date('Y-m-d H:i:s'),
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
}
