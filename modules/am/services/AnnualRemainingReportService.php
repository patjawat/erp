<?php

namespace app\modules\am\services;

use Yii;
use DateTimeImmutable;
use app\components\SiteHelper;
use app\modules\am\models\Asset;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * สร้างรายงานครุภัณฑ์คงเหลือประจำปีแบบ Excel ตามแนวรายงานราชการ
 * โดยแยกชีตตามหมวดประเภทอาคาร/สิ่งปลูกสร้างจากข้อมูลที่มีอยู่ในระบบ.
 */
class AnnualRemainingReportService
{
    /**
     * ดึงข้อมูลรายงานพร้อมสรุปยอดรวมรายชีต
     */
    public static function getReportData(int $thaiYear): array
    {
        $assets = self::fetchAssets();
        $periodEnd = new DateTimeImmutable(((int) ($thaiYear - 543)) . '-09-30');
        $previousEnd = $periodEnd->modify('-1 year');

        $buckets = [
            'อาคารที่พักอาศัย' => [],
            'อาคารสำนักงาน' => [],
            'อาคารเพื่อประโยชน์อื่น' => [],
            'สิ่งปลูกสร้าง' => [],
            'ระบบถนนภายใน' => [],
        ];

        $allRows = [];

        foreach ($assets as $asset) {
            $bucket = self::classifyAsset($asset);
            if ($bucket === null || !array_key_exists($bucket, $buckets)) {
                continue;
            }

            $snapshot = self::buildDepreciationSnapshot($asset, $previousEnd, $periodEnd);
            $row = [
                'asset' => $asset,
                'bucket' => $bucket,
                'code' => (string) ($asset->code ?? ''),
                'receive_date' => $asset->receive_date ? date('d/m/Y', strtotime($asset->receive_date)) : '',
                'name' => self::resolveAssetName($asset),
                'cost' => (float) ($asset->price ?? 0),
                'accumulated_previous' => $snapshot['accumulated_previous'],
                'remaining_previous' => $snapshot['remaining_previous'],
                'useful_life' => $snapshot['useful_life'],
                'accumulated_current' => $snapshot['accumulated_current'],
                'remaining_current' => $snapshot['remaining_current'],
                'condition' => $asset->asset_condition ?? '',
            ];

            $buckets[$bucket][] = $row;
            $allRows[] = $row;
        }

        $summary = [];
        foreach ($buckets as $bucket => $rows) {
            $summary[] = [
                'title' => $bucket,
                'count' => count($rows),
                'cost' => array_sum(array_column($rows, 'cost')),
                'accumulated' => array_sum(array_column($rows, 'accumulated_current')),
                'remaining' => array_sum(array_column($rows, 'remaining_current')),
            ];
        }

        return [
            'thaiYear' => $thaiYear,
            'periodStartLabel' => '1 ตุลาคม ' . ($thaiYear - 1),
            'periodEndLabel' => '30 กันยายน ' . $thaiYear,
            'surveyLabel' => '1 ตุลาคม ' . $thaiYear,
            'finishLabel' => '31 ตุลาคม ' . $thaiYear,
            'organizationName' => self::getOrganizationName(),
            'buckets' => $buckets,
            'summary' => $summary,
            'rows' => $allRows,
        ];
    }

    /**
     * สร้างไฟล์ Excel และบันทึกลง downloads
     */
    public static function saveXlsx(int $thaiYear): array
    {
        $workbook = self::buildWorkbook($thaiYear);
        $filename = 'รายงานครุภัณฑ์คงเหลือประจำปี_' . $thaiYear . '.xlsx';
        $dirPath = Yii::getAlias('@webroot') . '/downloads';
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $filePath = $dirPath . '/' . $filename;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
        $writer->save($filePath);

        return [
            'filePath' => $filePath,
            'fileName' => $filename,
        ];
    }

    /**
     * สร้าง workbook ตามรายงานตัวอย่าง
     */
    public static function buildWorkbook(int $thaiYear): Spreadsheet
    {
        $data = self::getReportData($thaiYear);

        $workbook = new Spreadsheet();
        $workbook->getProperties()
            ->setCreator((string) (SiteHelper::getInfo()['company_name'] ?? 'Codex'))
            ->setTitle('รายงานครุภัณฑ์คงเหลือประจำปี ' . $thaiYear)
            ->setSubject('รายงานครุภัณฑ์คงเหลือประจำปี');

        $workbook->removeSheetByIndex(0);

        $created = 0;
        foreach ($data['buckets'] as $title => $rows) {
            $sheet = new Worksheet($workbook, $title);
            $workbook->addSheet($sheet, $created);
            self::buildCategorySheet($sheet, $data, $title, $rows);
            $created++;
        }

        $summarySheet = new Worksheet($workbook, 'ค่าเสื่อม');
        $workbook->addSheet($summarySheet);
        self::buildDepreciationSheet($summarySheet, $data);
        $workbook->setActiveSheetIndex(0);

        return $workbook;
    }

