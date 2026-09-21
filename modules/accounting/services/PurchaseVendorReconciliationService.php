<?php

namespace app\modules\accounting\services;

use app\modules\purchase\models\Order;
use app\modules\sm\models\Vendor;

/** Read-only reconciliation of procurement vendor codes against the shared vendor master. */
class PurchaseVendorReconciliationService
{
    public const MATCHED = 'matched';
    public const REVIEW = 'review';
    public const MISSING = 'missing';

    public function report(int $fiscalYear): array
    {
        $vendors = Vendor::find()->where(['name' => 'vendor'])->all();
        $byCode = [];
        $byTaxId = [];
        foreach ($vendors as $vendor) {
            $code = self::normalizeCode($vendor->code);
            if ($code !== '') $byCode[$code][] = $vendor;
            $taxId = self::normalizeTaxId($vendor->data_json['tax_id'] ?? null);
            if ($taxId !== '') $byTaxId[$taxId][] = $vendor;
        }

        $groups = Order::find()
            ->select(['vendor_id', 'order_count' => 'COUNT(*)', 'last_order_id' => 'MAX(id)'])
            ->where(['name' => 'order', 'thai_year' => $fiscalYear])
            ->andWhere(['not', ['po_number' => null]])
            ->andWhere(['<>', 'po_number', ''])
            ->groupBy('vendor_id')
            ->asArray()->all();

        $rows = [];
        $counts = [self::MATCHED => 0, self::REVIEW => 0, self::MISSING => 0];
        foreach ($groups as $group) {
            $result = self::classify((string) ($group['vendor_id'] ?? ''), $byCode, $byTaxId);
            $count = (int) $group['order_count'];
            $counts[$result['status']] += $count;
            $rows[] = [
                'source_code' => (string) ($group['vendor_id'] ?? ''),
                'order_count' => $count,
                'last_order_id' => (int) $group['last_order_id'],
            ] + $result;
        }
        usort($rows, static fn(array $a, array $b) => [self::MISSING => 0, self::REVIEW => 1, self::MATCHED => 2][$a['status']] <=> [self::MISSING => 0, self::REVIEW => 1, self::MATCHED => 2][$b['status']] ?: strcmp($a['source_code'], $b['source_code']));
        return ['rows' => $rows, 'counts' => $counts, 'total' => array_sum($counts)];
    }

    public static function classify(string $sourceCode, array $byCode, array $byTaxId): array
    {
        $code = self::normalizeCode($sourceCode);
        if ($code === '') return ['status' => self::MISSING, 'reason' => 'ใบสั่งซื้อไม่มีรหัสผู้ขาย', 'candidates' => []];
        $matches = $byCode[$code] ?? [];
        if (count($matches) > 1) return ['status' => self::REVIEW, 'reason' => 'รหัสนี้พบผู้ขายหลายรายในทะเบียน', 'candidates' => $matches];
        if (count($matches) === 1) {
            $vendor = $matches[0];
            return ['status' => $vendor->active ? self::MATCHED : self::REVIEW,
                'reason' => $vendor->active ? 'รหัสตรงกับทะเบียนผู้ขายที่ใช้งาน' : 'รหัสตรง แต่ผู้ขายถูกปิดใช้งาน',
                'candidates' => $matches];
        }
        $taxId = self::normalizeTaxId($code);
        $taxMatches = $taxId !== '' ? ($byTaxId[$taxId] ?? []) : [];
        if ($taxMatches) return ['status' => self::REVIEW, 'reason' => count($taxMatches) > 1 ? 'เลขผู้เสียภาษีตรงหลายราย ต้องตรวจซ้ำ' : 'รหัสเก่าคล้ายเลขผู้เสียภาษี ต้องยืนยันก่อนจับคู่', 'candidates' => $taxMatches];
        return ['status' => self::MISSING, 'reason' => 'ไม่พบรหัสนี้ในทะเบียนผู้ขาย', 'candidates' => []];
    }

    private static function normalizeCode(?string $value): string
    {
        $value = trim((string) $value);
        return $value === '-' ? '' : mb_strtoupper($value, 'UTF-8');
    }

    private static function normalizeTaxId(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        return strlen($digits) === 13 ? $digits : '';
    }
}
