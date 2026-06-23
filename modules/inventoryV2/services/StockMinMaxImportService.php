<?php

namespace app\modules\inventoryV2\services;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Import/Export Excel ของ Min/Max ต่อคลัง
 *
 * pattern อ้างอิงจาก modules/sm/services/VendorImportService.php
 */
class StockMinMaxImportService
{
    public const MODE_TEMPLATE = 'template';
    public const MODE_SNAPSHOT = 'snapshot';

    /**
     * ลำดับคอลัมน์ในไฟล์ Excel (มาตรฐานภายในระบบ)
     */
    public const TEMPLATE_HEADERS = [
        'item_code', 'item_name', 'unit_name', 'category', 'balance', 'min_qty', 'max_qty', 'note',
    ];

    /**
     * label ภาษาไทยที่จะใส่ใน row header ของไฟล์
     */
    public const TEMPLATE_HEADER_LABELS_TH = [
        'item_code'  => 'รหัสวัสดุ',
        'item_name'  => 'ชื่อวัสดุ',
        'unit_name'  => 'หน่วย',
        'category'   => 'หมวด',
        'balance'    => 'คงเหลือปัจจุบัน',
        'min_qty'    => 'Min',
        'max_qty'    => 'Max',
        'note'       => 'หมายเหตุ',
    ];

    /**
     * รับ header ได้หลายแบบ (ไทย/อังกฤษ/ตัวพิมพ์เล็ก-ใหญ่) → map เป็น key มาตรฐาน
     */
    private const HEADER_ALIASES = [
        // EN
        'item_code' => 'item_code', 'code' => 'item_code', 'sku' => 'item_code',
        'item_name' => 'item_name', 'name' => 'item_name',
        'unit' => 'unit_name', 'unit_name' => 'unit_name',
        'category' => 'category', 'cat' => 'category',
        'balance' => 'balance', 'qty' => 'balance', 'on_hand' => 'balance',
        'min' => 'min_qty', 'min_qty' => 'min_qty', 'minimum' => 'min_qty',
        'max' => 'max_qty', 'max_qty' => 'max_qty', 'maximum' => 'max_qty',
        'note' => 'note', 'remark' => 'note',
        // TH
        'รหัสวัสดุ' => 'item_code',
        'รหัส' => 'item_code',
        'ชื่อวัสดุ' => 'item_name',
        'ชื่อ' => 'item_name',
        'หน่วย' => 'unit_name',
        'หมวด' => 'category',
        'หมวดหมู่' => 'category',
        'คงเหลือ' => 'balance',
        'คงเหลือปัจจุบัน' => 'balance',
        'จำนวนต่ำสุด' => 'min_qty',
        'ขั้นต่ำ' => 'min_qty',
        'จำนวนสูงสุด' => 'max_qty',
        'ขั้นสูง' => 'max_qty',
        'หมายเหตุ' => 'note',
    ];

    public const STATUS_NEW       = 'new';
    public const STATUS_UPDATE    = 'update';
    public const STATUS_UNCHANGED = 'unchanged';
    public const STATUS_ERROR     = 'error';