    private static function fetchAssets(): array
    {
        return Asset::find()
            ->alias('a')
            ->with(['assetCondition'])
            ->where(['a.deleted_at' => null])
            ->andWhere(['not', ['a.receive_date' => null]])
            ->andWhere(['>', 'a.price', 0])
            ->andWhere(['>', 'a.useful_life', 0])
            ->andWhere(['a.asset_group_id' => [2, 3, 7]])
            ->orderBy(['a.receive_date' => SORT_ASC, 'a.code' => SORT_ASC, 'a.id' => SORT_ASC])
            ->all();
    }

    private static function classifyAsset(Asset $asset): ?string
    {
        $groupId = (int) ($asset->asset_group_id ?? 0);
        $assetName = self::normalizeText(self::resolveAssetName($asset));
        $buildingType = self::normalizeText((string) ($asset->data_json['building_type_name'] ?? ''));
        $structureType = self::normalizeText((string) ($asset->data_json['structure_type_name'] ?? ''));
        $combined = trim($assetName . ' ' . $buildingType . ' ' . $structureType);

        if ($groupId === 2 || $buildingType !== '' || self::containsAny($assetName, ['อาคาร', 'บ้านพัก'])) {
            if (self::containsAny($combined, ['ที่พักอาศัย', 'บ้านพัก'])) {
                return 'อาคารที่พักอาศัย';
            }
            if (self::containsAny($combined, ['สำนักงาน'])) {
                return 'อาคารสำนักงาน';
            }
            return 'อาคารเพื่อประโยชน์อื่น';
        }

        if (in_array($groupId, [3, 7], true) || $structureType !== '' || self::containsAny($assetName, ['สิ่งปลูกสร้าง', 'ถนน'])) {
            if (self::containsAny($combined, ['ถนน', 'ทางเชื่อม'])) {
                return 'ระบบถนนภายใน';
            }
            return 'สิ่งปลูกสร้าง';
        }

        return null;
    }

