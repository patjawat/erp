<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Microsoft Excel
use PhpOffice\PhpSpreadsheet\Style\Border;
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
        //หาผลรวม
        list($sqlSummary, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            null,
            null
        );
        $sum = Yii::$app->db->createCommand($sqlSummary, $params)->queryOne();
        return $this->render('index', [
            'querys' => $querys,
            'sum' => $sum,
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
        $qWarehouseType = $searchModel->q_warehouse_type;
        if (!empty($qWarehouseType)) {
            $conditions[] = "wo.warehouse_type = :warehouse_type";
            $params[':warehouse_type'] = $qWarehouseType;
        }


        $where = implode(' AND ', $conditions);
        $sql = "SELECT
            v.code as vendor_id,
            v.title as vendor_name,
            wo.warehouse_name as form_warehouse_name,
            wo.warehouse_type as form_warehouse_type,
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

        // ฟังก์ชันสำหรับตัดทศนิยม 2 ตำแหน่งแบบไม่ปัดเศษ
        $trunc2 = function ($v) {
            return floor(($v ?? 0) * 100) / 100;
        };

        // Default font
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

        foreach ($querys as $value) {
            $numRow = $StartRow++;

            // แปลงประเภทคลัง
            switch ($value['warehouse_type']) {
                case 'MAIN':
                    $warehouseTypeText = 'คลังหลัก';
                    break;
                case 'SUB':
                    $warehouseTypeText = 'คลังย่อย';
                    break;
                case 'BRANCH':
                    $warehouseTypeText = 'คลังรพ.สต.';
                    break;
                default:
                    $warehouseTypeText = '';
            }

            // แปลงประเภทความเคลื่อนไหว
            if ($value['transaction_type'] == 'IN') {
                $sourceName = $value['vendor_name'];
                $transactionName = 'รับ';
            } else {
                $sourceName = $value['form_warehouse_name'];
                $transactionName = 'จ่าย';
            }

            // ✔ ตัดทศนิยมราคา
            $unitPrice = $value['unit_price'] ?? 0;
            $totalPrice = $value['end_price'] ?? 0;

            // ✔ ใส่ข้อมูลเป็น Number (ไม่ใช่ตัวหนังสือ)
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

            // ✔ คอลัมน์ราคาเป็นตัวเลขจริง พร้อม format 2 ตำแหน่ง
            $sheet->setCellValue('M' . $numRow, $unitPrice);
            $sheet->getStyle('M' . $numRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->setCellValue('N' . $numRow, $totalPrice);
            $sheet->getStyle('N' . $numRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // จัดตำแหน่งคอลัมน์
        $lastRow = $StartRow - 1;

        $sheet->getStyle("F2:H{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("L2:N{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ความกว้าง
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
        // Export File
        // --------------------------------------------------
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
            "a.group_id = 'MATER'",
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
        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );


        //หาผลรวม
        list($sqlSummary, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            null,
            null
        );
        $sum = Yii::$app->db->createCommand($sqlSummary, $params)->queryOne();

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
        $sheet->mergeCells('I4:I5'); // แก้ไขเป็น I4:I5 เพื่อรวมเซลล์เหมือนคอลัมน์อื่นๆ
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

        // ------------------------------------------------------------
        // 1) ตั้ง Format สำหรับคอลัมน์ตัวเลขให้เป็น 0.00000 (สำหรับคอลัมน์ทั้งหมด)
        // ------------------------------------------------------------
        $numberColumns = ['C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($numberColumns as $col) {
            // ✅ ใช้ '0.00000' สำหรับ 5 ตำแหน่ง
            $sheet->getStyle($col . '6:' . $col . (count($querys) + 6))->getNumberFormat()->setFormatCode('0.00000');
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ส่วนหัวรายงาน
        $rowF1 = 'F1';
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
        $sheet->getStyle($rowG2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG3 = 'G3';
        $showGenDate = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd);
        $sheet->setCellValue($rowG3, $showGenDate);
        $sheet->getStyle($rowG3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG3)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        // ส่วนหัวตาราง (A4:I5)
        $sheet->setCellValue('A4', 'ที่');
        $sheet->setCellValue('B4', 'รายการ');
        $sheet->setCellValue('C4', 'สินค้าคงเหลือ');
        $sheet->setCellValue('D4', 'ซื้อระหว่างเดือน');
        $sheet->setCellValue('E4', 'รวม');
        $sheet->setCellValue('F4', 'สินค้าที่ใช้ไป');
        $sheet->setCellValue('I4', 'สินค้าคงเหลือ'); // รวมเซลล์แล้ว
        $sheet->setCellValue('A5', ''); // A4:A5 รวมแล้ว
        $sheet->setCellValue('B5', ''); // B4:B5 รวมแล้ว
        $sheet->setCellValue('C5', ''); // C4:C5 รวมแล้ว
        $sheet->setCellValue('D5', ''); // D4:D5 รวมแล้ว
        $sheet->setCellValue('E5', ''); // E4:E5 รวมแล้ว
        $sheet->setCellValue('F5', 'จ่ายส่วนของ รพ.สต.');
        $sheet->setCellValue('G5', 'จ่ายส่วนของโรงพยาบาล');
        $sheet->setCellValue('H5', 'รวม');
        $sheet->setCellValue('I5', ''); // I4:I5 รวมแล้ว

        // จัดรูปแบบและเส้นขอบส่วนหัวตาราง
        $headerRange = 'A4:I5';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($headerRange)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);


        $StartRow = 6;
        $number = 1;

        // ส่วนแสดงข้อมูลแต่ละรายการ
        foreach ($querys as $value) {
            $numRow = $StartRow++;
            //สินค้าคงเหลือ
            $begin   = $value['begin_price'] ?? 0;
            //ซื้อระหว่างเดือน
            $in      = $value['price_in'] ?? 0;
            //สินค้าคงเหลือ+ซื้อระหว่างเดือน
            $totalPriceBegin = $value['total_price_begin'] ?? 0;
            $branch  = $value['branch_price_out'] ?? 0;
            $sub     = $value['price_out'] ?? 0;
            // จ่ายส่วนของ รพ.สต.+จ่ายส่วนของโรงพยาบาล
            $totalPriceOut = $value['total_price_out'] ?? 0;
            //สินค้าคงเหลือ
            $endPrice     = $value['end_price'] ?? 0;

            $sheet->setCellValue('A' . $numRow, $number++);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $numRow, $value['asset_type_name']);
            $sheet->getStyle('B' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // สินค้าคงเหลือ (C)
            $sheet->setCellValue('C' . $numRow, $begin);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // ซื้อระหว่างเดือน (D)
            $sheet->setCellValue('D' . $numRow, $in);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // รวม (E)
            $sheet->setCellValue('E' . $numRow, $totalPriceBegin);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // จ่ายส่วนของ รพ.สต. (F)
            $sheet->setCellValue('F' . $numRow, $branch);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // จ่ายส่วนของโรงพยาบาล (G)
            $sheet->setCellValue('G' . $numRow, $sub);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // รวม (H)
            $sheet->setCellValue('H' . $numRow, $totalPriceOut);
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // สินค้าคงเหลือ (I)
            $sheet->setCellValue('I' . $numRow, $endPrice);
            $sheet->getStyle('I' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // จัดรูปแบบตัวอักษรและเส้นขอบสำหรับแถวข้อมูล
            $dataRowRange = 'A' . $numRow . ':I' . $numRow;
            $sheet->getStyle($dataRowRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($dataRowRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($dataRowRange)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle($dataRowRange)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('C' . $numRow . ':I' . $numRow)->getFont()->setBold(true);

            // ✅ ตรวจสอบให้แน่ใจว่าได้ใช้ '0.00000' สำหรับแถวข้อมูลแต่ละแถว
            $sheet->getStyle('C' . $numRow . ':I' . $numRow)->getNumberFormat()->setFormatCode('0.00000');
        }

        // หาผลรวม
        $rowSum = $StartRow;
        $sumRange = 'A' . $rowSum . ':I' . $rowSum;

        $sheet->setCellValue('B' . $rowSum, 'รวม');
        $sheet->getStyle('B' . $rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('C' . $rowSum, $sum['begin_price'] ?? 0);
        $sheet->setCellValue('D' . $rowSum, $sum['price_in'] ?? 0);
        $sheet->setCellValue('E' . $rowSum, $sum['total_price_begin'] ?? 0);
        $sheet->setCellValue('F' . $rowSum, $sum['branch_price_out'] ?? 0);
        $sheet->setCellValue('G' . $rowSum, $sum['price_out'] ?? 0);
        $sheet->setCellValue('H' . $rowSum, $sum['total_price_out'] ?? 0);
        $sheet->setCellValue('I' . $rowSum, $sum['end_price'] ?? 0);

        // จัดรูปแบบแถวผลรวม
        $sheet->getStyle($sumRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($sumRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($sumRange)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($sumRange)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        // ✅ ตั้งค่าทศนิยม 5 ตำแหน่งสำหรับแถวผลรวม
        $sheet->getStyle('C' . $rowSum . ':I' . $rowSum)->getNumberFormat()->setFormatCode('0.00000');
        $sheet->getStyle('C' . $rowSum . ':I' . $rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);


        // ❌ ลบโค้ดนี้ออก เพราะมันจะเปลี่ยน format เป็น 2 ตำแหน่ง
        /*
        $endRow = $StartRow;
        $sheet->getStyle('C2:C' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('D2:D' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('E2:E' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('F2:F' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('G2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('H2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('I2:I' . $endRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        */

        // --- เริ่มต้นแผ่นงานที่สอง (สรุปรายการ) ---
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
        $querys2 = $this->listQueryByItem($dateStart, $dateEnd);

        foreach ($querys2 as $key => $value) {
            $numRow = $StartRowSheet2++;
            $sheet2->setCellValue('A' . $numRow, $key + 1); // เปลี่ยนจาก $numRow เป็น $key + 1 เพื่อให้เป็นลำดับที่
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
        $dataEndRow = $StartRowSheet2 - 1; // แถวสุดท้ายของข้อมูลจริง

        // เปิด AutoFilter
        $sheet2->setAutoFilter("A2:M" . ($dataEndRow));

        // ตั้งค่ารูปแบบพื้นฐาน
        $fullRangeSheet2 = 'A1:M' . $dataEndRow;
        $sheet2->getStyle($fullRangeSheet2)->getFont()->setName('TH Sarabun New')->setSize(16);
        $sheet2->getStyle($fullRangeSheet2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($fullRangeSheet2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($fullRangeSheet2)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($fullRangeSheet2)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle('A1:M2')->getFill()->getStartColor()->setRGB('8DB4E2'); // ใส่สีพื้นหลังหัวตาราง
        $sheet2->getStyle('A1:M2')->getFont()->setBold(true);

        // ✅ ตั้งค่าทศนิยม 5 ตำแหน่งสำหรับคอลัมน์มูลค่า (G, I, K, M)
        $valueColumnsSheet2 = ['G', 'I', 'K', 'M'];
        foreach ($valueColumnsSheet2 as $col) {
            $range = $col . '3:' . $col . $dataEndRow;
            $sheet2->getStyle($range)->getNumberFormat()->setFormatCode('0.00000');
            $sheet2->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        // หากคอลัมน์จำนวน (F, H, J, L) ต้องเป็นทศนิยม 5 ตำแหน่งด้วย ให้ใช้โค้ดนี้:
        $qtyColumnsSheet2 = ['F', 'H', 'J', 'L'];
        foreach ($qtyColumnsSheet2 as $col) {
            $range = $col . '3:' . $col . $dataEndRow;
            $sheet2->getStyle($range)->getNumberFormat()->setFormatCode('0.00000');
            $sheet2->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // SUBTOTAL แถวบน (Row 1)
        $totalRowFormula = count($querys2) + 2; // แถวสุดท้ายของข้อมูล + 2 (A1)
        
        $sheet2->setCellValue('G1', '=SUBTOTAL(9,G3:G' . $totalRowFormula . ')');
        $sheet2->getStyle('G1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('H1', '=SUBTOTAL(9,H3:H' . $totalRowFormula . ')');
        $sheet2->getStyle('H1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('I1', '=SUBTOTAL(9,I3:I' . $totalRowFormula . ')');
        $sheet2->getStyle('I1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('J1', '=SUBTOTAL(9,J3:J' . $totalRowFormula . ')');
        $sheet2->getStyle('J1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('K1', '=SUBTOTAL(9,K3:K' . $totalRowFormula . ')');
        $sheet2->getStyle('K1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('L1', '=SUBTOTAL(9,L3:L' . $totalRowFormula . ')');
        $sheet2->getStyle('L1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข
        
        $sheet2->setCellValue('M1', '=SUBTOTAL(9,M3:M' . $totalRowFormula . ')');
        $sheet2->getStyle('M1')->getNumberFormat()->setFormatCode('0.00000'); // ✅ แก้ไข

        // กำหนด alignment เพิ่มเติมสำหรับคอลัมน์ข้อความ/รหัส
        $sheet2->getStyle('B3:D' . $dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);


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


    private function listQueryByItem($dateStart, $dateEnd)
    {
        // querys by item ที่ตรงแล้ว
        $sql = "SELECT
    -- 🔹 Fields for Grouping (Item & Type Info)
    t.code AS asset_type_code,
    t.title AS asset_type_name,
    a.code AS asset_item,          -- รหัสรายการทรัพย์สิน
    a.title AS asset_name,
    a.data_json->>'$.unit' AS unit,

    -- 🔹 Aggregated Summary Fields (ยอดรวมที่ต้องการให้คงเดิม)
    -- ยอดยกมา (ก่อน 2025-11-01)
    SUM(
        CASE
            WHEN e.movement_date < :date_start AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN i.qty
            WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN -i.qty
            ELSE 0
        END
    ) AS begin_qty,

    -- ยอดยกมาราคา
    SUM(
        CAST(
            CASE
                WHEN e.movement_date < :date_start AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN (i.qty * i.unit_price)
                WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN - (i.qty * i.unit_price)
                ELSE 0
            END AS DECIMAL(18,10)
        )
    ) AS begin_price,

    -- ยอดรับเข้า
    SUM(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN i.qty ELSE 0 END) AS qty_in,
    SUM(CAST(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END AS DECIMAL(18,10))) AS price_in,

    -- ยอดเบิกออก รพ. (SUB)
    SUM(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB' THEN i.qty ELSE 0 END) AS qty_out,
    SUM(CAST(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB' THEN i.qty * i.unit_price ELSE 0 END AS DECIMAL(18,10))) AS price_out,

    -- ยอดเบิกออก รพ.สต. (BRANCH)
    SUM(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH' THEN i.qty ELSE 0 END) AS branch_qty_out,
    SUM(CAST(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH' THEN i.qty * i.unit_price ELSE 0 END AS DECIMAL(18,10))) AS branch_price_out,

    -- ยอดเบิกออก รวม
    SUM(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN i.qty ELSE 0 END) AS total_qty_out,
    SUM(CAST(CASE WHEN e.movement_date BETWEEN :date_start AND :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN i.qty * i.unit_price ELSE 0 END AS DECIMAL(18,10))) AS total_price_out,

    -- ยอดคงเหลือสิ้นงวด
    SUM(CASE WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN i.qty
             WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH') THEN -i.qty ELSE 0 END) AS end_qty,

    SUM(CAST(CASE WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN (i.qty * i.unit_price)
                  WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH') THEN - (i.qty * i.unit_price) ELSE 0 END AS DECIMAL(18,10))) AS end_price

FROM categorise a  -- 🚀 ตารางหลักคือรายการทรัพย์สินทั้งหมด
LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
LEFT JOIN stock_events i ON i.asset_item = a.code AND i.name = 'order_item'
LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
LEFT JOIN (
    SELECT code, title, ROW_NUMBER() OVER(PARTITION BY code ORDER BY code) AS rn
    FROM categorise
    WHERE name = 'vendor'
) v ON v.code = e.vendor_id AND v.rn = 1
WHERE a.name = 'asset_item'
-- AND a.group_id = 'MATER' -- เปิดใช้งานหากต้องการกรองกลุ่มสินค้า
GROUP BY
    a.code,
    a.title,
    t.code,
    t.title,
    a.data_json->>'$.unit'
ORDER BY
    CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED),
    CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED);";


        $query = Yii::$app->db->createCommand($sql, [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ])->queryAll();

        return $query;
    }
}
