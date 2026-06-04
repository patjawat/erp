<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
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
    public function actionSeedSkippedDownload($token)
    {
        if (!preg_match('/^[a-f0-9]{32}$/', (string) $token)) {
            throw new NotFoundHttpException('Invalid token');
        }
        $path = Yii::getAlias('@runtime') . '/seed-import-skipped/' . $token . '.csv';
        if (!is_file($path)) {
            throw new NotFoundHttpException('File not found หรือหมดอายุแล้ว');
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
    public function actionSeedTemplate()
    {
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
     * CSV header (case-insensitive): item_code, closing_qty, closing_value [, warehouse_name]
     * - ถ้าระบุ warehouse_name → ใช้ตามนั้น
     * - ถ้าเว้นว่าง → map คลังหลักอัตโนมัติจาก stock_item.category_id ที่ตรงกับ data_json.item_type ของคลัง
     *   (เจอ 1 คลัง = ใช้, 0 คลัง = ข้าม, >1 คลัง = ข้าม)
     * เขียนลง stock_monthly_report เป็น closing ของเดือนที่เลือก
     */
    public function actionSeedImport()
    {
        $reportYear  = (int) Yii::$app->request->post('report_year');
        $reportMonth = (int) Yii::$app->request->post('report_month');

        if (!$reportYear || !$reportMonth || $reportMonth < 1 || $reportMonth > 12) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุปีและเดือนของยอดยกมาให้ถูกต้อง');
            return $this->redirect(['index']);
        }

        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file || $file->error !== UPLOAD_ERR_OK) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ CSV');
            return $this->redirect(['index']);
        }

        $handle = fopen($file->tempName, 'r');
        if (!$handle) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอ่านไฟล์ CSV ได้');
            return $this->redirect(['index']);
        }

        $firstBytes = fread($handle, 3);
        if ($firstBytes !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            Yii::$app->session->setFlash('error', 'ไฟล์ CSV ว่างหรือไม่มี header');
            return $this->redirect(['index']);
        }
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);
        $required = ['item_code', 'closing_qty', 'closing_value'];
        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                fclose($handle);
                Yii::$app->session->setFlash('error', "Header ไม่ครบ ต้องมีคอลัมน์: " . implode(', ', $required) . " (warehouse_name เป็น optional)");
                return $this->redirect(['index']);
            }
        }
        $idx = array_flip($header);
        $hasWarehouseCol = isset($idx['warehouse_name']);

        // โหลด lookup คลังหลัก + ประเภทวัสดุที่แต่ละคลังรับ (data_json.item_type)
        $warehouseMap = [];
        $mainWarehouses = [];
        $categoryToWhIds = [];
        foreach (Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all() as $w) {
            $warehouseMap[mb_strtolower(trim((string) $w->warehouse_name))] = (int) $w->id;
            $mainWarehouses[(int) $w->id] = $w;
            $allowed = (is_array($w->data_json) && !empty($w->data_json['item_type']) && is_array($w->data_json['item_type']))
                ? $w->data_json['item_type'] : [];
            foreach ($allowed as $code) {
                $categoryToWhIds[(string) $code][] = (int) $w->id;
            }
        }

        // item_code → category_id
        $itemCategoryMap = [];
        foreach ((new Query())->select(['item_code', 'category_id'])->from('stock_item')->each() as $it) {
            $itemCategoryMap[(string) $it['item_code']] = $it['category_id'] !== null ? (string) $it['category_id'] : null;
        }

        $rowNum = 1;
        $okRows = [];
        $skipMissingWh = [];
        $skipMissingItem = [];
        $skipBadNumber = [];
        $skipNoMatch = [];
        $skipAmbiguous = [];
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

        $createdAt = time();
        $createdBy = Yii::$app->user->id;
        $inserted = 0;
        $updated = 0;

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
            return $this->redirect(['index']);
        }

        $skipTotal = count($skipMissingWh) + count($skipMissingItem) + count($skipBadNumber)
            + count($skipNoMatch) + count($skipAmbiguous);
        $monthNames = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        $periodLabel = ($monthNames[$reportMonth] ?? '') . ' ' . ($reportYear + 543);

        $skippedToken = self::writeSeedSkippedCsv(
            $skipMissingWh, $skipMissingItem, $skipBadNumber, $skipNoMatch, $skipAmbiguous
        );

        $maxList = 100; // จำกัดจำนวนรายการที่เก็บใน flash เพื่อไม่ให้ session.data ล้น
        Yii::$app->session->setFlash('seed_import_report', [
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
        ]);

        return $this->redirect(['index']);
    }
}
