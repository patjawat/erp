<?php

namespace app\modules\sm\services;

use app\modules\sm\models\Vendor;

/**
 * Validation for vendor import rows.
 * - Required: vendor_code, vendor_name
 * - Format: email, phone
 * - Duplicate: in file, in DB
 * - Trim & UTF-8
 */
class VendorImportValidationService
{
    /** @var array<string> key = vendor_code (trimmed), value not used (for duplicate-in-file check) */
    private $codesInFile = [];

    /**
     * Validate a single row (associative array from parsed file).
     * Keys: vendor_code, vendor_name, contact_name, phone, email, address, tax_id, status, account_name, account_number, bank_name
     * @param array $row
     * @param int $rowNumber 1-based
     * @return array list of error messages for this row
     */
    public function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $code = isset($row['vendor_code']) ? trim((string) $row['vendor_code']) : '';
        $name = isset($row['vendor_name']) ? trim((string) $row['vendor_name']) : '';
        $email = isset($row['email']) ? trim((string) $row['email']) : '';

        if ($code === '') {
            $errors[] = 'รหัสผู้แทนจำหน่าย (vendor_code) ห้ามว่าง';
        }
        if ($name === '') {
            $errors[] = 'ชื่อผู้แทนจำหน่าย (vendor_name) ห้ามว่าง';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }

        if ($code !== '') {
            if (isset($this->codesInFile[$code])) {
                $errors[] = 'รหัสซ้ำในไฟล์';
            } else {
                $this->codesInFile[$code] = true;
            }
            if (Vendor::find()->where(['name' => 'vendor', 'code' => $code])->exists()) {
                $errors[] = 'รหัสซ้ำในระบบ';
            }
        }

        return $errors;
    }

    /**
     * Normalize phone string.
     * หมายเหตุ: ปัจจุบันเปิดให้กรอก “โทรศัพท์” แบบฟรีเท็กซ์ในการนำเข้า
     * จึงไม่ทำการแก้/เติมเลขให้อัตโนมัติแล้ว (คืนค่าเป็นข้อความเดิมแบบ trim เท่านั้น)
     */
    public function normalizePhoneString($phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }
        return trim((string) $phone);
    }

    public function isValidPhone(string $phone): bool
    {
        // เปิดให้เป็น free text จึงไม่ validate รูปแบบแล้ว
        return true;
    }

    /**
     * Reset state when starting a new file (clear codes in file).
     */
    public function reset(): void
    {
        $this->codesInFile = [];
    }
}
