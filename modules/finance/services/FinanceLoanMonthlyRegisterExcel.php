<?php

namespace app\modules\finance\services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\helpers\FileHelper;

/**
 * เขียนทะเบียนคุมเงินยืมรายเดือนเป็น .xlsx หน้าตาเดียวกับชีตรายเดือนที่งานการเงินใช้อยู่
 * (คอลัมน์ A–J ฟอนต์ AngsanaUPC แนวนอน A4) เพื่อให้วางต่อในไฟล์เดิมหรือส่งต่อได้ทันที
 */
class FinanceLoanMonthlyRegisterExcel
{
    private const FONT = 'AngsanaUPC';

    /** @return string path ของไฟล์ชั่วคราว ผู้เรียกลบทิ้งหลังส่ง */
    public static function write(array $report, array $site, array $signers): string
    {
        $book = new Spreadsheet();
        $ws = $book->getActiveSheet();
        $ws->setTitle(self::sheetTitle($report['month']));
        $book->getDefaultStyle()->getFont()->setName(self::FONT)->setSize(12);

        foreach (['A' => 9.4, 'B' => 10, 'C' => 45, 'D' => 11.5, 'E' => 11, 'F' => 18, 'G' => 11, 'H' => 10, 'I' => 11, 'J' => 11.5] as $col => $width) {
            $ws->getColumnDimension($col)->setWidth($width);
        }

        $company = trim((string) ($site['company_name'] ?? ''));
        $province = trim((string) ($site['province'] ?? ''));
        if ($province !== '' && mb_strpos($province, 'จังหวัด') !== 0) {
            $province = 'จังหวัด' . $province;
        }
        $ws->setCellValue('A1', 'ส่วนราชการ ' . trim($company . ' ' . $province));
        $ws->setCellValue('A2', 'ทะเบียนคุมเอกสารแทนตัวเงิน สัญญารับรองการยืมเงิน ประจำเดือน ' . FinanceLoanMonthlyRegister::monthLabel($report['month']));
        foreach (['A1:J1', 'A2:J2'] as $range) {
            $ws->mergeCells($range);
            $ws->getStyle($range)->getFont()->setBold(true);
            $ws->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $headers = ['วัน/เดือน/ปี ที่ยืม', 'เลขที่เอกสาร', 'รายการ', 'ยอดคงเหลือยกมา', 'จำนวนเงิน', 'ชื่อผู้ยืม',
            'วันครบกำหนดส่งคืน', 'วันที่ส่งคืน', 'จำนวนเงิน', 'ลูกหนี้คงเหลือ'];
        $ws->fromArray($headers, null, 'A3');
        $ws->getStyle('A3:J3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $r = 4;
        foreach ($report['rows'] as $row) {
            $loan = $row['loan'];
            $ws->setCellValue("A{$r}", FinanceLoanMonthlyRegister::shortDate($loan->borrowed_at));
            $ws->setCellValue("B{$r}", (string) $loan->contract_no);
            $ws->setCellValue("C{$r}", (string) $loan->purpose);
            if ($row['brought'] != 0) {
                $ws->setCellValue("D{$r}", $row['brought']);
            }
            if ($row['borrowed'] != 0) {
                $ws->setCellValue("E{$r}", $row['borrowed']);
            }
            $ws->setCellValue("F{$r}", (string) $loan->borrower_name);
            $ws->setCellValue("G{$r}", FinanceLoanMonthlyRegister::shortDate($loan->due_at));
            $ws->setCellValue("H{$r}", implode("\n", array_map([FinanceLoanMonthlyRegister::class, 'shortDate'], $row['return_dates'])));
            if ($row['returned'] != 0) {
                $ws->setCellValue("I{$r}", $row['returned']);
            }
            // คงสูตรเดิมของไฟล์ไว้ ผู้ใช้แก้ตัวเลขใน Excel ต่อแล้วยอดยังคำนวณตาม
            $ws->setCellValue("J{$r}", "=D{$r}+E{$r}-I{$r}");
            $r++;
        }
        $last = max(3, $r - 1);
        $ws->getStyle("A4:J{$last}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $ws->getStyle("A4:B{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("G4:H{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $monthRow = $r;
        $ytdRow = $r + 1;
        $ws->setCellValue("C{$monthRow}", 'รวมเดือนนี้');
        foreach (['D', 'E', 'I', 'J'] as $col) {
            $ws->setCellValue("{$col}{$monthRow}", $r > 4 ? "=SUM({$col}4:{$col}{$last})" : 0);
        }
        $ytd = $report['ytd_total'];
        $ws->setCellValue("C{$ytdRow}", 'รวมตั้งแต่ต้นปี');
        $ws->setCellValue("D{$ytdRow}", $ytd['brought']);
        $ws->setCellValue("E{$ytdRow}", $ytd['borrowed']);
        $ws->setCellValue("I{$ytdRow}", $ytd['returned']);
        $ws->setCellValue("J{$ytdRow}", "=D{$ytdRow}+E{$ytdRow}-I{$ytdRow}");
        $ws->getStyle("A{$monthRow}:J{$ytdRow}")->getFont()->setBold(true);
        $ws->getStyle("C{$monthRow}:C{$ytdRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $ws->getStyle("D4:E{$ytdRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle("I4:J{$ytdRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle("A3:J{$ytdRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $s = $ytdRow + 2;
        $ws->setCellValue("C{$s}", 'ผู้จัดทำ');
        $ws->setCellValue('C' . ($s + 1), str_repeat('.', 52));
        $ws->setCellValue('G' . ($s + 1), str_repeat('.', 52));
        $ws->setCellValue('C' . ($s + 2), '(' . ($signers['preparer']['name'] ?: str_repeat('.', 40)) . ')');
        $ws->setCellValue('G' . ($s + 2), '(' . ($signers['director']['name'] ?: str_repeat('.', 40)) . ')');
        $ws->setCellValue('C' . ($s + 3), $signers['preparer']['position']);
        $ws->setCellValue('G' . ($s + 3), $signers['director']['position']);
        for ($i = $s + 1; $i <= $s + 3; $i++) {
            $ws->mergeCells("G{$i}:J{$i}");
        }
        $ws->getStyle("A{$s}:J" . ($s + 3))->getFont()->setBold(true);
        $ws->getStyle("A{$s}:J" . ($s + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $setup = $ws->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4);
        $setup->setFitToWidth(1)->setFitToHeight(0);
        $setup->setRowsToRepeatAtTopByStartAndEnd(3, 3);
        $ws->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.5)->setBottom(0.5);

        $dir = Yii::getAlias('@runtime/export');
        FileHelper::createDirectory($dir);
        $path = $dir . '/loan-monthly-' . $report['month'] . '-' . Yii::$app->security->generateRandomString(8) . '.xlsx';
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        return $path;
    }

    /** ชื่อชีตแบบเดียวกับไฟล์เดิม เช่น ส.ค.2569 */
    private static function sheetTitle(string $ym): string
    {
        static $names = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $ts = strtotime($ym . '-01');
        return $names[(int) date('n', $ts)] . ((int) date('Y', $ts) + 543);
    }
}
