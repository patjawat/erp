<?php

namespace app\modules\finance\services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\helpers\FileHelper;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceCashAccount;

/**
 * รายงานเช็คเป็น .xlsx — วันที่ / เลขที่เช็ค / เล่ม / บัญชีจ่าย / จ่ายให้ / จำนวนเงิน / สถานะ
 */
class ChequeReportExcel
{
    private const FONT = 'AngsanaUPC';

    /**
     * @param FinanceCheque[] $rows
     * @param array $meta ['from'=>Y-m-d|null,'to'=>...,'account'=>label]
     * @return string path ไฟล์ชั่วคราว
     */
    public function build(array $rows, array $meta = []): string
    {
        // prefetch บัญชีจ่าย กัน N+1
        $accIds = array_values(array_unique(array_filter(array_map(fn($c) => $c->cash_account_id, $rows))));
        $accounts = $accIds ? FinanceCashAccount::find()->where(['id' => $accIds])->indexBy('id')->all() : [];
        $statusLabels = FinanceCheque::statusOptions();

        $book = new Spreadsheet();
        $ws = $book->getActiveSheet();
        $ws->setTitle('รายงานเช็ค');
        $book->getDefaultStyle()->getFont()->setName(self::FONT)->setSize(14);

        foreach (['A' => 12, 'B' => 16, 'C' => 10, 'D' => 34, 'E' => 34, 'F' => 15, 'G' => 13] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }

        $ws->setCellValue('A1', 'รายงานเช็ค');
        $range = $this->rangeLabel($meta['from'] ?? null, $meta['to'] ?? null);
        $ws->setCellValue('A2', 'บัญชีจ่าย: ' . ($meta['account'] ?? 'ทุกบัญชี') . '   ช่วงวันที่: ' . $range);
        foreach (['A1:G1', 'A2:G2'] as $r) {
            $ws->mergeCells($r);
            $ws->getStyle($r)->getFont()->setBold(true);
            $ws->getStyle($r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $ws->fromArray(['วันที่สั่งจ่าย', 'เลขที่เช็ค', 'เล่ม', 'บัญชีจ่าย', 'จ่ายให้', 'จำนวนเงิน', 'สถานะ'], null, 'A4');
        $ws->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $r = 5;
        $total = 0.0;
        foreach ($rows as $c) {
            $acc = $accounts[$c->cash_account_id] ?? null;
            $ws->setCellValue("A{$r}", $this->thaiDate($c->cheque_date));
            $ws->setCellValueExplicit("B{$r}", (string) $c->cheque_no, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $ws->setCellValue("C{$r}", (string) $c->cheque_book_no);
            $ws->setCellValue("D{$r}", $acc ? $acc->label() : '');
            $ws->setCellValue("E{$r}", (string) $c->payee_name);
            $ws->setCellValue("F{$r}", (float) $c->amount);
            $ws->setCellValue("G{$r}", $statusLabels[$c->status] ?? $c->status);
            $total += (float) $c->amount;
            $r++;
        }
        $last = $r - 1;
        if ($last >= 5) {
            $ws->getStyle("A5:A{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("B5:C{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("G5:G{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $ws->setCellValue("E{$r}", 'รวม ' . count($rows) . ' ฉบับ');
        $ws->setCellValue("F{$r}", $total);
        $ws->getStyle("E{$r}:F{$r}")->getFont()->setBold(true);

        $ws->getStyle("F5:F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle('A4:G' . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $setup = $ws->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4);
        $setup->setFitToWidth(1)->setFitToHeight(0);
        $ws->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.5)->setBottom(0.5);

        $dir = Yii::getAlias('@runtime/export');
        FileHelper::createDirectory($dir);
        $path = $dir . '/cheque-report-' . Yii::$app->security->generateRandomString(8) . '.xlsx';
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        return $path;
    }

    private function thaiDate(?string $ymd): string
    {
        if (!$ymd || !($t = date_create($ymd))) {
            return '';
        }
        return $t->format('d/m/') . ((int) $t->format('Y') + 543);
    }

    private function rangeLabel(?string $from, ?string $to): string
    {
        if (!$from && !$to) {
            return 'ทั้งหมด';
        }
        return ($from ? $this->thaiDate($from) : '…') . ' – ' . ($to ? $this->thaiDate($to) : '…');
    }
}
