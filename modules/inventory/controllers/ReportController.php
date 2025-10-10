<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Microsoft Excel
use app\modules\inventory\models\Warehouse;
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
        $dataProvider = $searchModel->search($this->request->queryParams);
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }
        $querys = $this->GroupSummary($searchModel->warehouse_id, $dateStart, $dateEnd);


        return $this->render('index', [
            'querys' => $querys,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'searchModel' => $searchModel,
            // 'dataProvider' => $dataProvider
        ]);
    }

    //รายงานแบบแยกรายตัว
    public function actionListSummary()
    {
        $searchModel = new StockEventSearch([
            'name' => 'order_item', // กรองเฉพาะรายการที่เป็น item
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->alias('e') // stock_events เป็น e
            ->joinWith(['stockOrder order_event']) // alias = order_event
            ->joinWith(['warehouse w']); // alias = order_event

        $dataProvider->query->andFilterWhere(['w.warehouse_type' => 'main']);
        $dataProvider->query->andFilterWhere(['order_event.asset_type_id' => $searchModel->q_asset_type]);
        $dataProvider->query->andFilterWhere(['order_event.warehouse_id' => $searchModel->q_warehouse_id]);
        $dataProvider->query->andFilterWhere(['order_event.code' => $searchModel->q_code]);
        $dataProvider->query->andFilterWhere(['order_event.vendor_id' => $searchModel->q_vendor]);
        $dataProvider->query->andFilterWhere(['order_event.order_status' => 'success']);


        $dataProvider->query->andFilterWhere(['between', 'order_event.movement_date', AppHelper::convertToGregorian($searchModel->date_start), AppHelper::convertToGregorian($searchModel->date_end)]);

        // ถ้า request มี all=true ให้ปิด pagination
        if (Yii::$app->request->get('all') == 1) {
            $dataProvider->pagination = false;
        }


        return $this->render('list_summary', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportExcel()
    {
        // \Yii::$app->response->format = Response::FORMAT_JSON;
        $params = Yii::$app->request->queryParams;
        $dateStart = $params['date_start'];
        $dateEnd = $params['date_end'];
        $warehouse = isset($params['warehouse_id']) ? Warehouse::findOne($params['warehouse_id']) : '';
        $warehouseId = isset($params['warehouse_id']) ? $params['warehouse_id'] : '';
        // return $this->render('test',[
        //     'querys' => $this->ItemSummary($warehouseId,$dateStart,$dateEnd)
        // ]);

        $datas = $this->GroupSummary($warehouseId, $dateStart, $dateEnd);
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
        // $a = [];
        foreach ($datas as $key => $value) {
            $numRow = $StartRow++;
            $total =  $value['balance_after'];
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $numRow);
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

            $sheet->setCellValue('C' . $numRow, ($value['balance_before']));
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('C' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            // $sheet->setCellValue('D' . $numRow, $value['sum_month']);
            $sheet->setCellValue('D' . $numRow, $value['total_in_month']);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('E' . $numRow, ($value['total_before_out']));
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

            $sheet->setCellValue('G' . $numRow, $value['total_out_month']);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('G' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('H' . $numRow, ($value['balance_after']));
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . ($numRow))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('H' . ($numRow))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

            $sheet->setCellValue('I' . $numRow, $value['balance_after']);
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
        // $dataItems = $this->findModelItem($params);
        $dataItems = $this->ItemSummary($warehouseId, $dateStart, $dateEnd);
        foreach ($dataItems as $key => $value) {
            $numRow = $StartRowSheet2++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet2->setCellValue('A' . $numRow, $numRow);

            $sheet2->setCellValue('B' . $numRow, $value['warehouse_name']);

            $sheet2->setCellValue('C' . $numRow, $value['asset_item']);

            $sheet2->setCellValue('D' . $numRow, $value['asset_name']);

            $sheet2->setCellValue('E' . $numRow, $value['asset_type_name']);
            $sheet2->setCellValue('F' . $numRow, $value['unit']);
            $sheet2->setCellValue('G' . $numRow, $value['balance_before_qty']);
            $sheet2->setCellValue('H' . $numRow, $value['balance_before']);
            $sheet2->setCellValue('I' . $numRow, $value['total_in_month_qty']);
            $sheet2->setCellValue('J' . $numRow, $value['total_in_month']);
            $sheet2->setCellValue('K' . $numRow, $value['total_out_month_qty']);
            $sheet2->setCellValue('L' . $numRow, $value['total_out_month']);
            $sheet2->setCellValue('M' . $numRow, ($value['balance_before_qty'] + $value['total_in_month_qty']) - $value['total_out_month_qty']);
            $sheet2->setCellValue('N' . $numRow, $value['balance_after']);

            //  ((last_stock_in + sum_month)-sum_sub) as sum_qty,
        }

        // เปิด AutoFilter
        $sheet2->setAutoFilter("A2:N" . ($StartRowSheet2));
        // set font style ตั้งค่า font
        $setHeader = 'A1:Z3000';
        $sheet2->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
        $sheet2->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet2->getStyle('A1:N2')->getFont()->setBold(true)->setItalic(false);

        $sheet2->setCellValue('H1', '=SUBTOTAL(9,H3:H' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('H1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('I1', '=SUBTOTAL(9,I3:I' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('I1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('J1', '=SUBTOTAL(9,J3:J' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('J1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('K1', '=SUBTOTAL(9,K3:K' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('K1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('L1', '=SUBTOTAL(9,L3:L' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('L1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('M1', '=SUBTOTAL(9,M3:M' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('M1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->setCellValue('N1', '=SUBTOTAL(9,N3:N' . (count($dataItems) + 2) . ')');
        $sheet2->getStyle('N1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $rowsheet2B = 'B3:B' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2B)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2B)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2B)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2B)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));

        $rowsheet2D = 'D3:D' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2D)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2D)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2D)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2D)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));

        $rowsheet2E = 'E3:E' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2E)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle($rowsheet2E)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2E)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2E)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));

        $rowsheet2G = 'G3:G' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2G)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2H = 'H3:H' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2H)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2H)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2I = 'I3:I' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2I)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2J = 'J3:J' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2J)->getFont()->setBold(true)->setItalic(false);
        $sheet2->getStyle($rowsheet2J)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2J)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2K = 'K3:K' . count($dataItems);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2K)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2L = 'L3:L' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2L)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2L)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2M = 'M3:M' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2M)->getFont()->setBold(true)->setItalic(false);

        $rowsheet2N = 'N3:N' . (count($dataItems) + 2);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2N)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet2->getStyle($rowsheet2N)->getFont()->setBold(true)->setItalic(false);

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/myStock.xlsx';
        $writer->save($filePath);  // สร้าง excel

        // if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
        //     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        // }

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }

    protected function GroupSummary($warehouse_id, $dateStart, $dateEnd)
    {

        // ถ้าไม่เลือกคลังให้แสดงทั้งหมด
        $sql = "WITH stock_summary AS (
                            SELECT 
                                
                                asset_type.code AS asset_type_code,
                                asset_type.title AS asset_type_name,

                                -- ยอดยกมา (ก่อนเดือน)
                                SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'IN'  
                                        THEN i.qty * i.unit_price ELSE 0 END) 
                                -
                                SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS balance_before,

                                -- รับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_in_month,

                                -- จ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_out_month

                            FROM stock_events o
                            LEFT JOIN stock_events i ON i.category_id = o.id
                            LEFT JOIN categorise asset_type ON asset_type.code = o.asset_type_id
                            LEFT JOIN warehouses w ON w.id = o.warehouse_id
                            WHERE o.name = 'order'
                            AND o.order_status = 'success'
                            AND i.name = 'order_item'
                            AND w.warehouse_type = 'MAIN'
                            AND asset_type.category_id = 4
                            AND asset_type.name = 'asset_type'
                            GROUP BY asset_type.code, asset_type.title
                        )

                        SELECT 
                            asset_type_code,
                            asset_type_name,
                            balance_before,                     -- 1. ยอดยกมา
                            total_in_month,                     -- 2. รับเข้าระหว่างเดือน
                            (balance_before + total_in_month) AS total_before_out,   -- 3. รวม
                            total_out_month,                    -- 4. จ่ายไประหว่างเดือน
                            (balance_before + total_in_month - total_out_month) AS balance_after  -- 5. ยอดยกไป
                        FROM stock_summary
                        ORDER BY CAST(SUBSTRING(asset_type_code, 2) AS UNSIGNED);
                        ";

        return Yii::$app->db->createCommand($sql, [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ])->queryAll();
    }
    protected function ItemSummary($warehouse_id, $dateStart, $dateEnd)
    {
        // ถ้ามีการเลือกคลัง

        $sql = "WITH stock_summary AS (
                            SELECT 
                                
                                asset_type.code AS asset_type_code,
                                asset_type.title AS asset_type_name,
                                p.title as asset_name,
                                p.code as asset_item,
                                p.data_json->>'$.unit' AS unit,
                                w.warehouse_name as warehouse_name,
	-- จำนวนคงเหลือ (ก่อนเดือน)
            SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'IN'  
                 THEN i.qty ELSE 0 END) 
        -
        SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'OUT' 
                 THEN i.qty  ELSE 0 END) AS balance_before_qty,
                                -- ยอดยกมา (ก่อนเดือน)
                                SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'IN'  
                                        THEN i.qty * i.unit_price ELSE 0 END) 
                                -
                                SUM(CASE WHEN o.movement_date < :date_start AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS balance_before,

                               -- จำนวนรับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty ELSE 0 END) AS total_in_month_qty,

                                -- รับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_in_month,
 -- จำนวนจ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty  ELSE 0 END) AS total_out_month_qty,

                                -- จ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN :date_start AND :date_end 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_out_month

                            FROM stock_events o
                            LEFT JOIN stock_events i ON i.category_id = o.id
                            LEFT JOIN categorise asset_type ON asset_type.code = o.asset_type_id
                            LEFT JOIN warehouses w ON w.id = o.warehouse_id
                            LEFT JOIN categorise p ON p.code = i.asset_item
                            WHERE o.name = 'order'
                            AND o.order_status = 'success'
                            AND i.name = 'order_item'
                            AND w.warehouse_type = 'MAIN'
                            AND asset_type.category_id = 4
                            AND asset_type.name = 'asset_type'
                            AND p.name = 'asset_item'
                            GROUP BY p.code
                            ORDER BY p.code
                        )

                        SELECT 
                            warehouse_name,
                            asset_item,
                            asset_name,
                            unit,
                            asset_type_code,
                            asset_type_name,
                            balance_before_qty,
                            balance_before,                     -- 1. ยอดยกมา
                            total_in_month,                     -- 2. รับเข้าระหว่างเดือน
                            total_in_month_qty,                     -- 2. รับเข้าระหว่างเดือน
                            (balance_before + total_in_month) AS total_before_out,   -- 3. รวม
                            total_out_month,                    -- 4. จ่ายไประหว่างเดือน
                            total_out_month_qty,                    -- 4. จ่ายไประหว่างเดือน
                            (balance_before + total_in_month - total_out_month) AS balance_after  -- 5. ยอดยกไป
                        FROM stock_summary
                        ORDER BY CAST(SUBSTRING(asset_type_code, 2) AS UNSIGNED);";

        return Yii::$app->db->createCommand($sql, [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd
        ])->queryAll();
    }

    //แสดงรายงานแบบละเอียดแยกตามรายการสินค้า
    public function actionListByItem()
    {

        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);



        $querys = $this->geteportByItem($searchModel);
        return $this->render('list_by_item', [
            'querys' => $querys,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportExcelByItem()
    {
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $rows = $this->geteportByItem($searchModel);

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
        $sheet->setCellValue('D1', 'คลัง');
        $sheet->setCellValue('E1', 'ยอดยกมา');
        $sheet->setCellValue('G1', 'รับเข้า');
        $sheet->setCellValue('I1', 'จ่ายออก');
        $sheet->setCellValue('K1', 'คงเหลือสิ้นเดือน');

        // รวมเซลล์ตามโครงสร้าง
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');
        $sheet->mergeCells('E1:F1');
        $sheet->mergeCells('G1:H1');
        $sheet->mergeCells('I1:J1');
        $sheet->mergeCells('K1:L1');

        // แถวที่ 2
        $sheet->setCellValue('E2', 'จำนวน');
        $sheet->setCellValue('F2', 'มูลค่า');
        $sheet->setCellValue('G2', 'จำนวน');
        $sheet->setCellValue('H2', 'มูลค่า');
        $sheet->setCellValue('I2', 'จำนวน');
        $sheet->setCellValue('J2', 'มูลค่า');
        $sheet->setCellValue('K2', 'จำนวนคงเหลือ');
        $sheet->setCellValue('L2', 'มูลค่าคงเหลือ');

        // --------------------------------------------------
        // จัดสไตล์หัวตาราง
        // --------------------------------------------------
        $headerRange = 'A1:L2';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // ฟังก์ชันช่วยเปลี่ยน NULL หรือว่างเป็น 0
      $checkNumber = function($val) {
            return ($val === null || $val === '') ? 0 : $val;
        };
            
        // --------------------------------------------------
        // ข้อมูลเริ่มแถวที่ 3
        // --------------------------------------------------
        $rowIndex = 3;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $r['asset_item'],
                $r['title'],
                $r['asset_type_name'],
                $r['warehouse_name'],
                $checkNumber($r['begin_qty']),
                $checkNumber($r['begin_price']),
                $checkNumber($r['qty_in']),
                $checkNumber($r['price_in']),
                $checkNumber($r['qty_out']),
                $checkNumber($r['price_out']),
                $checkNumber($r['end_qty']),
                $checkNumber($r['end_price']),
            ], NULL, "A{$rowIndex}");
             // ทำตัวเลข (E-L) เป็นตัวหนา
            $sheet->getStyle("E{$rowIndex}:L{$rowIndex}")->getFont()->setBold(true);

            $rowIndex++;
        }

        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue("A{$rowIndex}", 'รวมทั้งหมด');
        $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");

        $sheet->setCellValue("E{$rowIndex}", "=SUM(E3:E" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("F{$rowIndex}", "=SUM(F3:F" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("G{$rowIndex}", "=SUM(G3:G" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("H{$rowIndex}", "=SUM(H3:H" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("I{$rowIndex}", "=SUM(I3:I" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("J{$rowIndex}", "=SUM(J3:J" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("K{$rowIndex}", "=SUM(K3:K" . ($rowIndex - 1) . ")");
        $sheet->setCellValue("L{$rowIndex}", "=SUM(L3:L" . ($rowIndex - 1) . ")");
        $sheet->getStyle("A{$rowIndex}:L{$rowIndex}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowIndex}:L{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        // ส่งไฟล์ออกไปยัง Browser
    
            try {
            $dateStart = $searchModel->date_start;
            $dateEnd = $searchModel->date_end;
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }
        $filename = 'รายงานวัสดุคงคลังวันที่ ' . $dateStart.'-'.$dateEnd . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition', "attachment;filename=\"{$filename}\"");
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }


    

    private function geteportByItem($searchModel)
    {

        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = '';
            $dateEnd = '';
        }

        $q = $searchModel->q;
        $warehouseId = $searchModel->warehouse_id;
        $assetTypeId = $searchModel->asset_type_id;

        // สร้างเงื่อนไข WHERE แบบ dynamic
        $where = "e.name = 'order' AND w.warehouse_type = 'MAIN' AND i.asset_item IS NOT NULL";

        // ถ้ามีค่า $q ให้กรอง asset_item หรือ code
        if (!empty($q)) {
            $where .= " AND (LOWER(a.title) LIKE LOWER(:q) OR LOWER(a.code) LIKE LOWER(:q))";
            $params[':q'] = "%{$q}%";
        }

        if (!empty($warehouseId)) {
            $where .= " AND (e.warehouse_id = :warehouse_id)";
        }

        if (!empty($assetTypeId)) {
            $where .= " AND (a.category_id = :asset_type_id)";
        }

        $sql = "SELECT 
                w.warehouse_name,
                t.title as asset_type_name,
                a.category_id,
                i.asset_item,
                a.title,
                    -- ยอดยกมาก่อนเดือนสิงหาคม (ปริมาณ)
                    SUM(
                        CASE 
                            WHEN e.movement_date < :date_start AND i.transaction_type = 'IN'  THEN i.qty
                            WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' THEN -i.qty
                            ELSE 0 
                        END
                    ) AS begin_qty,
                    -- ยอดยกมาก่อนเดือนสิงหาคม (มูลค่า)
                    SUM(
                        CASE 
                            WHEN e.movement_date < :date_start AND i.transaction_type = 'IN'  THEN i.qty * i.unit_price
                            WHEN e.movement_date < :date_start AND i.transaction_type = 'OUT' THEN -i.qty * i.unit_price
                            ELSE 0 
                        END
                    ) AS begin_price,
                    -- รับเข้าเดือนสิงหาคม
                    SUM(
                        CASE 
                            WHEN e.movement_date BETWEEN :date_start AND :date_end
                                AND i.transaction_type = 'IN' THEN i.qty
                            ELSE 0 
                        END
                    ) AS qty_in,
                    SUM(
                        CASE 
                            WHEN e.movement_date BETWEEN :date_start AND :date_end
                                AND i.transaction_type = 'IN' THEN i.qty * i.unit_price
                            ELSE 0 
                        END
                    ) AS price_in,
                    -- จ่ายออกเดือนสิงหาคม
                    SUM(
                        CASE 
                            WHEN e.movement_date BETWEEN :date_start AND :date_end
                                AND i.transaction_type = 'OUT' THEN i.qty
                            ELSE 0 
                        END
                    ) AS qty_out,
                    SUM(
                        CASE 
                            WHEN e.movement_date BETWEEN :date_start AND :date_end
                                AND i.transaction_type = 'OUT' THEN i.qty * i.unit_price
                            ELSE 0 
                        END
                    ) AS price_out,
                    -- คงเหลือสิ้นเดือน (ปริมาณ)
                    SUM(
                        CASE 
                            WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN'  THEN i.qty
                            WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' THEN -i.qty
                            ELSE 0 
                        END
                    ) AS end_qty,
                    -- คงเหลือสิ้นเดือน (มูลค่า)
                    SUM(
                        CASE 
                            WHEN e.movement_date <= :date_end AND i.transaction_type = 'IN'  THEN i.qty * i.unit_price
                            WHEN e.movement_date <= :date_end AND i.transaction_type = 'OUT' THEN -i.qty * i.unit_price
                            ELSE 0 
                        END
                    ) AS end_price

                FROM stock_events e
                LEFT JOIN stock_events i 
                    ON i.category_id = e.id 
                AND i.name = 'order_item'
                LEFT JOIN warehouses w ON w.id = e.warehouse_id
                LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
                LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
                WHERE $where
                GROUP BY i.asset_item
                ORDER BY i.asset_item";

        $q = $searchModel->q;

        $command = Yii::$app->db->createCommand($sql)
            ->bindValue(':date_start', $dateStart)
            ->bindValue(':date_end', $dateEnd);

        if (!empty($q)) {
            $command->bindValue(':q', "%{$q}%");
        }

        if (!empty($warehouseId)) {
            $command->bindValue(':warehouse_id', $warehouseId); // bind :warehouse_id เฉพาะกรณีมีค่า
        }

        if (!empty($assetTypeId)) {
            $command->bindValue(':asset_type_id', $assetTypeId); // bind :warehouse_id เฉพาะกรณีมีค่า
        }

        return  $command->queryAll();
    }
}
