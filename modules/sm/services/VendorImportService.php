<?php

namespace app\modules\sm\services;

use Yii;
use yii\helpers\Json;
use app\modules\sm\models\Vendor;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Parse CSV/Excel, validate, bulk insert with transaction.
 * UTF-8, trim all fields. vendor_code unique.
 */
class VendorImportService
{
    public const TEMPLATE_HEADERS = [
        'vendor_code',
        'vendor_name',
        'contact_name',
        'phone',
        'email',
        'address',
        'tax_id',
        'status',
        'account_name',
        'account_number',
        'bank_name',
        'contact_position',
        'fax',
    ];

    /**
     * แสดงชื่อคอลัมน์ในไฟล์ Template (ภาษาไทย)
     * key ต้องตรงกับ TEMPLATE_HEADERS
     */
    public const TEMPLATE_HEADER_LABELS_TH = [
        'vendor_code' => 'รหัสตัวแทนจำหน่าย',
        'vendor_name' => 'ชื่อผู้แทนจำหน่าย',
        'contact_name' => 'ชื่อผู้ติดต่อ',
        'phone' => 'โทรศัพท์',
        'email' => 'อีเมล',
        'address' => 'ที่อยู่',
        'tax_id' => 'เลขประจำตัวผู้เสียภาษี',
        'status' => 'สถานะ (active/inactive)',
        'account_name' => 'ชื่อบัญชี',
        'account_number' => 'เลขบัญชี',
        'bank_name' => 'ชื่อธนาคาร',
        'contact_position' => 'ตำแหน่งผู้ติดต่อ',
        'fax' => 'แฟกซ์',
    ];

    /**
     * รองรับหัวคอลัมน์หลายแบบ (ไทย/อังกฤษ) → map ให้เป็น key มาตรฐานในระบบ
     * หมายเหตุ: normalizeHeaders() จะ normalize ก่อน map
     */
    private const HEADER_ALIASES = [
        // English
        'vendor_code' => 'vendor_code',
        'vendor_name' => 'vendor_name',
        'contact_name' => 'contact_name',
        'contact_position' => 'contact_position',
        'phone' => 'phone',
        'email' => 'email',
        'address' => 'address',
        'tax_id' => 'tax_id',
        'status' => 'status',
        'account_name' => 'account_name',
        'account_number' => 'account_number',
        'bank_name' => 'bank_name',
        'fax' => 'fax',

        // Thai (normalized)
        'รหัสตัวแทนจำหน่าย' => 'vendor_code',
        'ชื่อผู้แทนจำหน่าย' => 'vendor_name',
        'ชื่อผู้ติดต่อ' => 'contact_name',
        'ตำแหน่งผู้ติดต่อ' => 'contact_position',
        'โทรศัพท์' => 'phone',
        'เบอร์โทร' => 'phone',
        'อีเมล' => 'email',
        'email' => 'email',
        'ที่อยู่' => 'address',
        'เลขประจำตัวผู้เสียภาษี' => 'tax_id',
        'เลขผู้เสียภาษี' => 'tax_id',
        'สถานะ_(active/inactive)' => 'status',
        'สถานะ_(active_inactive)' => 'status',
        'สถานะ' => 'status',
        'ชื่อบัญชี' => 'account_name',
        'เลขบัญชี' => 'account_number',
        'ชื่อธนาคาร' => 'bank_name',
        'แฟกซ์' => 'fax',
        'fax' => 'fax',
    ];

    /** @var VendorImportValidationService */
    private $validationService;

    public function __construct(?VendorImportValidationService $validationService = null)
    {
        $this->validationService = $validationService ?? new VendorImportValidationService();
    }

