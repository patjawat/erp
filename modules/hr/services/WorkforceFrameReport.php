<?php

namespace app\modules\hr\services;

use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\WorkforceStandardLine;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

/**
 * สรุปกรอบอัตรากำลังสำหรับส่ง สสจ.
 *
 * รูปแบบตารางยึดตามฟอร์มเดิมที่โรงพยาบาลใช้ส่ง คือรายสายงาน พร้อมแตกยอดตามประเภทการจ้าง
 * ต่างจากฟอร์มเดิมตรงที่จัดกลุ่มด้วย "สายงานตามเกณฑ์" ไม่ใช่ชื่อตำแหน่งที่โรงพยาบาลตั้งเอง
 * เพราะเกณฑ์กำหนดกรอบที่สายงาน การเทียบกรอบกับคนจึงต้องอยู่ระดับเดียวกัน
 */
class WorkforceFrameReport
{
    private int $thaiYear;
    private WorkforceFrameCalculator $calculator;

    public function __construct(int $thaiYear)
    {
        $this->thaiYear = $thaiYear;
        $this->calculator = WorkforceFrameCalculator::forYear($thaiYear);
    }

    /** ประเภทการจ้างเรียงตามลำดับที่ใช้ในฟอร์ม พร้อมธงว่านับในกรอบไหม */
    public function employmentTypes(): array
    {
        $rows = EmployeeType::find()
            ->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $countsInFrame = WorkforceFrameCalculator::frameCountingTypeIds();

        $types = [];
        foreach ($rows as $row) {
            $types[(int) $row->id] = [
                'title' => (string) $row->title,
                'in_frame' => in_array((int) $row->id, $countsInFrame, true),
            ];
        }

        return $types;
    }

    /** แถวรายงาน จัดกลุ่มตามประเภทสายงาน */
    public function rows(): array
    {
        return $this->calculator->results();
    }

    public function calculator(): WorkforceFrameCalculator
    {
        return $this->calculator;
    }

    public function totals(): array
    {
        $totals = ['frame' => 0.0, 'in_frame' => 0, 'outsource' => 0, 'gap' => 0.0, 'by_type' => []];

        foreach ($this->rows() as $row) {
            $totals['in_frame'] += $row['in_frame'];
            $totals['outsource'] += $row['outsource'];

            if (in_array($row['status'], WorkforceFrameCalculator::STATUS_RESOLVED, true)) {
                $totals['frame'] += (float) $row['frame'];
                $totals['gap'] += max(0, (float) $row['gap']);
            }

            foreach ($row['by_type'] as $typeId => $count) {
                $totals['by_type'][$typeId] = ($totals['by_type'][$typeId] ?? 0) + $count;
            }
        }

        return $totals;
    }

    /**
     * สร้างไฟล์ xlsx แล้วคืน path — ใช้แนวทางเดียวกับรายงานอื่นในระบบ
     *
     * @return array{filePath:string,fileName:string}
     */
    public function saveXlsx(): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('กรอบอัตรากำลัง ' . $this->thaiYear);

        $types = $this->employmentTypes();
        $headers = array_merge(
            ['ลำดับ', 'สายงานตามเกณฑ์', 'ประเภท', 'กรอบ', 'มีอยู่จริง', 'ส่วนขาด'],
            array_map(static fn ($t) => $t['title'], $types),
            ['ที่มาของกรอบ', 'หมายเหตุ']
        );

        $profile = $this->calculator->profile();
        $sheet->setCellValue('A1', 'กรอบอัตรากำลัง ปีงบประมาณ ' . $this->thaiYear
            . ' · ระดับ ' . ($profile->level_code ?: '—')
            . ' · สถานะ ' . $profile->statusLabel());
        // PhpSpreadsheet 2.x ตัดเมธอด *ByColumnAndRow ออกแล้ว ต้องแปลงเลขคอลัมน์เป็นตัวอักษรเอง
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->fromArray($headers, null, 'A3');
        $headerStyle = $sheet->getStyle('A3:' . $lastColumn . '3');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8EDF3');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        $rowIndex = 4;
        $sequence = 0;

        foreach ($this->rows() as $row) {
            /** @var WorkforceStandardLine $line */
            $line = $row['line'];
            $sequence++;

            $values = [
                $line->seq !== null ? (int) $line->seq : $sequence,
                $line->title,
                $line->categoryLabel(),
                $row['frame'],
                $row['in_frame'],
                $row['gap'],
            ];

            foreach (array_keys($types) as $typeId) {
                $values[] = $row['by_type'][$typeId] ?? null;
            }

            $values[] = WorkforceFrameCalculator::STATUS_LABELS[$row['status']] ?? $row['status'];
            $values[] = $this->calcSummary($row['calc']);

            $sheet->fromArray($values, null, 'A' . $rowIndex);
            $rowIndex++;
        }

        $totals = $this->totals();
        $totalRow = array_merge(
            ['', 'รวม', '', $totals['frame'], $totals['in_frame'], $totals['gap']],
            array_map(static fn ($typeId) => $totals['by_type'][$typeId] ?? null, array_keys($types)),
            ['', '']
        );
        $sheet->fromArray($totalRow, null, 'A' . $rowIndex);
        $sheet->getStyle('A' . $rowIndex . ':' . $lastColumn . $rowIndex)->getFont()->setBold(true);

        $sheet->getStyle('A3:' . $lastColumn . $rowIndex)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(52);
        $sheet->getColumnDimension('C')->setWidth(14);
        for ($column = 4; $column <= count($headers); $column++) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $sheet->getColumnDimension($letter)->setWidth($column >= count($headers) - 1 ? 40 : 13);
        }
        $sheet->freezePane('D4');

        $note = $rowIndex + 2;
        $sheet->setCellValue('A' . $note, 'หมายเหตุ: "มีอยู่จริง" นับเฉพาะ 5 ประเภทการจ้างที่เกณฑ์ให้นับรวมในกรอบ '
            . 'ส่วนลูกจ้างรายวันและจ้างเหมาแสดงแยกในคอลัมน์ประเภทการจ้าง แต่ไม่รวมในยอด "มีอยู่จริง"');
        $sheet->getStyle('A' . $note)->getFont()->setItalic(true)->setSize(9);

        $fileName = 'กรอบอัตรากำลัง_' . $this->thaiYear . '.xlsx';
        $dirPath = Yii::getAlias('@webroot') . '/downloads';
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $filePath = $dirPath . '/' . $fileName;
        (new Xlsx($spreadsheet))->save($filePath);

        return ['filePath' => $filePath, 'fileName' => $fileName];
    }

    /** ย่อที่มาให้อยู่ในเซลล์เดียว เพื่อให้ไฟล์ที่ส่งออกยังอธิบายตัวเองได้ */
    private function calcSummary(array $calc): string
    {
        $parts = [];
        foreach ($calc as [$label, $value]) {
            $parts[] = $label . ' = ' . $value;
        }

        return implode(' · ', $parts);
    }
}
