<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceInbox;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\ContractReceipt;

/**
 * สร้าง snapshot "ตรวจรับรายงวด" ของสัญญา ส่งเข้ากล่องรอรับของการเงิน
 *
 * ใช้กับสัญญาที่ออกใบสั่งซื้อเต็มวงเงินแต่ตรวจรับ/เรียกเก็บรายเดือน (Contract::isInstallment())
 * 1 งวด = 1 รายการในกล่องรอรับ = ตั้งหนี้ตามยอดเรียกเก็บของงวดนั้น
 * source_id = purchase_contract_receipt.id (ไม่ใช่ orders.id) — ใบสั่งซื้อเดียวจึงส่งได้หลายครั้งโดยไม่ชน unique
 *
 * ไม่แก้ข้อมูลต้นทาง — ผู้เรียกเป็นคนเปลี่ยนสถานะงวดเอง
 */
class ContractReceiptFinanceSnapshotBuilder
{
    public const SOURCE_SYSTEM = PurchaseFinanceSnapshotBuilder::SOURCE_SYSTEM;
    public const TYPE_CONTRACT_RECEIPT = 'contract_receipt';

    /**
     * @return array{source:array,payload:array,blocking_errors:string[]}
     */
    public function build(ContractReceipt $receipt): array
    {
        $contract = $receipt->contract;
        $order = $receipt->order_id ? Order::findOne($receipt->order_id) : null;
        $orderJson = $order && is_array($order->data_json) ? $order->data_json : [];

        $vendorCode = $contract->vendor_id ?: ($order->vendor_id ?? null);
        $vendorName = trim((string) ($contract->vendor_name ?? ''));
        if (($vendorName === '' || $vendorName === '-') && $order) {
            $vendorName = trim((string) ($order->vendor->title ?? ''));
        }

        $poNo = $order->po_number ?? null;
        $documentNo = ($poNo ?: ('CT-' . $contract->id)) . '/งวด' . $receipt->seq;

        $items = [];
        foreach ($receipt->items as $item) {
            $items[] = [
                'source_item_id' => (int) $item->id,
                'order_item_id' => $item->order_item_id,
                'item_code' => (string) $item->asset_item,
                'description' => $item->item_name . ($item->unit_name ? ' (' . $item->unit_name . ')' : ''),
                'quantity' => (float) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'line_amount' => round((float) $item->amount, 2),
            ];
        }

        $amount = round((float) $receipt->amount, 2);
        $payload = [
            'document_no' => $documentNo,
            'document_date' => $receipt->receive_date,
            'invoice_no' => $receipt->invoice_no,
            'invoice_date' => $receipt->invoice_date,
            'purchase_order_no' => $poNo,
            'purchase_request_no' => $order->pr_number ?? null,
            'contract' => [
                'contract_id' => (int) $contract->id,
                'contract_no' => $contract->contract_no ?: $contract->doc_no,
                'title' => $contract->title,
                'billing_mode' => $contract->billing_mode,
                'budget' => (float) $contract->budget,
            ],
            'installment' => [
                'receipt_id' => (int) $receipt->id,
                'seq' => (int) $receipt->seq,
                'period_start' => $receipt->period_start,
                'period_end' => $receipt->period_end,
                'delivered_date' => $receipt->delivered_date,
                'receive_date' => $receipt->receive_date,
                'fine_amount' => round((float) $receipt->fine_amount, 2),
                'withholding_tax_amount' => round((float) $receipt->wht_amount, 2),
                'net_payable' => $receipt->netPayable(),
            ],
            'vendor' => [
                'vendor_id' => $vendorCode,
                'name' => $vendorName,
            ],
            'destination_type' => self::TYPE_CONTRACT_RECEIPT,
            'items' => $items,
            'amount' => $amount,
            'vat' => [
                'type' => $receipt->vat_type,
                'before_vat' => round((float) $receipt->amount_before_vat, 2),
                'vat_amount' => round((float) $receipt->vat_amount, 2),
                'after_vat' => $amount,
            ],
            'funding' => [
                'budget_type' => $orderJson['pq_budget_type'] ?? null,
                'plan_group_id' => $order->plan_group_id ?? null,
                'plan_type_id' => $order->plan_type_id ?? null,
                'plan_category_id' => $order->plan_category_id ?? null,
                'plan_item_id' => $order->plan_item_id ?? null,
            ],
            'source_status_snapshot' => $receipt->status,
            'evidence' => [
                'goods' => $receipt->isGoods(),
                'inventory_received' => $receipt->isGoods() ? $receipt->isStocked() : null,
                'stock_order_no' => $receipt->stockOrderNo(),
            ],
            'captured_at' => date(DATE_ATOM),
        ];

        $errors = [];
        if ($receipt->status !== ContractReceipt::STATUS_RECEIVED) {
            $errors[] = 'งวดนี้ยังไม่ได้ยืนยันตรวจรับ';
        }
        if (!$receipt->receive_date) {
            $errors[] = 'ยังไม่ระบุวันที่ตรวจรับ';
        }
        if (trim((string) $receipt->invoice_no) === '') {
            $errors[] = 'ยังไม่ระบุเลขที่ใบแจ้งหนี้ของผู้รับจ้าง';
        }
        if (!$vendorCode || $vendorName === '') {
            $errors[] = 'สัญญายังไม่ได้ระบุคู่สัญญา';
        }
        if (!$items) {
            $errors[] = 'ไม่พบรายการที่ตรวจรับในงวดนี้';
        }
        if ($amount <= 0) {
            $errors[] = 'ยอดเรียกเก็บของงวดต้องมากกว่า 0';
        }
        // พัสดุ (วัสดุ/ยา) ต้องรับเข้าคลังก่อนส่งการเงิน — กติกาเดียวกับใบสั่งซื้อปกติ (สถานะ 6)
        if ($receipt->isGoods() && !$receipt->isStocked()) {
            $errors[] = 'พัสดุงวดนี้ยังไม่ได้รับเข้าคลัง (รับเข้าที่คลัง → รับจากใบสั่งซื้อ → เลือกงวดนี้)';
        }

        return [
            'source' => [
                'source_system' => self::SOURCE_SYSTEM,
                'source_type' => self::TYPE_CONTRACT_RECEIPT,
                'source_id' => (string) $receipt->id,
                // ส่งซ้ำหลังการเงินตีกลับ = รุ่นใหม่ (unique source+version)
                'source_version' => self::nextVersion((int) $receipt->id),
                'source_document_no' => $documentNo,
                'vendor_id' => null,
                'vendor_code_snapshot' => $vendorCode ? (string) $vendorCode : null,
                'vendor_name_snapshot' => $vendorName ?: null,
                'document_date' => $receipt->receive_date,
                'amount' => $amount,
            ],
            'payload' => $payload,
            'blocking_errors' => array_values(array_unique($errors)),
        ];
    }

    /** รายการในกล่องรอรับล่าสุดของงวดนี้ (null = ยังไม่เคยส่ง) */
    public static function latestInbox(int $receiptId): ?FinanceInbox
    {
        return FinanceInbox::find()->where([
            'source_system' => self::SOURCE_SYSTEM,
            'source_type' => self::TYPE_CONTRACT_RECEIPT,
            'source_id' => (string) $receiptId,
        ])->orderBy(['source_version' => SORT_DESC])->one();
    }

    public static function nextVersion(int $receiptId): int
    {
        $latest = self::latestInbox($receiptId);
        return $latest ? (int) $latest->source_version + 1 : 1;
    }

    /**
     * การเงินตีกลับ (rejected) — พัสดุเปิดแก้งวดแล้วส่งเป็นรุ่นใหม่ได้
     * "ขอข้อมูลเพิ่ม" (needs_information) ไม่นับ เพราะรายการเดิมยังเปิดอยู่ฝั่งการเงิน ส่งใหม่จะกลายเป็นซ้อนสองรายการ
     */
    public static function isReturned(?FinanceInbox $inbox): bool
    {
        return $inbox !== null && $inbox->status === FinanceInbox::STATUS_REJECTED;
    }
}
