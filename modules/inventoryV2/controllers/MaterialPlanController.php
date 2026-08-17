<?php

namespace app\modules\inventoryV2\controllers;

use app\components\SiteHelper;
use app\modules\inventoryV2\services\MaterialPlanForecastService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * จัดทำแผนวัสดุประจำปี — คาดการณ์ปริมาณการใช้จากยอดจ่ายจริงย้อนหลัง
 * แล้วเสนอปริมาณจัดซื้อแบ่ง 4 ไตรมาส ส่งออกตามแบบฟอร์มของทางราชการ
 */
class MaterialPlanController extends Controller
{
    /**
     * หน้าคำนวณแผน พร้อมตัวกรองและตารางที่ปรับตัวเลขได้
     */
    public function actionIndex()
    {
        $filter = $this->resolveFilter();
        $service = new MaterialPlanForecastService();
        $rows = $this->collectRows($service, $filter);

        return $this->render('index', [
            'filter' => $filter,
            'rows' => $rows,
            'summary' => $service->summarize($rows),
            'warehouses' => MaterialPlanForecastService::mainWarehouseOptions(),
            'departments' => MaterialPlanForecastService::departmentOptions(),
            'categories' => MaterialPlanForecastService::categoryOptions(),
            'quarterLabels' => MaterialPlanForecastService::quarterLabels(),
            'baseYear' => MaterialPlanForecastService::baseFiscalYear($filter['fiscal_year']),
            'balanceSource' => $service->getBalanceSource(),
            'coverage' => $service->getCoverage(),
        ]);
    }

    /**
     * ส่งออก Excel ตามแบบฟอร์ม "แผนการจัดวัสดุ" ของโรงพยาบาล
     * รับตัวเลขที่ผู้ใช้ปรับบนหน้าจอผ่าน POST เพื่อให้ไฟล์ตรงกับที่เห็น
     */
    public function actionExport()
    {
        $filter = $this->resolveFilter();
        $service = new MaterialPlanForecastService();
        $rows = $this->collectRows($service, $filter);

        return $this->streamXlsx($rows, $service->summarize($rows), $filter, $service->getCoverage());
    }

    /**
     * ค้นวัสดุจากทะเบียนเพื่อเพิ่มเข้าแผน (เรียกจากช่องค้นหาบนหน้าจอ)
     */
    public function actionSearchItem($q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return (new MaterialPlanForecastService())->searchItems((string) $q);
    }

    /**
     * แถวที่คำนวณได้ + แถวที่ผู้ใช้เพิ่มเอง แล้วทับด้วยตัวเลขที่ปรับบนหน้าจอ
     */
    protected function collectRows(MaterialPlanForecastService $service, array $filter): array
    {
        $rows = $service->buildRows($filter);

        $addedCodes = array_diff(
            $this->readAddedItems(),
            \yii\helpers\ArrayHelper::getColumn($rows, 'item_code')
        );
        if ($addedCodes !== []) {
            $rows = array_merge($rows, $service->buildManualRows($addedCodes, $filter));
            $seq = 1;
            foreach ($rows as &$row) {
                $row['seq'] = $seq++;
            }
            unset($row);
        }

        return $this->applyOverrides($rows, $this->readOverrides());
    }

    /**
     * รหัสวัสดุที่ผู้ใช้เพิ่มเข้าแผนเอง ส่งมาเป็น JSON array
     *
     * @return array<int, string>
     */
    protected function readAddedItems(): array
    {
        $raw = (string) Yii::$app->request->post('added_items', '');
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_map('strval', $decoded) : [];
    }

    /**
     * อ่านตัวกรองจาก request ให้อยู่ในรูปที่ service ใช้ได้
     */
    protected function resolveFilter(): array
    {
        $request = Yii::$app->request;
        $get = static fn ($key, $default = '') => trim((string) $request->post($key, $request->get($key, $default)));

        return [
            'fiscal_year' => MaterialPlanForecastService::normalizeFiscalYear($get('fiscal_year')),
            'growth_pct' => MaterialPlanForecastService::normalizeGrowthPct($get('growth_pct', (string) MaterialPlanForecastService::DEFAULT_GROWTH_PCT)),
            'warehouse_id' => $get('warehouse_id') !== '' ? (int) $get('warehouse_id') : null,
            'dept_warehouse_id' => $get('dept_warehouse_id') !== '' ? (int) $get('dept_warehouse_id') : null,
            'category_id' => $get('category_id'),
            'q' => $get('q'),
        ];
    }

