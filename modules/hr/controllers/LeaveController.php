<?php

namespace app\modules\hr\controllers;

use Yii;
use DateTime;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveStep;
use app\modules\hr\models\LeaveSearch;
use app\modules\approve\models\Approve;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\hr\components\LeaveHelper;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use app\modules\hr\models\LeaveEntitlements;
use app\modules\hr\models\LeaveSummarySearch;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * LeaveController implements the CRUD actions for Leave model.
 */
class LeaveController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Leave models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $status = $this->request->get('status');
        $searchModel = new LeaveSearch([
            'status' =>   $status ? [$status] : ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $query->with('employee');

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'date_start', $start])
            ->andFilterWhere(['<=', 'date_end', $end]);


        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }

        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }


        // search employee department
        // ค้นหาคามกลุ่มโครงสร้าง
        if ($searchModel->q_department) {
            $org1 = Organization::findOne($searchModel->q_department);

            if ($org1 && $org1->lvl == 1) {
                $cacheKey = 'org_child_' . $org1->id;
                $arrDepartment = Yii::$app->cache->get($cacheKey);
                if ($arrDepartment === false) {
                    $arrDepartment = Organization::find()
                        ->select('id')
                        ->where(['between', 'lft', $org1->lft, $org1->rgt])
                        ->column();
                    Yii::$app->cache->set($cacheKey, $arrDepartment, 3600);
                }

                // ✅ ใช้ emp_id จาก employees ที่อยู่ใน department เหล่านั้น
                $empIds = Employees::find()
                    ->select('id')
                    ->andWhere(['department' => $arrDepartment])
                    ->column();

                $query->andWhere(['in', 'emp_id', $empIds]);
            } else {
                $empIds = Employees::find()
                    ->select('id')
                    ->andWhere(['department' => $searchModel->q_department])
                    ->column();

                $query->andWhere(['in', 'emp_id', $empIds]);
            }
        }


        $dataProvider->setSort(['defaultOrder' => [
            'created_at' => SORT_DESC,
        ]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionDashboard()
    {
        $searchModel = new LeaveSummarySearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->query->groupBy('code');
        return $this->render('dashboard/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    // รายงานการลา
    public function actionReport()
    {

        $searchModel = new LeaveSearch([
            // 'thai_year' => AppHelper::YearBudget(),
            // 'date_filter' => 'this_month'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee e');
        $dataProvider->query->select([
            'leave.*',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT1" THEN total_days ELSE 0 END), 0) AS sum_lt1',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT2" THEN total_days ELSE 0 END), 0) AS sum_lt2',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT3" THEN total_days ELSE 0 END), 0) AS sum_lt3',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT4" THEN total_days ELSE 0 END), 0) AS sum_lt4',
        ]);
        $dataProvider->query->andFilterWhere(['leave.status' => 'Approve']);

        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }

        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }


        // search employee department
        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }



        $dataProvider->query->groupBy('emp_id');
        // $dataProvider->sort->defaultOrder = ['leave.emp_id' => SORT_DESC];

        if (isset($searchModel->export) && $searchModel->export == 'true') {
            // ไม่ต้องใส่ pagination
            $dataProvider->pagination = false;
            $this->ExportReport($dataProvider, $searchModel);
            // \Yii::$app->response->format = Response::FORMAT_JSON;
            // return 'xx';

        } else {
            return $this->render('report/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        // $dataProvider->query->andWhere(['status' => Leave::STATUS_APPROVED]);
    }


    public function actionExport()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
    }

    //ออกรายงานสรุปวันลา

    protected function ExportReport($dataProvider, $searchModel)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Merge cells for headers
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:D3'); // ตำแหน่ง
        $sheet->mergeCells('E2:E3'); // เลขบัตรประชาชน
        $sheet->mergeCells('F2:F3'); // ฝ่าย/แผนก
        $sheet->mergeCells('G2:J2'); // ประเภทการลา
        $sheet->mergeCells('K2:K3'); // รวมได้ลาแล้ว

        // ชื่อรายงาน
        $departmentName = $searchModel->q_department ? '(' . Organization::findOne($searchModel->q_department)->name . ')' : '(ทุกหน่วยงาน)';
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');
        $sheet->setCellValue('A1', 'รายงานวันลาประจำปีงบประมาณ ' . $searchModel->thai_year . ' วันที่ ' . $dateReport . ' ' . $departmentName);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true);

        // Header row
        $headers = [
            'A2' => ['label' => 'ลำดับ', 'width' => 6],
            'B2' => ['label' => 'ชื่อ-สกุล', 'width' => 40],
            'C2' => ['label' => 'ตำแหน่ง', 'width' => 30],
            'D2' => ['label' => 'เลขบัตรประชาชน', 'width' => 30],
            'E2' => ['label' => 'ฝ่าย/แผนก', 'width' => 40],
            'F3' => ['label' => 'ลาป่วย', 'width' => 15],
            'G3' => ['label' => 'ลากิจ', 'width' => 15],
            'H3' => ['label' => 'ลาคลอดบุตร', 'width' => 15],
            'I3' => ['label' => 'ลาพักผ่อน', 'width' => 15],
            'J2' => ['label' => 'ประเภทการลา'],
            'K2' => ['label' => 'รวมได้ลาแล้ว', 'width' => 20],
        ];

        foreach ($headers as $cell => $props) {
            $sheet->setCellValue($cell, $props['label']);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($cell)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true);
            if (isset($props['width'])) {
                $col = preg_replace('/[^A-Z]/', '', $cell);
                $sheet->getColumnDimension($col)->setWidth($props['width']);
            }
        }

        $sheet->setTitle('รายงานวันลา');

        // เติมข้อมูล
        $startRow = 4;
        foreach ($dataProvider->getModels() as $index => $item) {
            $row = $startRow + $index;
            $employee = $item->employee;

            $sheet->setCellValue("A$row", $index + 1);
            $sheet->setCellValue("B$row", $employee->fullname);
            $sheet->setCellValue("C$row", trim(str_replace('<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>', '', $employee->positionName())));
            $sheet->setCellValue("D$row", $employee->cid);
            $sheet->setCellValueExplicit("D$row", $employee->cid, DataType::TYPE_STRING);
            $sheet->setCellValue("E$row", $employee->departmentName());
            $sheet->setCellValue("F$row", $item->sum_lt1);
            $sheet->setCellValue("G$row", $item->sum_lt3);
            $sheet->setCellValue("H$row", $item->sum_lt2);
            $sheet->setCellValue("I$row", $item->sum_lt4);
            $sheet->setCellValue("K$row", $item->sum_lt1 + $item->sum_lt2 + $item->sum_lt3 + $item->sum_lt4);

            foreach (range('A', 'K') as $col) {
                $cell = "$col$row";
                $sheet->getStyle($cell)->getFont()->setName('TH Sarabun New')->setSize(16);
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('8DB4E2');
            }
        }

        // Export
        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/report-leave.xlsx';
        $writer->save($filePath);

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }



    // ส่งออกรายการวันลา
    public function actionExportLeave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],

            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(leave.data_json, '$.reason'))"), $searchModel->q],
            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(leave.data_json, '$.leave_work_send'))"), $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }


        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }

        if ($status) {
            $dataProvider->query->andFilterWhere(['leave.status' => $searchModel->status]);
        }

        // search employee department
        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }


        $dataProvider->setSort(['defaultOrder' => [
            // 'total_days' => SORT_DESC,
            'created_at' => SORT_DESC,
        ]]);
        $dataProvider->pagination = false;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:I1');

        $rowTitle = 'A1';
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');

        $sheet->setCellValue($rowTitle, 'ทะเบียนวันลา ปีงบประมาณ ' . $searchModel->thai_year . ' วันที่ ' . $dateReport);
        $sheet->getStyle($rowTitle)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowTitle)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowTitle)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('A')->setWidth(6);

        $rowA1 = 'A2';
        $sheet->setCellValue($rowA1, 'ลำดับ');
        $sheet->getStyle($rowA1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowA1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowA1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('A')->setWidth(6);

        $rowB1 = 'B2';
        $sheet->setCellValue($rowB1, 'สถานะ');
        $sheet->getStyle($rowB1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowB1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowB1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('B')->setWidth(13);

        $rowC1 = 'C2';
        $sheet->setCellValue($rowC1, 'ปีงบ');
        $sheet->getStyle($rowC1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowC1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowC1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('C')->setWidth(10);

        $rowD1 = 'D2';
        $sheet->setCellValue($rowD1, 'ชื่อผู้แจ้งลา');
        $sheet->getStyle($rowD1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowD1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowD1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('D')->setWidth(30);

        $rowE1 = 'E2';
        $sheet->setCellValue($rowE1, 'ประเภทการลา');
        $sheet->getStyle($rowE1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowE1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowE1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('E')->setWidth(15);

        $rowF1 = 'F2';
        $sheet->setCellValue($rowF1, 'เหตุผลการลา');
        $sheet->getStyle($rowF1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('F')->setWidth(35);


        $rowG1 = 'G2';
        $sheet->setCellValue($rowG1, 'ตำแหน่ง');
        $sheet->getStyle($rowG1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowG1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowG1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('G')->setWidth(35);
        $rowH1 = 'H2';
        $sheet->setCellValue($rowH1, 'หน่วยงาน');
        $sheet->getStyle($rowH1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowH1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowH1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('H')->setWidth(35);

        $rowI1 = 'I2';
        $sheet->setCellValue($rowI1, 'ระหว่างวันที่');
        $sheet->getStyle($rowI1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowI1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowI1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('I')->setWidth(40);

        $rowJ1 = 'J2';
        $sheet->setCellValue($rowJ1, 'จำนวนวันลา');
        $sheet->getStyle($rowJ1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowJ1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowJ1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('I')->setWidth(20);


        $StartRowSheet = 3;
        foreach ($dataProvider->getModels() as $key => $item) {
            $numRow = $StartRowSheet++;
            $sheet->setCellValue('A' . $numRow, ($key + 1));
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('B' . $numRow, $item->leaveStatus->title  ?? '-');
            $sheet->getStyle('B' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('B' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('B' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('B' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('C' . $numRow, $item->thai_year);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('C' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('C' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('D' . $numRow, $item->employee->fullname());
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('E' . $numRow, $item->leaveType?->title ?? '-');
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('E' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('E' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');


            // $sheet->setCellValue('F' . $numRow,  ThaiDateHelper::formatThaiDate($item->doc_transactions_date));
            $sheet->setCellValue('F' . $numRow,  $item->data_json['reason']);
            $sheet->getStyle('F' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('F' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('F' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('F' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');


            $sheet->setCellValue('G' . $numRow,  $item->employee->positionName());
            $sheet->getStyle('G' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('G' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('G' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('H' . $numRow,  $item->employee->departmentName());
            $sheet->getStyle('H' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('H' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('H' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('I' . $numRow,  $item->showLeaveDate());
            $sheet->getStyle('I' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('I' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('I' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('I' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('J' . $numRow,  $item->total_days);
            $sheet->getStyle('J' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('J' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('J' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('J' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/export-leave.xlsx';
        $writer->save($filePath);
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }

    public function actionExportLeaveExample()
    {
        return $this->render('export_leave');
    }


    public function actionMe()
    {

        $me = UserHelper::GetEmployee();
        $searchModel = new LeaveSearch([
            'emp_id' => $me->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', new Expression("JSON_EXTRACT(data_json, '$.reason')"), $searchModel->q],
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCalendar()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('calendar', []),
            ];
        } else {
            return $this->render('calendar', []);
        }
    }

    public function actionHoliday()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('holiday', []),
            ];
        } else {
            return $this->render('holiday');
        }
    }

    /**
     * Displays a single Leave model.
     *
     * @param int $id ID
     *
     * @return string
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $model = $this->findModel($id);

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $model->employee->getAvatar(false),
                'content' => $this->renderAjax('view', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model
            ]);
        }
    }

    public function actionTypeSelect()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('type_select', []),
            ];
        } else {
            return $this->render('type_select', []);
        }
    }

    /**
     * Creates a new Leave model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $leaveTypeId = $this->request->get('leave_type_id');
        $dateStart = AppHelper::convertToThai($this->request->get('date_start')) ?? '';
        $dateEnd = AppHelper::convertToThai($this->request->get('date_end')) ?? '';
        $model = new Leave([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'on_holidays' => 0,
            'total_days' => 0
        ]);

        $model->data_json = [
            'title' => $this->request->get('title'),
            'address' => strip_tags($model->CreateBy()->fulladdress),
            'phone' => $model->CreateBy()->phone,
            'approve_1' => $model->Approve()['approve_1']['id'],
            'approve_2' => $model->Approve()['approve_2']['id'],
            'leave_contact_phone' => $model->CreateBy()->phone,
            'director' => \Yii::$app->site::viewDirector()['id'],
            'director_fullname' => \Yii::$app->site::viewDirector()['fullname'],
        ];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $dateStart =  AppHelper::convertToGregorian($model->date_start);
                $dateEnd =  AppHelper::convertToGregorian($model->date_end);

                //ถ้าเป็น ผอ. ให้อนุมัติเลย
                $model->status = $model->employee->isDirector() ? 'Approve' : 'Pending';

                $model->thai_year = AppHelper::YearBudget($dateStart);
                $model->date_start = $dateStart;
                $model->date_end = $dateEnd;

                if ($model->save()) {
                    //ถ้าไม่ใช่ ผอ. ให้สร้างรายการอนุมัติ
                    if (!$model->employee->isDirector()) {
                        $model->createApprove();
                    }
                }
                return [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
       $id = Yii::$app->request->post('id', Yii::$app->request->get('id'));
        $model = $id ? Leave::findOne($id) : new Leave();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->date_start)) {
                preg_replace('/\D/', '', $model->date_start) == '' ? $model->addError('date_start', $requiredName) : null;
            }
            if (isset($model->date_end)) {
                preg_replace('/\D/', '', $model->date_end) == '' ? $model->addError('date_end', $requiredName) : null;
            }
            $dateStart = preg_replace('/\D/', '', $model->date_start) !== '' ? AppHelper::convertToGregorian($model->date_start) : '';
            $dateEnd = preg_replace('/\D/', '', $model->date_end) !== '' ? AppHelper::convertToGregorian($model->date_end) : '';

            if ($dateStart > $dateEnd && $dateEnd !== '') {
                $model->addError('date_start', 'มากกว่าวันสุดท้าย');
                $model->addError('date_end', 'มากกว่าวันเริ่มต้น');
            }

            $model->date_start_type == '' ? $model->addError('date_start_type', $requiredName) : null;
            $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
            $model->data_json['phone'] == '' ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            $model->data_json['leave_work_send_id'] == '' ? $model->addError('data_json[leave_work_send_id]', $requiredName) : null;
            $model->data_json['approve_1'] == '' ? $model->addError('data_json[approve_1]', $requiredName) : null;
            $model->data_json['approve_2'] == '' ? $model->addError('data_json[approve_2]', $requiredName) : null;

            // --- ✅ ตรวจสอบวันลาซ้ำ ---
            if ($dateStart && $dateEnd && !$model->hasErrors()) {
                $query = Leave::find()
                    ->where(['emp_id' => $model->emp_id])
                    ->andWhere(['<=', 'date_start', $dateEnd])
                    ->andWhere(['>=', 'date_end', $dateStart])
                    ->andWhere(['NOT IN', 'status', ['cancel']]); // ไม่ตรวจ record ที่ยกเลิก

                if (!$model->isNewRecord && $model->id) {
                    $query->andWhere(['!=', 'id', $model->id]); // ข้าม record ตัวเองตอนแก้ไข
                }

                $exists = $query->exists();

                if ($exists) {
                    $model->addError('date_start', 'คุณลาในวันนี้แล้ว');
                    $model->addError('date_end', 'คุณลาในวันนี้แล้ว');
                }
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }


    // บุคลากร
    public function actionGetLeaderApprove($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = (new \yii\db\Query())
            ->select(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader1'))"))
            ->from('tree')
            ->union(
                (new \yii\db\Query())
                    ->select(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader2'))"))
                    ->from('tree')
            )
            ->column();

        $models = Employees::find()
            ->where(['like', 'fname', $q])
            ->andWhere(['id' => $ids])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->fullname,
                'title' => $model->fname,
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $ids
        ];
    }


    public function actionCalDays()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $emp_id = (float) ($this->request->get('emp_id'));
        $date_start_type = (float) ($this->request->get('date_start_type'));
        $date_end_type = (float) ($this->request->get('date_end_type'));
        $on_holidays = $this->request->get('on_holidays');
        $leave_type_id = $this->request->get('leave_type_id');

        $date_start = preg_replace('/\D/', '', $this->request->get('date_start'));
        $date_end = preg_replace('/\D/', '', $this->request->get('date_end'));

        $dateStart = $date_start == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_start'));
        $dateEnd = $date_end == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_end'));



        $model = LeaveHelper::CalDay($dateStart, $dateEnd, $emp_id);

        if ($leave_type_id == 'LT2') {
            //ถ้าเป็นลาคลอดบุตร ไม่ต้องนับวันหยุด
            $total = ($model['allDays']);
        } else if ($model['shift'] == 'normal') {
            //ถ้าไม่กำหนดวัน OFF ให้นับวันหยุด
            $total = ($model['allDays'] - ($date_start_type + $date_end_type) - $model['satsunDays'] - $model['holiday']);
        } else {
            //จำเป็นต้องนับวัน off หรือไม่
            // $total = ($model['allDays']-($date_start_type+$date_end_type) - $model['dayOff']);
            // $total = ($model['allDays'] - ($date_start_type + $date_end_type + $model['dayOffBetweenLeave']));
            $total = ($model['allDays'] - ($date_start_type + $date_end_type));
        }

        // ตรวจสอบสิทธิ์การลาป้องกันลาเกินปีงบประมาณ
        $checkLeaveYear =  AppHelper::YearBudget($dateEnd);
        $checkLeaveEntitlements  = LeaveEntitlements::find()->andWhere(['thai_year' => $checkLeaveYear])->count();
        if ($checkLeaveEntitlements == 0) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลสิทธิ์การลาในปี ' . $checkLeaveYear . ' กรุณาติดต่อเจ้าหน้าที่',
            ];
        }


        return [
            $model,
            'allDays' => $model['allDays'],
            'satsunDays' => $model['satsunDays'],
            'holiday' => $model['holiday'],
            'shift' => $model['shift'],
            'shift_name' => $model['shift'] == 'normal' ? 'เวรปกติ' : 'เวร 8',
            // 'isDayOff' => $model['dayOffBetweenLeave'],
            // 'dayOff' => $holidaysMe,
            'on_holidays' => $on_holidays,
            'type_days' => ($date_start_type + $date_end_type),
            'total' => ($total ?? 0),
            'start_type' => $date_start_type,
            'start_end' => $date_end_type,
            'start' => $dateStart,
            'end' => $dateEnd,
        ];
    }

    /**
     * Updates an existing Leave model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);

            $model->save();
            $model->createApprove();
            return [
                'status' => 'success',
                'container' => '#leave'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }



    // public function actionViewHistory($emp_id)
    // {
    //     $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
    //     $searchModel = new LeaveSearch([
    //         'emp_id' => $emp_id,
    //         'thai_year' => AppHelper::YearBudget(),
    //     ]);
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->joinWith('employee');

    //     if ($this->request->isAJax) {
    //         \Yii::$app->response->format = Response::FORMAT_JSON;

    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('history', [
    //                 'searchModel' => $searchModel,
    //                 'dataProvider' => $dataProvider,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('history', [
    //             'searchModel' => $searchModel,
    //             'dataProvider' => $dataProvider,
    //         ]);
    //     }

    // }
    //แสดงรายการที่รอ Approve
    public function actionDashboardApprove()
    {
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['status' => 'Pending']);
        $dataProvider->query->andWhere(['thai_year' => AppHelper::YearBudget()]);
        return $this->render('dashboard_approve', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApprove($id)
    {
        $me = UserHelper::GetEmployee();
        // $model = Approve::findOne(["id" => $id, "emp_id" => $me->id]);
        $model = Approve::findOne(["id" => $id]);
        // $model = Approve::findOne(["id" => $id]);
        $leave = Leave::findOne($model->from_id);
        if (!$model) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;


            $approveDate = ["approve_date" => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if ($model->level == 3) {
                $model->emp_id = $me->id;
            }

            if ($model->save()) {
                $nextApprove = Approve::findOne(["from_id" => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }

                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($model->level == 4) {
                    $leave->status = 'Allow';
                    $leave->save();
                } else if ($model->status == 'Reject') {
                    $leave->status = 'Reject';
                    $leave->save();
                } else {
                    $leave->status = 'Checking';
                    $leave->save();
                }

                return [
                    'status' => 'success',
                    'container' => '#leave',
                ];
            }
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="bi bi-person-exclamation"></i> ' . $this->request->get('title'),
                'content' => $this->renderAjax('form_approve', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('form_approve', [
                'model' => $model,
            ]);
        }
    }


    // ตรวจสอบความถูกต้อง
    public function actionApproveValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new LeaveStep();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->status == '' ? $model->addError('status', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    /**
     * Deletes an existing Leave model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCancel($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->status = "Cancel";
        $checkerCancel = [
            'cancel_date' => date('Y-m-d H:i:s'),
            'cancel_user_id' => \Yii::$app->user->identity->id,
            'cancel_emp_id' => $me->id,
            'cancel_fullname' => $me->fullname,
        ];
        $model->data_json = ArrayHelper::merge($model->data_json, $checkerCancel);
        $model->save();
        $lineId = $model->employee->user->line_id;
        $message = 'ขอยกเลิกวัน' . ($model->leaveType->title ?? '-') . ' วันที่ ' . Yii::$app->thaiFormatter->asDate($model->date_start, 'long') . ' ถึง ' . Yii::$app->thaiFormatter->asDate($model->date_end, 'long') . 'ได้รับการอนุมัติแล้ว';
        LineMsg::sendMsg($lineId, $message);

        // return $this->redirect(['/hr/leave', 'status' => 'ReqCancel']);
        return [
            'status' => 'success'
        ];
    }



    //ประวัติการลา
    public function actionLeaveHistory()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $empId = $this->request->get('emp_id');
        $thaiYear = $this->request->get('thai_year');
        $leaveType = $this->request->get('leave_type_id');
        $dateStart = $this->request->get('date_start');
        $dateEnd = $this->request->get('date_end');
        $status = $this->request->get('status');
        $model = Leave::find()
            ->andFilterWhere(['status' => ($status ? $status : 'Approve')])
            ->andFilterWhere(['emp_id' => $empId])
            ->andFilterWhere(['thai_year' => $thaiYear])
            ->andFilterWhere(['emp_id' => $empId])
            ->andFilterWhere(['leave_type_id' => $leaveType])
            ->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd])
            ->all();
        return [
            'title' => 'ประวัติการลา' . ($thaiYear ? 'ประจำปี ' . $thaiYear : ''),
            'content' => $this->renderAjax('leave_history', ['model' => $model])
        ];
    }


    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Leave model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @return Leave the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Leave::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
