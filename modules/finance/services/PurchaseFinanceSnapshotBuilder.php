<?php

namespace app\modules\finance\services;

use yii\db\Expression;
use app\modules\am\models\Asset;
use app\modules\purchase\models\Order;

/**
 * Builds a read-only purchase snapshot for Finance Inbox.
 * No source record is saved or moved to another status.
 */
class PurchaseFinanceSnapshotBuilder
{
    public const SOURCE_SYSTEM = 'purchase';
    public const TYPE_SERVICE = 'service_acceptance';
    public const TYPE_ASSET = 'asset_receipt';
    public const TYPE_INVENTORY = 'inventory_receipt';

    /**
     * @return array{source:array,payload:array,blocking_errors:string[]}
     */
    public function build(Order $order): array
    {
        $items = $this->buildItems($order);
        $sourceType = self::classify((string) $order->category_id, (string) $order->group_id);
        $vendorName = trim((string) ($order->vendor_name ?? ''));
        if ($vendorName === '' || $vendorName === '-') {
            $vendorName = trim((string) ($order->vendor->title ?? ''));
        }

        $grDate = $this->dateOnly($order->data_json['gr_date'] ?? null);
        $vat = $order->calculateVAT();
        $amount = $this->number($vat['priceAfterVAT'] ?? $order->SumPo());
        $evidence = $this->evidence($order, $sourceType);

        $payload = [
            'document_no' => $order->gr_number ?: ($order->po_number ?: ('ORDER-' . $order->id)),
            'document_date' => $grDate,
            'purchase_order_no' => $order->po_number,
            'purchase_request_no' => $order->pr_number,
            'goods_receipt_no' => $order->gr_number,
            'goods_receipt_date' => $grDate,
            'vendor' => [
                'vendor_id' => $order->vendor_id,
                'name' => $vendorName,
            ],
            'destination_type' => $sourceType,
            'items' => $items,
            'amount' => $amount,
            'vat' => [
                'type' => $order->data_json['vat'] ?? null,
                'before_vat' => $this->number($vat['priceBeforeVAT'] ?? null),
                'vat_amount' => $this->number($vat['vatAmount'] ?? null),
                'after_vat' => $amount,
            ],
            'funding' => [
                'budget_type' => $order->data_json['pq_budget_type'] ?? null,
                'plan_group_id' => $order->plan_group_id,
                'plan_type_id' => $order->plan_type_id,
                'plan_category_id' => $order->plan_category_id,
                'plan_item_id' => $order->plan_item_id,
            ],
            'source_status_snapshot' => (string) $order->status,
            'evidence' => $evidence,
            'captured_at' => date(DATE_ATOM),
        ];

        return [
            'source' => [
                'source_system' => self::SOURCE_SYSTEM,
                'source_type' => $sourceType,
                'source_id' => (string) $order->id,
                'source_version' => 1,
                'source_document_no' => $payload['document_no'],
                // purchase.vendor_id is a business code (often a 13-digit tax/vendor code),
                // not the finance vendor table primary key. Resolve it during finance review.
                'vendor_id' => null,
                'vendor_code_snapshot' => $order->vendor_id ? (string) $order->vendor_id : null,
                'vendor_name_snapshot' => $vendorName ?: null,
                'document_date' => $grDate,
                'amount' => $amount,
            ],
            'payload' => $payload,
            'blocking_errors' => $this->blockingErrors($order, $sourceType, $items, $vendorName, $grDate, $evidence),
        ];
    }

    public static function classify(string $categoryId, string $groupId): string
    {
        if ($categoryId === 'M25') {
            return self::TYPE_SERVICE;
        }
        if ($groupId === '3') {
            return self::TYPE_ASSET;
        }
        return self::TYPE_INVENTORY;
    }

    private function buildItems(Order $order): array
    {
        $rows = [];
        foreach ($order->ListOrderItems() as $item) {
            $qty = (float) $item->qty;
            $price = (float) $item->price;
            $rows[] = [
                'source_item_id' => (int) $item->id,
                'item_code' => (string) $item->asset_item,
                'description' => $item->data_json['item_name'] ?? $item->data_json['title'] ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'line_amount' => round($qty * $price, 2),
            ];
        }
        return $rows;
    }

    private function evidence(Order $order, string $sourceType): array
    {
        $evidence = [
            'goods_receipt_complete' => !empty($order->gr_number)
                && !empty($order->data_json['gr_date'])
                && ($order->data_json['order_item_checker'] ?? null) === 'ถูกต้องครบถ้วน',
            'source_status' => (string) $order->status,
            'inventory_received' => null,
            'asset_registered_count' => null,
        ];

        if ($sourceType === self::TYPE_INVENTORY) {
            $evidence['inventory_received'] = (int) $order->status >= 6;
        }
        if ($sourceType === self::TYPE_ASSET && $order->po_number) {
            $evidence['asset_registered_count'] = (int) Asset::find()
                ->where(new Expression(
                    "JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.po_number')) = :po_number",
                    [':po_number' => (string) $order->po_number]
                ))
                ->count();
        }
        return $evidence;
    }

    private function blockingErrors(
        Order $order,
        string $sourceType,
        array $items,
        string $vendorName,
        ?string $grDate,
        array $evidence
    ): array {
        $errors = [];
        if (!$order->po_number) {
            $errors[] = 'ยังไม่มีเลขที่ใบสั่งซื้อ/สั่งจ้าง';
        }
        if (!$order->gr_number || !$grDate) {
            $errors[] = 'ข้อมูลใบตรวจรับยังไม่ครบ';
        }
        if (empty($evidence['goods_receipt_complete'])) {
            $errors[] = 'ผลตรวจรับยังไม่ยืนยันว่าถูกต้องครบถ้วน';
        }
        if (!$order->vendor_id || $vendorName === '') {
            $errors[] = 'ยังไม่ได้ระบุผู้แทนจำหน่ายให้ครบถ้วน';
        }
        if (!$items) {
            $errors[] = 'ไม่พบรายการที่ตรวจรับ';
        }
        if ($sourceType === self::TYPE_INVENTORY && empty($evidence['inventory_received'])) {
            $errors[] = 'วัสดุยังไม่มีหลักฐานรับเข้าคลัง';
        }
        if ($sourceType === self::TYPE_ASSET) {
            $expected = array_sum(array_map(static fn(array $item) => (float) $item['quantity'], $items));
            if ((int) ($evidence['asset_registered_count'] ?? 0) < (int) ceil($expected)) {
                $errors[] = 'ทะเบียนสินทรัพย์ยังไม่ครบตามจำนวนที่ตรวจรับ';
            }
        }
        if ($sourceType === self::TYPE_SERVICE && (int) $order->status < 6) {
            $errors[] = 'งานจ้างยังไม่ผ่านขั้นตอนตรวจรับ';
        }
        return array_values(array_unique($errors));
    }

    private function dateOnly($value): ?string
    {
        if (!$value) {
            return null;
        }
        $date = substr((string) $value, 0, 10);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }

    private function number($value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }
        return round((float) str_replace(',', '', (string) $value), 2);
    }
}
