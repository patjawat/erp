<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use app\components\AppHelper;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;

/**
 * ส่งออกรายงานวัสดุคงคลังรายตัว
 * - กรองตามประเภทวัสดุ (asset_type_id)
 * - เลือกช่วงวันที่ (date_start, date_end) เหมือน /inventory/report
 */
class ExportStockController extends \yii\web\Controller
{
    /**
     * แสดงรายงานวัสดุคงคลังรายตัวตามเงื่อนไข (ช่วงวันที่ + ประเภทวัสดุ) และปุ่มส่งออก Excel
     */
    public function actionIndex()
    {
        $searchModel = new StockEventSearch([
            'date_filter' => 'this_month',
        ]);
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->search(Yii::$app->request->queryParams);

        list($querys, $groupSummary) = $this->getReportData($searchModel);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'querys' => $querys,
            'groupSummary' => $groupSummary,
        ]);
    }

    /**
     * คืนค่า [รายการรายตัว, แถวรวม] ตาม searchModel (date_start, date_end, asset_type_id)
     * @return array [array $querys, array $groupSummary]
     */
    private function getReportData(StockEventSearch $searchModel)
    {
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }

        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "a.name = 'asset_item'",
            "a.group_id = 'MATER'",
        ];
        $groupFields = ['a.code'];
        $groupBy = implode(', ', $groupFields);
        $orderBy = "CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED), 
        CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED) LIMIT 99999999";

        $assetTypeId = $searchModel->asset_type_id;
        if (!empty($assetTypeId)) {
            $conditions[] = "a.category_id = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }

        list($sql, $params) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params,
            $groupBy,
            $orderBy
        );
        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        list($sqlSummary, $paramsSummary) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params,
            null,
            null
        );
        $groupSummary = Yii::$app->db->createCommand($sqlSummary, $paramsSummary)->queryOne();

        return [$querys, $groupSummary ?: []];
    }

    /**
     * ส่งออก Excel รายงานวัสดุคงคลังรายตัว ตามประเภทวัสดุ + ช่วงวันที่
     */
    public function actionExcel()
    {
        $searchModel = new StockEventSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->search(Yii::$app->request->queryParams);

        list($querys, ) = $this->getReportData($searchModel);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานวัสดุคงคลังรายตัว');

        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('TH Sarabun New')
            ->setSize(16);

        // หัวตาราง 2 แถว
        $sheet->setCellValue('A1', 'รหัสสินค้า');
        $sheet->setCellValue('B1', 'รายการสินค้า');
        $sheet->setCellValue('C1', 'ประเภทวัสดุ');
        $sheet->setCellValue('D1', 'ยอดยกมา');
        $sheet->setCellValue('F1', 'รับเข้า');
        $sheet->setCellValue('H1', 'จ่ายออก');
        $sheet->setCellValue('J1', 'คงเหลือสิ้นเดือน');

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:E1');
        $sheet->mergeCells('F1:G1');
        $sheet->mergeCells('H1:I1');
        $sheet->mergeCells('J1:K1');

        $sheet->setCellValue('D2', 'จำนวน');
        $sheet->setCellValue('E2', 'มูลค่า');
        $sheet->setCellValue('F2', 'จำนวน');
        $sheet->setCellValue('G2', 'มูลค่า');
        $sheet->setCellValue('H2', 'จำนวน');
        $sheet->setCellValue('I2', 'มูลค่า');
        $sheet->setCellValue('J2', 'จำนวนคงเหลือ');
        $sheet->setCellValue('K2', 'มูลค่าคงเหลือ');

        $headerRange = 'A1:K2';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowIndex = 3;
        foreach ($querys as $r) {
            $sheet->fromArray([
                $r['asset_item'],
                $r['asset_name'],
                $r['asset_type_name'],
                $r['begin_qty'],
                $r['begin_price'],
                $r['qty_in'],
                $r['price_in'],
                $r['total_qty_out'],
                $r['total_price_out'],
                $r['end_qty'],
                $r['end_price'],
            ], null, "A{$rowIndex}");

            $sheet->getStyle("E{$rowIndex}:K{$rowIndex}")->getFont()->setBold(true);
            $sheet->getStyle("E{$rowIndex}:K{$rowIndex}")
                ->getNumberFormat()->setFormatCode('#,##0.00');

            $rowIndex++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // แถวรวม
        $sheet->setCellValue("A{$rowIndex}", 'รวมทั้งหมด');
        $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");
        $sheet->setCellValue("E{$rowIndex}", "=SUM(E3:E" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("F{$rowIndex}", "=SUM(F3:F" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("G{$rowIndex}", "=SUM(G3:G" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("H{$rowIndex}", "=SUM(H3:H" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("I{$rowIndex}", "=SUM(I3:I" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("J{$rowIndex}", "=SUM(J3:J" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("K{$rowIndex}", "=SUM(K3:K" . ($rowIndex - 1) . ")");
        $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $filename = 'รายงานวัสดุคงคลังรายตัว_' . ($searchModel->date_start ?? '') . '_' . ($searchModel->date_end ?? '') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}
