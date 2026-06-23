<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * รายงานสรุปรายงานวัสดุคงคลัง (ส่งบัญชี)
 */
class ReportController extends Controller
{
    /** รหัสคลังที่จัดเป็น "จ่ายส่วนของ รพ.aq" (ที่เหลือนับเป็นโรงพยาบาล) */
    public static function getDisburseSubWarehouseIds()
    {
        return (array) (Yii::$app->params['inventoryV2.disburseSubWarehouseIds'] ?? []);
    }

    /**
     * รายงานสรุปรายงานวัสดุคงคลัง แยกตามประเภทวัสดุ
     */
    public function actionMaterialSummary()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        $rows = $this->aggregateByCategory($year, $month, $warehouseId);
        $hasData = !empty($rows);

        return $this->render('material-summary', [
            'year' => $year,
            'month' => $month,
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'hasData' => $hasData,
        ]);
    }

    public function actionBalanceByWarehouse()
{
    $request = $this->request;
    $warehouseId = $request->get('warehouse_id') !== '' ? $request->get('warehouse_id') : null;
    
    // รับค่าตัวกรองใหม่
    $categoryId = $request->get('category_id');
    $status = $request->get('status');
    $search = $request->get('search');

    // ... (ส่วนการดึง $warehouses เหมือนเดิม) ...
    $listMain = Warehouse::find()->where(['warehouse_type' => 'MAIN'])->orderBy(['warehouse_name' => SORT_ASC])->all();
    $listSub = Warehouse::find()->where(['warehouse_type' => 'SUB'])->andWhere(['or', ['delete' => null], ['delete' => '']])->orderBy(['warehouse_name' => SORT_ASC])->all();
    
    $warehouses = ['' => '-- ทุกคลัง --'];
    foreach ($listMain as $w) $warehouses[$w->id] = 'คลังหลัก: ' . $w->warehouse_name;
    foreach ($listSub as $w) $warehouses[$w->id] = 'คลังย่อย: ' . $w->warehouse_name;

    $allWarehouses = array_merge($listMain, $listSub);
    $warehouseIds = $warehouseId ? [$warehouseId] : array_column($allWarehouses, 'id');

    // ดึงข้อมูลดิบมาก่อน
    $data = $this->getBalanceByWarehouseData($warehouseIds, $listMain, $listSub);
    $rows = $data['rows'];

    // --- ส่วนการกรองข้อมูล (แก้ไขเพื่อป้องกัน Error) ---
if ($categoryId || $status || $search) {
    $rows = array_filter($rows, function($item) use ($categoryId, $status, $search) {
        $match = true;
        
        // 1. กรองประเภท (เพิ่มการเช็ก isset หรือใช้ ?? null)
        if ($categoryId) {
            $itemCatId = $item['category_id'] ?? null; // ป้องกัน Undefined array key
            if ($itemCatId != $categoryId) {
                $match = false;
            }
        }
        
        // 2. กรองสถานะ
        if ($status && $match) {
            $isBelowMin = $item['below_min'] ?? false;
            $isBelowMax = $item['below_max'] ?? false;

            if ($status == 'below_min' && !$isBelowMin) $match = false;
            if ($status == 'below_max' && (!$isBelowMax || $isBelowMin)) $match = false;
            if ($status == 'normal' && ($isBelowMin || $isBelowMax)) $match = false;
        }
        
        // 3. ค้นหาแบบเร็ว (ป้องกันค่า null ใน item_code/item_name)
        if ($search && $match) {
            $s = mb_strtolower($search, 'UTF-8');
            $itemCode = mb_strtolower($item['item_code'] ?? '', 'UTF-8');
            $itemName = mb_strtolower($item['item_name'] ?? '', 'UTF-8');
            
            if (strpos($itemCode, $s) === false && strpos($itemName, $s) === false) {
                $match = false;
            }
        }
        
        return $match;
    });
        
        // คำนวณ Summary ใหม่หลังจากกรอง
        $summary = [
            'total_value' => array_sum(array_column($rows, 'value')),
            'items_count' => count($rows),
            'below_min_count' => count(array_filter($rows, fn($r) => $r['below_min'])),
            'below_max_count' => count(array_filter($rows, fn($r) => $r['below_max'] && !$r['below_min'])),
        ];
    } else {
        $summary = $data['summary'];
    }

    // ดึงรายการประเภทวัสดุไปแสดงใน Dropdown
    $categories = \yii\helpers\ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','group_id' => 'MATER'])->all(), '', 'title');

    return $this->render('balance-by-warehouse', [
        'warehouseId' => $warehouseId,
        'warehouses' => $warehouses,
        'rows' => $rows,
        'summary' => $summary,
        'categories' => $categories,
        'categoryId' => $categoryId,
        'status' => $status,
        'search' => $search,
    ]);
}

    /**
     * ดึงข้อมูลรายการวัสดุคงเหลือตามคลัง (ใช้ทั้งหน้าแสดงและ export Excel)
     * @param int[] $warehouseIds
     * @param \app\modules\inventoryV2\models\Warehouse[] $listMain
     * @param \app\modules\inventoryV2\models\Warehouse[] $listSub
     * @return array{rows: array, summary: array}
     */
    protected function getBalanceByWarehouseData($warehouseIds, $listMain, $listSub)
    {
        if (empty($warehouseIds)) {
            return [
                'rows' => [],
                'summary' => ['total_value' => 0, 'below_min_count' => 0, 'below_max_count' => 0, 'items_count' => 0],
            ];
        }
        $latestPriceSub = (new Query())
            ->select(['sd2.item_code', 'sd2.lot_number', 'sd2.unit_price'])
            ->from(['sd2' => StockDetail::tableName()])
            ->innerJoin(['so2' => StockOrder::tableName()], 'so2.id = sd2.stock_order_id AND so2.order_type = \'IN\'')
            ->innerJoin(
                ['latest' => (new Query())
                    ->select(['sd3.item_code', 'sd3.lot_number', 'MAX(sd3.id) AS mid'])
                    ->from(['sd3' => StockDetail::tableName()])
                    ->innerJoin(['so3' => StockOrder::tableName()], 'so3.id = sd3.stock_order_id AND so3.order_type = \'IN\'')
                    ->groupBy('sd3.item_code', 'sd3.lot_number')],
                'latest.item_code = sd2.item_code AND latest.lot_number = sd2.lot_number AND latest.mid = sd2.id'
            );

        $query = (new Query())
            ->select([
                'sb.warehouse_id',
                'sb.item_code',
                new Expression('i.title AS item_name'),
                new Expression('i.qty_min AS min_qty'),
                new Expression('i.qty_max AS max_qty'),
                new Expression('COALESCE(cat.title, i.category_id, \'อื่นๆ\') AS category_title'),
                new Expression('SUM(sb.balance_qty) AS balance_qty'),
                new Expression('SUM(sb.balance_qty * COALESCE(lp.unit_price, 0)) AS value'),
            ])
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->leftJoin(['lp' => $latestPriceSub], 'lp.item_code = sb.item_code AND lp.lot_number = sb.lot_number')
            ->where(['sb.warehouse_id' => $warehouseIds])
            // ->andWhere(['>', 'sb.balance_qty', 0]) // แสดงรายการที่มียอดคงเหลือ 0 ด้วย (เพื่อให้เห็นว่ามีรายการอะไรบ้างที่เคยมีการรับเข้ามาในคลัง แต่ตอนนี้หมดแล้ว)
            ->groupBy('sb.item_code', 'i.title', 'i.qty_min', 'i.qty_max', 'cat.title', 'i.category_id');

        $raw = $query->all();
        $warehouseNames = [];
        foreach ($listMain as $w) {
            $warehouseNames[$w->id] = 'คลังหลัก: ' . $w->warehouse_name;
        }
        foreach ($listSub as $w) {
            $warehouseNames[$w->id] = 'คลังย่อย: ' . $w->warehouse_name;
        }

        // Batch resolve image URLs (avoid N+1 + avoid base64 placeholder bloat)
        $itemCodes = array_values(array_unique(array_map(fn($r) => (string) $r['item_code'], $raw)));
        $items = empty($itemCodes)
            ? []
            : StockItem::find()->where(['code' => $itemCodes])->indexBy('code')->all();
        $refs = array_values(array_filter(array_map(fn($i) => $i->ref ?? null, $items)));
        $uploadsByRef = empty($refs)
            ? []
            : Uploads::find()->where(['ref' => $refs])->indexBy('ref')->all();
        $placeholderUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';

        $resolveImage = function ($itemCode) use ($items, $uploadsByRef, $placeholderUrl) {
            $item = $items[$itemCode] ?? null;
            if (!$item || empty($item->ref) || !isset($uploadsByRef[$item->ref])) {
                return $placeholderUrl;
            }
            return FileManagerHelper::getImg($uploadsByRef[$item->ref]->id);
        };

        $rows = [];
        $totalValue = 0;
        $belowMinCount = 0;
        $belowMaxCount = 0;

        foreach ($raw as $r) {
            $balance = (float) $r['balance_qty'];
            $value = (float) $r['value'];
            $minQty = $r['min_qty'] !== null ? (float) $r['min_qty'] : null;
            $maxQty = $r['max_qty'] !== null ? (float) $r['max_qty'] : null;
            $belowMin = $minQty !== null && $minQty > 0 && $balance < $minQty;
            $belowMax = $maxQty !== null && $maxQty > 0 && $balance < $maxQty;
            if ($belowMin) {
                $belowMinCount++;
            }
            if ($belowMax) {
                $belowMaxCount++;
            }
            $totalValue += $value;
            $item = $items[$r['item_code']] ?? null;
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $rows[] = [
                'warehouse_id' => (int) $r['warehouse_id'],
                'warehouse_name' => $warehouseNames[(int) $r['warehouse_id']] ?? (string) $r['warehouse_id'],
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) $r['item_name'],
                'category_title' => (string) ($r['category_title'] ?? 'อื่นๆ'),
                'unit_name' => $unitName ? (string) $unitName : '-',
                'image_url' => $resolveImage($r['item_code']),
                'balance_qty' => $balance,
                'value' => $value,
                'min_qty' => $minQty,
                'max_qty' => $maxQty,
                'below_min' => $belowMin,
                'below_max' => $belowMax,
            ];
        }
        $itemsCountQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->where(['sb.warehouse_id' => $warehouseIds])
            ->andWhere(['>', 'sb.balance_qty', 0]);
        $itemsCount = (int) (clone $itemsCountQuery)->select('sb.item_code')->groupBy('sb.item_code')->count();

        return [
            'rows' => $rows,
            'summary' => [
                'total_value' => $totalValue,
                'below_min_count' => $belowMinCount,
                'below_max_count' => $belowMaxCount,
                'items_count' => $itemsCount,
            ],
        ];
    }

    /**
     * ประวัติการเคลื่อนไหวของวัสดุ 1 รายการ ภายในคลังที่ระบุ (modal popup)
     * รองรับทั้งคลังหลัก (main_warehouse_id = X) และคลังย่อย (sub_warehouse_id = X)
     */
    public function actionItemHistory($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->getItemHistoryData($item_code, $warehouse_id, $start_date, $end_date);
    }

    /**
     * ดึงข้อมูลประวัติการเคลื่อนไหววัสดุ — ใช้ทั้ง modal (JSON) และ Excel export
     * @return array{meta: array, summary: array, transactions: array}
     */
    protected function getItemHistoryData($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        $warehouseId = (int) $warehouse_id;
        $startDate = $start_date ?: date('Y-m-01');
        $endDate = $end_date ?: date('Y-m-d');

        $warehouse = Warehouse::findOne($warehouseId);
        $warehouseLabel = $warehouse
            ? (($warehouse->warehouse_type === 'MAIN' ? 'คลังหลัก: ' : 'คลังย่อย: ') . $warehouse->warehouse_name)
            : (string) $warehouseId;

        $item = StockItem::find()->where(['code' => $item_code])->one();
        $itemName = $item->title ?? (string) $item_code;
        $unitName = ($item && method_exists($item, 'getUnitName')) ? (string) $item->getUnitName() : '-';

        $imageUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        if ($item && !empty($item->ref)) {
            $upload = Uploads::find()->where(['ref' => $item->ref])->one();
            if ($upload) {
                $imageUrl = FileManagerHelper::getImg($upload->id);
            }
        }

        $baseSelect = [
            'so.id',
            'so.order_no',
            'so.order_type',
            'so.source_type',
            'so.order_date',
            'so.main_warehouse_id',
            'so.sub_warehouse_id',
            'sd.qty',
            'sd.unit_price',
            'sd.lot_number',
        ];

        $perspective = function ($row) use ($warehouseId) {
            $isMain = (int) $row['main_warehouse_id'] === $warehouseId;
            $isSub = (int) ($row['sub_warehouse_id'] ?? 0) === $warehouseId;
            if ($row['order_type'] === 'IN' && $isMain) return 'in';
            if ($row['order_type'] === 'OUT' && $isSub && !$isMain) return 'in';
            if ($row['order_type'] === 'OUT' && $isMain) return 'out';
            if ($row['order_type'] === 'IN' && $isSub && !$isMain) return 'out';
            return 'out';
        };

        // 1) ยอดยกมาก่อนวันที่เริ่มต้น
        $bfRows = (new Query())
            ->select($baseSelect)
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $item_code])
            ->andWhere(['or',
                ['so.main_warehouse_id' => $warehouseId],
                ['so.sub_warehouse_id' => $warehouseId],
            ])
            ->andWhere(['<', 'so.order_date', $startDate . ' 00:00:00'])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->all();

        $qtyBF = 0.0;
        $valueBF = 0.0;
        foreach ($bfRows as $r) {
            $q = (float) $r['qty'];
            $p = (float) $r['unit_price'];
            if ($perspective($r) === 'in') {
                $qtyBF += $q;
                $valueBF += $q * $p;
            } else {
                $qtyBF -= $q;
                $valueBF -= $q * $p;
            }
        }

        // 2) Transactions ในช่วงเวลา
        $txRows = (new Query())
            ->select($baseSelect)
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $item_code])
            ->andWhere(['or',
                ['so.main_warehouse_id' => $warehouseId],
                ['so.sub_warehouse_id' => $warehouseId],
            ])
            ->andWhere(['between', 'so.order_date', $startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->orderBy(['so.order_date' => SORT_ASC, 'so.id' => SORT_ASC])
            ->all();

        $sourceLabel = [
            'PO' => 'รับเข้าจาก PO',
            'NORMAL' => 'รับเข้าทั่วไป',
            'DONATE' => 'รับบริจาค',
            'INITIAL' => 'ยอดยกมา (ตั้งต้น)',
            'FREE_GIFT' => 'ของแถม',
            'REQUEST' => 'จ่ายตามใบขอเบิก',
            'ADJUST' => 'ปรับยอด',
            'TRANSFER' => 'โอนย้ายคลัง',
            'ISSUE' => 'จ่ายออก',
        ];

        $runningQty = $qtyBF;
        $runningValue = $valueBF;
        $totalIn = 0.0;
        $totalOut = 0.0;
        $transactions = [];

        foreach ($txRows as $r) {
            $q = (float) $r['qty'];
            $p = (float) $r['unit_price'];
            $direction = $perspective($r);
            $delta = $q * $p;

            if ($direction === 'in') {
                $runningQty += $q;
                $runningValue += $delta;
                $totalIn += $q;
            } else {
                $runningQty -= $q;
                $runningValue -= $delta;
                $totalOut += $q;
            }

            $transactions[] = [
                'date' => date('d/m/Y', strtotime($r['order_date'])),
                'time' => date('H:i', strtotime($r['order_date'])),
                'order_no' => (string) $r['order_no'],
                'order_type' => (string) $r['order_type'],
                'source_label' => $sourceLabel[$r['source_type']] ?? ($r['source_type'] ?? '-'),
                'direction' => $direction,
                'qty' => $q,
                'unit_price' => $p,
                'balance_qty' => $runningQty,
                'balance_value' => $runningValue,
                'lot' => (string) ($r['lot_number'] ?? '-'),
            ];
        }

        // 3) ยอดคงเหลือปัจจุบันที่ stock_balance (truth source)
        $currentBalance = (float) (new Query())
            ->from(StockBalance::tableName())
            ->where(['item_code' => $item_code, 'warehouse_id' => $warehouseId])
            ->sum('balance_qty');

        return [
            'meta' => [
                'item_code' => (string) $item_code,
                'item_name' => $itemName,
                'unit_name' => $unitName,
                'image_url' => $imageUrl,
                'warehouse_label' => $warehouseLabel,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'qty_bf' => $qtyBF,
                'value_bf' => $valueBF,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'current_qty' => $currentBalance,
                'current_value' => $runningValue,
                'tx_count' => count($transactions),
            ],
            'transactions' => $transactions,
        ];
    }

    /**
     * Export ประวัติการเคลื่อนไหววัสดุ 1 รายการ ภายในคลัง — Excel
     */
    public function actionExportItemHistory($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        $data = $this->getItemHistoryData($item_code, $warehouse_id, $start_date, $end_date);
        $meta = $data['meta'];
        $summary = $data['summary'];
        $transactions = $data['transactions'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ประวัติเคลื่อนไหววัสดุ');

        // Header block
        $sheet->setCellValue('A1', 'ประวัติการเคลื่อนไหววัสดุ');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'รหัสวัสดุ: ' . $meta['item_code'] . '  |  ชื่อ: ' . $meta['item_name'] . '  |  หน่วย: ' . $meta['unit_name']);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $sheet->setCellValue('A3', 'คลัง: ' . $meta['warehouse_label'] . '  |  ช่วง: ' . $meta['start_date'] . ' ถึง ' . $meta['end_date']);
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getFont()->setSize(10);

        // Summary row
        $sheet->setCellValue('A5', 'ยอดยกมา (จำนวน)');
        $sheet->setCellValue('B5', $summary['qty_bf']);
        $sheet->setCellValue('C5', 'มูลค่ายกมา');
        $sheet->setCellValue('D5', $summary['value_bf']);
        $sheet->setCellValue('E5', 'รับเข้ารวม');
        $sheet->setCellValue('F5', $summary['total_in']);
        $sheet->setCellValue('G5', 'จ่ายออกรวม');
        $sheet->setCellValue('H5', $summary['total_out']);
        $sheet->setCellValue('I5', 'คงเหลือปัจจุบัน');
        $sheet->setCellValue('J5', $summary['current_qty']);
        $sheet->getStyle('A5:J5')->getFont()->setBold(true);
        $sheet->getStyle('A5:J5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3CD');
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('D5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J5')->getNumberFormat()->setFormatCode('#,##0.00');

        // Headers
        $headers = ['ลำดับ', 'วันที่', 'เวลา', 'เลขที่เอกสาร', 'รายการ', 'ทิศทาง', 'จำนวน', 'ราคา/หน่วย', 'ยอดสะสม', 'มูลค่าสะสม', 'Lot'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '7', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A7:' . $lastCol . '7')->getFont()->setBold(true);
        $sheet->getStyle('A7:' . $lastCol . '7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A7:' . $lastCol . '7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Opening row (BF)
        $rowNum = 8;
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, $meta['start_date']);
        $sheet->setCellValue('C' . $rowNum, '');
        $sheet->setCellValue('D' . $rowNum, '');
        $sheet->setCellValue('E' . $rowNum, 'ยอดยกมา');
        $sheet->setCellValue('F' . $rowNum, '');
        $sheet->setCellValue('G' . $rowNum, '');
        $sheet->setCellValue('H' . $rowNum, '');
        $sheet->setCellValue('I' . $rowNum, $summary['qty_bf']);
        $sheet->setCellValue('J' . $rowNum, $summary['value_bf']);
        $sheet->setCellValue('K' . $rowNum, '');
        $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F8F9FA');
        $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getFont()->setItalic(true);
        $rowNum++;

        foreach ($transactions as $i => $t) {
            $direction = $t['direction'] === 'in' ? 'รับเข้า' : 'จ่ายออก';
            $signedQty = $t['direction'] === 'in' ? $t['qty'] : -$t['qty'];
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $t['date']);
            $sheet->setCellValue('C' . $rowNum, $t['time']);
            $sheet->setCellValue('D' . $rowNum, $t['order_no']);
            $sheet->setCellValue('E' . $rowNum, $t['source_label']);
            $sheet->setCellValue('F' . $rowNum, $direction);
            $sheet->setCellValue('G' . $rowNum, $signedQty);
            $sheet->setCellValue('H' . $rowNum, $t['unit_price']);
            $sheet->setCellValue('I' . $rowNum, $t['balance_qty']);
            $sheet->setCellValue('J' . $rowNum, $t['balance_value']);
            $sheet->setCellValue('K' . $rowNum, $t['lot']);
            $rowNum++;
        }

        // Number format for numeric columns
        $sheet->getStyle('G8:J' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'K') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getStyle('A7:' . $lastCol . ($rowNum - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Output
        $filename = 'item-history-' . preg_replace('/[^A-Za-z0-9_\-]/', '', $meta['item_code']) . '-w' . (int) $warehouse_id . '-' . date('Ymd-His') . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/item_history_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function ($event) use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    /**
     * Export รายงานยอดคงเหลือตามคลังเป็น Excel
     */
    public function actionExportBalanceByWarehouse()
    {
        $warehouseId = $this->request->get('warehouse_id') !== null && $this->request->get('warehouse_id') !== ''
            ? (int) $this->request->get('warehouse_id')
            : null;

        $listMain = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $listSub = Warehouse::find()
            ->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $allWarehouses = array_merge($listMain, $listSub);
        $warehouseIds = $warehouseId ? [$warehouseId] : array_column($allWarehouses, 'id');

        $data = $this->getBalanceByWarehouseData($warehouseIds, $listMain, $listSub);
        $rows = $data['rows'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ยอดคงเหลือตามคลัง');

        $sheet->setCellValue('A1', 'รายการวัสดุคงเหลือตามคลัง');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['ลำดับ', 'คลัง', 'รหัส', 'ชื่อวัสดุ', 'ประเภท', 'หน่วย', 'จำนวนคงเหลือ', 'มูลค่า (บาท)', 'Min', 'Max', 'สถานะ'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFont()->setBold(true);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');

        $rowNum = 4;
        foreach ($rows as $i => $r) {
            $status = $r['below_min'] ? 'ต่ำกว่า Min' : ($r['below_max'] ? 'ต่ำกว่า Max' : 'พอดี');
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['warehouse_name']);
            $sheet->setCellValue('C' . $rowNum, $r['item_code']);
            $sheet->setCellValue('D' . $rowNum, $r['item_name']);
            $sheet->setCellValue('E' . $rowNum, $r['category_title']);
            $sheet->setCellValue('F' . $rowNum, $r['unit_name']);
            $sheet->setCellValue('G' . $rowNum, $r['balance_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['value']);
            $sheet->setCellValue('I' . $rowNum, $r['min_qty'] !== null ? $r['min_qty'] : '-');
            $sheet->setCellValue('J' . $rowNum, $r['max_qty'] !== null ? $r['max_qty'] : '-');
            $sheet->setCellValue('K' . $rowNum, $status);
            $rowNum++;
        }

        $sheet->getStyle('G4:H' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        if (!empty($rows)) {
            $sheet->getStyle('I4:J' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = 'balance-by-warehouse-' . date('Ymd-His') . '.xlsx';
        $this->response->format = Response::FORMAT_RAW;
        $this->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        Yii::$app->end();
    }

    /**
     * รายงานวัสดุไม่พอจ่าย: จากยอดที่ขอเบิก (ใบ PENDING + APPROVED) เทียบยอดคงเหลือในคลังหลัก
     * แสดงแต่ละรายการ ต้องซื้อเพิ่มเท่าไหร่ (shortfall) — รวมรายการที่ยังไม่เคยรับเข้าคลัง (ยอดคงเหลือ 0) เพื่อออกใบสั่งซื้อ
     * Filter: ประเภทวัสดุ, คลังย่อยที่ขอเบิก, คลังหลักที่รอจ่าย
     */
    public function actionInsufficientToDisburse()
    {
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;
        $categoryId = $this->request->get('category_id') !== null && $this->request->get('category_id') !== ''
            ? (string) $this->request->get('category_id') : null;

        $data = $this->getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId,$categoryId);
        $rows = $data['rows'];
        $listMain = $data['listMain'];
        $listSub = $data['listSub'];

        $categories = ['' => '-- ทุกประเภท --'] + \yii\helpers\ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'MATER'])->orderBy('title')->all(),
            'code',
            'title'
        );
        $mainWarehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listMain, 'id', 'warehouse_name');
        $subWarehouses = ['' => '-- ทุกคลังย่อยที่ขอเบิก --'] + \yii\helpers\ArrayHelper::map($listSub, 'id', 'warehouse_name');

        $this->view->params['active'] = 'report-balance';
        return $this->render('insufficient-to-disburse', [
            'rows' => $rows,
            'mainWarehouseId' => $mainWarehouseId,
            'subWarehouseId' => $subWarehouseId,
            'categoryId' => $categoryId,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
            'categories' => $categories,
        ]);
    }

    /**
     * Export รายงานวัสดุไม่พอจ่ายเป็น Excel (ใช้ filter เดียวกับหน้ารายงาน)
     */
    public function actionExportInsufficientToDisburse()
    {
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;

        $data = $this->getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId);
        $rows = $data['rows'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('วัสดุไม่พอจ่าย');

        $sheet->setCellValue('A1', 'รายงานวัสดุไม่พอจ่าย');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'จากรายการที่ขอเบิก (ใบรออนุมัติ + อนุมัติแล้ว) เทียบยอดคงเหลือในคลังหลัก — ต้องซื้อเพิ่ม');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $headers = ['ลำดับ', 'คลังหลักที่รอจ่าย', 'รหัส', 'ชื่อวัสดุ', 'ประเภท', 'หน่วย', 'จำนวนที่ขอเบิก', 'ยอดคงเหลือในคลัง', 'ต้องซื้อเพิ่ม'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:I4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3CD');

        $rowNum = 5;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['main_warehouse_name']);
            $sheet->setCellValue('C' . $rowNum, $r['item_code']);
            $sheet->setCellValue('D' . $rowNum, $r['item_name']);
            $sheet->setCellValue('E' . $rowNum, $r['category_title']);
            $sheet->setCellValue('F' . $rowNum, $r['unit_name']);
            $sheet->setCellValue('G' . $rowNum, $r['requested_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['balance_qty']);
            $sheet->setCellValue('I' . $rowNum, $r['shortfall']);
            $rowNum++;
        }

        $sheet->getStyle('G5:I' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        $filename = 'insufficient-to-disburse-' . date('Ymd-His') . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/insufficient_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function ($event) use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    /**
     * ดึงข้อมูลรายงานวัสดุไม่พอจ่าย (ใช้ทั้งหน้าแสดงและ export Excel)
     * @return array { rows: array, listMain: Warehouse[] }
     */
    // --- เพิ่ม $categoryId = null ใน signature ---
    protected function getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId, $categoryId = null)
    {
        $listMain = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $listSub = Warehouse::find()
            ->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $mainWarehouseIds = array_column($listMain, 'id');
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseIds = $mainWarehouseId ? [$mainWarehouseId] : $mainWarehouseIds;

        $reqSub = (new Query())
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds]);
        if ($subWarehouseId !== null) {
            $reqSub->andWhere(['so.sub_warehouse_id' => $subWarehouseId]);
        }
        if ($mainWarehouseId) {
            $reqSub->select(['so.main_warehouse_id', 'sd.item_code', 'SUM(sd.qty) AS requested_qty'])->groupBy('so.main_warehouse_id', 'sd.item_code');
        } else {
            $reqSub->select(['sd.item_code', 'SUM(sd.qty) AS requested_qty'])->groupBy('sd.item_code');
        }

        $balSub = (new Query())
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds]);
        if ($mainWarehouseId) {
            $balSub->select(['warehouse_id AS main_warehouse_id', 'item_code', 'SUM(balance_qty) AS balance_qty'])->groupBy('warehouse_id', 'item_code');
        } else {
            $balSub->select(['item_code', 'SUM(balance_qty) AS balance_qty'])->groupBy('item_code');
        }

        if ($mainWarehouseId) {
            $query = (new Query())
                ->select([
                    'req.item_code',
                    'req.main_warehouse_id',
                    'req.requested_qty',
                    'balance_qty' => new Expression('COALESCE(bal.balance_qty, 0)'),
                    'shortfall' => new Expression('req.requested_qty - COALESCE(bal.balance_qty, 0)'),
                    'item_name' => new Expression("COALESCE(i.title, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.main_warehouse_id = req.main_warehouse_id AND bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.code = req.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->andWhere(new Expression('req.requested_qty > COALESCE(bal.balance_qty, 0)'));
        } else {
            $query = (new Query())
                ->select([
                    'req.item_code',
                    'main_warehouse_id' => new Expression('NULL'),
                    'req.requested_qty',
                    'balance_qty' => new Expression('COALESCE(bal.balance_qty, 0)'),
                    'shortfall' => new Expression('req.requested_qty - COALESCE(bal.balance_qty, 0)'),
                    'item_name' => new Expression("COALESCE(i.title, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.code = req.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->andWhere(new Expression('req.requested_qty > COALESCE(bal.balance_qty, 0)'));
        }

        // --- เพิ่มเงื่อนไขค้นหาด้วย $categoryId ---
        // ใช้ andFilterWhere() เพื่อให้ถ้าค่า $categoryId ว่างเปล่า หรือ null ระบบจะไม่นำไปต่อท้ายเงื่อนไข WHERE
        $query->andFilterWhere(['i.category_id' => $categoryId]);

        $query->orderBy(['shortfall' => SORT_DESC]);
        $rows = $query->all();

        $mainNames = [];
        foreach ($listMain as $w) {
            $mainNames[$w->id] = $w->warehouse_name;
        }
        foreach ($rows as &$r) {
            $r['main_warehouse_name'] = isset($r['main_warehouse_id']) && $r['main_warehouse_id'] !== null
                ? ($mainNames[$r['main_warehouse_id']] ?? (string) $r['main_warehouse_id'])
                : 'ทุกคลังหลัก';
            $r['balance_qty'] = (float) ($r['balance_qty'] ?? 0);
            $r['requested_qty'] = (float) $r['requested_qty'];
            $r['shortfall'] = (float) $r['shortfall'];
            $item = StockItem::findOne($r['item_code']);
            $r['unit_name'] = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : '-';
        }
        unset($r);

        return ['rows' => $rows, 'listMain' => $listMain, 'listSub' => $listSub];
    }

    /**
     * รวมยอดจาก stock_monthly_report ตามประเภทวัสดุ (category)
     */
    protected function aggregateByCategory($year, $month, $warehouseId = null)
    {
        $query = (new Query())
            ->select([
                new Expression("COALESCE(cat.code, i.category_id, 'OTHER') AS category_code"),
                new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ') AS category_title"),
                'SUM(r.opening_qty) AS opening_qty',
                'SUM(r.opening_value) AS opening_value',
                'SUM(r.in_qty) AS in_qty',
                'SUM(r.in_value) AS in_value',
                'SUM(r.out_sub_qty) AS out_sub_qty',
                'SUM(r.out_hosp_qty) AS out_hosp_qty',
                'SUM(r.total_out_qty) AS total_out_qty',
                'SUM(r.total_out_value) AS total_out_value',
                'SUM(r.closing_qty) AS closing_qty',
                'SUM(r.closing_value) AS closing_value',
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'r.report_year' => $year,
                'r.report_month' => $month,
            ])
            ->groupBy(new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"));

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $raw = $query->all();
        $categories = Categorise::find()
            ->where(['name' => 'asset_type', 'category_id' => 4])
            ->orderBy(['code' => SORT_ASC])
            ->indexBy('code')
            ->all();

        $out = [];
        foreach ($raw as $r) {
            $code = $r['category_code'] ?? 'OTHER';
            $title = isset($categories[$code])
                ? '(' . $categories[$code]->code . ')' . $categories[$code]->title
                : '(' . $code . ') ' . ($r['category_title'] ?? '');
            $out[] = [
                'category_code' => $code,
                'category_label' => $title,
                'opening_qty' => (float) $r['opening_qty'],
                'opening_value' => (float) $r['opening_value'],
                'in_qty' => (float) $r['in_qty'],
                'in_value' => (float) $r['in_value'],
                'out_sub_qty' => (float) $r['out_sub_qty'],
                'out_hosp_qty' => (float) $r['out_hosp_qty'],
                'total_out_qty' => (float) $r['total_out_qty'],
                'total_out_value' => (float) $r['total_out_value'],
                'closing_qty' => (float) $r['closing_qty'],
                'closing_value' => (float) $r['closing_value'],
            ];
        }
        usort($out, function ($a, $b) {
            return strcmp($a['category_code'], $b['category_code']);
        });
        return $out;
    }

    /**
     * ปิดเดือน: คำนวณและบันทึกลง stock_monthly_report
     * ส่ง warehouse_id เป็นตัวเลข = ปิดเฉพาะคลังนั้น, ส่ง "all" หรือไม่ส่ง = ปิดรวมทุกคลังหลัก
     */
    public function actionCloseMonth()
    {
        $this->response->format = Response::FORMAT_JSON;
        $year = (int) $this->request->post('year', date('Y'));
        $month = (int) $this->request->post('month', (int) date('n'));
        $warehouseIdParam = $this->request->post('warehouse_id');

        $warehouseIds = [];
        if ($warehouseIdParam === 'all' || $warehouseIdParam === '' || $warehouseIdParam === null) {
            $warehouseIds = Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->select('id')
                ->column();
        } else {
            $wid = (int) $warehouseIdParam;
            if ($wid <= 0) {
                return ['success' => false, 'message' => 'กรุณาเลือกคลังหรือเลือกปิดรวมทุกคลัง'];
            }
            $warehouseIds = [$wid];
        }

        if (empty($warehouseIds)) {
            return ['success' => false, 'message' => 'ไม่พบคลังหลักในระบบ'];
        }

        $totalCount = 0;
        foreach ($warehouseIds as $warehouseId) {
            $result = $this->closeMonthForWarehouse((int) $warehouseId, $year, $month);
            $totalCount += $result['count'];
        }

        return ['success' => true, 'message' => 'ปิดเดือนเรียบร้อย', 'count' => $totalCount, 'warehouses_count' => count($warehouseIds)];
    }

    /**
     * ปิดเดือนสำหรับคลังเดียว
     */
    protected function closeMonthForWarehouse($warehouseId, $year, $month)
    {
        $subIds = self::getDisburseSubWarehouseIds();
        $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $lastDay = (int) date('t', strtotime($dateStart));
        $dateEnd = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $lastDay);

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth += 12;
            $prevYear--;
        }

        $prevClosing = (new Query())
            ->select(['item_code', 'closing_qty', 'closing_value'])
            ->from(StockMonthlyReport::tableName())
            ->where([
                'report_year' => $prevYear,
                'report_month' => $prevMonth,
                'warehouse_id' => $warehouseId,
            ])
            ->indexBy('item_code')
            ->all();

        $itemCodes = array_keys($prevClosing);

        $inRows = (new Query())
            ->select([
                'sd.item_code',
                'SUM(sd.qty) AS in_qty',
                'SUM(sd.qty * COALESCE(sd.unit_price, 0)) AS in_value',
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['so.main_warehouse_id' => $warehouseId])
            ->andWhere(['between', 'so.order_date', $dateStart, $dateEnd])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->groupBy('sd.item_code')
            ->all();
        foreach ($inRows as $row) {
            $itemCodes[] = $row['item_code'];
        }
        $itemCodes = array_unique($itemCodes);

        $outQuery = StockDetail::find()
            ->alias('sd')
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_OUT])
            ->andWhere(['so.main_warehouse_id' => $warehouseId])
            ->andWhere(['between', 'so.order_date', $dateStart, $dateEnd])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED]);
        $outQuery->select([
            'sd.item_code',
            'so.sub_warehouse_id',
            'sd.qty',
            'sd.unit_price',
        ]);
        $outRows = $outQuery->asArray()->all();

        $outSub = [];
        $outHosp = [];
        foreach ($outRows as $row) {
            $code = $row['item_code'];
            $q = (float) $row['qty'];
            $v = $q * (float) ($row['unit_price'] ?? 0);
            if (!isset($outSub[$code])) {
                $outSub[$code] = ['qty' => 0, 'value' => 0];
            }
            if (!isset($outHosp[$code])) {
                $outHosp[$code] = ['qty' => 0, 'value' => 0];
            }
            $isSub = in_array((int) $row['sub_warehouse_id'], $subIds, true);
            if ($isSub) {
                $outSub[$code]['qty'] += $q;
                $outSub[$code]['value'] += $v;
            } else {
                $outHosp[$code]['qty'] += $q;
                $outHosp[$code]['value'] += $v;
            }
            $itemCodes[] = $code;
        }
        $itemCodes = array_unique($itemCodes);

        $inMap = [];
        foreach ($inRows as $row) {
            $inMap[$row['item_code']] = [
                'in_qty' => (float) $row['in_qty'],
                'in_value' => (float) ($row['in_value'] ?? 0),
            ];
        }

        StockMonthlyReport::deleteAll([
            'report_year' => $year,
            'report_month' => $month,
            'warehouse_id' => $warehouseId,
        ]);

        $items = StockItem::find()->where(['item_code' => $itemCodes])->indexBy('item_code')->all();
        $createdAt = time();
        $createdBy = Yii::$app->user->id;

        foreach ($itemCodes as $itemCode) {
            $prev = $prevClosing[$itemCode] ?? null;
            $openingQty = $prev ? (float) $prev['closing_qty'] : 0;
            $openingValue = $prev ? (float) $prev['closing_value'] : 0;

            $in = $inMap[$itemCode] ?? ['in_qty' => 0, 'in_value' => 0];
            $inQty = $in['in_qty'];
            $inValue = $in['in_value'];

            $sub = $outSub[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $hosp = $outHosp[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $outSubQty = $sub['qty'];
            $outHospQty = $hosp['qty'];
            $totalOutQty = $outSubQty + $outHospQty;
            $totalOutValue = $sub['value'] + $hosp['value'];

            $closingQty = $openingQty + $inQty - $totalOutQty;
            $closingValue = $openingValue + $inValue - $totalOutValue;

            $item = $items[$itemCode] ?? null;
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;

            $r = new StockMonthlyReport();
            $r->report_year = $year;
            $r->report_month = $month;
            $r->warehouse_id = $warehouseId;
            $r->item_code = $itemCode;
            $r->unit_name = $unitName;
            $r->opening_qty = $openingQty;
            $r->opening_value = $openingValue;
            $r->in_qty = $inQty;
            $r->in_value = $inValue;
            $r->out_sub_qty = $outSubQty;
            $r->out_hosp_qty = $outHospQty;
            $r->total_out_qty = $totalOutQty;
            $r->total_out_value = $totalOutValue;
            $r->closing_qty = $closingQty;
            $r->closing_value = $closingValue;
            $r->created_at = $createdAt;
            $r->created_by = $createdBy;
            $r->save(false);
        }

        return ['count' => count($itemCodes)];
    }

    /**
     * Export รายงานสรุปเป็น Excel
     */
    public function actionExportExcel()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $rows = $this->aggregateByCategory($year, $month, $warehouseId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปรายงานวัสดุคงคลัง');

        $title = 'สรุปรายงานวัสดุคงคลัง  เดือน ' . $month . '/' . ($year + 543);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $headers = ['#', 'รายการ', 'สินค้าคงเหลือ', 'ซื้อระหว่างเดือน', 'รวม', 'จ่ายส่วนของ รพ.aq', 'จ่ายส่วนของโรงพยาบาล', 'รวมจ่าย', 'ยอดยกไป'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet->getStyle('A3:I3')->getFont()->setBold(true);
        $sheet->getStyle('A3:I3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');

        $rowNum = 4;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['category_label']);
            $totalAvail = $r['opening_qty'] + $r['in_qty'];
            $sheet->setCellValue('C' . $rowNum, $r['opening_qty']);
            $sheet->setCellValue('D' . $rowNum, $r['in_qty']);
            $sheet->setCellValue('E' . $rowNum, $totalAvail);
            $sheet->setCellValue('F' . $rowNum, $r['out_sub_qty']);
            $sheet->setCellValue('G' . $rowNum, $r['out_hosp_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['total_out_qty']);
            $sheet->setCellValue('I' . $rowNum, $r['closing_qty']);
            $rowNum++;
        }

        if (!empty($rows)) {
            $tot = [
                'opening' => array_sum(array_column($rows, 'opening_qty')),
                'in' => array_sum(array_column($rows, 'in_qty')),
                'out_sub' => array_sum(array_column($rows, 'out_sub_qty')),
                'out_hosp' => array_sum(array_column($rows, 'out_hosp_qty')),
                'total_out' => array_sum(array_column($rows, 'total_out_qty')),
                'closing' => array_sum(array_column($rows, 'closing_qty')),
            ];
            $sheet->setCellValue('A' . $rowNum, '');
            $sheet->setCellValue('B' . $rowNum, 'รวม');
            $sheet->setCellValue('C' . $rowNum, $tot['opening']);
            $sheet->setCellValue('D' . $rowNum, $tot['in']);
            $sheet->setCellValue('E' . $rowNum, $tot['opening'] + $tot['in']);
            $sheet->setCellValue('F' . $rowNum, $tot['out_sub']);
            $sheet->setCellValue('G' . $rowNum, $tot['out_hosp']);
            $sheet->setCellValue('H' . $rowNum, $tot['total_out']);
            $sheet->setCellValue('I' . $rowNum, $tot['closing']);
            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getFont()->setBold(true);
            $sheet->getStyle('B' . $rowNum . ':I' . $rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF59D');
        }

        foreach (range('C', 'I') as $c) {
            $sheet->getStyle($c . '4:' . $c . ($rowNum))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = 'material-summary-' . $year . '-' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * รายงานแยกรายการ (ระดับรายการสินค้า): ลำดับ, รหัสสินค้า, รายการสินค้า, ประเภทวัสดุ, ยอดยกมา(จำนวน/มูลค่า), รับเข้า, จ่ายออก, คงเหลือสิ้นเดือน
     */
    public function actionMaterialByItem()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        $rows = $this->getRowsByItem($year, $month, $warehouseId);
        $hasData = !empty($rows);

        return $this->render('material-by-item', [
            'year' => $year,
            'month' => $month,
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'hasData' => $hasData,
        ]);
    }

    /**
     * ดึงรายงานระดับรายการ (ไม่รวมตาม category) จาก stock_monthly_report
     */
    protected function getRowsByItem($year, $month, $warehouseId = null)
    {
        $query = (new Query())
            ->select([
                'r.item_code',
                'i.title',
                new Expression("COALESCE(cat.title, i.category_id, '') AS category_title"),
                'r.opening_qty',
                'r.opening_value',
                'r.in_qty',
                'r.in_value',
                'r.total_out_qty',
                'r.total_out_value',
                'r.closing_qty',
                'r.closing_value',
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'r.report_year' => $year,
                'r.report_month' => $month,
            ])
            ->orderBy([new Expression('COALESCE(cat.code, i.category_id)'), 'r.item_code' => SORT_ASC]);

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        return $query->all();
    }

    /**
     * Export รายงานแยกรายการเป็น Excel
     */
    public function actionExportExcelByItem()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $rows = $this->getRowsByItem($year, $month, $warehouseId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานแยกรายการ');

        $title = 'รายงานวัสดุคงคลังแยกรายการ  เดือน ' . $month . '/' . ($year + 543);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $headers = ['ลำดับ', 'รหัสสินค้า', 'รายการสินค้า', 'ประเภทวัสดุ', 'ยอดยกมา(จำนวน)', 'ยอดยกมา(มูลค่า)', 'รับเข้า(จำนวน)', 'รับเข้า(มูลค่า)', 'จ่ายออก(จำนวน)', 'จ่ายออก(มูลค่า)', 'คงเหลือสิ้นเดือน(จำนวน)', 'คงเหลือสิ้นเดือน(มูลค่า)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        if (strlen($col) > 1) {
            $lastCol = chr(ord('A') + count($headers) - 1);
            $sheet->getStyle('A3:' . $lastCol . '3')->getFont()->setBold(true);
            $sheet->getStyle('A3:' . $lastCol . '3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
        } else {
            $sheet->getStyle('A3:L3')->getFont()->setBold(true);
            $sheet->getStyle('A3:L3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
        }

        $rowNum = 4;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['item_code']);
            $sheet->setCellValue('C' . $rowNum, $r['item_name']);
            $sheet->setCellValue('D' . $rowNum, $r['category_title']);
            $sheet->setCellValue('E' . $rowNum, $r['opening_qty']);
            $sheet->setCellValue('F' . $rowNum, $r['opening_value']);
            $sheet->setCellValue('G' . $rowNum, $r['in_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['in_value']);
            $sheet->setCellValue('I' . $rowNum, $r['total_out_qty']);
            $sheet->setCellValue('J' . $rowNum, $r['total_out_value']);
            $sheet->setCellValue('K' . $rowNum, $r['closing_qty']);
            $sheet->setCellValue('L' . $rowNum, $r['closing_value']);
            $rowNum++;
        }

        if (!empty($rows)) {
            $tot = [
                'opening_qty' => array_sum(array_column($rows, 'opening_qty')),
                'opening_value' => array_sum(array_column($rows, 'opening_value')),
                'in_qty' => array_sum(array_column($rows, 'in_qty')),
                'in_value' => array_sum(array_column($rows, 'in_value')),
                'total_out_qty' => array_sum(array_column($rows, 'total_out_qty')),
                'total_out_value' => array_sum(array_column($rows, 'total_out_value')),
                'closing_qty' => array_sum(array_column($rows, 'closing_qty')),
                'closing_value' => array_sum(array_column($rows, 'closing_value')),
            ];
            $sheet->setCellValue('A' . $rowNum, '');
            $sheet->setCellValue('B' . $rowNum, 'รวมทั้งหมด');
            $sheet->setCellValue('C' . $rowNum, '');
            $sheet->setCellValue('D' . $rowNum, '');
            $sheet->setCellValue('E' . $rowNum, $tot['opening_qty']);
            $sheet->setCellValue('F' . $rowNum, $tot['opening_value']);
            $sheet->setCellValue('G' . $rowNum, $tot['in_qty']);
            $sheet->setCellValue('H' . $rowNum, $tot['in_value']);
            $sheet->setCellValue('I' . $rowNum, $tot['total_out_qty']);
            $sheet->setCellValue('J' . $rowNum, $tot['total_out_value']);
            $sheet->setCellValue('K' . $rowNum, $tot['closing_qty']);
            $sheet->setCellValue('L' . $rowNum, $tot['closing_value']);
            $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->getFont()->setBold(true);
            $sheet->getStyle('B' . $rowNum . ':L' . $rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF59D');
        }

        foreach (range('E', 'L') as $c) {
            $sheet->getStyle($c . '4:' . $c . ($rowNum))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = 'material-by-item-' . $year . '-' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