    /**
     * สร้าง Spreadsheet สำหรับ download
     * @param int $warehouseId
     * @param string $mode template (ว่าง) | snapshot (เติมค่าเดิม)
     * @return Spreadsheet
     */
    public function generateTemplate(int $warehouseId, string $mode = self::MODE_TEMPLATE): Spreadsheet
    {
        $warehouse = Warehouse::findOne(['id' => $warehouseId]);
        if (!$warehouse) {
            throw new \RuntimeException('ไม่พบคลัง id=' . $warehouseId);
        }

        $rows = $this->loadItemsWithSettings($warehouse);

        $spread = new Spreadsheet();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('MinMax');

        // Header row
        $headers = self::TEMPLATE_HEADERS;
        foreach ($headers as $i => $k) {
            $col = chr(65 + $i); // A, B, C, ...
            $sheet->setCellValue($col . '1', self::TEMPLATE_HEADER_LABELS_TH[$k]);
        }
        $sheet->getStyle('A1:' . chr(64 + count($headers)) . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->freezePane('A2');

        // Data rows
        $rowNum = 2;
        foreach ($rows as $r) {
            $isSnapshot = $mode === self::MODE_SNAPSHOT && !empty($r['setting_id']);
            $minVal = $isSnapshot ? (float) $r['setting_min_qty'] : '';
            $maxVal = $isSnapshot ? (float) $r['setting_max_qty'] : '';
            $note = $isSnapshot ? (string) ($r['setting_note'] ?? '') : '';

            // item_code เป็น TEXT (กัน Excel แปลง 0-00001 เป็น date)
            $sheet->setCellValueExplicit('A' . $rowNum, $r['item_code'], DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNum, $r['item_name']);
            $sheet->setCellValue('C' . $rowNum, $r['unit_name']);
            $sheet->setCellValue('D' . $rowNum, $r['category_title']);
            $sheet->setCellValue('E' . $rowNum, (float) ($r['balance_qty'] ?? 0));
            if ($minVal !== '') $sheet->setCellValue('F' . $rowNum, $minVal);
            if ($maxVal !== '') $sheet->setCellValue('G' . $rowNum, $maxVal);
            $sheet->setCellValue('H' . $rowNum, $note);
            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        if ($lastRow >= 2) {
            // read-only cols (A-E) — สี gray bg
            $sheet->getStyle('A2:E' . $lastRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
            ]);
            // editable cols (F-H) — สี yellow อ่อนๆ
            $sheet->getStyle('F2:H' . $lastRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDF0']],
            ]);
            // number format cols E, F, G
            $sheet->getStyle('E2:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.##');
            // alignment
            $sheet->getStyle('E2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // border
            $sheet->getStyle('A1:H' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('DEE2E6'));
        }

        // auto-size cols (พอประมาณ)
        $widths = ['A' => 14, 'B' => 38, 'C' => 10, 'D' => 18, 'E' => 14, 'F' => 12, 'G' => 12, 'H' => 24];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $spread;
    }

    /**
     * Parse ไฟล์ Excel/CSV ที่ user upload → return array ของ row ที่ classify แล้ว
     *
     * @param string $filepath path ของไฟล์ที่ upload
     * @param int $warehouseId
     * @return array {rows: [...], summary: [...], headers: [...]}
     */
    public function parseFile(string $filepath, int $warehouseId): array
    {
        $warehouse = Warehouse::findOne(['id' => $warehouseId]);
        if (!$warehouse) {
            throw new \RuntimeException('ไม่พบคลัง');
        }

        $reader = IOFactory::createReaderForFile($filepath);
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }
        $spread = $reader->load($filepath);
        $sheet = $spread->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        if (count($rawRows) < 2) {
            return ['rows' => [], 'summary' => ['total' => 0], 'headers' => []];
        }

        // map header → index
        $rawHeader = array_shift($rawRows);
        $colMap = $this->mapHeaders($rawHeader);

        if (!isset($colMap['item_code']) || !isset($colMap['min_qty']) || !isset($colMap['max_qty'])) {
            throw new \RuntimeException('ไฟล์ขาด header: ต้องมี item_code, min_qty, max_qty');
        }

        // โหลด context: items ที่ valid ใน warehouse นี้ + existing settings
        $allowedTypes = $warehouse->getAllowedItemTypeCodes();
        $validItems = (new Query())
            ->select(['code', 'title', 'category_id'])
            ->from('{{%categorise}}')
            ->where(['name' => 'asset_item', 'group_id' => 'MATER', 'active' => 1])
            ->indexBy('code')
            ->all();
        $existing = (new Query())
            ->select(['item_code', 'min_qty', 'max_qty'])
            ->from('{{%stock_item_warehouse_setting}}')
            ->where(['warehouse_id' => $warehouseId])
            ->indexBy('item_code')
            ->all();

        $rows = [];
        $summary = [self::STATUS_NEW => 0, self::STATUS_UPDATE => 0, self::STATUS_UNCHANGED => 0, self::STATUS_ERROR => 0];
        $seenCodes = [];

        foreach ($rawRows as $i => $raw) {
            $rowIndex = $i + 2; // +2 เพราะ array_shift หัก 1 + index 0-based
            $code = isset($colMap['item_code']) ? trim((string) ($raw[$colMap['item_code']] ?? '')) : '';
            // skip blank rows
            if ($code === '' && $this->isRowEmpty($raw)) {
                continue;
            }
            $minVal = isset($colMap['min_qty']) ? trim((string) ($raw[$colMap['min_qty']] ?? '')) : '';
            $maxVal = isset($colMap['max_qty']) ? trim((string) ($raw[$colMap['max_qty']] ?? '')) : '';
            $note = isset($colMap['note']) ? trim((string) ($raw[$colMap['note']] ?? '')) : '';

            $errors = [];

            if ($code === '') {
                $errors[] = 'รหัสวัสดุห้ามว่าง';
            } elseif (!isset($validItems[$code])) {
                $errors[] = 'ไม่มีวัสดุรหัสนี้ในระบบ';
            } else {
                $item = $validItems[$code];
                if (!empty($allowedTypes) && !in_array((string) $item['category_id'], array_map('strval', $allowedTypes), true)) {
                    $errors[] = 'หมวดวัสดุไม่ตรงประเภทที่คลังนี้รับเข้า';
                }
            }
            if ($code !== '' && isset($seenCodes[$code])) {
                $errors[] = 'รหัสซ้ำกับแถวก่อนหน้า';
            }
            if ($minVal === '') {
                $errors[] = 'Min ห้ามว่าง';
            } elseif (!is_numeric($minVal)) {
                $errors[] = 'Min ต้องเป็นตัวเลข';
            } elseif ((float) $minVal < 0) {
                $errors[] = 'Min ต้องไม่ติดลบ';
            }
            if ($maxVal === '') {
                $errors[] = 'Max ห้ามว่าง';
            } elseif (!is_numeric($maxVal)) {
                $errors[] = 'Max ต้องเป็นตัวเลข';
            } elseif ((float) $maxVal < 0) {
                $errors[] = 'Max ต้องไม่ติดลบ';
            }
            if (empty($errors) && (float) $maxVal < (float) $minVal) {
                $errors[] = 'Max ต้องไม่น้อยกว่า Min';
            }

            $item = $validItems[$code] ?? null;
            $existingRow = $existing[$code] ?? null;
            $status = self::STATUS_ERROR;
            if (empty($errors)) {
                if (!$existingRow) {
                    $status = self::STATUS_NEW;
                } elseif ((float) $existingRow['min_qty'] === (float) $minVal && (float) $existingRow['max_qty'] === (float) $maxVal) {
                    $status = self::STATUS_UNCHANGED;
                } else {
                    $status = self::STATUS_UPDATE;
                }
                if ($code !== '') $seenCodes[$code] = true;
            }
            $summary[$status]++;

            $rows[] = [
                'row_index'   => $rowIndex,
                'item_code'   => $code,
                'item_name'   => $item['title'] ?? '(ไม่พบในระบบ)',
                'min_qty'     => $minVal === '' ? null : (float) $minVal,
                'max_qty'     => $maxVal === '' ? null : (float) $maxVal,
                'note'        => $note,
                'current_min' => $existingRow ? (float) $existingRow['min_qty'] : null,
                'current_max' => $existingRow ? (float) $existingRow['max_qty'] : null,
                'status'      => $status,
                'errors'      => $errors,
            ];
        }

        $summary['total'] = count($rows);
        return ['rows' => $rows, 'summary' => $summary, 'headers' => array_keys($colMap)];
    }

