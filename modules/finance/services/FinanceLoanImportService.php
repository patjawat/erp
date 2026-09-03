<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanAccount;
use app\modules\finance\models\FinanceLoanItem;
use app\modules\finance\models\FinanceLoanItemKind;
use app\modules\finance\models\FinanceLoanSettlement;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Yii;

/**
 * นำเข้าทะเบียนคุมลูกหนี้เงินยืมจากไฟล์ Excel เดิม
 *
 * ไฟล์ต้นทางเป็นทะเบียนแบบแบน หนึ่งแถวต่อหนึ่งสัญญา และมียอดรวมสี่ก้อนกับการส่งใช้
 * ชุดเดียว ส่วนระบบใหม่แยกเป็นหัวสัญญากับตารางลูก ตัวนำเข้าจึงต้องกระจายข้อมูล
 * หนึ่งแถวออกไปเป็นสามตาราง คือหัวสัญญา บรรทัดประมาณการ และรายการส่งใช้
 *
 * สิ่งที่ไฟล์เดิมไม่มีและระบบใหม่ต้องการ (วันที่ดำเนินการเสร็จ) จะปล่อยว่างไว้
 * แล้วตั้ง due_is_manual เพื่อยึดวันครบกำหนดตามที่ทะเบียนเดิมบันทึกไว้ ไม่ให้ระบบ
 * คำนวณทับด้วยกติกาปัจจุบัน — ข้อมูลย้อนหลังต้องตรงกับกระดาษที่ออกไปแล้ว
 */
class FinanceLoanImportService
{
    /** กันไฟล์ผิดประเภทที่มีแถวมหาศาล แต่ยังรองรับทะเบียนจริงที่มีหลายพันแถว */
    public const MAX_ROWS = 5000;

    /** แถว 1-5 เป็นหัวตารางของแบบฟอร์มทะเบียนเดิม ข้อมูลจริงเริ่มแถวที่ 6 */
    private const FIRST_DATA_ROW = 6;

    /** คอลัมน์ยอดสี่ก้อนในทะเบียนเดิม → ช่องรวมยอดของระบบใหม่ */
    private const AMOUNT_COLUMNS = [
        'J' => ['column' => FinanceLoanItemKind::COL_ALLOWANCE, 'label' => 'ค่าเบี้ยเลี้ยง'],
        'K' => ['column' => FinanceLoanItemKind::COL_ACCOMMODATION, 'label' => 'ค่าที่พัก'],
        'L' => ['column' => FinanceLoanItemKind::COL_TRANSPORT, 'label' => 'ค่าพาหนะ'],
        'M' => ['column' => FinanceLoanItemKind::COL_OTHER, 'label' => 'ค่าใช้จ่ายอื่น ๆ'],
    ];

    /** @var array<string,int> register_column => id ของประเภทรายการที่จะใช้ */
    private array $kindByColumn = [];

    /** @var array<string,int> ชื่อ/เลขบัญชี => id */
    private array $accounts = [];

    public function __construct()
    {
        foreach (FinanceLoanItemKind::find()->orderBy(['sort_order' => SORT_ASC])->all() as $kind) {
            // ใช้ตัวแรกของแต่ละช่อง เพราะทะเบียนเดิมมีแค่ยอดรวมต่อช่อง ไม่ได้แยกรายการ
            // ชื่อที่พิมพ์ออกมาใช้ label ของบรรทัดแทน จึงไม่เพี้ยนจากต้นฉบับ
            $this->kindByColumn[$kind->register_column] ??= (int) $kind->id;
        }
        foreach (FinanceLoanAccount::find()->all() as $account) {
            $this->accounts[$this->normalize($account->account_no)] = (int) $account->id;
            $this->accounts[$this->normalize($account->name)] = (int) $account->id;
        }
    }