    private static function buildCategorySheet(Worksheet $sheet, array $data, string $title, array $rows): void
    {
        $thaiYear = (int) $data['thaiYear'];
        $prevThaiYear = $thaiYear - 1;

        $sheet->setTitle($title);

        $sheet->mergeCells('B1:O1');
        $sheet->setCellValue('B1', 'แบบฟอร์มรายงานพัสดุคงเหลือประจำปี ' . $thaiYear . ' (ครุภัณฑ์ตามเกณฑ์)');
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', 'บัญชีแสดงการรับ - จ่ายพัสดุคงเหลือ');
        $sheet->mergeCells('K2:Q2');
        $sheet->setCellValue('K2', 'งวดตั้งแต่วันที่ 1 ตุลาคม ' . $prevThaiYear . ' - 30 กันยายน ' . $thaiYear);
        $sheet->mergeCells('A3:C3');
        $sheet->setCellValue('A3', $data['organizationName']);
        $sheet->mergeCells('K3:Q3');
        $sheet->setCellValue('K3', 'สำรวจเมื่อ 1 ตุลาคม ' . $thaiYear);
        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', $title);
        $sheet->mergeCells('K4:Q4');
        $sheet->setCellValue('K4', 'วันเสร็จสิ้น 31 ตุลาคม ' . $thaiYear);

        $sheet->mergeCells('A5:A8');
        $sheet->setCellValue('A5', 'ลำดับที่');
        $sheet->mergeCells('B5:B8');
        $sheet->setCellValue('B5', 'รหัสครุภัณฑ์');
        $sheet->mergeCells('C5:C8');
        $sheet->setCellValue('C5', 'วัน/เดือน/ปีที่ได้มา');
        $sheet->mergeCells('D5:D8');
        $sheet->setCellValue('D5', 'รายการทรัพย์สิน');
        $sheet->mergeCells('E5:E8');
        $sheet->setCellValue('E5', 'ราคาทุนทรัพย์สินที่ซื้อมา');
        $sheet->mergeCells('F5:G5');
        $sheet->setCellValue('F5', 'รับใหม่');
        $sheet->mergeCells('H5:I5');
        $sheet->setCellValue('H5', 'จ่ายทั้งหมด');
        $sheet->mergeCells('J5:J8');
        $sheet->setCellValue('J5', 'ค่าเสื่อมราคาสะสม');
        $sheet->mergeCells('K5:K8');
        $sheet->setCellValue('K5', 'คงเหลือ');
        $sheet->mergeCells('L5:L8');
        $sheet->setCellValue('L5', 'ผลการตรวจ / ถูกต้อง');
        $sheet->mergeCells('M5:N5');
        $sheet->setCellValue('M5', 'ถ้าไม่ถูกต้อง');
        $sheet->mergeCells('O5:Q5');
        $sheet->setCellValue('O5', 'สภาพพัสดุ (จำนวน)');

        $sheet->setCellValue('F6', '1 ต.ค. ' . substr((string) $prevThaiYear, -2));
        $sheet->setCellValue('G6', 'จำนวนเงิน');
        $sheet->setCellValue('H6', '1 ต.ค. ' . substr((string) $prevThaiYear, -2));
        $sheet->setCellValue('I6', 'จำนวนเงิน');
        $sheet->setCellValue('M6', 'ขาด');
        $sheet->setCellValue('N6', 'เกิน');
        $sheet->setCellValue('O6', 'ชำรุด');
        $sheet->setCellValue('P6', 'เสื่อมสภาพ');
        $sheet->setCellValue('Q6', 'ไม่จำเป็น');

        $sheet->setCellValue('F7', 'ถึง');
        $sheet->setCellValue('H7', 'ถึง');
        $sheet->setCellValue('L7', '✓ ถูกต้อง');
        $sheet->setCellValue('O7', '');
        $sheet->setCellValue('P7', '');
        $sheet->setCellValue('Q7', '');

        $sheet->setCellValue('F8', '30 ก.ย. ' . substr((string) $thaiYear, -2));
        $sheet->setCellValue('H8', '30 ก.ย. ' . substr((string) $thaiYear, -2));
        $sheet->setCellValue('M8', '');
        $sheet->setCellValue('N8', '');
        $sheet->setCellValue('O8', '');
        $sheet->setCellValue('P8', '');
        $sheet->setCellValue('Q8', '');

        $headerRange = 'A1:Q8';
        $sheet->getStyle($headerRange)->getFont()->setName('TH Sarabun New');
        $sheet->getStyle('A1:Q8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:Q8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:Q8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:Q8')->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:Q8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');

        $sheet->freezePane('A9');

        $widths = [
            'A' => 8,
            'B' => 18,
            'C' => 14,
            'D' => 36,
            'E' => 16,
            'F' => 11,
            'G' => 12,
            'H' => 11,
            'I' => 12,
            'J' => 16,
            'K' => 16,
            'L' => 12,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 10,
            'Q' => 10,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $rowNum = 9;
        foreach ($rows as $index => $row) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $row['code']);
            $sheet->setCellValue('C' . $rowNum, $row['receive_date']);
            $sheet->setCellValue('D' . $rowNum, '    ' . $row['name']);
            $sheet->setCellValue('E' . $rowNum, $row['cost']);
            $sheet->setCellValue('J' . $rowNum, "=VLOOKUP(\$B{$rowNum},'ค่าเสื่อม'!\$A:\$K,10,FALSE)");
            $sheet->setCellValue('K' . $rowNum, "=VLOOKUP(\$B{$rowNum},'ค่าเสื่อม'!\$A:\$K,11,FALSE)");
            $sheet->setCellValue('L' . $rowNum, '/');

            $condition = (string) ($row['condition'] ?? '');
            if ($condition === 'damaged') {
                $sheet->setCellValue('O' . $rowNum, '/');
            } elseif ($condition === 'worn') {
                $sheet->setCellValue('P' . $rowNum, '/');
            } else {
                $sheet->setCellValue('Q' . $rowNum, '');
            }

            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . $rowNum . ':K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNum++;
        }