    /**
     * โหลด items + setting + balance สำหรับ export (ใช้ logic เดียวกับ controller actionStockMinMax)
     */
    protected function loadItemsWithSettings(Warehouse $warehouse): array
    {
        $allowedTypes = $warehouse->getAllowedItemTypeCodes();

        $balanceSub = (new Query())
            ->select(['item_code', 'balance_total' => new Expression('SUM(balance_qty)')])
            ->from('{{%stock_balance}}')
            ->where(['warehouse_id' => $warehouse->id])
            ->groupBy('item_code');

        $q = (new Query())
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'i.category_id',
                'i.data_json AS item_data_json',
                'category_title' => 'cat.title',
                's.id AS setting_id',
                's.min_qty AS setting_min_qty',
                's.max_qty AS setting_max_qty',
                's.note AS setting_note',
                'balance_qty' => new Expression('COALESCE(b.balance_total, 0)'),
            ])
            ->from(['i' => '{{%categorise}}'])
            ->leftJoin(
                ['s' => '{{%stock_item_warehouse_setting}}'],
                's.item_code = i.code AND s.warehouse_id = :wid',
                [':wid' => $warehouse->id]
            )
            ->leftJoin(['b' => $balanceSub], 'b.item_code = i.code')
            ->leftJoin(['cat' => '{{%categorise}}'], 'cat.code = i.category_id AND cat.name = :cn', [':cn' => 'asset_type'])
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER', 'i.active' => 1]);

        if (!empty($allowedTypes)) {
            $q->andWhere(['i.category_id' => $allowedTypes]);
        }

        $rows = $q->orderBy(['i.code' => SORT_ASC])->all();
        foreach ($rows as &$r) {
            $j = $r['item_data_json'];
            if (is_string($j)) $j = json_decode($j, true);
            $r['unit_name'] = $j['unit_name'] ?? '';
            $r['category_title'] = $r['category_title'] ?? '';
        }
        return $rows;
    }

    /**
     * แปลง header แถวแรกของไฟล์ → [logical_key => column_index]
     */
    protected function mapHeaders(array $raw): array
    {
        $map = [];
        foreach ($raw as $idx => $cell) {
            $norm = $this->normalizeHeader((string) $cell);
            if ($norm === '') continue;
            if (isset(self::HEADER_ALIASES[$norm])) {
                $map[self::HEADER_ALIASES[$norm]] = $idx;
            }
        }
        return $map;
    }

    protected function normalizeHeader(string $s): string
    {
        $s = trim($s);
        if ($s === '') return '';
        // lowercase สำหรับ English
        $s = mb_strtolower($s, 'UTF-8');
        // ตัด space ออก (กัน 'item code' vs 'item_code')
        $s = preg_replace('/\s+/u', '_', $s);
        return $s;
    }

    protected function isRowEmpty(array $raw): bool
    {
        foreach ($raw as $v) {
            if ($v !== null && trim((string) $v) !== '') return false;
        }
        return true;
    }
}
