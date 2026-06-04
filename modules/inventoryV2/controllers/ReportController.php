<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use app\modules\inventoryV2\components\MovementBridge;
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
use yii\web\UploadedFile;
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
                'i.item_name',
                'i.min_qty',
                'i.max_qty',
                new Expression('COALESCE(cat.title, i.category_id, \'อื่นๆ\') AS category_title'),
                new Expression('SUM(sb.balance_qty) AS balance_qty'),
                new Expression('SUM(sb.balance_qty * COALESCE(lp.unit_price, 0)) AS value'),
            ])
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.item_code = sb.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->leftJoin(['lp' => $latestPriceSub], 'lp.item_code = sb.item_code AND lp.lot_number = sb.lot_number')
            ->where(['sb.warehouse_id' => $warehouseIds])
            // ->andWhere(['>', 'sb.balance_qty', 0]) // แสดงรายการที่มียอดคงเหลือ 0 ด้วย (เพื่อให้เห็นว่ามีรายการอะไรบ้างที่เคยมีการรับเข้ามาในคลัง แต่ตอนนี้หมดแล้ว)
            ->groupBy('sb.item_code', 'i.item_name', 'i.min_qty', 'i.max_qty', 'cat.title', 'i.category_id');

        $raw = $query->all();
        $warehouseNames = [];
        foreach ($listMain as $w) {
            $warehouseNames[$w->id] = 'คลังหลัก: ' . $w->warehouse_name;
        }
        foreach ($listSub as $w) {
            $warehouseNames[$w->id] = 'คลังย่อย: ' . $w->warehouse_name;
        }
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
            $item = StockItem::findOne($r['item_code']);
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $rows[] = [
                'warehouse_id' => (int) $r['warehouse_id'],
                'warehouse_name' => $warehouseNames[(int) $r['warehouse_id']] ?? (string) $r['warehouse_id'],
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) $r['item_name'],
                'category_title' => (string) ($r['category_title'] ?? 'อื่นๆ'),
                'unit_name' => $unitName ? (string) $unitName : '-',
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
            ->innerJoin(['i' => StockItem::tableName()], 'i.item_code = sb.item_code')
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
                    'item_name' => new Expression("COALESCE(i.item_name, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.main_warehouse_id = req.main_warehouse_id AND bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.item_code = req.item_code')
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
                    'item_name' => new Expression("COALESCE(i.item_name, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.item_code = req.item_code')
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
            ->innerJoin(['i' => StockItem::tableName()], 'i.item_code = r.item_code')
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

        // ดึง movement ในเดือนจากทั้ง V1 + V2 (ใช้ MovementBridge เพื่อความต่อเนื่อง)
        $monthMoves = MovementBridge::movements([
            'dateFrom'    => sprintf('%04d-%02d-01', $year, $month),
            'dateTo'      => sprintf('%04d-%02d-%02d', $year, $month, $lastDay),
            'warehouseId' => $warehouseId,
        ]);

        // ถ้าไม่มี prev_closing — อ่าน opening จากประวัติทั้งหมดก่อนเริ่มเดือน
        $needBootstrap = empty($prevClosing);
        $bootstrapMoves = [];
        if ($needBootstrap) {
            $bootstrapMoves = MovementBridge::movements([
                'dateTo'      => date('Y-m-d', strtotime($dateStart . ' -1 day')),
                'warehouseId' => $warehouseId,
            ]);
        }

        $inMap = [];     // [item_code => ['in_qty','in_value']]
        $outSub = [];    // จ่ายส่วนของ รพ.สต. (warehouses ใน getDisburseSubWarehouseIds)
        $outHosp = [];   // จ่ายส่วนของโรงพยาบาล (ที่เหลือ)
        $bootstrapMap = []; // [item_code => ['qty','value']] - opening จากประวัติทั้งหมด

        foreach ($monthMoves as $m) {
            $code = (string) $m['item_code'];
            if ($code === '') continue;
            $q = (float) $m['qty'];
            $v = (float) $m['total_price'];
            $itemCodes[] = $code;
            if ($m['order_type'] === 'IN') {
                if (!isset($inMap[$code])) {
                    $inMap[$code] = ['in_qty' => 0.0, 'in_value' => 0.0];
                }
                $inMap[$code]['in_qty']   += $q;
                $inMap[$code]['in_value'] += $v;
            } else {
                if (!isset($outSub[$code]))  $outSub[$code]  = ['qty' => 0.0, 'value' => 0.0];
                if (!isset($outHosp[$code])) $outHosp[$code] = ['qty' => 0.0, 'value' => 0.0];
                $cp = $m['counterparty_id'];
                $isSub = $cp !== null && in_array((int) $cp, $subIds, true);
                if ($isSub) {
                    $outSub[$code]['qty']   += $q;
                    $outSub[$code]['value'] += $v;
                } else {
                    $outHosp[$code]['qty']   += $q;
                    $outHosp[$code]['value'] += $v;
                }
            }
        }

        foreach ($bootstrapMoves as $m) {
            $code = (string) $m['item_code'];
            if ($code === '') continue;
            if (!isset($bootstrapMap[$code])) {
                $bootstrapMap[$code] = ['qty' => 0.0, 'value' => 0.0];
            }
            $q = (float) $m['qty'];
            $v = (float) $m['total_price'];
            if ($m['order_type'] === 'IN') {
                $bootstrapMap[$code]['qty']   += $q;
                $bootstrapMap[$code]['value'] += $v;
            } else {
                $bootstrapMap[$code]['qty']   -= $q;
                $bootstrapMap[$code]['value'] -= $v;
            }
            $itemCodes[] = $code;
        }

        $itemCodes = array_unique($itemCodes);

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
            if ($prev) {
                $openingQty   = (float) $prev['closing_qty'];
                $openingValue = (float) $prev['closing_value'];
            } else {
                // bootstrap จากประวัติทั้งหมดก่อนเดือนนี้ (สำหรับเดือนแรกของระบบ)
                $boot = $bootstrapMap[$itemCode] ?? null;
                $openingQty   = $boot ? $boot['qty']   : 0;
                $openingValue = $boot ? $boot['value'] : 0;
            }

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
        $assetType = $this->request->get('asset_type_id') ?: null;

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        $assetTypes = ['' => '-- ทุกประเภท --'] + \yii\helpers\ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'asset_type', 'group_id' => 'MATER'])
                ->orderBy(['code' => SORT_ASC])
                ->all(),
            'code',
            function ($m) { return '(' . $m->code . ') ' . $m->title; }
        );

        $rows = $this->getRowsByItem($year, $month, $warehouseId, $assetType);
        $hasData = !empty($rows);

        return $this->render('material-by-item', [
            'year' => $year,
            'month' => $month,
            'warehouseId' => $warehouseId,
            'assetType' => $assetType,
            'warehouses' => $warehouses,
            'assetTypes' => $assetTypes,
            'rows' => $rows,
            'hasData' => $hasData,
        ]);
    }

    /**
     * ดึงรายงานระดับรายการ (ไม่รวมตาม category) จาก stock_monthly_report
     */
    protected function getRowsByItem($year, $month, $warehouseId = null, $assetTypeId = null)
    {
        $query = (new Query())
            ->select([
                'r.item_code',
                'i.item_name',
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
            ->innerJoin(['i' => StockItem::tableName()], 'i.item_code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'r.report_year' => $year,
                'r.report_month' => $month,
            ])
            ->orderBy([new Expression('COALESCE(cat.code, i.category_id)'), 'r.item_code' => SORT_ASC]);

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }
        if ($assetTypeId !== null && $assetTypeId !== '') {
            $query->andWhere(['i.category_id' => $assetTypeId]);
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
        $assetType = $this->request->get('asset_type_id') ?: null;

        $rows = $this->getRowsByItem($year, $month, $warehouseId, $assetType);

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

    /**
     * รายงานวัสดุคงคลังหลักรายตัว — สรุปยอด ยกมา / รับเข้า / จ่ายออก / คงเหลือ ต่อรายการ
     * ในช่วงเวลาที่เลือก (รวม V1 stock_events + V2 stock_order/stock_detail)
     */
    public function actionListByItem()
    {
        $request = $this->request;
        $assetType = $request->get('asset_type_id') ?: null;
        $warehouseId = $request->get('warehouse_id') !== '' ? $request->get('warehouse_id') : null;
        $warehouseId = $warehouseId ? (int) $warehouseId : null;
        $dateStart = $request->get('date_start') ?: date('Y-m-01');
        $dateEnd   = $request->get('date_end')   ?: date('Y-m-t');

        $rows = MovementBridge::aggregateByItem([
            'dateFrom'      => $dateStart,
            'dateTo'        => $dateEnd,
            'warehouseId'   => $warehouseId,
            'assetTypeCode' => $assetType,
        ]);

        $summary = [
            'begin_qty' => 0, 'begin_price' => 0,
            'qty_in' => 0,    'price_in' => 0,
            'qty_out' => 0,   'price_out' => 0,
            'end_qty' => 0,   'end_price' => 0,
        ];
        foreach ($rows as $r) {
            foreach (array_keys($summary) as $k) {
                $summary[$k] += (float) ($r[$k] ?? 0);
            }
        }

        $assetTypeOptions = \yii\helpers\ArrayHelper::map(
            \app\models\Categorise::find()
                ->where(['name' => 'asset_type'])
                ->orderBy(['code' => SORT_ASC])
                ->all(),
            'code',
            function ($m) { return '(' . $m->code . ') ' . $m->title; }
        );

        $warehouseOptions = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id', 'warehouse_name'
        );

        return $this->render('list-by-item', [
            'rows' => $rows,
            'summary' => $summary,
            'assetType' => $assetType,
            'warehouseId' => $warehouseId,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'assetTypeOptions' => $assetTypeOptions,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }

    /**
     * รายงานวัสดุรับ-จ่าย — รายละเอียดบรรทัดต่อบรรทัด (รวม V1 + V2)
     */
    public function actionListByOrder()
    {
        $request = $this->request;
        $warehouseId = $request->get('warehouse_id') !== '' ? $request->get('warehouse_id') : null;
        $warehouseId = $warehouseId ? (int) $warehouseId : null;
        $assetType = $request->get('asset_type_id') ?: null;
        $assetItem = $request->get('asset_item') ?: null;
        $transactionType = $request->get('transaction_type') ?: null;
        $dateStart = $request->get('date_start') ?: date('Y-m-01');
        $dateEnd   = $request->get('date_end')   ?: date('Y-m-t');

        $rows = MovementBridge::movements([
            'dateFrom'        => $dateStart,
            'dateTo'          => $dateEnd,
            'warehouseId'     => $warehouseId,
            'assetTypeCode'   => $assetType,
            'itemCode'        => $assetItem,
            'transactionType' => $transactionType,
            'orderBy'         => 'ASC',
        ]);

        $totalPrice = 0.0;
        foreach ($rows as $r) {
            $totalPrice += (float) $r['total_price'];
        }

        $assetTypeOptions = \yii\helpers\ArrayHelper::map(
            \app\models\Categorise::find()
                ->where(['name' => 'asset_type'])
                ->orderBy(['code' => SORT_ASC])
                ->all(),
            'code',
            function ($m) { return '(' . $m->code . ') ' . $m->title; }
        );

        $warehouseOptions = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id', 'warehouse_name'
        );

        return $this->render('list-by-order', [
            'rows' => $rows,
            'totalPrice' => $totalPrice,
            'warehouseId' => $warehouseId,
            'assetType' => $assetType,
            'assetItem' => $assetItem,
            'transactionType' => $transactionType,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'assetTypeOptions' => $assetTypeOptions,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }

    /**
     * รายงานสรุปคงคลังรายเดือน (ปิดเดือน) — ระดับรายการพัสดุ
     * อ่านจาก stock_monthly_report (snapshot ที่กดปิดเดือนแล้ว — V1 + V2 ใช้ตารางเดียวกัน)
     */
    public function actionStockMonthly()
    {
        $request = $this->request;
        $reportYear  = (int) ($request->get('report_year') ?: date('Y'));
        $reportMonth = (int) ($request->get('report_month') ?: date('n'));
        $warehouseId = $request->get('warehouse_id') !== '' ? $request->get('warehouse_id') : null;
        $warehouseId = $warehouseId ? (int) $warehouseId : null;
        $assetType   = $request->get('asset_type_id') ?: null;
        $q           = trim((string) $request->get('q', ''));

        $query = (new Query())
            ->select([
                'r.*',
                'si.item_name',
                'si.category_id AS asset_type_code',
                't.title AS asset_type_name',
                'w.warehouse_name',
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->leftJoin(['si' => 'stock_item'], 'si.item_code = r.item_code')
            ->leftJoin(['t'  => 'categorise'], "t.code = si.category_id AND t.name = 'asset_type'")
            ->leftJoin(['w'  => 'warehouses'], 'w.id = r.warehouse_id')
            ->where([
                'r.report_year'  => $reportYear,
                'r.report_month' => $reportMonth,
            ]);

        if ($warehouseId !== null) {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }
        if ($assetType !== null && $assetType !== '') {
            $query->andWhere(['si.category_id' => $assetType]);
        }
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'r.item_code', $q],
                ['like', 'si.item_name', $q],
            ]);
        }
        $query->orderBy(['r.item_code' => SORT_ASC]);

        $rows = $query->all();
        $summary = [
            'opening_qty' => 0, 'opening_value' => 0,
            'in_qty' => 0, 'in_value' => 0,
            'out_sub_qty' => 0, 'out_hosp_qty' => 0,
            'total_out_qty' => 0, 'total_out_value' => 0,
            'closing_qty' => 0, 'closing_value' => 0,
        ];
        foreach ($rows as $r) {
            foreach (array_keys($summary) as $k) {
                $summary[$k] += (float) ($r[$k] ?? 0);
            }
        }

        $assetTypeOptions = \yii\helpers\ArrayHelper::map(
            \app\models\Categorise::find()
                ->where(['name' => 'asset_type'])
                ->orderBy(['code' => SORT_ASC])
                ->all(),
            'code',
            function ($m) { return '(' . $m->code . ') ' . $m->title; }
        );

        $warehouseOptions = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id', 'warehouse_name'
        );

        return $this->render('stock-monthly', [
            'rows' => $rows,
            'summary' => $summary,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'warehouseId' => $warehouseId,
            'assetType' => $assetType,
            'q' => $q,
            'assetTypeOptions' => $assetTypeOptions,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }

    /**
     * Trigger ปิดเดือนจากหน้า stock-monthly (POST) — wrapper เรียก closeMonthForWarehouse
     */
    public function actionStockMonthlyGenerate()
    {
        $reportYear  = (int) $this->request->post('report_year');
        $reportMonth = (int) $this->request->post('report_month');
        $warehouseIdParam = $this->request->post('warehouse_id');

        if (!$reportYear || !$reportMonth) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุปีและเดือน');
            return $this->redirect(['stock-monthly']);
        }

        if ($warehouseIdParam === 'all' || $warehouseIdParam === '' || $warehouseIdParam === null) {
            $warehouseIds = Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->select('id')
                ->column();
        } else {
            $warehouseIds = [(int) $warehouseIdParam];
        }

        $total = 0;
        try {
            foreach ($warehouseIds as $wid) {
                $r = $this->closeMonthForWarehouse((int) $wid, $reportYear, $reportMonth);
                $total += $r['count'];
            }
            $monthNames = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
            $monthLabel = ($monthNames[$reportMonth] ?? '') . ' ' . ($reportYear + 543);
            Yii::$app->session->setFlash('success', "ปิดเดือน $monthLabel เรียบร้อย — บันทึก $total รายการ (รวม V1+V2)");
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect([
            'stock-monthly',
            'report_year' => $reportYear,
            'report_month' => $reportMonth,
            'warehouse_id' => is_numeric($warehouseIdParam) ? (int) $warehouseIdParam : '',
        ]);
    }

    /**
     * เขียน CSV สรุปรายการที่ skip ตอน import ลงไฟล์ temp ที่ @runtime/seed-import-skipped/
     * คืน token (สำหรับใส่ใน URL ดาวน์โหลด) หรือ null ถ้าไม่มี skip
     */
    private static function writeSeedSkippedCsv(array $skipMissingWh, array $skipMissingItem, array $skipBadNumber, array $skipNoMatch, array $skipAmbiguous)
    {
        $total = count($skipMissingWh) + count($skipMissingItem) + count($skipBadNumber) + count($skipNoMatch) + count($skipAmbiguous);
        if ($total === 0) return null;

        $dir = Yii::getAlias('@runtime') . '/seed-import-skipped';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $token = bin2hex(random_bytes(16));
        $path = $dir . '/' . $token . '.csv';

        $fp = fopen($path, 'w');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, ['reason', 'row', 'item_code', 'warehouse_name', 'category_id', 'candidates']);
        $writeRows = static function ($reason, array $rows) use ($fp) {
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $reason,
                    (string) ($r['row'] ?? ''),
                    (string) ($r['item_code'] ?? ''),
                    (string) ($r['warehouse_name'] ?? ''),
                    (string) ($r['category_id'] ?? ''),
                    isset($r['candidates']) && is_array($r['candidates']) ? implode(' | ', $r['candidates']) : '',
                ]);
            }
        };
        $writeRows('ไม่พบคลัง', $skipMissingWh);
        $writeRows('ไม่พบ item_code', $skipMissingItem);
        $writeRows('ไม่พบคลังหลักที่รับประเภทวัสดุนี้', $skipNoMatch);
        $writeRows('มีหลายคลังที่รับประเภทนี้ — ต้องระบุ warehouse_name', $skipAmbiguous);
        $writeRows('จำนวน/มูลค่าไม่ใช่ตัวเลข', $skipBadNumber);
        fclose($fp);

        // เคลียร์ไฟล์เก่าที่เก็บเกิน 24 ชั่วโมง
        foreach (glob($dir . '/*.csv') ?: [] as $old) {
            if (is_file($old) && (time() - filemtime($old)) > 86400) {
                @unlink($old);
            }
        }
        return $token;
    }

    /**
     * ดาวน์โหลด CSV รายการ skip จาก import ครั้งล่าสุด
     */
    public function actionStockMonthlySeedSkippedDownload($token)
    {
        if (!preg_match('/^[a-f0-9]{32}$/', (string) $token)) {
            throw new \yii\web\NotFoundHttpException('Invalid token');
        }
        $path = Yii::getAlias('@runtime') . '/seed-import-skipped/' . $token . '.csv';
        if (!is_file($path)) {
            throw new \yii\web\NotFoundHttpException('File not found หรือหมดอายุแล้ว');
        }
        return Yii::$app->response->sendFile($path, 'seed-import-skipped.csv', [
            'mimeType' => 'text/csv',
            'inline' => false,
        ]);
    }

    /**
     * แปลงตัวเลขจาก CSV ที่อาจมีหลายรูปแบบ ให้เป็น float
     * รองรับ: ทศนิยม, คั่นพัน (,), currency (฿ $ บาท THB € ¥), whitespace,
     * non-breaking space, Excel leading apostrophe ('), ค่าติดลบ
     * @return float|null null ถ้าแปลงไม่ได้
     */
    private static function parseNumberFlexible($raw)
    {
        if ($raw === null) return null;
        $s = (string) $raw;
        $s = preg_replace('/\xEF\xBB\xBF/', '', $s);                  // BOM
        $s = preg_replace('/[\s\x{00A0}]+/u', '', $s);                // whitespace + NBSP
        $s = ltrim($s, "'");                                          // Excel leading apostrophe
        $s = str_replace(['฿', '$', 'บาท', 'THB', '€', '¥'], '', $s); // currency
        $s = str_replace(',', '', $s);                                // thousand separators
        // dash-only (-, –, —) แทนค่าว่าง ให้เป็น 0
        if (in_array($s, ['-', '–', '—'], true)) {
            return 0.0;
        }
        if ($s === '' || !is_numeric($s)) {
            return null;
        }
        return (float) $s;
    }

    /**
     * ดาวน์โหลดเทมเพลต CSV สำหรับ seed ยอดยกมา
     */
    public function actionStockMonthlySeedTemplate()
    {
        // warehouse_name เป็น optional — ถ้าเว้นว่าง ระบบจะ map คลังหลักให้อัตโนมัติตาม
        // ประเภทวัสดุ (stock_item.category_id) ที่คลังตั้งค่ารับใน data_json.item_type
        $rows = [
            ['item_code', 'closing_qty', 'closing_value', 'warehouse_name'],
            ['M001', '150', '4500.00', ''],
            ['M002', '80',  '2400.00', ''],
            ['D001', '30',  '900.00',  'คลังเวชภัณฑ์ (ระบุเอง เมื่อมีหลายคลังรับประเภทนี้)'],
        ];

        $filename = 'stock_monthly_seed_template.csv';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        Yii::$app->response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }

    /**
     * Import CSV เพื่อ seed ยอดยกมา (opening balance) สำหรับเริ่มต้นใช้งานระบบ
     * CSV header: warehouse_name, item_code, closing_qty, closing_value
     * ระบบจะเขียนลง stock_monthly_report ของ "เดือนก่อนหน้า" ที่ผู้ใช้เลือก
     * โดยมี closing_qty/value = ค่าจาก CSV เพื่อให้เดือนถัดไปดึงไปเป็น opening อัตโนมัติ
     */
    public function actionStockMonthlySeedImport()
    {
        $reportYear  = (int) $this->request->post('report_year');
        $reportMonth = (int) $this->request->post('report_month');

        if (!$reportYear || !$reportMonth || $reportMonth < 1 || $reportMonth > 12) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุปีและเดือนของยอดยกมาให้ถูกต้อง');
            return $this->redirect(['stock-monthly']);
        }

        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file || $file->error !== UPLOAD_ERR_OK) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ CSV');
            return $this->redirect(['stock-monthly']);
        }

        $handle = fopen($file->tempName, 'r');
        if (!$handle) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอ่านไฟล์ CSV ได้');
            return $this->redirect(['stock-monthly']);
        }

        // ข้าม BOM ถ้ามี
        $firstBytes = fread($handle, 3);
        if ($firstBytes !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            Yii::$app->session->setFlash('error', 'ไฟล์ CSV ว่างหรือไม่มี header');
            return $this->redirect(['stock-monthly']);
        }
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);
        $required = ['item_code', 'closing_qty', 'closing_value'];
        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                fclose($handle);
                Yii::$app->session->setFlash('error', "Header ไม่ครบ ต้องมีคอลัมน์: " . implode(', ', $required) . " (warehouse_name เป็น optional)");
                return $this->redirect(['stock-monthly']);
            }
        }
        $idx = array_flip($header);
        $hasWarehouseCol = isset($idx['warehouse_name']);

        // โหลด lookup คลังหลัก + ประเภทวัสดุที่แต่ละคลังรับ
        $warehouseMap = [];        // [lowercased name => id]
        $mainWarehouses = [];      // [id => Warehouse]
        $categoryToWhIds = [];     // [category_code => [warehouse_id, ...]] — เฉพาะคลังที่ระบุ item_type
        foreach (Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all() as $w) {
            $warehouseMap[mb_strtolower(trim((string) $w->warehouse_name))] = (int) $w->id;
            $mainWarehouses[(int) $w->id] = $w;
            foreach ($w->getAllowedItemTypeCodes() as $code) {
                $categoryToWhIds[(string) $code][] = (int) $w->id;
            }
        }

        // โหลด item_code → category_id (asset_type) เพื่อใช้ map คลังอัตโนมัติ
        $itemCategoryMap = [];
        foreach ((new Query())->select(['item_code', 'category_id'])->from(StockItem::tableName())->each() as $it) {
            $itemCategoryMap[(string) $it['item_code']] = $it['category_id'] !== null ? (string) $it['category_id'] : null;
        }

        $rowNum = 1;
        $okRows = [];           // [['warehouse_id','item_code','qty','value']]
        $skipMissingWh = [];    // CSV ระบุ warehouse_name แต่ไม่พบ
        $skipMissingItem = [];  // item_code ไม่อยู่ใน stock_item
        $skipBadNumber = [];    // closing_qty/value ไม่ใช่ตัวเลข
        $skipNoMatch = [];      // ไม่ระบุ warehouse + map ตาม category ไม่ได้ (0 คลัง)
        $skipAmbiguous = [];    // ไม่ระบุ warehouse + พบหลายคลัง
        $skipEmpty = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($data, fn($c) => $c !== null && trim((string) $c) !== '')) === 0) {
                $skipEmpty++;
                continue;
            }
            $wName = $hasWarehouseCol ? trim((string) ($data[$idx['warehouse_name']] ?? '')) : '';
            $code  = trim((string) ($data[$idx['item_code']] ?? ''));
            $qRaw  = trim((string) ($data[$idx['closing_qty']] ?? ''));
            $vRaw  = trim((string) ($data[$idx['closing_value']] ?? ''));

            if (!array_key_exists($code, $itemCategoryMap)) {
                $skipMissingItem[] = ['row' => $rowNum, 'warehouse_name' => $wName, 'item_code' => $code];
                continue;
            }

            // หา warehouse_id
            $whId = null;
            if ($wName !== '') {
                $whId = $warehouseMap[mb_strtolower($wName)] ?? null;
                if ($whId === null) {
                    $skipMissingWh[] = ['row' => $rowNum, 'warehouse_name' => $wName, 'item_code' => $code];
                    continue;
                }
            } else {
                $cat = $itemCategoryMap[$code];
                $candidates = $cat !== null && isset($categoryToWhIds[$cat]) ? array_unique($categoryToWhIds[$cat]) : [];
                if (count($candidates) === 0) {
                    $skipNoMatch[] = ['row' => $rowNum, 'item_code' => $code, 'category_id' => $cat];
                    continue;
                }
                if (count($candidates) > 1) {
                    $names = array_map(fn($id) => $mainWarehouses[$id]->warehouse_name ?? ('#' . $id), $candidates);
                    $skipAmbiguous[] = ['row' => $rowNum, 'item_code' => $code, 'category_id' => $cat, 'candidates' => $names];
                    continue;
                }
                $whId = (int) $candidates[0];
            }

            $qty = self::parseNumberFlexible($qRaw);
            $val = self::parseNumberFlexible($vRaw);
            if ($qty === null || $val === null) {
                $skipBadNumber[] = ['row' => $rowNum, 'warehouse_name' => $wName, 'item_code' => $code];
                continue;
            }
            $okRows[] = [
                'warehouse_id' => $whId,
                'item_code' => $code,
                'closing_qty' => $qty,
                'closing_value' => $val,
            ];
        }
        fclose($handle);

        // เขียนลง stock_monthly_report (replace per warehouse_id+item_code+period)
        $createdAt = time();
        $createdBy = Yii::$app->user->id;
        $inserted = 0;
        $updated = 0;
        $itemCache = [];

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($okRows as $r) {
                $existing = StockMonthlyReport::findOne([
                    'report_year' => $reportYear,
                    'report_month' => $reportMonth,
                    'warehouse_id' => $r['warehouse_id'],
                    'item_code' => $r['item_code'],
                ]);
                $isNew = !$existing;
                $rec = $existing ?: new StockMonthlyReport();
                $rec->report_year = $reportYear;
                $rec->report_month = $reportMonth;
                $rec->warehouse_id = $r['warehouse_id'];
                $rec->item_code = $r['item_code'];

                if (!isset($itemCache[$r['item_code']])) {
                    $itemCache[$r['item_code']] = StockItem::findOne(['item_code' => $r['item_code']]);
                }
                $item = $itemCache[$r['item_code']];
                $rec->unit_name = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;

                $rec->opening_qty = 0;
                $rec->opening_value = 0;
                $rec->in_qty = 0;
                $rec->in_value = 0;
                $rec->out_sub_qty = 0;
                $rec->out_hosp_qty = 0;
                $rec->total_out_qty = 0;
                $rec->total_out_value = 0;
                $rec->closing_qty = $r['closing_qty'];
                $rec->closing_value = $r['closing_value'];
                if ($isNew) {
                    $rec->created_at = $createdAt;
                    $rec->created_by = $createdBy;
                }
                $rec->save(false);
                $isNew ? $inserted++ : $updated++;
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดระหว่างบันทึก: ' . $e->getMessage());
            return $this->redirect(['stock-monthly']);
        }

        $skipTotal = count($skipMissingWh) + count($skipMissingItem) + count($skipBadNumber)
            + count($skipNoMatch) + count($skipAmbiguous);
        $monthNames = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        $periodLabel = ($monthNames[$reportMonth] ?? '') . ' ' . ($reportYear + 543);

        $skippedToken = self::writeSeedSkippedCsv(
            $skipMissingWh, $skipMissingItem, $skipBadNumber, $skipNoMatch, $skipAmbiguous
        );

        $maxList = 100; // จำกัดจำนวนรายการที่เก็บใน flash เพื่อไม่ให้ session.data ล้น
        $report = [
            'period' => $periodLabel,
            'inserted' => $inserted,
            'updated' => $updated,
            'skip_total' => $skipTotal,
            'skip_empty' => $skipEmpty,
            'skip_missing_wh' => array_slice($skipMissingWh, 0, $maxList),
            'skip_missing_item' => array_slice($skipMissingItem, 0, $maxList),
            'skip_bad_number' => array_slice($skipBadNumber, 0, $maxList),
            'skip_no_match' => array_slice($skipNoMatch, 0, $maxList),
            'skip_ambiguous' => array_slice($skipAmbiguous, 0, $maxList),
            'skip_missing_wh_count' => count($skipMissingWh),
            'skip_missing_item_count' => count($skipMissingItem),
            'skip_bad_number_count' => count($skipBadNumber),
            'skip_no_match_count' => count($skipNoMatch),
            'skip_ambiguous_count' => count($skipAmbiguous),
            'skipped_token' => $skippedToken,
        ];
        Yii::$app->session->setFlash('seed_import_report', $report);

        // คำนวณเดือนถัดไปเพื่อ redirect ให้ผู้ใช้เห็นว่า opening พร้อมใช้
        $nextMonth = $reportMonth + 1;
        $nextYear = $reportYear;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        return $this->redirect([
            'stock-monthly',
            'report_year' => $nextYear,
            'report_month' => $nextMonth,
        ]);
    }
}
