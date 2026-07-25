<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\BillingPeriod;
use app\modules\housing\models\Invoice;
use app\modules\housing\models\InvoiceItem;
use app\modules\housing\models\Occupancy;
use Yii;

final class InvoiceService
{
    public function createDraft(BillingPeriod $period, Occupancy $occupancy): Invoice
    {
        $existing = Invoice::findOne(['billing_period_id' => $period->id, 'occupancy_id' => $occupancy->id]);
        if ($existing) {
            return $existing;
        }
        $invoice = new Invoice([
            'invoice_no' => (new DocumentNumberService())->temporary('HIN'),
            'billing_period_id' => $period->id,
            'occupancy_id' => $occupancy->id,
            'payer_emp_id' => $occupancy->payer_emp_id,
            'due_date' => $period->due_date,
            'status' => Invoice::DRAFT,
        ]);
        if (!$invoice->save()) {
            throw new \RuntimeException(implode(' ', $invoice->getFirstErrors()));
        }
        return $invoice;
    }

    public function addItem(Invoice $invoice, array $data): InvoiceItem
    {
        if ($invoice->status !== Invoice::DRAFT) {
            throw new \DomainException('เพิ่มรายการได้เฉพาะใบแจ้งสถานะร่าง');
        }
        $quantity = round((float)($data['quantity'] ?? 1), 2);
        $unitPrice = round((float)($data['unit_price'] ?? 0), 2);
        $amount = isset($data['amount']) && $data['amount'] !== ''
            ? round((float)$data['amount'], 2)
            : round($quantity * $unitPrice, 2);
        $item = new InvoiceItem([
            'invoice_id' => $invoice->id,
            'charge_type_id' => $data['charge_type_id'] ?: null,
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit_name' => $data['unit_name'] ?? null,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'calculation_note' => $data['calculation_note'] ?? null,
        ]);
        if (!$item->save()) {
            throw new \RuntimeException(implode(' ', $item->getFirstErrors()));
        }
        $this->recalculate($invoice);
        return $item;
    }

    public function recalculate(Invoice $invoice): void
    {
        $subtotal = round((float)InvoiceItem::find()->where(['invoice_id' => $invoice->id])->sum('amount'), 2);
        $discount = round((float)$invoice->discount, 2);
        $total = max(0, round($subtotal - $discount, 2));
        $paid = round((float)$invoice->paid_amount, 2);
        $invoice->updateAttributes([
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'balance_amount' => max(0, round($total - $paid, 2)),
        ]);
        $invoice->refresh();
    }

    public function confirm(Invoice $invoice): void
    {
        if ($invoice->status !== Invoice::DRAFT || !$invoice->getItems()->exists()) {
            throw new \DomainException('ใบแจ้งต้องเป็นร่างและมีอย่างน้อยหนึ่งรายการ');
        }
        $this->recalculate($invoice);
        if ((float)$invoice->total_amount <= 0) {
            throw new \DomainException('ยอดใบแจ้งต้องมากกว่า 0 บาท');
        }
        $invoice->updateAttributes([
            'status' => Invoice::CONFIRMED,
            'issued_at' => date('Y-m-d H:i:s'),
            'confirmed_at' => date('Y-m-d H:i:s'),
            'confirmed_by' => Yii::$app->user->id ?: null,
        ]);
    }
}
