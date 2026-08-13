<?php

namespace app\modules\roster\helpers;

use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\ShiftType;
use app\modules\roster\models\UnitShift;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * สร้างไฟล์ Excel ตารางเวร แยกสีตามผลัด
 *
 * สีในไฟล์ Excel ต้องเป็น hex ตรงๆ เพราะไฟล์ Excel ไม่มีธีมสว่าง/มืดแบบเว็บ
 * ค่ามาจาก ShiftType::excelFill() ที่แปลงจากชื่อสี Bootstrap ที่ผู้ใช้เลือกไว้
 */
class RosterExporter
{
    private const DOW = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

    /**
     * @param array $employees แถวจาก Employees::find()->asArray() (ต้องมี id, prefix, fname, lname)
     */
    public static function build(Period $period, array $employees): Spreadsheet
    {
        $unitId = (int) $period->unit_id;
        $grid = Item::gridForPeriod($period->id);
        $counts = Item::countByDayShift($period->id);
        $unitShifts = $period->sheetShifts(); // เฉพาะเวรของแผ่นนี้
        $types = ShiftType::activeList();
        $holidays = RosterContext::holidays($period->firstDate(), $period->lastDate());
        $weekends = RosterContext::weekends((int) $period->year_ce, (int) $period->month);
        $days = $period->daysInMonth();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ตารางเวร');

        // A ลำดับ, B ชื่อ, วัน 1..n, แล้วสรุป 4 คอลัมน์: รวมเวร / วันหยุด / OT / ค่าตอบแทน
        $sumStart = 3 + $days;
        $lastCol = Coordinate::stringFromColumnIndex($sumStart + 3);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $period->title . ' — ' . $period->unitName() . ' — ' . $period->monthLabel());
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'ลำดับ');
        $sheet->setCellValue('B2', 'ชื่อ-นามสกุล');
        for ($d = 1; $d <= $days; $d++) {
            $col = Coordinate::stringFromColumnIndex(2 + $d);
            $ts = strtotime($period->dateOfDay($d));
            $sheet->setCellValue($col . '2', $d . "\n" . self::DOW[(int) date('w', $ts)]);
            if (isset($holidays[$d])) {
                $sheet->getStyle($col . '2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
            } elseif (!empty($weekends[$d])) {
                $sheet->getStyle($col . '2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E3E5');
            }
        }
        foreach (['รวมเวร', 'วันหยุด', 'OT', 'ค่าตอบแทน'] as $i => $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($sumStart + $i) . '2', $label);
        }
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $row = 3;
        foreach ($employees as $index => $emp) {
            $empId = (int) $emp['id'];
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname']));
            $total = 0;
            $offDays = 0;
            $otShifts = 0;
            $pay = 0.0;
            for ($d = 1; $d <= $days; $d++) {
                $items = $grid[$empId][$d] ?? [];
                if (empty($items)) {
                    continue;
                }
                $col = Coordinate::stringFromColumnIndex(2 + $d);
                $labels = [];
                $fill = null;
                foreach ($items as $item) {
                    $labels[] = $item->shiftShort();
                    $fill = $fill ?: ($item->unitShift ? $item->unitShift->excelFill()
                        : ($item->shiftType ? $item->shiftType->excelFill() : null));
                    // วันหยุดไม่นับเป็นเวรทำงานและไม่คิดเงิน
                    if ($item->isOff()) {
                        $offDays++;
                        continue;
                    }
                    $total++;
                    if ($item->isOt()) {
                        $otShifts++;
                    }
                    $pay += $item->payAmount();
                }
                $sheet->setCellValue($col . $row, implode('/', $labels));
                if ($fill) {
                    $sheet->getStyle($col . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
                }
            }
            foreach ([$total, $offDays, $otShifts, $pay > 0 ? round($pay, 2) : null] as $i => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($sumStart + $i) . $row, $value);
            }
            $row++;
        }

        // แถวสรุปจำนวนคนต่อเวรต่อวัน — ตัวเดียวกับที่กริดบนเว็บแสดง
        foreach ($unitShifts as $shiftId => $unitShift) {
            $need = (int) $unitShift->required_staff;
            $sheet->setCellValue('B' . $row, $unitShift->displayName() . ($need > 0 ? " (ต้องการ $need)" : ''));
            $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            for ($d = 1; $d <= $days; $d++) {
                $col = Coordinate::stringFromColumnIndex(2 + $d);
                $have = $counts[$d][(int) $shiftId] ?? 0;
                $sheet->setCellValue($col . $row, $need > 0 ? "$have/$need" : $have);
                if ($need > 0 && $have < $need) {
                    $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('B02A37');
                }
            }
            $row++;
        }

        $sheet->getStyle("A2:{$lastCol}" . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("C3:{$lastCol}" . ($row - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(28);
        for ($d = 1; $d <= $days; $d++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(2 + $d))->setWidth(5);
        }
        foreach ([8, 8, 6, 13] as $i => $width) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($sumStart + $i))->setWidth($width);
        }
        $sheet->freezePane('C3');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        return $spreadsheet;
    }

    public static function filename(Period $period): string
    {
        return sprintf('roster-%04d%02d-%d.xlsx', $period->year_ce, $period->month, $period->id);
    }
}
