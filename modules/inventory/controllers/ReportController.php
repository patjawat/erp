<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use app\components\AppHelper;
use yii\data\ArrayDataProvider;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\modules\inventory\models\StockSummary;
// Microsoft Excel
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\StockSummarySearch;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockEventSearch([
            'thai_year' => AppHelper::YearBudget(),
            'receive_month' => date('m')
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->groupBy('type_code');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportExcel()
    {
        $params = Yii::$app->request->queryParams;
        $warehouse = Warehouse::findOne($params['warehouse_id']);
        $datas = $this->findModel($params);
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
        $sheet->setCellValue($rowF1, 'สรุปงานวัสดุคงคลัง ' . ($warehouse ? $warehouse->warehouse_name : 'ทั้งหมด'));
        $sheet->getStyle($rowF1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF2 = 'F2';
        $sheet->setCellValue($rowF2, 'เดือน');
        $sheet->getStyle($rowF2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowF3 = 'F3';
        $sheet->setCellValue($rowF3, 'รายงาน ณ วันที่');
        $sheet->getStyle($rowF3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF3)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG2 = 'G2';
        $monthName = AppHelper::getMonthName($params['receive_month']);
        $sheet->setCellValue($rowG2, $monthName);
        $sheet->getStyle($rowG2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowG3 = 'G3';
        $showGenDate = AppHelper::convertToThai(date('Y-m-d'));
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

        // $a = [];
        foreach ($datas as $key => $value) {
            $numRow = $StartRow++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $numRow);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

            $sheet->setCellValue('B' . $numRow, $value['asset_type']);
            $sheet->getStyle('B' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('B' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('B' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('B' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);

            $sheet->setCellValue('C' . $numRow, $value['sum_last']);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('C' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('D' . $numRow, $value['sum_po']);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('E' . $numRow, ($value['sum_last'] + $value['sum_po']));
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('E' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('F' . $numRow, $value['sum_branch']);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('F' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('F' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('G' . $numRow, $value['sum_sub']);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('G' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('H' . $numRow, ($value['sum_branch'] + $value['sum_sub']));
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('H' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('I' . $numRow, $value['total']);
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
        $sheet->setCellValue($rowSum, '=SUM(C6:C' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'D' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(D6:D' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'E' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(E6:E' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'F' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(F6:F' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'G' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(G6:G' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'H' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(H6:H' . ($StartRow - 1) . ')');
        $sheet->getStyle($rowSum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($rowSum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($rowSum)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($rowSum)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowSum = 'I' . $StartRow;
        $sheet->setCellValue($rowSum, '=SUM(I6:I' . ($StartRow - 1) . ')');
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

        // เพิ่มแผ่นงานที่สอง
        $sheet2 = $spreadsheet->createSheet();  // สร้างแผ่นงานใหม่
        $sheet2->setTitle('สรุปรายการ');  // ตั้งชื่อแผ่นงานที่สอง
        $sheet2->setCellValue('A1', 'วดป.ที่รายงาน');  
        $sheet2->setCellValue('A2', 'ที่');  
        $sheet2->setCellValue('B1', AppHelper::convertToThai(date('Y-m-d')));  
        $sheet2->setCellValue('B2', 'คลัง');  
        $sheet2->setCellValue('C2', 'รหัส');  
        $sheet2->setCellValue('D2', 'รายการสินค้า');  
        $sheet2->setCellValue('E2', 'ประเภท');  
        $sheet2->setCellValue('F2', 'หน่วย');  
        $sheet2->setCellValue('G2', 'จำนวนคงเหลือ');  
        $sheet2->setCellValue('H2', 'มูลค่าคงเหลือ');  
        $sheet2->setCellValue('I2', 'จำนวนรับใหม่');  
        $sheet2->setCellValue('J2', 'มูลค่ารับใหม่');  
        $sheet2->setCellValue('K2', 'จำนวนจ่ายใหม่');  
        $sheet2->setCellValue('L2', 'มูลค่าจ่ายใหม่');  
        $sheet2->setCellValue('M2', 'จำนวนคงเหลือ');  
        $sheet2->setCellValue('N2', 'มูลค่าคงเหลือ');  

        $sheet2->getColumnDimension('A')->setWidth(12);
        $sheet2->getColumnDimension('B')->setWidth(20);
        $sheet2->getColumnDimension('C')->setWidth(10);
        $sheet2->getColumnDimension('D')->setWidth(40);
        $sheet2->getColumnDimension('E')->setWidth(25);
        $sheet2->getColumnDimension('F')->setWidth(9);
        $sheet2->getColumnDimension('G')->setWidth(13);
        $sheet2->getColumnDimension('H')->setWidth(13);
        $sheet2->getColumnDimension('I')->setWidth(13);
        $sheet2->getColumnDimension('J')->setWidth(13);
        $sheet2->getColumnDimension('K')->setWidth(13);
        $sheet2->getColumnDimension('L')->setWidth(13);
        $sheet2->getColumnDimension('M')->setWidth(13);
        $sheet2->getColumnDimension('N')->setWidth(13);

        
        $StartRowSheet2 = 3;
        $dataItems = $this->findModelItem($params);
        foreach ($dataItems as $key => $value) {
            $numRow = $StartRowSheet2++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet2->setCellValue('A' . $numRow, $numRow);
            
            $sheet2->setCellValue('B' . $numRow, $value['warehouse_name']);

            $sheet2->setCellValue('C' . $numRow, $value['asset_item']);

            $sheet2->setCellValue('D' . $numRow, $value['asset_name']);

            $sheet2->setCellValue('E' . $numRow, $value['asset_type']);
            $sheet2->setCellValue('F' . $numRow, $value['unit']);
            $sheet2->setCellValue('G' . $numRow, $value['qty_last']);
            $sheet2->setCellValue('H' . $numRow, ($value['sum_last']));
            $sheet2->setCellValue('I' . $numRow, $value['qty_po']);
            $sheet2->setCellValue('J' . $numRow, ($value['sum_po']));
            $sheet2->setCellValue('K' . $numRow, $value['qty_sub']);
            $sheet2->setCellValue('L' . $numRow, ($value['sum_sub']));
            $sheet2->setCellValue('M' . $numRow, (($value['qty_last'] - $value['qty_sub'])));
            $sheet2->setCellValue('N' . $numRow, ($value['sum_last'] + ($value['sum_po'] - $value['sum_sub'])));
            
        }

        // set font style ตั้งค่า font
        $setHeader = 'A1:Z3000';
        $sheet2->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
        $sheet2->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet2->getStyle('A1:N2')->getFont()->setBold(true)->setItalic(false);


        $sheet2->setCellValue('H1','=SUM(H3:H' .(count($dataItems)+2). ')');
        $sheet2->getStyle('H1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('J1','=SUM(J3:J' .(count($dataItems)+2). ')');
        $sheet2->getStyle('J1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('L1','=SUM(L3:L' .(count($dataItems)+2). ')');
        $sheet2->getStyle('L1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('N1','=SUM(N3:N' .(count($dataItems)+2). ')');
        $sheet2->getStyle('N1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        
        $rowsheet2B = 'B3:B'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2B)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2B)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2B)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2B)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));

        $rowsheet2D = 'D3:D'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2D)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2D)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2D)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2D)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        
        $rowsheet2E = 'E3:E'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2E)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2E)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2E)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2E)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        
        
        $rowsheet2G = 'G3:G'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2G)->getFont()->setBold(true)->setItalic(false);


        
        $rowsheet2H = 'H3:H'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2H)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2H)->getFont()->setBold(true)->setItalic(false);
        
        $rowsheet2I = 'I3:I'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2I)->getFont()->setBold(true)->setItalic(false);


        
        $rowsheet2J = 'J3:J'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2J)->getFont()->setBold(true)->setItalic(false);
        $sheet2->getStyle($rowsheet2J)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2J)->getFont()->setBold(true)->setItalic(false);
        
        $rowsheet2K = 'K3:K'.count($dataItems);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2K)->getFont()->setBold(true)->setItalic(false);


        $rowsheet2L = 'L3:L'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2L)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2L)->getFont()->setBold(true)->setItalic(false);
        
        $rowsheet2M = 'M3:M'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2M)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2N = 'N3:N'.(count($dataItems)+2);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2N)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2N)->getFont()->setBold(true)->setItalic(false);
        
       
        // $this->Sheet2($spreadsheet,$params);

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/myStock.xlsx';
        $writer->save($filePath);  // สร้าง excel

        // if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
        //     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        // }

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException("The file does not exist.");
        }
        

    }


     protected function findModel($params)
    {
        //ถ้ามีการเลือกคลัง
        if(isset($params['warehouse_id'])){
     
        $sql = "SELECT x.*,((x.sum_last + x.sum_po) - (x.sum_branch - x.sum_sub)) as total FROM(SELECT *,
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND  warehouse_id = :warehouse_id AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND  warehouse_id = :warehouse_id  AND stock_month < :receive_month AND thai_year = :thai_year) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END)  AS sum_last,
    
                    SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND  warehouse_id = :warehouse_id AND stock_month = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success'  AND  warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success' AND  warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM view_stock_transaction GROUP BY category_id) as x ";

        return Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $params['warehouse_id'],
            ':receive_month' => $params['receive_month'],
            ':thai_year' => $params['thai_year'],
        ])->queryAll();
               
    }else{
        // ถ้าไม่เลือกคลังให้แสดงทั้งหมด
        $sql = "SELECT x.*,((x.sum_last + x.sum_po) - (x.sum_branch - x.sum_sub)) as total FROM(SELECT *,
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success'  AND stock_month < :receive_month AND thai_year = :thai_year) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END)  AS sum_last,
    
                    SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND stock_month = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success' AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success'  AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM view_stock_transaction GROUP BY category_id) as x ";

        return Yii::$app->db->createCommand($sql, [
            ':receive_month' => $params['receive_month'],
            ':thai_year' => $params['thai_year'],
        ])->queryAll();
    }
    }

    protected function findModelItem($params)
    {
 
          //ถ้ามีการเลือกคลัง
          if(isset($params['warehouse_id'])){
        $sql = "SELECT x.* FROM(SELECT *,
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = :warehouse_id AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                            THEN (qty) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = :warehouse_id  AND stock_month < :receive_month AND thai_year = :thai_year) 
                            THEN (qty) 
                            ELSE 0 
                        END)  AS qty_last,

                         (
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = :warehouse_id AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                            THEN (unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = :warehouse_id  AND stock_month < :receive_month AND thai_year = :thai_year) 
                            THEN (unit_price) 
                            ELSE 0 
                        END)
                ) AS unit_price_last,

                         (
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = :warehouse_id AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = :warehouse_id  AND stock_month < :receive_month AND thai_year = :thai_year) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END)
                ) AS sum_last,
                
                -- Sum of Purchase Orders (PO) where PO number is not NULL
                    SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = :warehouse_id AND stock_month = :receive_month AND thai_year = :thai_year) 
                        THEN (qty) 
                        ELSE 0 
                    END
                ) AS qty_po,
                          SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = :warehouse_id AND stock_month = :receive_month AND thai_year = :thai_year) 
                        THEN (unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_po,
                          SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = :warehouse_id AND stock_month = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,    
                -- Calculate total for 'IN' transactions in sub-warehouse
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB','BRANCH') AND order_status = 'success' AND warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty) 
                        ELSE 0 
                    END
                ) AS qty_sub,
                          SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB','BRANCH') AND order_status = 'success' AND warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_sub,
                          SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success' AND warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM view_stock_transaction GROUP BY asset_item) as x";

        return Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $params['warehouse_id'],
            ':receive_month' => $params['receive_month'],
            ':thai_year' => $params['thai_year'],
        ])->queryAll();
          }else{
            $sql = "SELECT x.* FROM(SELECT *,
            SUM(CASE 
                    WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success'  AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                    THEN (qty) 
                    ELSE 0 
                END) 
            - SUM(CASE 
                    WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success'  AND stock_month < :receive_month AND thai_year = :thai_year) 
                    THEN (qty) 
                    ELSE 0 
                END)  AS qty_last,

                 (
            SUM(CASE 
                    WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success'  AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                    THEN (unit_price) 
                    ELSE 0 
                END) 
            - SUM(CASE 
                    WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success'   AND stock_month < :receive_month AND thai_year = :thai_year) 
                    THEN (unit_price) 
                    ELSE 0 
                END)
        ) AS unit_price_last,

                 (
            SUM(CASE 
                    WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success'  AND stock_month < :receive_month AND thai_year = (:thai_year -1)) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END) 
            - SUM(CASE 
                    WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success'   AND stock_month < :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END)
        ) AS sum_last,
        
        -- Sum of Purchase Orders (PO) where PO number is not NULL
            SUM(
            CASE 
                WHEN (po_number IS NOT NULL AND stock_month = :receive_month AND thai_year = :thai_year) 
                THEN (qty) 
                ELSE 0 
            END
        ) AS qty_po,
                  SUM(
            CASE 
                WHEN (po_number IS NOT NULL  AND stock_month = :receive_month AND thai_year = :thai_year) 
                THEN (unit_price) 
                ELSE 0 
            END
        ) AS unit_price_po,
                  SUM(
            CASE 
                WHEN (po_number IS NOT NULL  AND stock_month = :receive_month AND thai_year = :thai_year) 
                THEN (qty * unit_price) 
                ELSE 0 
            END
        ) AS sum_po,    
        -- Calculate total for 'IN' transactions in sub-warehouse
        SUM(
            CASE 
                WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB','BRANCH') AND order_status = 'success'  AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                THEN (qty) 
                ELSE 0 
            END
        ) AS qty_sub,
                  SUM(
            CASE 
                WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB','BRANCH') AND order_status = 'success'  AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                THEN (unit_price) 
                ELSE 0 
            END
        ) AS unit_price_sub,
                  SUM(
            CASE 
                WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success'  AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                THEN (qty * unit_price) 
                ELSE 0 
            END
        ) AS sum_sub

    FROM view_stock_transaction GROUP BY asset_item) as x";

return Yii::$app->db->createCommand($sql, [
    ':receive_month' => $params['receive_month'],
    ':thai_year' => $params['thai_year'],
])->queryAll();
          }

    }
}