    /**
     * ตัวเลขที่ผู้ใช้แก้บนหน้าจอ ส่งมาเป็น JSON ก้อนเดียวเฉพาะแถวที่เปลี่ยน
     *
     * @return array<string, array>
     */
    protected function readOverrides(): array
    {
        $raw = (string) Yii::$app->request->post('overrides', '');
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * ทับค่าที่คำนวณได้ด้วยตัวเลขที่ผู้ใช้ปรับ แล้วคิดมูลค่ากับไตรมาสใหม่
     */
    protected function applyOverrides(array $rows, array $overrides): array
    {
        if ($overrides === []) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $override = $overrides[$row['item_code']] ?? null;
            if (!is_array($override)) {
                continue;
            }

            $row['is_adjusted'] = true;

            if (isset($override['forecast_qty']) && is_numeric($override['forecast_qty'])) {
                $row['forecast_qty'] = (int) round((float) $override['forecast_qty']);
                $row['plan_qty'] = MaterialPlanForecastService::planQty($row['forecast_qty'], $row['opening_qty']);
            }
            if (isset($override['plan_qty']) && is_numeric($override['plan_qty'])) {
                $row['plan_qty'] = (int) ceil(max((float) $override['plan_qty'], 0));
            }
            if (isset($override['unit_price']) && is_numeric($override['unit_price'])) {
                $row['unit_price'] = round(max((float) $override['unit_price'], 0), 2);
                $row['price_source'] = 'manual';
            }

            $quarters = MaterialPlanForecastService::splitQuarters($row['plan_qty']);
            foreach ($quarters as $index => $default) {
                $key = 'q' . ($index + 1);
                if (isset($override[$key]) && is_numeric($override[$key])) {
                    $quarters[$index] = (int) ceil(max((float) $override[$key], 0));
                }
            }

            $row['quarters'] = $quarters;
            $row['quarter_values'] = array_map(static fn ($qty) => round($qty * $row['unit_price'], 2), $quarters);
            $row['plan_value'] = round($row['plan_qty'] * $row['unit_price'], 2);
        }
        unset($row);

        return $rows;
    }

    /**
     * เขียนไฟล์ Excel ให้มีคอลัมน์และลำดับตรงกับแบบฟอร์มต้นฉบับ
     */
    protected function streamXlsx(array $rows, array $summary, array $filter, array $coverage = [])
    {
        $planYear = $filter['fiscal_year'];
        $baseYear = MaterialPlanForecastService::baseFiscalYear($planYear);
        $quarterLabels = MaterialPlanForecastService::quarterLabels();
        $historyYears = array_keys(MaterialPlanForecastService::historyUsage($baseYear, 0.0, $filter['growth_pct']));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('แผนจัดซื้อ ปี ' . $planYear);

        $lastColumn = 'AF';
        $sheet->setCellValue('A1', 'แผนการจัดวัสดุ ' . $this->organizationName() . ' ประจำปีงบประมาณ ' . $planYear);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A2', $this->filterCaption($filter, $coverage));
        $sheet->mergeCells("A2:{$lastColumn}2");

        $this->writeHeader($sheet, $historyYears, $planYear, $quarterLabels);

        $rowIndex = 6;
        foreach ($rows as $row) {
            $history = $row['history'];
            $sheet->setCellValue('A' . $rowIndex, $row['item_code']);
            $sheet->setCellValue('B' . $rowIndex, $row['item_name']);
            $sheet->setCellValue('C' . $rowIndex, $row['category_title']);
            $sheet->setCellValue('D' . $rowIndex, $row['unit_name']);

            $column = 'E';
            foreach ($historyYears as $year) {
                $sheet->setCellValue($column . $rowIndex, $history[$year] ?? 0);
                $column++;
            }

            $sheet->setCellValue('H' . $rowIndex, $row['forecast_qty']);
            $sheet->setCellValue('I' . $rowIndex, $row['opening_qty']);
            $sheet->setCellValue('J' . $rowIndex, $row['plan_qty']);
            $sheet->setCellValue('K' . $rowIndex, $row['unit_price']);
            $sheet->setCellValue('L' . $rowIndex, $row['plan_value']);

            // ไตรมาส 1-4 บล็อกละ 4 คอลัมน์: แผนจัดซื้อ, มูลค่า, ซื้อจริง, มูลค่าซื้อจริง
            // ช่อง "ซื้อจริง" เว้นว่างไว้ให้กรอกระหว่างปี ตามแบบฟอร์มเดิม
            $blockStart = 13; // คอลัมน์ M
            foreach ($row['quarters'] as $index => $qty) {
                $qtyColumn = Coordinate::stringFromColumnIndex($blockStart + ($index * 4));
                $valueColumn = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + 1);
                $sheet->setCellValue($qtyColumn . $rowIndex, $qty);
                $sheet->setCellValue($valueColumn . $rowIndex, $row['quarter_values'][$index]);
            }

            $sheet->setCellValue('AC' . $rowIndex, array_sum($row['quarters']));
            $sheet->setCellValue('AD' . $rowIndex, array_sum($row['quarter_values']));
            $rowIndex++;
        }

