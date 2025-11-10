<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
// Microsoft Excel
use app\modules\inventory\models\StockEvent;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use app\modules\inventory\models\StockEventSearch;

class ReportController extends \yii\web\Controller
{
    //รายงานรวมวัสดุคงคลัง
    public function actionIndex()
    {

        $searchModel = new StockEventSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_filter' => 'this_month',
        ]);
        $searchModel->search($this->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }
        // ----- Dynamic Filters -----
        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "e.name = 'order'",
            "i.asset_item IS NOT NULL",
        ];

        // ----- Auto GROUP / ORDER -----
        $groupFields = [
            't.code'
        ];
        $groupBy = implode(', ', $groupFields);
        $orderBy = 'CAST(SUBSTRING_INDEX(a.code, \'-\', 1) AS UNSIGNED), ' .
            'CAST(SUBSTRING_INDEX(a.code, \'-\', -1) AS UNSIGNED), ' .
            'CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED)';

        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return $this->render('index', [
            'querys' => $querys,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'searchModel' => $searchModel,
        ]);
    }



    public function actionListByOrder()
    {
        $export = $this->request->get('export');
        $searchModel = new StockEventSearch([
            'name' => 'order_item', // กรองเฉพาะรายการที่เป็น item
        ]);

        $searchModel->load(Yii::$app->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }

        // ----- Base conditions / params -----
        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "e.name = 'order'",
            "i.asset_item IS NOT NULL",
            "e.order_status = 'success'",
            "i.order_status = 'success'",
            "wi.warehouse_type = 'MAIN'",
            "e.movement_date BETWEEN :date_start AND :date_end"
        ];

        // ----- Dynamic Filters (append to $conditions / $params) -----
        $warehouseId = $searchModel->q_warehouse_id;
        if (!empty($warehouseId)) {
            $conditions[] = "e.warehouse_id = :warehouse_id";
            $params[':warehouse_id'] = $warehouseId;
        }

        $transactionType = $searchModel->transaction_type;
        if (!empty($transactionType)) {
            $conditions[] = "i.transaction_type = :transaction_type";
            $params[':transaction_type'] = $transactionType;
        }

        $assetTypeId = $searchModel->q_asset_type;
        if (!empty($assetTypeId)) {
            // ตรวจเช็คว่าคุณจะกรองด้วย t.code หรือ t.id ให้ตรงกับฐานข้อมูลของคุณ
            $conditions[] = "t.code = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }

        $assetItemId = $searchModel->asset_item;
        if (!empty($assetItemId)) {
            $conditions[] = "i.asset_item = :asset_item";
            $params[':asset_item'] = $assetItemId;
        }
        $vendorId = $searchModel->q_vendor;
        if (!empty($vendorId)) {
            $conditions[] = "e.vendor_id = :q_vendor";
            $params[':q_vendor'] = $vendorId;
        }
        $qCode = $searchModel->q_code;
        if (!empty($qCode)) {
            $conditions[] = "e.code = :q_code";
            $params[':q_code'] = $qCode;
        }


        $where = implode(' AND ', $conditions);
        $sql = "SELECT
            v.code as vendor_id,
            v.title as vendor_name,
            wo.warehouse_name as form_warehouse_name,
            wi.warehouse_name,
            wi.warehouse_type,
            i.asset_item,
            i.qty AS item_qty,
             i.unit_price,
            a.title as asset_name,
            a.data_json->>'$.unit' AS unit,
            t.code as asset_type_code,
            t.title  as asset_type_name,
            e.code,
            e.movement_date,
            e.transaction_type,
            e.movement_date,
            i.qty,
            i.unit_price,
            SUM(i.qty*i.unit_price) as end_price
                FROM `stock_events` i
                LEFT JOIN `stock_events` e ON e.id = i.category_id
                LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
                LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
                LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
                LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
                -- เพื่อเลือกแถวเดียวจาก vendor ไม่อย่างนั้นจะซ้ำกัน 2 แถว
                LEFT JOIN (
    SELECT code, title
    FROM (
        SELECT *,
               ROW_NUMBER() OVER(PARTITION BY code ORDER BY code) AS rn
        FROM categorise
        WHERE name = 'vendor'
    ) t
    WHERE rn = 1
) v ON v.code = e.vendor_id
                WHERE $where
                GROUP BY i.id
                ORDER BY i.id,e.movement_date ASC;";


        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();
        if ($export == 1) {
            return $this->ListByOrderExport($searchModel, $querys);
        }
        return $this->render('list_by_order', [
            'searchModel' => $searchModel,
            'querys' => $querys,
        ]);
    }

    private function ListByOrderExport($searchModel, $querys)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานวัสดุรับ-จ่าย');

        // ตั้งค่า default font
        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('TH Sarabun New')
            ->setSize(16);

        // --------------------------------------------------
        // หัวตาราง
        // --------------------------------------------------
        $sheet->setCellValue('A1', 'ลำดับ');
        $sheet->setCellValue('B1', 'ชื่อคลัง');
        $sheet->setCellValue('C1', 'ประเภทคลัง');
        $sheet->setCellValue('D1', 'ประเภทวัสดุ');
        $sheet->setCellValue('E1', 'คลังที่ขอเบิก');
        $sheet->setCellValue('F1', 'วันที่');
        $sheet->setCellValue('G1', 'เลขที่');
        $sheet->setCellValue('H1', 'ความเคลื่อนไหว');
        $sheet->setCellValue('I1', 'รหัสวัสดุ');
        $sheet->setCellValue('J1', 'ชื่อวัสดุ');
        $sheet->setCellValue('K1', 'หน่วย');
        $sheet->setCellValue('L1', 'จำนวน');
        $sheet->setCellValue('M1', 'ราคาต่อหน่วย');
        $sheet->setCellValue('N1', 'รวมราคา');

        $headerRange = 'A1:N1';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // --------------------------------------------------
        // เนื้อหาตาราง
        // --------------------------------------------------
        $StartRow = 2;
        $row = 1;

        foreach ($querys as $key => $value) {
            $numRow = $StartRow++;

            if ($value['warehouse_type'] == 'MAIN') {
                $warehouseTypeText = 'คลังหลัก';
            } elseif ($value['warehouse_type'] == 'SUB') {
                $warehouseTypeText = 'คลังย่อย';
            } elseif ($value['warehouse_type'] == 'BRANCH') {
                $warehouseTypeText = 'คลังรพ.สต.';
            } else {
                $warehouseTypeText = '';
            }

            if ($value['transaction_type'] == 'IN') {
                $sourceName = $value['vendor_name'];
                $transactionName = 'รับ';
            } else {
                $sourceName = $value['form_warehouse_name'];
                $transactionName = 'จ่าย';
            }

            $sheet->setCellValue('A' . $numRow, $row++);
            $sheet->setCellValue('B' . $numRow, $value['warehouse_name']);
            $sheet->setCellValue('C' . $numRow, $warehouseTypeText);
            $sheet->setCellValue('D' . $numRow, $value['asset_type_name']);
            $sheet->setCellValue('E' . $numRow, $sourceName);
            $sheet->setCellValue('F' . $numRow, AppHelper::convertToThai($value['movement_date']));
            $sheet->setCellValue('G' . $numRow, $value['code']);
            $sheet->setCellValue('H' . $numRow, $transactionName);
            $sheet->setCellValue('I' . $numRow, $value['asset_item']);
            $sheet->setCellValue('J' . $numRow, $value['asset_name']);
            $sheet->setCellValue('K' . $numRow, $value['unit']);
            $sheet->setCellValue('L' . $numRow, $value['item_qty']);
            $sheet->setCellValue('M' . $numRow, number_format($value['unit_price'] ?? 0, 2));
            $sheet->setCellValue('N' . $numRow, number_format(($value['end_price']) ?? 0, 2));
        }

        // --------------------------------------------------
        // จัดตำแหน่งคอลัมน์
        // --------------------------------------------------
        $lastRow = $StartRow - 1;

        // F,G,H = กลาง
        $sheet->getStyle("F2:H{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // L,M,N = ขวา
        $sheet->getStyle("L2:N{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // --------------------------------------------------
        // ความกว้างคอลัมน์
        // --------------------------------------------------
        $widths = [
            'A' => 8,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 15,
            'I' => 20,
            'J' => 40,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // --------------------------------------------------
        // สร้างไฟล์ดาวน์โหลด
        // --------------------------------------------------
        try {
            $dateStart = $searchModel->date_start;
            $dateEnd = $searchModel->date_end;
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }


        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/myStock.xlsx';
        $writer->save($filePath);


        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }






    //แสดงรายงานแบบละเอียดแยกตามรายการสินค้า
    public function actionListByItem()
    {

        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }

        // ----- Auto GROUP / ORDER -----
        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];

        $conditions = [
            "a.name = 'asset_item'",
            "a.group_id = 4",
        ];

        // ----- Auto GROUP / ORDER -----
        $groupFields = [
            'a.code'
        ];
        $groupBy = implode(', ', $groupFields);
        $orderBy = 'CAST(SUBSTRING_INDEX(a.code, \'-\', 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(a.code, \'-\', -1) AS UNSIGNED), 
        CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED) limit 99999999';



        $assetTypeId = $searchModel->asset_type_id;
        if (!empty($assetTypeId)) {
            $conditions[] = "a.category_id = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }


        list($sql, $params) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // ----- Query 2: ไม่มี group/order -----
        list($sqlSummary, $paramsForSummary) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params
            // ไม่มี groupBy / orderBy
        );
        $groupSummary = Yii::$app->db->createCommand($sqlSummary, $paramsForSummary)->queryOne();

        return $this->render('list_by_item', [
            'querys' => $querys,
            'groupSummary' => $groupSummary,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    public function actionExportExcelByItem()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new StockEventSearch();
        $searchModel->search(Yii::$app->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }

        // ----- Auto GROUP / ORDER -----
        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];

        $conditions = [
            "a.name = 'asset_item'",
            "a.group_id = 4",
        ];

        // ----- Auto GROUP / ORDER -----
        $groupFields = [
            'a.code'
        ];
        $groupBy = implode(', ', $groupFields);
        $orderBy = 'CAST(SUBSTRING_INDEX(a.code, \'-\', 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(a.code, \'-\', -1) AS UNSIGNED), 
        CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED) limit 99999999';



        $assetTypeId = $searchModel->asset_type_id;
        if (!empty($assetTypeId)) {
            $conditions[] = "a.category_id = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }


        list($sql, $params) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // ✅ สร้างไฟล์ Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock Summary');
        // ตั้งค่า default font ทั้งหมด
        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('TH Sarabun New')
            ->setSize(16);

        // --------------------------------------------------
        // สร้างหัวตาราง 2 แถวเหมือนใน HTML
        // --------------------------------------------------
        // แถวที่ 1
        $sheet->setCellValue('A1', 'รหัสสินค้า');
        $sheet->setCellValue('B1', 'รายการสินค้า');
        $sheet->setCellValue('C1', 'ประเภทวัสดุ');
        $sheet->setCellValue('D1', 'ยอดยกมา');
        $sheet->setCellValue('F1', 'รับเข้า');
        $sheet->setCellValue('H1', 'จ่ายออก');
        $sheet->setCellValue('J1', 'คงเหลือสิ้นเดือน');

        // รวมเซลล์ตามโครงสร้าง
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:E1');
        $sheet->mergeCells('F1:G1');
        $sheet->mergeCells('H1:I1');
        $sheet->mergeCells('J1:K1');

        // แถวที่ 2
        $sheet->setCellValue('D2', 'จำนวน');
        $sheet->setCellValue('E2', 'มูลค่า');
        $sheet->setCellValue('F2', 'จำนวน');
        $sheet->setCellValue('G2', 'มูลค่า');
        $sheet->setCellValue('H2', 'จำนวน');
        $sheet->setCellValue('I2', 'มูลค่า');
        $sheet->setCellValue('J2', 'จำนวนคงเหลือ');
        $sheet->setCellValue('K2', 'มูลค่าคงเหลือ');

        // --------------------------------------------------
        // จัดสไตล์หัวตาราง
        // --------------------------------------------------
        $headerRange = 'A1:K2';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        // --------------------------------------------------
        // ข้อมูลเริ่มแถวที่ 3
        // --------------------------------------------------
        $rowIndex = 3;
        foreach ($querys as $r) {
            $sheet->fromArray([
                $r['asset_item'],
                $r['asset_name'],
                $r['asset_type_name'],
                (string)$r['begin_qty'],
                (string)$r['begin_price'],
                (string)$r['qty_in'],
                (string)$r['price_in'],
                (string)$r['total_qty_out'],
                (string)$r['total_price_out'],
                (string)$r['end_qty'],
                (string)$r['end_price'],
            ], NULL, "A{$rowIndex}");

            // ทำตัวเลข (E-L) เป็นตัวหนาและกำหนด NumberFormat
            $sheet->getStyle("E{$rowIndex}:L{$rowIndex}")->getFont()->setBold(true);
            $sheet->getStyle("E{$rowIndex}:L{$rowIndex}")
                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_0);

            $rowIndex++;
        }


        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue("A{$rowIndex}", 'รวมทั้งหมด');
        $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");

        $sheet->setCellValue("D{$rowIndex}", "=SUM(D3:D" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("E{$rowIndex}", "=SUM(E3:E" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("F{$rowIndex}", "=SUM(F3:F" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("G{$rowIndex}", "=SUM(G3:G" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("H{$rowIndex}", "=SUM(H3:H" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("I{$rowIndex}", "=SUM(I3:I" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("J{$rowIndex}", "=SUM(J3:J" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("K{$rowIndex}", "=SUM(K3:K" . ($rowIndex - 1) . ")");
        $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ส่งไฟล์ออกไปยัง Browser

        try {
            $dateStart = $searchModel->date_start;
            $dateEnd = $searchModel->date_end;
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }
        $filename = 'รายงานวัสดุคงคลังวันที่ ' . $dateStart . '-' . $dateEnd . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition', "attachment;filename=\"{$filename}\"");
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    public function actionExportExcel()
    {

        $searchModel = new StockEventSearch();
        $searchModel->search($this->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }
        // ----- Dynamic Filters -----
        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "e.name = 'order'",
            "i.asset_item IS NOT NULL",

        ];

        // ----- Auto GROUP / ORDER -----
        $groupFields = [
            't.code'
        ];
        $groupBy = implode(', ', $groupFields);
        $orderBy = 'CAST(SUBSTRING(t.code, 2) AS UNSIGNED) ASC';

        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // ตั้งชื่อแผ่นงาน
        $sheet->setTitle('สรุปวัสดุคงคลัง');
        // รวมเซลล์
        $sheet->mergeCells('F1:H1');
        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');
        $sheet->mergeCells('F4:H4');
        $sheet->mergeCells('I4:I4');
        // กำหนดความกว้างของคอลัมน์
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(30);

        $rowF1 = 'F1';
        // $sheet->setCellValue($rowF1, 'สรุปงานวัสดุคงคลัง ' . ($warehouse ? $warehouse->warehouse_name : 'ทั้งหมด'));
        $sheet->setCellValue($rowF1, 'สรุปงานวัสดุคงคลัง');
        $sheet->getStyle($rowF1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF2 = 'F2';
        $sheet->setCellValue($rowF2, 'เดือน ');
        $sheet->getStyle($rowF2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF3 = 'F3';
        $sheet->setCellValue($rowF3, 'รายงาน ณ วันที่');
        $sheet->getStyle($rowF3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF3)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG2 = 'G2';
        // $monthName = AppHelper::getMonthName($params['receive_month']);
        // $sheet->setCellValue($rowG2, $monthName);
        $sheet->getStyle($rowG2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG3 = 'G3';
        $showGenDate = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd);
        $sheet->setCellValue($rowG3, $showGenDate);
        $sheet->getStyle($rowG3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG3)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowA = 'A4';
        $sheet->setCellValue($rowA, 'ที่');
        $sheet->getStyle($rowA)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowA)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowA)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowA)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('A5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowA)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowB = 'B4';
        $sheet->setCellValue($rowB, 'รายการ');
        $sheet->getStyle($rowB)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowB)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowB)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowB)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowB)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowC = 'C4';
        $sheet->setCellValue($rowC, 'สินค้าคงเหลือ');
        $sheet->getStyle($rowC)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowC)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowC)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowC)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('C5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('C5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowC)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowD = 'D4';
        $sheet->setCellValue($rowD, 'ซื้อระหว่างเดือน');
        $sheet->getStyle($rowD)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowD)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowD)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowD)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('D5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowD)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowE = 'E4';
        $sheet->setCellValue($rowE, 'รวม');
        $sheet->getStyle($rowE)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowE)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowE)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowE)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('E5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('E5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowD)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF = 'F4';
        $sheet->setCellValue($rowF, 'สินค้าที่ใช้ไป');
        $sheet->getStyle($rowF)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowF)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowF)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF5 = 'F5';
        $sheet->setCellValue($rowF5, 'จ่ายส่วนของ รพ.สต.');
        $sheet->getStyle($rowF5)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF5)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF5)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowF5)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowF5)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG = 'G5';
        $sheet->setCellValue($rowG, 'จ่ายส่วนของโรงพยาบาล');
        $sheet->getStyle($rowG)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowG)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('G4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('G4')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowG)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowH = 'H5';
        $sheet->setCellValue($rowH, 'รวม');
        $sheet->getStyle($rowH)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowH)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowH)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowH)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('H4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('H4')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowH)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowI = 'I4';
        $sheet->setCellValue($rowI, 'สินค้าคงเหลือ');
        $sheet->getStyle($rowI)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowI)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowI)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowI)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('I5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('I5')->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle('I5')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $StartRow = 6;
        $number = 1;
        foreach ($querys as $value) {
            $numRow = $StartRow++;
            // $total =  $value['balance_after'];
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $number++);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

            $sheet->setCellValue('B' . $numRow, $value['asset_type_name']);
            $sheet->getStyle('B' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('B' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('B' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('B' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

            $sheet->setCellValue('C' . $numRow, ($value['begin_price']));
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('C' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            // $sheet->setCellValue('D' . $numRow, $value['sum_month']);
            $sheet->setCellValue('D' . $numRow, $value['price_in']);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('E' . $numRow, ($value['begin_price'] + $value['price_in']) ?? 0);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('E' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('F' . $numRow, $value['branch_price_out']);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('F' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('F' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('G' . $numRow, $value['price_out']);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('G' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('H' . $numRow, ($value['branch_price_out'] + $value['price_out'] ?? 0));
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('H' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('I' . $numRow, $value['end_price']);
            $sheet->getStyle('I' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('I' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('I' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('I' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        }

        // หาผลรวม
        $rowSum = 'A' . $StartRow;
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

        $rowSum = 'B' . $StartRow;
        $sheet->setCellValue($rowSum, 'รวม');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

        $rowSum = 'C' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,C6:C' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'D' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,D6:D' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'E' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,E6:E' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'F' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,F6:F' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'G' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,G6:G' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'H' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,H6:H' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'I' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUBTOTAL(9,I6:I' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $endRow = $StartRow;
        $sheet->getStyle('C2:C' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('D2:D' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('E2:E' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('F2:F' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('G2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('H2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('I2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        // // เพิ่มแผ่นงานที่สอง
        $sheet2 = $spreadsheet->createSheet();  // สร้างแผ่นงานใหม่
        $sheet2->setTitle('สรุปรายการ');  // ตั้งชื่อแผ่นงานที่สอง

        // Header
        $sheet2->setCellValue('A1', 'วดป.ที่รายงาน');
        $sheet2->setCellValue('A2', 'ที่');
        $sheet2->setCellValue('B1', AppHelper::convertToThai(date('Y-m-d')));
        $sheet2->setCellValue('B2', 'รหัส');
        $sheet2->setCellValue('C2', 'รายการสินค้า');
        $sheet2->setCellValue('D2', 'ประเภท');
        $sheet2->setCellValue('E2', 'หน่วย');
        $sheet2->setCellValue('F2', 'จำนวนคงเหลือ');
        $sheet2->setCellValue('G2', 'มูลค่าคงเหลือ');
        $sheet2->setCellValue('H2', 'จำนวนรับใหม่');
        $sheet2->setCellValue('I2', 'มูลค่ารับใหม่');
        $sheet2->setCellValue('J2', 'จำนวนจ่ายใหม่');
        $sheet2->setCellValue('K2', 'มูลค่าจ่ายใหม่');
        $sheet2->setCellValue('L2', 'จำนวนคงเหลือ');
        $sheet2->setCellValue('M2', 'มูลค่าคงเหลือ');

        // ปรับความกว้างคอลัมน์
        $sheet2->getColumnDimension('A')->setWidth(12);
        $sheet2->getColumnDimension('B')->setWidth(10);
        $sheet2->getColumnDimension('C')->setWidth(40);
        $sheet2->getColumnDimension('D')->setWidth(25);
        $sheet2->getColumnDimension('E')->setWidth(9);
        $sheet2->getColumnDimension('F')->setWidth(13);
        $sheet2->getColumnDimension('G')->setWidth(13);
        $sheet2->getColumnDimension('H')->setWidth(13);
        $sheet2->getColumnDimension('I')->setWidth(13);
        $sheet2->getColumnDimension('J')->setWidth(13);
        $sheet2->getColumnDimension('K')->setWidth(13);
        $sheet2->getColumnDimension('L')->setWidth(13);
        $sheet2->getColumnDimension('M')->setWidth(13);

        $StartRowSheet2 = 3;

        $params2 = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];

        $conditions2 = [
            "a.name = 'asset_item'",
            "a.group_id = 4",
        ];

        // ----- Auto GROUP / ORDER -----
        $groupFields2 = [
            'a.code'
        ];
        $groupBy2 = implode(', ', $groupFields2);
        $orderBy2 = 'CAST(SUBSTRING_INDEX(a.code, \'-\', 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(a.code, \'-\', -1) AS UNSIGNED), 
        CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED) limit 99999999';


        list($sql2, $params) = StockEvent::buildStockAssetItemSql(
            $conditions2,
            $params2,
            $groupBy2 ?? null,
            $orderBy2 ?? null
        );

        $querys2 = Yii::$app->db->createCommand($sql2, $params2)->queryAll();

        foreach ($querys2 as $key => $value) {
            $numRow = $StartRowSheet2++;
            $sheet2->setCellValue('A' . $numRow, $numRow);
            $sheet2->setCellValue('B' . $numRow, $value['asset_item']);
            $sheet2->setCellValue('C' . $numRow, $value['asset_name']);
            $sheet2->setCellValue('D' . $numRow, $value['asset_type_name']);
            $sheet2->setCellValue('E' . $numRow, $value['unit']);
            $sheet2->setCellValue('F' . $numRow, $value['begin_qty']);
            $sheet2->setCellValue('G' . $numRow, $value['begin_price']);
            $sheet2->setCellValue('H' . $numRow, $value['qty_in']);
            $sheet2->setCellValue('I' . $numRow, $value['price_in']);
            $sheet2->setCellValue('J' . $numRow, $value['total_qty_out']);
            $sheet2->setCellValue('K' . $numRow, $value['total_price_out']);
            $sheet2->setCellValue('L' . $numRow, $value['end_qty']);
            $sheet2->setCellValue('M' . $numRow, $value['end_price']);
        }

        // เปิด AutoFilter
        $sheet2->setAutoFilter("A2:M" . ($StartRowSheet2));

        // ตั้งค่ารูปแบบ
        $setHeader = 'A1:Z3000';
        $sheet2->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16);
        $sheet2->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet2->getStyle('A1:M2')->getFont()->setBold(true);

        // SUBTOTAL แถวบน
        $sheet2->setCellValue('G1', '=SUBTOTAL(9,G3:G' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('G1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('H1', '=SUBTOTAL(9,H3:H' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('H1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('I1', '=SUBTOTAL(9,I3:I' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('I1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('J1', '=SUBTOTAL(9,J3:J' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('J1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('K1', '=SUBTOTAL(9,K3:K' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('K1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('L1', '=SUBTOTAL(9,L3:L' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('L1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('M1', '=SUBTOTAL(9,M3:M' . (count($querys2) + 2) . ')');
        $sheet2->getStyle('M1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        // กำหนด alignment เพิ่มเติม (เฉพาะที่จำเป็น)
        $rowsheet2B = 'B3:B' . (count($querys2) + 2);
        $sheet2->getStyle($rowsheet2B)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2B)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowsheet2C = 'C3:C' . (count($querys2) + 2);
        $sheet2->getStyle($rowsheet2C)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2C)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowsheet2D = 'D3:D' . (count($querys2) + 2);
        $sheet2->getStyle($rowsheet2D)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2D)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ... (ส่วนจัด alignment คอลัมน์ F–M สามารถคงไว้ได้เหมือนเดิม)

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/myStock.xlsx';
        $writer->save($filePath);


        // if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
        //     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        // }

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }


    //รายงานจัดซื้อรับสินค้า
    public function actionPurchaseReceive()
    {
        return $this->render('purchase_receive');
    }
}
