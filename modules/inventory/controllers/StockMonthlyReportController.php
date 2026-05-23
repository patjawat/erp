<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use app\modules\inventory\models\StockMonthlyReport;
use app\modules\inventory\models\StockMonthlyReportSearch;
use app\modules\inventory\models\Warehouse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockMonthlyReportController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'generate' => ['POST'],
                    'delete-month' => ['POST'],
                    'reset-adjust' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * หน้าฟอร์มปรับยอด — แสดงได้ทั้งหน้าเดี่ยว / AJAX (modal=1) สำหรับ Offcanvas
     */
    public function actionAdjust($id)
    {
        $model = $this->findModel($id);
        $isModal = Yii::$app->request->get('modal') || Yii::$app->request->isAjax;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->original_closing_qty === null) {
                $model->original_closing_qty = $model->getOldAttribute('closing_qty');
            }
            if ($model->original_closing_value === null) {
                $model->original_closing_value = $model->getOldAttribute('closing_value');
            }
            $model->adjusted_at = time();
            $model->adjusted_by = isset(Yii::$app->user) && !Yii::$app->user->isGuest
                ? Yii::$app->user->id : null;

            if ($model->save()) {
                if ($isModal) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'ปรับยอด ' . $model->item_code . ' เรียบร้อย',
                    ];
                }
                Yii::$app->session->setFlash('success',
                    'ปรับยอด ' . $model->item_code . ' เรียบร้อย');
                return $this->redirect(['index',
                    'StockMonthlyReportSearch' => [
                        'report_year'  => $model->report_year,
                        'report_month' => $model->report_month,
                        'warehouse_id' => $model->warehouse_id,
                    ],
                ]);
            }
            if ($isModal) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึก: ' . implode(', ', $model->getFirstErrors()),
                ];
            }
            Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกการปรับยอดได้');
        }

        if ($isModal) {
            return $this->renderAjax('_adjust_form', ['model' => $model]);
        }
        return $this->render('adjust', ['model' => $model]);
    }

    /**
     * ยกเลิกการปรับยอด — คืนค่า closing เป็นค่าเดิมที่ระบบคำนวณ
     * รองรับโหมด modal (JSON response)
     */
    public function actionResetAdjust($id)
    {
        $model = $this->findModel($id);
        $isModal = Yii::$app->request->get('modal') || Yii::$app->request->isAjax;

        if (!$model->isAdjusted()) {
            if ($isModal) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'แถวนี้ไม่ได้ถูกปรับยอด'];
            }
            Yii::$app->session->setFlash('warning', 'แถวนี้ไม่ได้ถูกปรับยอด');
            return $this->redirect(['index']);
        }

        if ($model->original_closing_qty !== null) {
            $model->closing_qty = $model->original_closing_qty;
        } else {
            $model->closing_qty = (float) $model->opening_qty + (float) $model->in_qty - (float) $model->total_out_qty;
        }
        if ($model->original_closing_value !== null) {
            $model->closing_value = $model->original_closing_value;
        } else {
            $model->closing_value = (float) $model->opening_value + (float) $model->in_value - (float) $model->total_out_value;
        }
        $model->adjusted_at = null;
        $model->adjusted_by = null;
        $model->adjustment_note = null;
        $model->original_closing_qty = null;
        $model->original_closing_value = null;
        $model->save(false);

        if ($isModal) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'message' => 'ยกเลิกการปรับยอด ' . $model->item_code . ' เรียบร้อย',
            ];
        }

        Yii::$app->session->setFlash('success', 'ยกเลิกการปรับยอด ' . $model->item_code . ' เรียบร้อย');
        return $this->redirect(['index',
            'StockMonthlyReportSearch' => [
                'report_year'  => $model->report_year,
                'report_month' => $model->report_month,
                'warehouse_id' => $model->warehouse_id,
            ],
        ]);
    }

    /**
     * รายงานสรุปปิดยอดเดือน — แยกตามประเภทวัสดุ (asset_type)
     * รูปแบบตาม /inventory/report (มูลค่า ยกมา / ซื้อ / รวม / จ่าย รพ.สต. / จ่าย รพ. / รวมจ่าย / ยกไป)
     */
    public function actionSummary()
    {
        $reportYear  = (int) (Yii::$app->request->get('report_year') ?: date('Y'));
        $reportMonth = (int) (Yii::$app->request->get('report_month') ?: date('n'));
        $warehouseId = Yii::$app->request->get('warehouse_id');
        $warehouseId = ($warehouseId === '' || $warehouseId === null) ? null : (int) $warehouseId;

        // ----- aggregate ตาม asset_type -----
        $query = (new Query())
            ->select([
                'asset_type_code'  => new Expression("COALESCE(t.code, si.category_id, 'OTHER')"),
                'asset_type_name'  => new Expression("COALESCE(t.title, si.category_id, 'อื่นๆ')"),
                'begin_price'      => new Expression('SUM(r.opening_value)'),
                'price_in'         => new Expression('SUM(r.in_value)'),
                'total_price_begin'=> new Expression('SUM(r.opening_value + r.in_value)'),
                'branch_price_out' => new Expression('SUM(r.out_sub_value)'),
                'price_out'        => new Expression('SUM(r.out_hosp_value)'),
                'total_price_out'  => new Expression('SUM(r.total_out_value)'),
                'end_price'        => new Expression('SUM(r.closing_value)'),
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->leftJoin(['si' => 'stock_item'], 'si.item_code = r.item_code')
            ->leftJoin(['t' => 'categorise'],
                "t.code = si.category_id AND t.name = 'asset_type'")
            ->where([
                'r.report_year'  => $reportYear,
                'r.report_month' => $reportMonth,
            ])
            ->groupBy(new Expression("COALESCE(t.code, si.category_id, 'OTHER')"))
            ->orderBy(new Expression("CAST(SUBSTRING(COALESCE(t.code, si.category_id, '0'), 2) AS UNSIGNED)"));

        if ($warehouseId !== null) {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $querys = $query->all();

        // ----- ผลรวมทั้งหมด -----
        $sumQuery = (new Query())
            ->select([
                'begin_price'      => new Expression('SUM(r.opening_value)'),
                'price_in'         => new Expression('SUM(r.in_value)'),
                'total_price_begin'=> new Expression('SUM(r.opening_value + r.in_value)'),
                'branch_price_out' => new Expression('SUM(r.out_sub_value)'),
                'price_out'        => new Expression('SUM(r.out_hosp_value)'),
                'total_price_out'  => new Expression('SUM(r.total_out_value)'),
                'end_price'        => new Expression('SUM(r.closing_value)'),
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->where([
                'r.report_year'  => $reportYear,
                'r.report_month' => $reportMonth,
            ]);
        if ($warehouseId !== null) {
            $sumQuery->andWhere(['r.warehouse_id' => $warehouseId]);
        }
        $sum = $sumQuery->one() ?: [];

        // ----- options สำหรับ filter -----
        $currentYear = (int) date('Y');
        $yearOptions = [];
        for ($y = $currentYear + 1; $y >= $currentYear - 5; $y--) {
            $yearOptions[$y] = $y . ' (พ.ศ. ' . ($y + 543) . ')';
        }
        $monthOptions = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthOptions[$m] = StockMonthlyReport::thaiMonthName($m);
        }
        $warehouseOptions = ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id', 'warehouse_name'
        );

        return $this->render('summary', [
            'querys' => $querys,
            'sum' => $sum,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'warehouseId' => $warehouseId,
            'yearOptions' => $yearOptions,
            'monthOptions' => $monthOptions,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }

    /**
     * Export Excel ของรายงานสรุปปิดยอดเดือน (แยกประเภท)
     */
    public function actionSummaryExcel()
    {
        $reportYear  = (int) (Yii::$app->request->get('report_year') ?: date('Y'));
        $reportMonth = (int) (Yii::$app->request->get('report_month') ?: date('n'));
        $warehouseId = Yii::$app->request->get('warehouse_id');
        $warehouseId = ($warehouseId === '' || $warehouseId === null) ? null : (int) $warehouseId;

        // re-use aggregation logic
        $query = (new Query())
            ->select([
                'asset_type_code'  => new Expression("COALESCE(t.code, si.category_id, 'OTHER')"),
                'asset_type_name'  => new Expression("COALESCE(t.title, si.category_id, 'อื่นๆ')"),
                'begin_price'      => new Expression('SUM(r.opening_value)'),
                'price_in'         => new Expression('SUM(r.in_value)'),
                'total_price_begin'=> new Expression('SUM(r.opening_value + r.in_value)'),
                'branch_price_out' => new Expression('SUM(r.out_sub_value)'),
                'price_out'        => new Expression('SUM(r.out_hosp_value)'),
                'total_price_out'  => new Expression('SUM(r.total_out_value)'),
                'end_price'        => new Expression('SUM(r.closing_value)'),
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->leftJoin(['si' => 'stock_item'], 'si.item_code = r.item_code')
            ->leftJoin(['t' => 'categorise'],
                "t.code = si.category_id AND t.name = 'asset_type'")
            ->where([
                'r.report_year'  => $reportYear,
                'r.report_month' => $reportMonth,
            ])
            ->groupBy(new Expression("COALESCE(t.code, si.category_id, 'OTHER')"))
            ->orderBy(new Expression("CAST(SUBSTRING(COALESCE(t.code, si.category_id, '0'), 2) AS UNSIGNED)"));

        if ($warehouseId !== null) {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $rows = $query->all();
        $monthLabel = StockMonthlyReport::thaiMonthName($reportMonth) . ' ' . ($reportYear + 543);
        $warehouseName = '';
        if ($warehouseId !== null) {
            $wh = Warehouse::findOne($warehouseId);
            $warehouseName = $wh ? $wh->warehouse_name : '';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปปิดยอดเดือนแยกประเภท');

        $spreadsheet->getDefaultStyle()->getFont()->setName('TH Sarabun New')->setSize(16);

        // ----- ส่วนหัวรายงาน -----
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'สรุปปิดยอดเดือน (แยกประเภทวัสดุ) — เดือน ' . $monthLabel
            . ($warehouseName ? ' | คลัง: ' . $warehouseName : ''));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ----- หัวตาราง 2 แถว (A3:I4) -----
        $sheet->setCellValue('A3', 'ที่');
        $sheet->setCellValue('B3', 'รายการ (ประเภทวัสดุ)');
        $sheet->setCellValue('C3', 'สินค้าคงเหลือ (ยกมา)');
        $sheet->setCellValue('D3', 'ซื้อระหว่างเดือน');
        $sheet->setCellValue('E3', 'รวม');
        $sheet->setCellValue('F3', 'สินค้าที่ใช้ไป');
        $sheet->setCellValue('I3', 'ยอดยกไป (คงเหลือ)');

        $sheet->mergeCells('A3:A4');
        $sheet->mergeCells('B3:B4');
        $sheet->mergeCells('C3:C4');
        $sheet->mergeCells('D3:D4');
        $sheet->mergeCells('E3:E4');
        $sheet->mergeCells('F3:H3');
        $sheet->mergeCells('I3:I4');

        $sheet->setCellValue('F4', 'จ่ายส่วนของ รพ.สต.');
        $sheet->setCellValue('G4', 'จ่ายส่วนของโรงพยาบาล');
        $sheet->setCellValue('H4', 'รวม');

        $headerRange = 'A3:I4';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ----- เนื้อหา -----
        $startRow = 5;
        $rowIndex = $startRow;
        $num = 1;
        $totBegin = $totIn = $totBeginAll = $totBranch = $totSub = $totOut = $totEnd = 0;

        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowIndex, $num++);
            $sheet->setCellValue('B' . $rowIndex, '(' . $r['asset_type_code'] . ') ' . $r['asset_type_name']);
            $sheet->setCellValue('C' . $rowIndex, (float) $r['begin_price']);
            $sheet->setCellValue('D' . $rowIndex, (float) $r['price_in']);
            $sheet->setCellValue('E' . $rowIndex, (float) $r['total_price_begin']);
            $sheet->setCellValue('F' . $rowIndex, (float) $r['branch_price_out']);
            $sheet->setCellValue('G' . $rowIndex, (float) $r['price_out']);
            $sheet->setCellValue('H' . $rowIndex, (float) $r['total_price_out']);
            $sheet->setCellValue('I' . $rowIndex, (float) $r['end_price']);

            $totBegin    += (float) $r['begin_price'];
            $totIn       += (float) $r['price_in'];
            $totBeginAll += (float) $r['total_price_begin'];
            $totBranch   += (float) $r['branch_price_out'];
            $totSub      += (float) $r['price_out'];
            $totOut      += (float) $r['total_price_out'];
            $totEnd      += (float) $r['end_price'];

            $rowIndex++;
        }

        // ----- แถวรวม -----
        if ($rowIndex > $startRow) {
            $sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);
            $sheet->setCellValue('A' . $rowIndex, 'รวมทั้งหมด');
            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('C' . $rowIndex, $totBegin);
            $sheet->setCellValue('D' . $rowIndex, $totIn);
            $sheet->setCellValue('E' . $rowIndex, $totBeginAll);
            $sheet->setCellValue('F' . $rowIndex, $totBranch);
            $sheet->setCellValue('G' . $rowIndex, $totSub);
            $sheet->setCellValue('H' . $rowIndex, $totOut);
            $sheet->setCellValue('I' . $rowIndex, $totEnd);
            $totalRange = 'A' . $rowIndex . ':I' . $rowIndex;
            $sheet->getStyle($totalRange)->getFont()->setBold(true);
            $sheet->getStyle($totalRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');
        }

        // ----- สไตล์เนื้อหา -----
        if ($rowIndex > $startRow) {
            $bodyRange  = 'A' . $startRow . ':I' . $rowIndex;
            $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . $startRow . ':A' . $rowIndex)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $startRow . ':I' . $rowIndex)
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'สรุปปิดยอดเดือน_แยกประเภท_' . $reportYear . sprintf('%02d', $reportMonth)
            . ($warehouseId ? '_w' . $warehouseId : '') . '.xlsx';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition',
            'attachment; filename="' . addslashes($filename) . '"');
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        return ob_get_clean();
    }

    protected function findModel($id)
    {
        $model = StockMonthlyReport::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ระบุ');
        }
        return $model;
    }

    public function actionIndex()
    {
        $searchModel = new StockMonthlyReportSearch();

        if ($searchModel->report_year === null) {
            $searchModel->report_year = (int) date('Y');
        }
        if ($searchModel->report_month === null) {
            $searchModel->report_month = (int) date('n');
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGenerate()
    {
        $reportYear  = (int) Yii::$app->request->post('report_year');
        $reportMonth = (int) Yii::$app->request->post('report_month');
        $warehouseId = Yii::$app->request->post('warehouse_id');
        $warehouseId = ($warehouseId === '' || $warehouseId === null) ? null : (int) $warehouseId;
        $assetTypeId = Yii::$app->request->post('asset_type_id');
        $assetTypeId = ($assetTypeId === '' || $assetTypeId === null) ? null : $assetTypeId;

        if (!$reportYear || !$reportMonth) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุปีและเดือนที่ต้องการสรุป');
            return $this->redirect(['index']);
        }

        try {
            $result = StockMonthlyReport::generateMonth($reportYear, $reportMonth, $warehouseId, $assetTypeId);
            $monthLabel = StockMonthlyReport::thaiMonthName($reportMonth) . ' ' . ($reportYear + 543);
            $msg = "สรุปคงคลังเดือน{$monthLabel} เรียบร้อย — บันทึก {$result['inserted']} รายการ";
            if (!empty($result['skipped'])) {
                $msg .= " (ข้าม {$result['skipped']} รายการที่ยังไม่มีใน stock_item ของ V2)";
            }
            Yii::$app->session->setFlash('success', $msg);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect([
            'index',
            'StockMonthlyReportSearch' => [
                'report_year' => $reportYear,
                'report_month' => $reportMonth,
                'warehouse_id' => $warehouseId,
                'category_id'  => $assetTypeId,
            ],
        ]);
    }

    public function actionDeleteMonth()
    {
        $reportYear  = (int) Yii::$app->request->post('report_year');
        $reportMonth = (int) Yii::$app->request->post('report_month');
        $warehouseId = Yii::$app->request->post('warehouse_id');
        $warehouseId = ($warehouseId === '' || $warehouseId === null) ? null : (int) $warehouseId;

        if (!$reportYear || !$reportMonth) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุปีและเดือนที่ต้องการลบ');
            return $this->redirect(['index']);
        }

        $cond = [
            'report_year' => $reportYear,
            'report_month' => $reportMonth,
        ];
        if ($warehouseId !== null) {
            $cond['warehouse_id'] = $warehouseId;
        }

        $deleted = StockMonthlyReport::deleteAll($cond);
        Yii::$app->session->setFlash('success', "ลบข้อมูลสรุปแล้ว {$deleted} รายการ");

        return $this->redirect(['index']);
    }

    public function actionExportExcel()
    {
        $searchModel = new StockMonthlyReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $query = clone $dataProvider->query;
        $rows = $query
            ->select([
                'r.*',
                'item_name' => 'si.item_name',
                'warehouse_name' => 'w.warehouse_name',
            ])
            ->leftJoin('warehouses w', 'w.id = r.warehouse_id')
            ->asArray()
            ->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปคงคลังรายเดือน');

        $titleParts = [];
        if ($searchModel->report_year && $searchModel->report_month) {
            $titleParts[] = 'เดือน ' . StockMonthlyReport::thaiMonthName($searchModel->report_month)
                . ' ' . ($searchModel->report_year + 543);
        }
        if ($searchModel->warehouse_id) {
            $wh = Warehouse::findOne($searchModel->warehouse_id);
            if ($wh) {
                $titleParts[] = 'คลัง: ' . $wh->warehouse_name;
            }
        }
        $titleLine = 'รายงานสรุปคงคลังรายเดือน' . ($titleParts ? ' (' . implode(', ', $titleParts) . ')' : '');

        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', $titleLine);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true);

        $headers = [
            'A' => 'ที่',
            'B' => 'ปี/เดือน',
            'C' => 'คลังสินค้า',
            'D' => 'รหัสพัสดุ',
            'E' => 'รายการ',
            'F' => 'หน่วย',
            'G' => 'ยกมา (จำนวน)',
            'H' => 'ยกมา (มูลค่า)',
            'I' => 'รับเข้า (จำนวน)',
            'J' => 'รับเข้า (มูลค่า)',
            'K' => 'จ่าย รพ.สต. (จำนวน)',
            'L' => 'จ่าย รพ. (จำนวน)',
            'M' => 'รวมจ่าย (มูลค่า)',
            'N' => 'คงเหลือ (มูลค่า)',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '3', $label);
        }
        $headerRange = 'A3:N3';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setName('TH Sarabun New')->setSize(14)->setBold(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F0FE');

        $rowIndex = 4;
        $num = 1;
        $sumOpenQty = $sumOpenVal = $sumInQty = $sumInVal = 0;
        $sumOutSub = $sumOutHosp = $sumOutVal = $sumClosingVal = 0;

        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowIndex, $num++);
            $sheet->setCellValue('B' . $rowIndex,
                StockMonthlyReport::thaiMonthName($row['report_month']) . ' ' . ($row['report_year'] + 543));
            $sheet->setCellValue('C' . $rowIndex, $row['warehouse_name'] ?? '');
            $sheet->setCellValue('D' . $rowIndex, $row['item_code']);
            $sheet->setCellValue('E' . $rowIndex, $row['item_name'] ?? '');
            $sheet->setCellValue('F' . $rowIndex, $row['unit_name'] ?? '');
            $sheet->setCellValue('G' . $rowIndex, (float) $row['opening_qty']);
            $sheet->setCellValue('H' . $rowIndex, (float) $row['opening_value']);
            $sheet->setCellValue('I' . $rowIndex, (float) $row['in_qty']);
            $sheet->setCellValue('J' . $rowIndex, (float) $row['in_value']);
            $sheet->setCellValue('K' . $rowIndex, (float) $row['out_sub_qty']);
            $sheet->setCellValue('L' . $rowIndex, (float) $row['out_hosp_qty']);
            $sheet->setCellValue('M' . $rowIndex, (float) $row['total_out_value']);
            $sheet->setCellValue('N' . $rowIndex, (float) $row['closing_value']);

            $sumOpenQty    += (float) $row['opening_qty'];
            $sumOpenVal    += (float) $row['opening_value'];
            $sumInQty      += (float) $row['in_qty'];
            $sumInVal      += (float) $row['in_value'];
            $sumOutSub     += (float) $row['out_sub_qty'];
            $sumOutHosp    += (float) $row['out_hosp_qty'];
            $sumOutVal     += (float) $row['total_out_value'];
            $sumClosingVal += (float) $row['closing_value'];

            $rowIndex++;
        }

        if ($rowIndex > 4) {
            $sheet->mergeCells('A' . $rowIndex . ':F' . $rowIndex);
            $sheet->setCellValue('A' . $rowIndex, 'รวม');
            $sheet->setCellValue('G' . $rowIndex, $sumOpenQty);
            $sheet->setCellValue('H' . $rowIndex, $sumOpenVal);
            $sheet->setCellValue('I' . $rowIndex, $sumInQty);
            $sheet->setCellValue('J' . $rowIndex, $sumInVal);
            $sheet->setCellValue('K' . $rowIndex, $sumOutSub);
            $sheet->setCellValue('L' . $rowIndex, $sumOutHosp);
            $sheet->setCellValue('M' . $rowIndex, $sumOutVal);
            $sheet->setCellValue('N' . $rowIndex, $sumClosingVal);
            $totalRange = 'A' . $rowIndex . ':N' . $rowIndex;
            $sheet->getStyle($totalRange)->getFont()->setBold(true);
            $sheet->getStyle($totalRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF7D6');
        }

        $bodyRange = 'A3:N' . $rowIndex;
        $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('A4:F' . $rowIndex)->getFont()->setName('TH Sarabun New')->setSize(13);
        $sheet->getStyle('G4:N' . $rowIndex)
            ->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'stock_monthly_report_'
            . ($searchModel->report_year ?: 'all') . '_'
            . sprintf('%02d', $searchModel->report_month ?: 0)
            . '.xlsx';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        Yii::$app->response->headers->set(
            'Content-Disposition',
            "attachment;filename=\"{$filename}\""
        );
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        return ob_get_clean();
    }
}
