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



    //รายงานแบบแยกรายตัว
    // public function actionListByOrder()
    // {
    //     $searchModel = new StockEventSearch([
    //         'name' => 'order_item', // กรองเฉพาะรายการที่เป็น item
    //     ]);

    //     $searchModel->load(Yii::$app->request->queryParams);
    //     try {
    //         $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
    //         $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
    //     } catch (\Throwable $th) {
    //         $dateStart = $dateEnd = '';
    //     }

    //     // ----- Dynamic Filters -----
    //     // เพิ่มเงื่อนไขการค้นหาจาดคลัง
    //     $warehouseId = $searchModel->q_warehouse_id;
    //     if (!empty($warehouseId)) {
    //         $conditions[] = "e.warehouse_id = :warehouse_id";
    //         $params[':warehouse_id'] = $warehouseId;
    //     }
    //     // เพิ่มเงื่อนไขการค้นหาจาการรับจ่าย
    //     $transactionType = $searchModel->transaction_type;
    //     if (!empty($transactionType)) {
    //         $conditions[] = "e.transaction_type = :transaction_type";
    //         $params[':transaction_type'] = $transactionType;
    //     }

    //     // เพิ่มเงื่อนไขการค้นหาจากประเภท
    //     $assetTypeId = $searchModel->q_asset_type;
    //     if (!empty($assetTypeId)) {
    //         $conditions[] = "t.code = :asset_type_id";
    //         $params[':asset_type_id'] = $assetTypeId;
    //     }
    //     // ----- Auto GROUP / ORDER -----
    //     $params = [
    //         ':date_start' => $dateStart,
    //         ':date_end' => $dateEnd,
    //     ];
    //     $conditions = [
    //         "e.name = 'order'",
    //         "w.warehouse_type = 'MAIN'",
    //         "i.asset_item IS NOT NULL",
    //         "e.order_status = 'success'",
    //         "i.order_status = 'success'",
    //     ];
    //     // ----- Auto GROUP / ORDER -----
    //     $groupFields = [
    //         'i.id'
    //     ];
    //     $groupBy = implode(', ', $groupFields);
    //     $orderBy = 'i.id,e.movement_date ASC';

    //     list($sql, $params) = StockEvent::buildStockOrderSql(
    //         $conditions,
    //         $params,
    //         $groupBy ?? null,
    //         $orderBy ?? null
    //     );

    //     $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

    //     return $this->render('list_by_order', [
    //         'searchModel' => $searchModel,
    //         'querys' => $querys,
    //     ]);
    // }


    public function actionListByOrder()
    {
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
            $conditions[] = "e.transaction_type = :transaction_type";
            $params[':transaction_type'] = $transactionType;
        }

        $assetTypeId = $searchModel->q_asset_type;
        if (!empty($assetTypeId)) {
            // ตรวจเช็คว่าคุณจะกรองด้วย t.code หรือ t.id ให้ตรงกับฐานข้อมูลของคุณ
            $conditions[] = "t.code = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }

        // ----- Group / Order -----
        $groupFields = ['i.id'];
        $groupBy = implode(', ', $groupFields);
        $orderBy = 'i.id,e.movement_date ASC';

        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        // debug: ถ้ายังไม่เห็นผล ให้ log ดู SQL และ params
        // \Yii::info($sql . ' -- ' . json_encode($params), __METHOD__);

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return $this->render('list_by_order', [
            'searchModel' => $searchModel,
            'querys' => $querys,
        ]);
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
            "e.order_status = 'success'",
        ];

        $warehouseId = $searchModel->q_warehouse_id;
        if (!empty($warehouseId)) {
            $conditions[] = "e.warehouse_id = :warehouse_id";
            $params[':warehouse_id'] = $warehouseId;
        }

        $assetTypeId = $searchModel->q_asset_type;
        if (!empty($assetTypeId)) {
            // ตรวจเช็คว่าคุณจะกรองด้วย t.code หรือ t.id ให้ตรงกับฐานข้อมูลของคุณ
            $conditions[] = "t.code = :asset_type_id";
            $params[':asset_type_id'] = $assetTypeId;
        }



        list($sql, $params) = StockEvent::buildStockAssetItemSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return $this->render('list_by_item', [
            'querys' => $querys,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    // private function geteportByItem($searchModel)
    // {

    //     try {
    //         $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
    //         $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
    //     } catch (\Throwable $th) {
    //         $dateStart = '';
    //         $dateEnd = '';
    //     }

    //     $q = $searchModel->q;
    //     $warehouseId = $searchModel->warehouse_id;
    //     $assetTypeId = $searchModel->asset_type_id;

    //     // สร้างเงื่อนไข WHERE แบบ dynamic
    //     $where = "e.name = 'order'  AND i.asset_item IS NOT NULL";

    //     // ถ้ามีค่า $q ให้กรอง asset_item หรือ code
    //     if (!empty($q)) {
    //         $where .= " AND (LOWER(a.title) LIKE LOWER(:q) OR LOWER(a.code) LIKE LOWER(:q))";
    //         $params[':q'] = "%{$q}%";
    //     }

    //     if (!empty($warehouseId)) {
    //         $where .= " AND (e.warehouse_id = :warehouse_id)";
    //     }

    //     if (!empty($assetTypeId)) {
    //         $where .= " AND (a.category_id = :asset_type_id)";
    //     }

    //     $sql = "SELECT 
    //             w.warehouse_name,
    //             t.title as asset_type_name,
    //             a.category_id,
    //             i.asset_item,
    //             a.title,
    //                 -- ยอดยกมาก่อนเดือนสิงหาคม (ปริมาณ)
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date < :date_start AND i.transaction_type = 'IN'  THEN i.qty
    //                         WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' THEN -i.qty
    //                         ELSE 0 
    //                     END
    //                 ) AS begin_qty,
    //                 -- ยอดยกมาก่อนเดือนสิงหาคม (มูลค่า)
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date < :date_start AND i.transaction_type = 'IN'  THEN i.qty * i.unit_price
    //                         WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' THEN -i.qty * i.unit_price
    //                         ELSE 0 
    //                     END
    //                 ) AS begin_price,
    //                 -- รับเข้าเดือนสิงหาคม
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date BETWEEN :date_start AND :date_end
    //                             AND i.transaction_type = 'IN' THEN i.qty
    //                         ELSE 0 
    //                     END
    //                 ) AS qty_in,
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date BETWEEN :date_start AND :date_end
    //                             AND i.transaction_type = 'IN' THEN i.qty * i.unit_price
    //                         ELSE 0 
    //                     END
    //                 ) AS price_in,
    //                 -- จ่ายออกเดือนสิงหาคม
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date BETWEEN :date_start AND :date_end
    //                             AND i.transaction_type = 'OUT' THEN i.qty
    //                         ELSE 0 
    //                     END
    //                 ) AS qty_out,
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date BETWEEN :date_start AND :date_end
    //                             AND i.transaction_type = 'OUT' THEN i.qty * i.unit_price
    //                         ELSE 0 
    //                     END
    //                 ) AS price_out,
    //                 -- คงเหลือสิ้นเดือน (ปริมาณ)
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN'  THEN i.qty
    //                         WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' THEN -i.qty
    //                         ELSE 0 
    //                     END
    //                 ) AS end_qty,
    //                 -- คงเหลือสิ้นเดือน (มูลค่า)
    //                 SUM(
    //                     CASE 
    //                         WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN'  THEN i.qty * i.unit_price
    //                         WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' THEN -i.qty * i.unit_price
    //                         ELSE 0 
    //                     END
    //                 ) AS end_price

    //             FROM stock_events e
    //             LEFT JOIN stock_events i 
    //                 ON i.category_id = e.id 
    //             AND i.name = 'order_item'
    //             LEFT JOIN warehouses w ON w.id = e.warehouse_id
    //             LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
    //             LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
    //             WHERE $where
    //             GROUP BY i.asset_item
    //             ORDER BY i.asset_item";

    //     $q = $searchModel->q;

    //     $command = Yii::$app->db->createCommand($sql)
    //         ->bindValue(':date_start', $dateStart)
    //         ->bindValue(':date_end', $dateEnd);

    //     if (!empty($q)) {
    //         $command->bindValue(':q', "%{$q}%");
    //     }

    //     if (!empty($warehouseId)) {
    //         $command->bindValue(':warehouse_id', $warehouseId); // bind :warehouse_id เฉพาะกรณีมีค่า
    //     }

    //     if (!empty($assetTypeId)) {
    //         $command->bindValue(':asset_type_id', $assetTypeId); // bind :warehouse_id เฉพาะกรณีมีค่า
    //     }

    //     return  $command->queryAll();
    // }

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
            "e.order_status = 'success'",
        ];

        $warehouseId = $searchModel->q_warehouse_id;
        if (!empty($warehouseId)) {
            $conditions[] = "e.warehouse_id = :warehouse_id";
            $params[':warehouse_id'] = $warehouseId;
        }

        $assetTypeId = $searchModel->q_asset_type;
        if (!empty($assetTypeId)) {
            // ตรวจเช็คว่าคุณจะกรองด้วย t.code หรือ t.id ให้ตรงกับฐานข้อมูลของคุณ
            $conditions[] = "t.code = :asset_type_id";
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

        // ฟังก์ชันช่วยเปลี่ยน NULL หรือว่างเป็น 0
        $checkNumber = function ($val) {
            // trim ก่อน
            $val = trim((string)$val);
            return 0;

            // ถ้าเป็น '', 'null', NULL, หรือไม่ใช่ตัวเลข ให้เป็น 0
            if ($val === '' || strtolower($val) === 'null' || !is_numeric($val)) {
                return 0;
            }

            return (float)$val;
        };

        // --------------------------------------------------
        // ข้อมูลเริ่มแถวที่ 3
        // --------------------------------------------------
        $rowIndex = 3;
        foreach ($querys as $r) {
            $sheet->fromArray([
                $r['code'],
                $r['title'],
                $r['asset_type_name'],
                (string)$r['begin_qty'],
                (string)$r['begin_price'],
                (string)$r['qty_in'],
                (string)$r['price_in'],
                (string)$r['qty_out'],
                (string)$r['price_out'],
                (string)$r['balance_qty'],
                (string)$r['balance_price'],
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

            $sheet->setCellValue('E' . $numRow, ($value['begin_price']));
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('E' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('F' . $numRow, '0.00');
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

            $sheet->setCellValue('H' . $numRow, ($value['begin_price']));
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('H' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('I' . $numRow, $value['begin_price']);
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
            "e.order_status = 'success'",
        ];

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
            $sheet2->setCellValue('B' . $numRow, $value['code']);
            $sheet2->setCellValue('C' . $numRow, $value['title']);
            $sheet2->setCellValue('D' . $numRow, $value['asset_type_name']);
            $sheet2->setCellValue('E' . $numRow, $value['unit']);
            $sheet2->setCellValue('F' . $numRow, $value['begin_qty']);
            $sheet2->setCellValue('G' . $numRow, $value['begin_price']);
            $sheet2->setCellValue('H' . $numRow, $value['qty_in']);
            $sheet2->setCellValue('I' . $numRow, $value['price_in']);
            $sheet2->setCellValue('J' . $numRow, $value['qty_out']);
            $sheet2->setCellValue('K' . $numRow, $value['price_out']);
            $sheet2->setCellValue('L' . $numRow, $value['balance_qty']);
            $sheet2->setCellValue('M' . $numRow, $value['balance_price']);
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
}
