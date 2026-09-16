<?php

namespace app\modules\inventoryV2\services;

use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\db\Query;

/** Read-only preflight for the certified legacy monthly workbook. Never writes stock or snapshots. */
class MonthlySnapshotReconciliationService
{
    public const MAX_ROWS = 20000;
    private const FIELDS = [6 => 'opening_qty', 7 => 'opening_value', 8 => 'in_qty', 9 => 'in_value',
        10 => 'out_qty', 11 => 'out_value', 12 => 'closing_qty', 13 => 'closing_value'];

    public static function readFile(string $path, int $year, int $month): array
    {
        if (!is_file($path) || filesize($path) > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('ไฟล์ต้องเป็น XLSX ขนาดไม่เกิน 10 MB');
        }
        // Bound the uncompressed archive before asking the spreadsheet reader to allocate memory.
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) throw new \InvalidArgumentException('อ่านไฟล์ XLSX ไม่ได้');
        try {
            $size = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) $size += $zip->statIndex($i)['size'];
            if ($size > 100 * 1024 * 1024) throw new \InvalidArgumentException('ข้อมูลภายในไฟล์ใหญ่เกิน 100 MB');
        } finally { $zip->close(); }
        $reader = new Xlsx();
        $reader->setReadDataOnly(false);
        if (!$reader->canRead($path)) throw new \InvalidArgumentException('รองรับเฉพาะไฟล์ XLSX');
        $book = $reader->load($path);
        try {
            $result = self::readWorkbook($book, $year, $month);
            $result['sha256'] = hash_file('sha256', $path);
            return $result;
        } finally { $book->disconnectWorksheets(); }
    }

    public static function readWorkbook(Spreadsheet $book, int $year, int $month): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            throw new \InvalidArgumentException('ระบุปี ค.ศ. และเดือนให้ถูกต้อง');
        }
        $detail = $book->getSheetByName('สรุปรายการ');
        $summary = $book->getSheetByName('สรุปวัสดุคงคลัง');
        if (!$detail || !$summary) throw new \InvalidArgumentException('ต้องมีชีต สรุปรายการ และ สรุปวัสดุคงคลัง');
        $months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $label = preg_replace('/\s+/u', ' ', trim((string) $summary->getCell('A2')->getValue()));
        if ($label !== 'เดือน ' . $months[$month] . ' ' . ($year + 543)) {
            throw new \InvalidArgumentException('งวดที่เลือกไม่ตรงกับหัวรายงานใน Excel');
        }
        $headers = [2 => 'รหัส', 3 => 'รายการสินค้า', 4 => 'ประเภท', 5 => 'หน่วย',
            6 => 'จำนวนคงเหลือ', 7 => 'มูลค่าคงเหลือ', 8 => 'จำนวนรับใหม่', 9 => 'มูลค่ารับใหม่',
            10 => 'จำนวนจ่ายใหม่', 11 => 'มูลค่าจ่ายใหม่', 12 => 'จำนวนคงเหลือ', 13 => 'มูลค่าคงเหลือ'];
        foreach ($headers as $col => $expected) {
            if (trim((string) $detail->getCell([$col, 2])->getValue()) !== $expected) {
                throw new \InvalidArgumentException('หัวคอลัมน์รายละเอียดไม่ตรงรูปแบบ: ' . $expected);
            }
        }
        if ($detail->getHighestDataRow() > self::MAX_ROWS + 2) throw new \InvalidArgumentException('จำนวนแถวเกิน 20,000 แถว');
        $rows = []; $categories = []; $codes = []; $errors = [];
        for ($n = 3; $n <= $detail->getHighestDataRow(); $n++) {
            $code = trim((string) $detail->getCell([2, $n])->getValue());
            if ($code === '') {
                foreach (range(3, 13) as $c) {
                    if ($detail->getCell([$c, $n])->getValue() !== null) $errors[] = "แถว {$n}: มีข้อมูลแต่ไม่มีรหัสวัสดุ";
                }
                continue;
            }
            if ($detail->getCell([2, $n])->isFormula()) throw new \InvalidArgumentException("รหัสวัสดุเป็นสูตรที่แถว {$n}");
            $row = ['row' => $n, 'item_code' => $code, 'item_name' => (string) $detail->getCell([3, $n])->getValue(),
                'category' => trim((string) $detail->getCell([4, $n])->getValue()), 'unit' => (string) $detail->getCell([5, $n])->getValue(), 'issues' => []];
            if (mb_strlen($code) > 50 || preg_match('/^[=+@]/', $code)) $row['issues'][] = 'รูปแบบรหัสวัสดุไม่ถูกต้อง';
            foreach (self::FIELDS as $c => $field) {
                $cell = $detail->getCell([$c, $n]);
                // Source amounts must be literal: do not evaluate arbitrary workbook formulas or external links.
                $raw = $cell->getValue();
                if ($cell->isFormula() || !is_numeric($raw) || !is_finite((float) $raw) || abs((float) $raw) > 1e12) {
                    throw new \InvalidArgumentException("ช่อง {$cell->getCoordinate()} ต้องเป็นตัวเลขที่บันทึกไว้ ไม่ใช่สูตรหรือช่องว่าง");
                }
                $row[$field] = (float) $raw;
            }
            $row['qty_equation_delta'] = round($row['opening_qty'] + $row['in_qty'] - $row['out_qty'] - $row['closing_qty'], 6);
            $row['value_equation_delta'] = round($row['opening_value'] + $row['in_value'] - $row['out_value'] - $row['closing_value'], 4);
            if (abs($row['qty_equation_delta']) > 0.00001) $row['issues'][] = 'จำนวนไม่ตรงสูตร';
            if (abs($row['value_equation_delta']) > 0.011) $row['issues'][] = 'มูลค่าไม่ตรงสูตร';
            if ($row['closing_qty'] < 0 || $row['closing_value'] < 0) $row['issues'][] = 'ยอดคงเหลือติดลบ';
            $codes[$code][] = $n;
            $categories[$row['category']] = ($categories[$row['category']] ?? 0) + $row['closing_value'];
            $rows[] = $row;
        }
        if (!$rows) throw new \InvalidArgumentException('ไม่พบรายละเอียดวัสดุ');
        foreach ($rows as &$row) if (count($codes[$row['item_code']]) > 1) $row['issues'][] = 'รหัสซ้ำ: ต้องแยกคลัง/แถว';
        unset($row);
        $summaryCategories = []; $summaryTotal = null;
        for ($n = 6; $n <= min($summary->getHighestDataRow(), 200); $n++) {
            $name = trim((string) $summary->getCell([2, $n])->getValue());
            if ($name === '') continue;
            $cell = $summary->getCell([9, $n]);
            $cached = $cell->isFormula() ? $cell->getOldCalculatedValue() : $cell->getValue();
            if (!is_numeric($cached) || !is_finite((float) $cached)) throw new \InvalidArgumentException("ยอดสรุป I{$n} ไม่มีค่าตัวเลขที่บันทึกไว้ กรุณาบันทึกไฟล์จาก Excel อีกครั้ง");
            if ($name === 'รวม') { $summaryTotal = (float) $cached; break; }
            if (isset($summaryCategories[$name])) $errors[] = 'ประเภทซ้ำในหน้าสรุป: ' . $name;
            $summaryCategories[$name] = (float) $cached;
        }
        foreach (array_unique(array_merge(array_keys($categories), array_keys($summaryCategories))) as $name) {
            if (!array_key_exists($name, $categories) || !array_key_exists($name, $summaryCategories)
                || abs($categories[$name] - $summaryCategories[$name]) > 0.011) $errors[] = 'ยอดรายประเภทไม่ตรงหน้าสรุป: ' . $name;
        }
        $total = round(array_sum($categories), 2);
        if ($summaryTotal === null || abs($total - $summaryTotal) > 0.011) $errors[] = 'ยอดรวมรายละเอียดไม่ตรงยอดรวมหน้าสรุป';
        return ['year' => $year, 'month' => $month, 'rows' => $rows, 'categories' => $categories,
            'source_total' => $total, 'errors' => array_values(array_unique($errors)),
            'duplicates' => array_filter($codes, static fn($v) => count($v) > 1)];
    }

    public static function inspectDatabase(array $source): array
    {
        $db = Yii::$app->db;
        // One repeatable-read view: totals and individual rows must describe the same database state.
        $tx = $db->beginTransaction($db->driverName === 'mysql' ? \yii\db\Transaction::REPEATABLE_READ : \yii\db\Transaction::SERIALIZABLE);
        try {
            $warehouses = Warehouse::find()->where(['warehouse_type' => 'MAIN'])->indexBy('id')->asArray()->all();
            $ids = array_keys($warehouses);
            $snapshots = (new Query())->from('stock_monthly_report')->where(['report_year' => $source['year'],
                'report_month' => $source['month'], 'warehouse_id' => $ids])->orderBy(['warehouse_id' => SORT_ASC, 'item_code' => SORT_ASC, 'id' => SORT_ASC])->all();
            $items = StockItem::find()->select(['code', 'title', 'category_id'])->orderBy(['code'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
            $settings = (new Query())->select(['item_code', 'warehouse_id'])->from('stock_item_warehouse_setting')->where(['warehouse_id' => $ids])->orderBy(['item_code'=>SORT_ASC,'warehouse_id'=>SORT_ASC])->all();
            $later = (new Query())->select(['report_year', 'report_month', 'warehouse_id'])->distinct()->from('stock_monthly_report')
                ->where(['warehouse_id' => $ids])->andWhere(['>', new \yii\db\Expression('report_year * 12 + report_month'), $source['year'] * 12 + $source['month']])->orderBy(['report_year'=>SORT_ASC,'report_month'=>SORT_ASC,'warehouse_id'=>SORT_ASC])->all();
            $result = self::reconcile($source, $snapshots, $items, $settings, $warehouses);
            $result['later_periods'] = $later;
            $result['fingerprint'] = hash('sha256', json_encode([$snapshots, $items, $settings, $later]));
            $result['checked_at'] = date('Y-m-d H:i:s');
            return $result;
        } finally { $tx->rollBack(); }
    }

    /** Pure comparison keeps duplicate source rows intact. Candidate warehouses are evidence, never assignments. */
    public static function reconcile(array $source, array $snapshots, array $items, array $settings, array $warehouses): array
    {
        $catalog = []; $current = []; $candidate = []; $sourceCodes = []; $changes = []; $missing = [];
        foreach ($items as $i) $catalog[$i['code']][] = $i;
        foreach ($snapshots as $s) {
            $current[$s['item_code']][] = $s;
            $candidate[$s['item_code']][(int) $s['warehouse_id']] = true;
        }
        foreach ($settings as $s) $candidate[$s['item_code']][(int) $s['warehouse_id']] = true;
        foreach ($source['rows'] as &$r) {
            $code = $r['item_code'];
            $sourceCodes[$code][] = $r;
            $r['candidates'] = [];
            foreach (array_keys($candidate[$code] ?? []) as $wid) {
                if (isset($warehouses[$wid])) $r['candidates'][$wid] = $warehouses[$wid]['warehouse_name'];
            }
            if (count($catalog[$code] ?? []) !== 1) $r['issues'][] = 'ไม่พบรหัสวัสดุ MATER ที่ไม่ซ้ำในทะเบียน';
            if (count($r['candidates']) !== 1) $r['issues'][] = 'ต้องระบุคลัง';
            $r['current_snapshots'] = array_map(static fn($s) => [
                'warehouse_id' => (int) $s['warehouse_id'], 'closing_qty' => (float) $s['closing_qty'],
                'closing_value' => (float) $s['closing_value'], 'created_at' => $s['created_at'] ?? null,
                'unit_name' => $s['unit_name'] ?? '',
            ], $current[$code] ?? []);
        }
        unset($r);
        foreach ($sourceCodes as $code => $rows) {
            $now = $current[$code] ?? [];
            $oldQty = array_sum(array_column($rows, 'closing_qty')); $oldValue = array_sum(array_column($rows, 'closing_value'));
            $nowQty = array_sum(array_column($now, 'closing_qty')); $nowValue = array_sum(array_column($now, 'closing_value'));
            if (!$now || abs($nowQty - $oldQty) > 0.00001 || abs($nowValue - $oldValue) > 0.011) {
                $changes[] = ['item_code' => $code, 'item_name' => $rows[0]['item_name'], 'source_qty' => $oldQty,
                    'current_qty' => $nowQty, 'source_value' => round($oldValue, 2), 'current_value' => round($nowValue, 2),
                    'delta' => round($nowValue - $oldValue, 2), 'snapshot_missing' => !$now];
            }
        }
        foreach ($current as $code => $rows) if (!isset($sourceCodes[$code])) $missing[] = [
            'item_code' => $code, 'closing_qty' => array_sum(array_column($rows, 'closing_qty')),
            'closing_value' => round(array_sum(array_column($rows, 'closing_value')), 2)];
        usort($changes, static fn($a, $b) => abs($b['delta']) <=> abs($a['delta']));
        $source['changes'] = $changes; $source['database_only'] = $missing;
        $source['current_total'] = round(array_sum(array_column($snapshots, 'closing_value')), 2);
        $source['delta'] = round($source['current_total'] - $source['source_total'], 2);
        $source['unresolved'] = array_values(array_filter($source['rows'], static fn($r) => !empty($r['issues'])));
        $source['warehouses'] = array_map(static fn($w) => $w['warehouse_name'], $warehouses);
        return $source;
    }
}