        $lastRow = $rowIndex - 1;
        $this->styleSheet($sheet, $lastColumn, $lastRow);
        $this->writeFooter($sheet, $rowIndex, $summary);

        $tempPath = tempnam(sys_get_temp_dir(), 'material-plan-');
        (new Xlsx($spreadsheet))->save($tempPath);
        $filename = 'material-plan-' . $planYear . '.xlsx';

        return Yii::$app->response
            ->sendFile($tempPath, $filename)
            ->on(Response::EVENT_AFTER_SEND, static function () use ($tempPath) {
                @unlink($tempPath);
            });
    }

    /**
     * หัวตาราง 2 ชั้นตามแบบฟอร์ม (แถว 4 = กลุ่ม, แถว 5 = คอลัมน์ย่อย)
     */
    protected function writeHeader($sheet, array $historyYears, int $planYear, array $quarterLabels): void
    {
        $single = [
            'A' => 'รหัส',
            'B' => 'รายการสินค้า',
            'C' => 'ประเภทรายการ',
            'D' => 'หน่วยบรรจุ',
        ];
        foreach ($single as $column => $label) {
            $sheet->setCellValue($column . '4', $label);
            $sheet->mergeCells("{$column}4:{$column}5");
        }

        $sheet->setCellValue('E4', 'ข้อมูลการใช้ย้อนหลัง ' . count($historyYears) . ' ปี');
        $sheet->mergeCells('E4:G4');
        $column = 'E';
        foreach ($historyYears as $year) {
            $sheet->setCellValue($column . '5', $year);
            $column++;
        }

        $stacked = [
            'H' => ['ประมาณการใช้', 'ปี ' . $planYear],
            'I' => ['ยอด', 'คงคลัง'],
            'J' => ['ประมาณ', 'การจัดซื้อ'],
            'K' => ['ราคา', 'ต่อขนาดบรรจุ'],
            'L' => ['ประมาณ', 'มูลค่า'],
        ];
        foreach ($stacked as $col => $labels) {
            $sheet->setCellValue($col . '4', $labels[0]);
            $sheet->setCellValue($col . '5', $labels[1]);
        }

        $blockStart = 13; // คอลัมน์ M
        foreach ($quarterLabels as $index => $label) {
            $from = Coordinate::stringFromColumnIndex($blockStart + ($index * 4));
            $to = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + 3);
            $sheet->setCellValue($from . '4', $label);
            $sheet->mergeCells("{$from}4:{$to}4");

            $subs = ['แผนจัดซื้อ', 'มูลค่า', 'ซื้อจริง', 'มูลค่าซื้อจริง'];
            foreach ($subs as $offset => $sub) {
                $column = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + $offset);
                $sheet->setCellValue($column . '5', $sub);
            }
        }

        $sheet->setCellValue('AC4', 'ยอดรวม');
        $sheet->mergeCells('AC4:AF4');
        foreach (['AC' => 'แผนจัดซื้อ', 'AD' => 'มูลค่า', 'AE' => 'ซื้อจริง', 'AF' => 'มูลค่าซื้อจริง'] as $column => $label) {
            $sheet->setCellValue($column . '5', $label);
        }
    }

    /**
     * แถวสรุปและช่องลงนามท้ายแบบฟอร์ม
     */
    protected function writeFooter($sheet, int $rowIndex, array $summary): void
    {
        $summaryRow = $rowIndex + 1;
        $sheet->setCellValue('C' . $summaryRow, 'จำนวน');
        $sheet->setCellValue('D' . $summaryRow, $summary['item_count']);
        $sheet->setCellValue('E' . $summaryRow, 'รายการ');
        $sheet->setCellValue('K' . $summaryRow, 'มูลค่าประมาณการ');
        $sheet->setCellValue('L' . $summaryRow, $summary['plan_value']);
        $sheet->setCellValue('M' . $summaryRow, 'บาท');
        $sheet->getStyle("C{$summaryRow}:M{$summaryRow}")->getFont()->setBold(true);
        $sheet->getStyle("L{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        $signRow = $summaryRow + 3;
        $signatures = [
            'B' => 'ผู้จัดทำแผน.............................................................',
            'F' => 'ผู้เสนอแผน.............................................................',
            'M' => 'ผู้เห็นชอบแผน.............................................................',
        ];
        foreach ($signatures as $column => $label) {
            $sheet->setCellValue($column . $signRow, $label);
            $sheet->setCellValue($column . ($signRow + 1), '(.............................................................)');
        }
    }

    /**
     * ตกแต่งตารางให้อ่านง่ายและพิมพ์ได้ (ไม่ใช้สีนอกเหนือจากเส้นตารางมาตรฐาน)
     */
    protected function styleSheet($sheet, string $lastColumn, int $lastRow): void
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A4:{$lastColumn}5")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        if ($lastRow >= 6) {
            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("E6:{$lastColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('E6');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(42);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(12);
        foreach (range('E', 'L') as $column) {
            $sheet->getColumnDimension($column)->setWidth(14);
        }
        for ($index = 13; $index <= 32; $index++) {
            $sheet->getColumnDimensionByColumn($index)->setWidth(13);
        }
        $sheet->getRowDimension(4)->setRowHeight(24);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    }

    /**
     * บรรทัดบอกเงื่อนไขที่ใช้คำนวณ เพื่อให้ผู้อ่านไฟล์รู้ที่มาของตัวเลข
     */
    protected function filterCaption(array $filter, array $coverage = []): string
    {
        $baseYear = MaterialPlanForecastService::baseFiscalYear($filter['fiscal_year']);
        $parts = ['คำนวณจากยอดใช้จริงปีงบ ' . $baseYear];

        $months = (int) ($coverage['months'] ?? 12);
        if ($months < 12) {
            $parts[] = 'ข้อมูล ' . $months . ' เดือน ปรับเป็นเต็มปี ×' . $coverage['factor'];
        }

        $parts[] = 'อัตราปรับ ' . $filter['growth_pct'] . '%';

        if ($filter['dept_warehouse_id']) {
            $departments = MaterialPlanForecastService::departmentOptions();
            $parts[] = 'หน่วยงาน ' . ($departments[$filter['dept_warehouse_id']] ?? '-');
        }
        if ($filter['warehouse_id']) {
            $warehouses = MaterialPlanForecastService::mainWarehouseOptions();
            $parts[] = 'คลัง ' . ($warehouses[$filter['warehouse_id']] ?? '-');
        }
        if ($filter['category_id'] !== '') {
            $categories = MaterialPlanForecastService::categoryOptions();
            $parts[] = 'หมวด ' . ($categories[$filter['category_id']] ?? '-');
        }

        return implode(' · ', $parts);
    }

    protected function organizationName(): string
    {
        $name = SiteHelper::getInfo()['company_name'] ?? null;

        return trim((string) $name) !== '' ? (string) $name : 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย';
    }
}