    /**
     * Parse file (csv or xlsx) and return rows as array of associative arrays.
     * First row = headers (normalized to lowercase with underscores).
     * @param string $filePath
     * @return array{headers: array, rows: array<int, array>}
     */
    public function parseFile(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            return $this->parseCsv($filePath);
        }
        if ($ext === 'xlsx' || $ext === 'xls') {
            return $this->parseExcel($filePath);
        }
        throw new \InvalidArgumentException('รองรับเฉพาะไฟล์ .csv และ .xlsx');
    }

    private function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('ไม่สามารถเปิดไฟล์ได้');
        }
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        $headerRow = fgetcsv($handle, 0, ',');
        if ($headerRow === false) {
            fclose($handle);
            return ['headers' => [], 'rows' => []];
        }
        $headers = $this->normalizeHeaders(array_map('trim', $headerRow));
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $row = [];
            foreach ($headers as $i => $key) {
                $row[$key] = isset($data[$i]) ? trim((string) $data[$i]) : '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return ['headers' => $headers, 'rows' => $rows];
    }

    private function parseExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        $headerRow = [];
        for ($c = 1; $c <= $colIndex; $c++) {
            $val = $sheet->getCell(Coordinate::stringFromColumnIndex($c) . '1')->getValue();
            $headerRow[] = is_string($val) ? trim($val) : (string) $val;
        }
        $headers = $this->normalizeHeaders($headerRow);
        $rows = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $row = [];
            for ($c = 0; $c < count($headers); $c++) {
                $key = $headers[$c];
                $val = $sheet->getCell(Coordinate::stringFromColumnIndex($c + 1) . $r)->getValue();
                $row[$key] = $val !== null ? trim((string) $val) : '';
            }
            $rows[] = $row;
        }
        return ['headers' => $headers, 'rows' => $rows];
    }

    private function normalizeHeaders(array $headerRow): array
    {
        $out = [];
        foreach ($headerRow as $h) {
            $normalized = strtolower(preg_replace('/\s+/', '_', trim((string) $h)));
            $key = $normalized ?: 'col_' . count($out);
            $out[] = self::HEADER_ALIASES[$key] ?? $key;
        }
        return $out;
    }

    /**
     * Validate all rows. Returns structure for frontend: each row has errors array and status.
     * @param array $rows
     * @return array{valid: int, error: int, rows: array<int, array{row: array, rowNumber: int, errors: array, valid: bool}>}
     */
    public function validateRows(array $rows): array
    {
        $this->validationService->reset();
        $result = ['valid' => 0, 'error' => 0, 'rows' => []];
        $rowNumber = 1;
        foreach ($rows as $row) {
            $rowNumber++;
            $errors = $this->validationService->validateRow($row, $rowNumber);
            $valid = empty($errors);
            if ($valid) {
                $result['valid']++;
            } else {
                $result['error']++;
            }
            $result['rows'][] = [
                'row' => $row,
                'rowNumber' => $rowNumber,
                'errors' => $errors,
                'valid' => $valid,
            ];
        }
        return $result;
    }

    /**
     * Import only valid rows inside a DB transaction. Rollback on any failure.
     * @param array $validatedRows from validateRows()['rows'], only rows with valid=true
     * @return array{imported: int, message: string}
     */
    public function import(array $validatedRows): array
    {
        $toInsert = [];
        foreach ($validatedRows as $item) {
            if (empty($item['valid']) || !empty($item['errors'])) {
                continue;
            }
            $row = $item['row'];
            $code = trim((string) ($row['vendor_code'] ?? ''));
            $title = trim((string) ($row['vendor_name'] ?? ''));
            $status = $this->normalizeStatus($row['status'] ?? 'active');
            $dataJson = [
                'tax_id' => trim((string) ($row['tax_id'] ?? '')),
                'address' => trim((string) ($row['address'] ?? '')),
                'contact_name' => trim((string) ($row['contact_name'] ?? '')),
                'contact_position' => trim((string) ($row['contact_position'] ?? '')),
                'phone' => trim((string) ($row['phone'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'fax' => trim((string) ($row['fax'] ?? '')),
                'account_name' => trim((string) ($row['account_name'] ?? '')),
                'account_number' => trim((string) ($row['account_number'] ?? '')),
                'bank_name' => trim((string) ($row['bank_name'] ?? '')),
            ];
            $toInsert[] = [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'name' => 'vendor',
                'code' => $code,
                'title' => $title,
                'data_json' => $dataJson,
                'active' => $status,
            ];
        }

        $imported = 0;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            foreach ($toInsert as $attrs) {
                $dataJson = $attrs['data_json'];
                unset($attrs['data_json']);
                $model = new Vendor();
                $model->setAttributes($attrs, false);
                $model->data_json = is_array($dataJson) ? Json::encode($dataJson) : $dataJson;
                $model->save(false);
                $imported++;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            'imported' => $imported,
            'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} รายการ",
        ];
    }

    private function normalizeStatus($value): int
    {
        $v = strtolower(trim((string) $value));
        if ($v === '0' || $v === 'inactive' || $v === 'ไม่ใช้งาน') {
            return 0;
        }
        return 1;
    }

    /**
     * Generate CSV template content (with BOM for UTF-8).
     */
    public function generateCsvTemplate(): string
    {
        $bom = "\xEF\xBB\xBF";
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $bom);
        $thHeaders = array_map(function ($key) {
            return self::TEMPLATE_HEADER_LABELS_TH[$key] ?? $key;
        }, self::TEMPLATE_HEADERS);
        fputcsv($fp, $thHeaders);
        fputcsv($fp, [
            'V001',
            'บริษัท ตัวอย่าง จำกัด',
            'คุณสมชาย',
            '02-1234567',
            'contact@example.com',
            '123 ถ.สุขุมวิท กทม.',
            '1234567890123',
            'active',
            'บัญชีตัวอย่าง',
            '123-4-56789-0',
            'ธนาคารกรุงเทพ',
            'ผู้จัดการ',
            '02-1234568',
        ]);
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }
}
