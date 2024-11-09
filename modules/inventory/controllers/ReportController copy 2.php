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
// Microsoft Excel
use app\modules\inventory\models\StockSummary;
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
        \Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->add('Content-Type', 'text/html; charset=UTF-8');
        $params = Yii::$app->request->queryParams;
        // return $this->findModel($params);
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
        $sheet->setCellValue($rowF1, 'สรุปงานวัสดุคงคลัง ' . $warehouse->warehouse_name);
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

            $sheet->setCellValue('B' . $numRow, $value['title']);
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
        $sheet->getStyle('C2:C' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('D2:D' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('E2:E' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('F2:F' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('G2:I' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('H2:I' . $endRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('I2:I' . $endRow)->getNumberFormat()->setFormatCode('0.00');

        // เพิ่มแผ่นงานที่สอง
       
        $this->Sheet2($spreadsheet,$params);

        $writer = new Xlsx($spreadsheet);
        $output_file = Yii::getAlias('@webroot') . '/myData.xlsx';
        $writer->save($output_file);  // สร้าง excel

        if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        }

        // $exporter->renderCell('A2', 'Overridden serial column header');

        // $exporter->save(Yii::getAlias('@webroot').'/myData.xlsx');
    }


    protected function Sheet2($spreadsheet,$params)
    {
        $sheet = $spreadsheet->createSheet();  // สร้างแผ่นงานใหม่
        $datas = $this->findModelItem($params);

        $sheet->setTitle('แผ่นงานที่สอง');  // ตั้งชื่อแผ่นงานที่สอง
        $sheet->setCellValue('A1', 'วดป.ที่รายงาน');  
        $sheet->setCellValue('B1', 'คลัง');  
        $sheet->setCellValue('C1', 'รหัส');  
        $sheet->setCellValue('D1', 'รายการสินค้า');  
        $sheet->setCellValue('E1', 'ประเภท');  
        $sheet->setCellValue('F1', 'หน่วย');  
        $sheet->setCellValue('G1', 'จำนวนคงเหลือ');  
        $sheet->setCellValue('H1', 'มูลค่าคงเหลือ');  
        $sheet->setCellValue('I1', 'จำนวนรับใหม่');  
        $sheet->setCellValue('J1', 'มูลค่ารับใหม่');  
        $sheet->setCellValue('K1', 'จำนวนจ่ายใหม่');  
        $sheet->setCellValue('L1', 'มูลค่าจ่ายใหม่');  
        $sheet->setCellValue('M1', 'จำนวนคงเหลือ');  
        $sheet->setCellValue('N1', 'มูลค่าคงเหลือ');  

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(14);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(25);
        $sheet->getColumnDimension('M')->setWidth(25);
        $sheet->getColumnDimension('N')->setWidth(25);

        
        $StartRow = 2;
        foreach ($datas as $key => $value) {
            $numRow = $StartRow++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $numRow);
            
            $sheet->setCellValue('B' . $numRow, $value['warehouse_name']);

            $sheet->setCellValue('C' . $numRow, $value['asset_code']);

            $sheet->setCellValue('D' . $numRow, $value['asset_name']);

            $sheet->setCellValue('E' . $numRow, $value['title']);
            $sheet->setCellValue('F' . $numRow, $value['unit_name']);
            $sheet->setCellValue('G' . $numRow, $value['qty']);
            $sheet->setCellValue('H' . $numRow, ($value['qty'] + $value['unit_price']));
            // $sheet->setCellValue('N' . $numRow, ($value['total']));
            
        }
        return $sheet;  
    }

     protected function findModel($params)
    {
        // return $params['warehouse_id'];
        $sql = "WITH t as (SELECT  
            t.title,
                i.category_id,
                so.code,
                w.warehouse_type,
                si.transaction_type,
                si.qty,
                si.unit_price,
                
                -- Extract month from the receive_date in JSON and convert it to integer for stock month
                MONTH(so.data_json->>'\$.receive_date') AS stock_month,
                
                -- Calculate Thai year with adjustment based on receive_date month
                (IF(MONTH(so.data_json->>'\$.receive_date') > 9, 
                    YEAR(so.data_json->>'\$.receive_date') + 1, 
                    YEAR(so.data_json->>'\$.receive_date')
                ) + 543) AS thai_year,
                
                -- Calculate sum for main warehouse 'IN' transactions minus 'OUT' transactions for sub/branch warehouses
                (
                    SUM(CASE 
                            WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END)
                ) AS sum_last,
                
                -- Sum of Purchase Orders (PO) where PO number is not NULL
                    SUM(
                    CASE 
                        WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                -- Calculate total for 'IN' transactions in branch warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                -- Calculate total for 'IN' transactions in sub-warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM 
                stock_events so
                LEFT OUTER JOIN stock_events si 
                    ON si.category_id = so.id AND si.name = 'order_item'
                LEFT OUTER JOIN categorise i 
                    ON i.code = si.asset_item AND i.name = 'asset_item'
                LEFT OUTER JOIN categorise t 
                    ON t.code = i.category_id AND t.name='asset_type'
                LEFT OUTER JOIN warehouses w 
                    ON w.id = si.warehouse_id
            WHERE i.category_id <> ''

            -- Group results by category ID
            GROUP BY 
                i.category_id  
            -- Order results by category ID in ascending order
            ORDER BY 
                i.category_id ASC) SELECT *,((t.sum_last + t.sum_po) - (t.sum_branch - t.sum_sub)) as total FROM t";

        return Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $params['warehouse_id'],
            ':receive_month' => $params['receive_month'],
            ':thai_year' => $params['thai_year'],
        ])->queryAll();
        return $query;

        $data = [];
        foreach ($querys as $key => $value) {
            $data[] = [
                'ที่' => $key + 1,
                'รายการ' => $value['title'],
                'สินค้าคงเหลือ' => $value['sum_last'],
                'ซื้อระหว่างเดือน' => $value['sum_po'],
                'รวม' => ($value['sum_po'] + $value['sum_last']),
                'จ่ายส่วนของ รพ.สต.' => $value['sum_branch'],
                'จ่ายส่วนของโรงพยาบาล' => $value['sum_sub'],
                'รวม2' => ($value['sum_branch'] + $value['sum_sub']),
                'รวมสินค้าคงเหลือ' => $value['total'],
            ];
        }

        return $data;
    }

    protected function findModelItem($params)
    {
        // return $params['warehouse_id'];
        $sql = "WITH t as (SELECT  
          i.code as asset_code,
               i.code asset_item,
           i.title as asset_name,
           i.data_json->>'$.unit' as unit_name,
            w.warehouse_name,
            t.title,
                i.category_id,
                so.code,
                w.warehouse_type,
                si.transaction_type,
                si.qty,
                si.unit_price,
                
                -- Extract month from the receive_date in JSON and convert it to integer for stock month
                MONTH(so.data_json->>'\$.receive_date') AS stock_month,
                
                -- Calculate Thai year with adjustment based on receive_date month
                (IF(MONTH(so.data_json->>'\$.receive_date') > 9, 
                    YEAR(so.data_json->>'\$.receive_date') + 1, 
                    YEAR(so.data_json->>'\$.receive_date')
                ) + 543) AS thai_year,
                
                -- Calculate sum for main warehouse 'IN' transactions minus 'OUT' transactions for sub/branch warehouses
                (
                    SUM(CASE 
                            WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                            THEN (si.qty) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                            THEN (si.qty) 
                            ELSE 0 
                        END)
                ) AS qty_last,

                         (
                    SUM(CASE 
                            WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                            THEN (si.unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                            THEN (si.unit_price) 
                            ELSE 0 
                        END)
                ) AS unit_price_last,

                         (
                    SUM(CASE 
                            WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'\$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END)
                ) AS sum_last,
                
                -- Sum of Purchase Orders (PO) where PO number is not NULL
                    SUM(
                    CASE 
                        WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty) 
                        ELSE 0 
                    END
                ) AS qty_po,
                          SUM(
                    CASE 
                        WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_po,
                          SUM(
                    CASE 
                        WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'\$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                -- Calculate total for 'IN' transactions in branch warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty) 
                        ELSE 0 
                    END
                ) AS qty_branch,
                 SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_branch,
                 SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                -- Calculate total for 'IN' transactions in sub-warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty) 
                        ELSE 0 
                    END
                ) AS qty_sub
                          SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_sub
                          SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM 
                stock_events so
                LEFT OUTER JOIN stock_events si 
                    ON si.category_id = so.id AND si.name = 'order_item'
                LEFT OUTER JOIN categorise i 
                    ON i.code = si.asset_item AND i.name = 'asset_item'
                LEFT OUTER JOIN categorise t 
                    ON t.code = i.category_id AND t.name='asset_type'
                LEFT OUTER JOIN warehouses w 
                    ON w.id = si.warehouse_id
            WHERE i.category_id <> ''

            -- Group results by category ID
            GROUP BY 
                i.code  
            -- Order results by category ID in ascending order
            ORDER BY 
                i.category_id ASC) SELECT *,((t.qty_last + t.qty_po) - (t.qty_branch - t.qty_sub)) as total_item,((t.unit_price_last + t.unit_price_po) - (t.unit_price_branch - t.unit_price_sub)) as total_price FROM t";

        return Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $params['warehouse_id'],
            ':receive_month' => $params['receive_month'],
            ':thai_year' => $params['thai_year'],
        ])->queryAll();
        return $query;

        $data = [];
        foreach ($querys as $key => $value) {
            $data[] = [
                'ที่' => $key + 1,
                'รายการ' => $value['title'],
                'สินค้าคงเหลือ' => $value['sum_last'],
                'ซื้อระหว่างเดือน' => $value['sum_po'],
                'รวม' => ($value['sum_po'] + $value['sum_last']),
                'จ่ายส่วนของ รพ.สต.' => $value['sum_branch'],
                'จ่ายส่วนของโรงพยาบาล' => $value['sum_sub'],
                'รวม2' => ($value['sum_branch'] + $value['sum_sub']),
                'รวมสินค้าคงเหลือ' => $value['total'],
            ];
        }

        return $data;
    }
}
