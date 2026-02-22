<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
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

    /**
     * รายงานสรุปยอดคงเหลือตามคลัง: วัสดุเหลืออะไรเท่าไหร่ ประเภทอะไร มูลค่ารวม น้อยกว่ากำหนดเท่าไหร่
     * รองรับทั้งคลังหลัก (ดูได้ทุกคลังหลัก + คลังย่อย) และคลังย่อย (ดูของตัวเอง)
     */
    public function actionBalanceByWarehouse()
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

        $warehouses = ['' => '-- ทุกคลัง (หลัก + ย่อย) --'];
        foreach ($listMain as $w) {
            $warehouses[$w->id] = 'คลังหลัก: ' . $w->warehouse_name;
        }
        foreach ($listSub as $w) {
            $warehouses[$w->id] = 'คลังย่อย: ' . $w->warehouse_name;
        }

        $allWarehouses = array_merge($listMain, $listSub);
        $warehouseIds = $warehouseId
            ? [$warehouseId]
            : array_column($allWarehouses, 'id');

        if (empty($warehouseIds)) {
            $rows = [];
            $summary = ['total_value' => 0, 'below_min_count' => 0, 'below_max_count' => 0, 'items_count' => 0];
        } else {
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
                ->andWhere(['>', 'sb.balance_qty', 0])
                ->groupBy('sb.warehouse_id', 'sb.item_code', 'i.item_name', 'i.min_qty', 'i.max_qty', 'cat.title', 'i.category_id');

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
            $summary = [
                'total_value' => $totalValue,
                'below_min_count' => $belowMinCount,
                'below_max_count' => $belowMaxCount,
                'items_count' => count($rows),
            ];
        }

        $this->view->params['active'] = 'report-balance';
        return $this->render('balance-by-warehouse', [
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'summary' => $summary,
        ]);
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