    /**
     * อ่านไฟล์แล้วคืนผลตรวจสอบรายแถว โดยยังไม่บันทึกลงฐานข้อมูล
     */
    public function preview(string $path, string $originalName, int $fiscalYear, ?string $sheetName = null): array
    {
        $resolvedSheet = $this->resolveSheetName($path, $fiscalYear, $sheetName);
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly([$resolvedSheet]);
        }
        $book = $reader->load($path);
        $sheet = $book->getSheetByName($resolvedSheet);
        if (!$sheet) {
            throw new \RuntimeException('ไม่พบแท็บ “' . $resolvedSheet . '” ในไฟล์');
        }
        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow > self::MAX_ROWS) {
            throw new \RuntimeException('ไฟล์มีข้อมูล ' . number_format($highestRow) . ' แถว เกินขีดจำกัด '
                . number_format(self::MAX_ROWS) . ' แถว กรุณาตรวจสอบว่าเลือกไฟล์ทะเบียนถูกต้อง');
        }

        $fileHash = hash_file('sha256', $path);
        $existing = $this->existingContracts($sheet, $highestRow);
        $rows = [];
        $seen = [];

        for ($row = self::FIRST_DATA_ROW; $row <= $highestRow; $row++) {
            $contractNo = $this->text($sheet, 'C', $row);
            if ($contractNo === '') {
                continue;
            }
            $parsed = $this->parseRow($sheet, $row, $fiscalYear, $fileHash, $originalName);
            $errors = $this->validateRow($parsed, $sheet, $row);

            if (isset($seen[$contractNo])) {
                $errors[] = 'เลขที่สัญญาซ้ำในไฟล์';
            }
            if (isset($existing[$contractNo])) {
                $errors[] = 'มีเลขที่สัญญานี้ในระบบแล้ว';
            }
            $seen[$contractNo] = true;
            $rows[] = ['data' => $parsed, 'errors' => $errors];
        }

        return [
            'sheet' => $sheet->getTitle(),
            'fiscal_year' => $fiscalYear,
            'file_name' => $originalName,
            'file_hash' => $fileHash,
            'rows' => $rows,
            'valid' => count(array_filter($rows, static fn($item) => !$item['errors'])),
            'invalid' => count(array_filter($rows, static fn($item) => $item['errors'])),
        ];
    }

    /** บันทึกเฉพาะแถวที่ผ่านการตรวจสอบ ทั้งหมดอยู่ในธุรกรรมเดียว */
    public function save(array $preview, string $batch): array
    {
        $transaction = Yii::$app->db->beginTransaction();
        $saved = 0;
        $settlements = 0;
        try {
            foreach ($preview['rows'] as $item) {
                if ($item['errors']) {
                    continue;
                }
                $data = $item['data'];
                $loan = new FinanceLoan();
                $loan->setAttributes($data['loan'], false);
                $loan->import_batch = $batch;
                if (!$loan->save(false)) {
                    throw new \RuntimeException('แถวที่ ' . $data['import_row'] . ' (' . $data['loan']['contract_no'] . '): บันทึกหัวสัญญาไม่สำเร็จ');
                }

                foreach ($data['items'] as $index => $line) {
                    $itemModel = new FinanceLoanItem();
                    $itemModel->setAttributes($line, false);
                    $itemModel->loan_id = $loan->id;
                    $itemModel->sort_order = $index * 10;
                    if (!$itemModel->save(false)) {
                        throw new \RuntimeException('แถวที่ ' . $data['import_row'] . ': บันทึกบรรทัดประมาณการไม่สำเร็จ');
                    }
                }

                if ($data['settlement']) {
                    $settlement = new FinanceLoanSettlement();
                    $settlement->setAttributes($data['settlement'], false);
                    $settlement->loan_id = $loan->id;
                    $settlement->seq = 1;
                    if (!$settlement->save(false)) {
                        throw new \RuntimeException('แถวที่ ' . $data['import_row'] . ': บันทึกรายการส่งใช้ไม่สำเร็จ');
                    }
                    $settlements++;
                }

                // คำนวณยอดสรุปและยอดคงค้างจากตารางลูก ไม่ใช้ยอดในไฟล์ตรง ๆ
                // เพื่อให้ข้อมูลที่นำเข้ามีความสอดคล้องแบบเดียวกับที่บันทึกผ่านหน้าจอ
                (new FinanceLoanSettlementService())->refresh($loan);
                $saved++;
            }
            $transaction->commit();
            return ['success' => true, 'saved' => $saved, 'settlements' => $settlements, 'error' => null];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'saved' => 0, 'settlements' => 0, 'error' => $e->getMessage()];
        }
    }

    // ── อ่านหนึ่งแถว ─────────────────────────────────────────────

    private function parseRow($sheet, int $row, int $fiscalYear, string $fileHash, string $originalName): array
    {
        $legacyStatus = $this->text($sheet, 'B', $row);
        $contractNo = $this->text($sheet, 'C', $row);
        $borrowedAt = $this->dateValue($sheet->getCell("D{$row}")->getValue());
        $dueAt = $this->dateValue($sheet->getCell("O{$row}")->getValue());

        $amounts = [];
        foreach (self::AMOUNT_COLUMNS as $column => $meta) {
            $amounts[$column] = $this->number($sheet->getCell($column . $row)->getCalculatedValue());
        }
        $approved = $this->number($sheet->getCell("N{$row}")->getCalculatedValue());
        $voucher = $this->number($sheet->getCell("R{$row}")->getCalculatedValue());
        $cash = $this->number($sheet->getCell("S{$row}")->getCalculatedValue());
        $outstanding = $this->number($sheet->getCell("T{$row}")->getCalculatedValue());
        $settledAt = $this->dateValue($sheet->getCell("P{$row}")->getValue());
        $fundingSource = $this->text($sheet, 'W', $row);

        $loan = [
            'contract_no' => $contractNo,
            'contract_seq' => FinanceLoan::parseSeq($contractNo),
            'fiscal_year' => $fiscalYear,
            'status' => FinanceLoan::fromLegacyStatus($legacyStatus),
            'account_id' => $this->matchAccount($fundingSource),
            'borrower_name' => $this->text($sheet, 'G', $row),
            'borrower_position' => $this->text($sheet, 'H', $row) ?: null,
            'purpose' => $this->text($sheet, 'I', $row),
            'request_document_no' => $this->text($sheet, 'F', $row) ?: null,
            'borrowed_at' => $borrowedAt,
            'received_at' => $this->dateValue($sheet->getCell("E{$row}")->getValue()),
            // ทะเบียนเดิมไม่มีวันที่ดำเนินการเสร็จ จึงยึดวันครบกำหนดตามที่บันทึกไว้
            'activity_end_at' => null,
            'due_at' => $dueAt,
            'due_is_manual' => true,
            'evidence_sent_at' => $this->dateValue($sheet->getCell("U{$row}")->getValue()),
            'disbursement_document_no' => $this->text($sheet, 'V', $row) ?: null,
            'source_ref_type' => 'import',
            'source_event_key' => 'loan-import:' . $fileHash . ':' . $row,
            'source_ref_id' => substr($fileHash, 0, 16),
            'import_row' => $row,
            'note' => $fundingSource !== '' && !$this->matchAccount($fundingSource)
                ? 'แหล่งเงินในทะเบียนเดิม: ' . $fundingSource
                : null,
        ];

        return [
            'import_row' => $row,
            'legacy_status' => $legacyStatus,
            'loan' => $loan,
            'items' => $this->buildItems($amounts, $approved),
            'settlement' => $this->buildSettlement($sheet, $row, $voucher, $cash, $settledAt),
            'legacy' => [
                'approved' => $approved,
                'voucher' => $voucher,
                'cash' => $cash,
                'outstanding' => $outstanding,
                'detail_total' => array_sum($amounts),
                'funding_source' => $fundingSource,
            ],
        ];
    }

    /**
     * แปลงยอดสี่ก้อนเป็นบรรทัดประมาณการ
     *
     * ถ้าทะเบียนเดิมกรอกแต่ยอดรวมโดยไม่แยกสี่ช่อง (พบบ่อยในใบยืมที่ไม่ใช่การเดินทาง
     * เช่น ค่าตอบแทน ฉ.11) จะสร้างบรรทัดเดียวด้วยยอดรวมแทน ไม่ใช่ปฏิเสธทั้งแถว
     * เพราะข้อมูลย้อนหลังแก้ไม่ได้แล้ว และยอดรวมยังถูกต้องอยู่
     */
    private function buildItems(array $amounts, float $approved): array
    {
        $items = [];
        foreach (self::AMOUNT_COLUMNS as $column => $meta) {
            if ($amounts[$column] <= 0) {
                continue;
            }
            $items[] = [
                'item_kind_id' => $this->kindByColumn[$meta['column']] ?? null,
                'label' => $meta['label'],
                'amount' => $amounts[$column],
            ];
        }
        if (!$items && $approved > 0) {
            $items[] = [
                'item_kind_id' => $this->kindByColumn[FinanceLoanItemKind::COL_OTHER] ?? null,
                'label' => 'ค่าใช้จ่ายตามสัญญายืมเงิน',
                'amount' => $approved,
            ];
        }
        return $items;
    }

    private function buildSettlement($sheet, int $row, float $voucher, float $cash, ?string $settledAt): ?array
    {
        if ($voucher + $cash <= 0) {
            return null;
        }
        return [
            // ทะเบียนเดิมบางแถวมียอดส่งใช้แต่ไม่ได้ลงวันที่ ใช้วันครบกำหนดแทนเพื่อให้บันทึกได้
            'settled_at' => $settledAt ?: $this->dateValue($sheet->getCell("O{$row}")->getValue()),
            'voucher_amount' => $voucher,
            'cash_amount' => $cash,
            'document_no' => $this->text($sheet, 'Q', $row) ?: null,
            'receipt_no' => $this->text($sheet, 'V', $row) ?: null,
            'evidence_sent_at' => $this->dateValue($sheet->getCell("U{$row}")->getValue()),
            'note' => 'นำเข้าจากทะเบียนเดิม',
        ];
    }

    // ── ตรวจความถูกต้อง ─────────────────────────────────────────

    private function validateRow(array $parsed, $sheet, int $row): array
    {
        $errors = [];
        $loan = $parsed['loan'];
        $legacy = $parsed['legacy'];

        if ($parsed['legacy_status'] === '' || !in_array($parsed['legacy_status'], FinanceLoan::statusOptions(), true)) {
            $errors[] = 'สถานะไม่อยู่ในรายการที่รองรับ';
        }
        if ($loan['borrower_name'] === '') {
            $errors[] = 'ไม่พบชื่อผู้ยืม';
        }
        if ($loan['purpose'] === '') {
            $errors[] = 'ไม่พบรายการ/วัตถุประสงค์';
        }
        if (!$loan['borrowed_at']) {
            $errors[] = 'วันที่ยืมไม่ถูกต้อง';
        }
        foreach (['E' => 'วันที่รับเงิน', 'O' => 'วันครบกำหนด', 'P' => 'วันที่ส่งใช้', 'U' => 'วันที่ส่งหลักฐาน'] as $column => $label) {
            $raw = $sheet->getCell($column . $row)->getValue();
            if ($raw !== null && $raw !== '' && !$this->dateValue($raw)) {
                $errors[] = $label . 'ไม่ถูกต้อง';
            }
        }
        if ($legacy['approved'] <= 0) {
            $errors[] = 'ไม่พบจำนวนเงินที่ยืม';
        }
        // ยอมให้ไฟล์กรอกแต่ยอดรวมได้ แต่ถ้าแยกสี่ช่องแล้วต้องบวกได้เท่ายอดรวม
        if ($legacy['detail_total'] > 0 && abs($legacy['detail_total'] - $legacy['approved']) > 0.01) {
            $errors[] = 'ยอดแยกรายการ (' . number_format($legacy['detail_total'], 2) . ') ไม่เท่ากับยอดยืม (' . number_format($legacy['approved'], 2) . ')';
        }
        if (abs($legacy['approved'] - $legacy['voucher'] - $legacy['cash'] - $legacy['outstanding']) > 0.01) {
            $errors[] = 'ยอดส่งใช้ไม่สมดุลกับยอดคงเหลือในไฟล์';
        }
        foreach (['received_at' => 'วันที่รับเงิน', 'due_at' => 'วันครบกำหนด', 'evidence_sent_at' => 'วันที่ส่งหลักฐาน'] as $key => $label) {
            if ($loan[$key] && $loan['borrowed_at'] && $loan[$key] < $loan['borrowed_at']) {
                $errors[] = $label . 'อยู่ก่อนวันที่ยืม';
            }
        }
        if ($parsed['settlement'] && !$parsed['settlement']['settled_at']) {
            $errors[] = 'มียอดส่งใช้แต่ไม่มีวันที่ส่งใช้และไม่มีวันครบกำหนดให้ใช้แทน';
        }
        if (in_array($loan['status'], [FinanceLoan::STATUS_CLEARED, FinanceLoan::STATUS_COMPLETED], true) && $legacy['outstanding'] > 0.01) {
            $errors[] = 'สถานะปิดแล้วแต่ยังมียอดคงเหลือ';
        }
        return $errors;
    }

    // ── ตัวช่วย ──────────────────────────────────────────────────

    private function existingContracts($sheet, int $highestRow): array
    {
        $numbers = [];
        for ($row = self::FIRST_DATA_ROW; $row <= $highestRow; $row++) {
            $number = $this->text($sheet, 'C', $row);
            if ($number !== '') {
                $numbers[] = $number;
            }
        }
        $existing = [];
        foreach (array_chunk(array_values(array_unique($numbers)), 500) as $chunk) {
            foreach (FinanceLoan::find()->select('contract_no')->where(['contract_no' => $chunk])->column() as $contractNo) {
                $existing[$contractNo] = true;
            }
        }
        return $existing;
    }

    /** จับคู่ข้อความแหล่งเงินในไฟล์กับบัญชีที่ตั้งค่าไว้ */
    private function matchAccount(string $text): ?int
    {
        $key = $this->normalize($text);
        if ($key === '') {
            return null;
        }
        if (isset($this->accounts[$key])) {
            return $this->accounts[$key];
        }
        foreach ($this->accounts as $candidate => $id) {
            if ($candidate !== '' && (str_contains($key, $candidate) || str_contains($candidate, $key))) {
                return $id;
            }
        }
        return null;
    }

    private function normalize(?string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }

    private function text($sheet, string $column, int $row): string
    {
        return trim((string) $sheet->getCell($column . $row)->getFormattedValue());
    }

    /** ชื่อแท็บที่จะอ่าน: ใช้ที่ผู้ใช้ระบุก่อน ถ้าไม่ระบุจึงเดาจากปีงบประมาณ */
    private function resolveSheetName(string $path, int $fiscalYear, ?string $sheetName): string
    {
        $names = IOFactory::createReaderForFile($path)->listWorksheetNames($path);
        if (!$names) {
            throw new \RuntimeException('ไฟล์นี้ไม่มีแท็บข้อมูล');
        }
        $available = ' · แท็บที่มีในไฟล์: ' . implode(', ', array_map(static fn($name) => '“' . $name . '”', $names));
        $requested = trim((string) $sheetName);
        if ($requested !== '') {
            foreach ($names as $name) {
                if (mb_strtolower(trim($name)) === mb_strtolower($requested)) {
                    return $name;
                }
            }
            throw new \RuntimeException('ไม่พบแท็บ “' . $requested . '” ในไฟล์' . $available);
        }
        $year = (string) $fiscalYear;
        foreach ($names as $name) {
            if (trim($name) === $year) {
                return $name;
            }
        }
        foreach ($names as $name) {
            if (mb_strpos($name, $year) !== false) {
                return $name;
            }
        }
        if (count($names) === 1) {
            return $names[0];
        }
        throw new \RuntimeException('ไม่พบแท็บของปีงบประมาณ ' . $year . ' กรุณาระบุชื่อแท็บให้ชัดเจน' . $available);
    }

    private function number($value): float
    {
        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    private function dateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }
        $text = trim((string) $value);
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $text, $m)) {
            $year = (int) $m[3];
            if ($year > 2400) {
                $year -= 543;
            } elseif ($year < 100) {
                $year += 1957;
            }
            if (!checkdate((int) $m[2], (int) $m[1], $year)) {
                return null;
            }
            return sprintf('%04d-%02d-%02d', $year, $m[2], $m[1]);
        }
        return null;
    }
}