        $sheet->getStyle('A1:Q8')->getFont()->setSize(14);
        $sheet->getStyle('A1:Q' . max(8, $rowNum - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private static function buildDepreciationSheet(Worksheet $sheet, array $data): void
    {
        $thaiYear = (int) $data['thaiYear'];
        $prevThaiYear = $thaiYear - 1;

        $sheet->setTitle('ค่าเสื่อม');
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'รายละเอียดทรัพย์สินถาวรและการคิดค่าเสื่อมราคา');
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'สำหรับปี สิ้นสุดวันที่ 30/9/' . $thaiYear . ' ก่อน ' . $prevThaiYear);

        $sheet->mergeCells('A3:A5');
        $sheet->setCellValue('A3', 'รหัสสินทรัพย์');
        $sheet->mergeCells('B3:B5');
        $sheet->setCellValue('B3', 'วัน/เดือน/ปีที่ซื้อมา');
        $sheet->mergeCells('C3:C5');
        $sheet->setCellValue('C3', 'รายการทรัพย์สิน');
        $sheet->mergeCells('D3:D5');
        $sheet->setCellValue('D3', 'ราคาทุนทรัพย์สินที่ซื้อมา');
        $sheet->mergeCells('E3:E5');
        $sheet->setCellValue('E3', 'ค่าเสื่อมราคาสะสมยกมา');
        $sheet->mergeCells('F3:F5');
        $sheet->setCellValue('F3', 'ราคาทุนสุทธิทรัพย์สินของรอบระยะเวลา');
        $sheet->mergeCells('G3:G5');
        $sheet->setCellValue('G3', 'อัตรา');
        $sheet->mergeCells('H3:H5');
        $sheet->setCellValue('H3', 'จำนวน');
        $sheet->mergeCells('I3:I5');
        $sheet->setCellValue('I3', 'ค่าเสื่อมราคาเดือนนี้');
        $sheet->mergeCells('J3:J5');
        $sheet->setCellValue('J3', 'ค่าเสื่อมราคาสะสมยกไป');
        $sheet->mergeCells('K3:K5');
        $sheet->setCellValue('K3', 'ราคาทุนสุทธิทรัพย์สินของรอบระยะเวลาปัจจุบัน');

        $sheet->getStyle('A1:K5')->getFont()->setName('TH Sarabun New')->setBold(true);
        $sheet->getStyle('A1:K5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:K5')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
        $sheet->getStyle('A1:K5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $widths = [
            'A' => 18,
            'B' => 14,
            'C' => 34,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 10,
            'H' => 10,
            'I' => 12,
            'J' => 15,
            'K' => 15,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A6');

        $rowNum = 6;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['code']);
            $sheet->setCellValue('B' . $rowNum, $row['receive_date']);
            $sheet->setCellValue('C' . $rowNum, $row['name']);
            $sheet->setCellValue('D' . $rowNum, $row['cost']);
            $sheet->setCellValue('E' . $rowNum, $row['accumulated_previous']);
            $sheet->setCellValue('F' . $rowNum, $row['remaining_previous']);
            $sheet->setCellValue('G' . $rowNum, $row['useful_life']);
            $sheet->setCellValue('H' . $rowNum, '');
            $sheet->setCellValue('I' . $rowNum, '');
            $sheet->setCellValue('J' . $rowNum, $row['accumulated_current']);
            $sheet->setCellValue('K' . $rowNum, $row['remaining_current']);

            $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . $rowNum . ':F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $rowNum . ':K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNum++;
        }

        $sheet->getStyle('A1:K' . max(5, $rowNum - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private static function buildDepreciationSnapshot(Asset $asset, DateTimeImmutable $previousEnd, DateTimeImmutable $periodEnd): array
    {
        $schedule = AssetDepreciationService::generateMonthlySchedule($asset);
        $rows = $schedule['schedule'] ?? [];
        $cost = (float) ($asset->price ?? 0);
        $usefulLife = (int) ($asset->useful_life ?? 0);

        $accPrevious = self::sumScheduleUntil($rows, $previousEnd);
        $accCurrent = self::sumScheduleUntil($rows, $periodEnd);
        $accPrevious = min($accPrevious, max(0.0, $cost - 1.0));
        $accCurrent = min($accCurrent, max(0.0, $cost - 1.0));

        return [
            'accumulated_previous' => round($accPrevious, 2),
            'remaining_previous' => round(max(1.0, $cost - $accPrevious), 2),
            'accumulated_current' => round($accCurrent, 2),
            'remaining_current' => round(max(1.0, $cost - $accCurrent), 2),
            'useful_life' => $usefulLife,
        ];
    }

    private static function sumScheduleUntil(array $rows, DateTimeImmutable $cutoff): float
    {
        $total = 0.0;
        foreach ($rows as $row) {
            $year = (int) ($row['year'] ?? 0);
            $month = (int) ($row['month'] ?? 0);
            if ($year <= 0 || $month <= 0) {
                continue;
            }
            $rowDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            if ($rowDate > $cutoff) {
                break;
            }
            $total += (float) ($row['depreciation'] ?? 0);
        }

        return $total;
    }

    private static function resolveAssetName(Asset $asset): string
    {
        $name = trim((string) ($asset->asset_name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $buildingName = trim((string) ($asset->data_json['building_type_name'] ?? ''));
        if ($buildingName !== '') {
            return $buildingName;
        }

        $structureName = trim((string) ($asset->data_json['structure_type_name'] ?? ''));
        if ($structureName !== '') {
            return $structureName;
        }

        return trim((string) ($asset->AssetitemName() ?? ''));
    }

    private static function getOrganizationName(): string
    {
        $info = SiteHelper::getInfo();
        return (string) ($info['company_name'] ?? 'หน่วยงาน');
    }

    private static function normalizeText(string $value): string
    {
        return trim(mb_strtolower($value, 'UTF-8'));
    }

    private static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle === '') {
                continue;
            }
            if (mb_strpos($haystack, $needle, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }
}
