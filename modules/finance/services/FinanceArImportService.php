<?php

namespace app\modules\finance\services;

use app\components\AppHelper;
use app\modules\finance\models\FinanceArFund;
use app\modules\finance\models\FinanceArImportBatch;
use app\modules\finance\models\FinanceArInvoice;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

/**
 * นำเข้าลูกหนี้ค่ารักษาจาก Excel (แม่แบบของ ERP — ผู้ใช้ map ข้อมูลจาก HIS มาลงแม่แบบ)
 *
 * แม่แบบ (แถว 1 = หัวตาราง, ข้อมูลเริ่มแถว 2):
 *   A รหัสสิทธิ | B เดือน(1-12) | C วันที่บริการ | D HN | E ชื่อผู้ป่วย | F เลขอ้างอิง | G ยอดตั้งเบิก
 */
class FinanceArImportService
{
    public const HEADERS = ['รหัสสิทธิ', 'เดือน(1-12)', 'วันที่บริการ', 'HN', 'ชื่อผู้ป่วย', 'เลขอ้างอิง/เลขเคลม', 'ยอดตั้งเบิก'];
    private const FIRST_DATA_ROW = 2;
    private const MAX_ROWS = 20000;

    /**
     * @return array{batch:?FinanceArImportBatch, imported:int, errors:string[]}
     */
    public static function import(string $path, int $fiscalYear, ?string $sourceLabel, string $fileName): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($path)->getActiveSheet();
        $highestRow = min($sheet->getHighestDataRow(), self::MAX_ROWS);

        $codeMap = FinanceArFund::codeMap(); // [code => id]
        $errors = [];
        $parsed = [];
        $total = 0.0;

        for ($r = self::FIRST_DATA_ROW; $r <= $highestRow; $r++) {
            $code = trim((string) $sheet->getCell('A' . $r)->getValue());
            $amountRaw = $sheet->getCell('G' . $r)->getValue();
            if ($code === '' && ($amountRaw === null || $amountRaw === '')) {
                continue; // แถวว่าง
            }
            if ($code === '' || !isset($codeMap[$code])) {
                $errors[] = "แถว $r: ไม่พบรหัสสิทธิ \"$code\"";
                continue;
            }
            $amount = (float) str_replace([',', ' '], '', (string) $amountRaw);
            if ($amount <= 0) {
                $errors[] = "แถว $r: ยอดตั้งเบิกไม่ถูกต้อง";
                continue;
            }
            $month = (int) $sheet->getCell('B' . $r)->getValue();
            $parsed[] = [
                'ar_fund_id' => (int) $codeMap[$code],
                'period_month' => ($month >= 1 && $month <= 12) ? $month : null,
                'service_date' => self::parseDate($sheet->getCell('C' . $r)),
                'hn' => trim((string) $sheet->getCell('D' . $r)->getValue()) ?: null,
                'patient_name' => trim((string) $sheet->getCell('E' . $r)->getValue()) ?: null,
                'doc_no' => trim((string) $sheet->getCell('F' . $r)->getValue()) ?: null,
                'billed_amount' => $amount,
            ];
            $total += $amount;
        }

        if (!$parsed) {
            return ['batch' => null, 'imported' => 0, 'errors' => $errors ?: ['ไม่พบข้อมูลในไฟล์']];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $batch = new FinanceArImportBatch([
                'fiscal_year' => $fiscalYear,
                'source_label' => $sourceLabel,
                'file_name' => $fileName,
                'row_count' => count($parsed),
                'total_amount' => $total,
            ]);
            $batch->save(false);

            $imported = 0;
            foreach ($parsed as $row) {
                $inv = new FinanceArInvoice($row);
                $inv->fiscal_year = $fiscalYear;
                $inv->import_batch_id = $batch->id;
                $inv->status = FinanceArInvoice::STATUS_BILLED;
                if ($inv->save()) {
                    $imported++;
                } else {
                    $errors[] = 'บันทึกไม่สำเร็จ: ' . implode(' ', $inv->getFirstErrors());
                }
            }
            $tx->commit();
            return ['batch' => $batch, 'imported' => $imported, 'errors' => $errors];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['batch' => null, 'imported' => 0, 'errors' => ['เกิดข้อผิดพลาด: ' . $e->getMessage()]];
        }
    }

    private static function parseDate($cell): ?string
    {
        $val = $cell->getValue();
        if ($val === null || $val === '') {
            return null;
        }
        if (is_numeric($val)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $val)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        $db = AppHelper::normalizeDateToDb((string) $val);
        return $db ?: null;
    }

    /** ไฟล์แม่แบบเปล่า */
    public static function templateSpreadsheet(): Spreadsheet
    {
        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('ลูกหนี้');
        $col = 'A';
        foreach (self::HEADERS as $h) {
            $s->setCellValue($col . '1', $h);
            $s->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $s->getStyle('A1:G1')->getFont()->setBold(true);
        // ตัวอย่าง 1 แถว
        $s->fromArray([['UC', 8, '31/08/2569', '000123', 'ตัวอย่าง ผู้ป่วย', 'REP-2569-08', 15000]], null, 'A2');

        $funds = FinanceArFund::find()->orderBy(['sort_order' => SORT_ASC])->all();
        $note = $book->createSheet();
        $note->setTitle('รหัสสิทธิ');
        $note->setCellValue('A1', 'รหัส');
        $note->setCellValue('B1', 'ชื่อสิทธิ');
        $rr = 2;
        foreach ($funds as $f) {
            $note->setCellValue('A' . $rr, $f->code);
            $note->setCellValue('B' . $rr, $f->name);
            $rr++;
        }
        $note->getColumnDimension('A')->setAutoSize(true);
        $note->getColumnDimension('B')->setAutoSize(true);
        return $book;
    }

    public static function writeXlsx(Spreadsheet $book, string $fileName)
    {
        $path = Yii::getAlias('@runtime') . '/ar_' . date('YmdHis') . '.xlsx';
        (new Xlsx($book))->save($path);
        return Yii::$app->response
            ->sendFile($path, $fileName, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($path) {
                @unlink($path);
            });
    }
}
