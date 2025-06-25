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
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Calendar;
use app\components\DateFilterHelper;
use app\modules\hr\models\LeaveStep;
use app\modules\hr\models\LeaveSearch;
use app\modules\approve\models\Approve;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\hr\components\LeaveHelper;
use app\modules\hr\models\LeavePermission;
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
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
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' =>   $status ? [$status] : ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
          
            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.leave_work_send')"), $searchModel->q],
        ]);

         if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        if(!$searchModel->date_filter && !$searchModel->thai_year){
            $dateStart= AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
              $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        }
        
      
    
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        // if (!empty($searchModel->status)) {
        //     $dataProvider->query->andFilterWhere(['in', 'leave.status', $searchModel->status]);
        // }
        if($status)
        {
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
       
        
        // $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];

          $dataProvider->setSort(['defaultOrder' => [
            // 'total_days' => SORT_DESC,
            'created_at' => SORT_DESC,
        ]]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dateStart' => $dateStart,
            // 'dateEnd' => $dateEnd,
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

            $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
            $searchModel = new LeaveSearch([
                'thai_year' => AppHelper::YearBudget(),
                'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
                'date_end' => AppHelper::convertToThai($lastDay)
            ]);

            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->joinWith('employee e');
            $dataProvider->query->select([
                'emp_id',
                'IFNULL(SUM(CASE WHEN leave_type_id = "LT1" THEN total_days ELSE 0 END), 0) AS sum_lt1',
                'IFNULL(SUM(CASE WHEN leave_type_id = "LT2" THEN total_days ELSE 0 END), 0) AS sum_lt2',
                'IFNULL(SUM(CASE WHEN leave_type_id = "LT3" THEN total_days ELSE 0 END), 0) AS sum_lt3',
                'IFNULL(SUM(CASE WHEN leave_type_id = "LT4" THEN total_days ELSE 0 END), 0) AS sum_lt4',
            ]);
            $dataProvider->query->andFilterWhere(['leave.status' => 'Approve']);
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

         if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
            
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        
            $dataProvider->query->groupBy('emp_id');
            $dataProvider->sort->defaultOrder = ['emp_id' => SORT_DESC];

            if(isset($searchModel->data_json['export']) && $searchModel->data_json['export'] == 'true'){
                $dataProvider->pagination->pageSize = 1000000000000;
                $this->ExportLeave($dataProvider,$searchModel);
                // \Yii::$app->response->format = Response::FORMAT_JSON;
                // return 'xx';
               
            }else{
                return $this->render('report/index',[
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }
            // $dataProvider->query->andWhere(['status' => Leave::STATUS_APPROVED]);
        }

         public function actionReport2()
    {
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' =>   $status ? [$status] : ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
          
            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.leave_work_send')"), $searchModel->q],
        ]);

         if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        if(!$searchModel->date_filter && !$searchModel->thai_year){
            $date_start= AppHelper::convertToGregorian($searchModel->date_start);
            $date_end = AppHelper::convertToGregorian($searchModel->date_end);
             $dataProvider->query->andWhere(['between', 'doc_transactions_date', $date_start, $date_end]);
        }
        
      
    
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        // if (!empty($searchModel->status)) {
        //     $dataProvider->query->andFilterWhere(['in', 'leave.status', $searchModel->status]);
        // }
        if($status)
        {
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
       
        
        // $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];

          $dataProvider->setSort(['defaultOrder' => [
            // 'total_days' => SORT_DESC,
            'created_at' => SORT_DESC,
        ]]);


        return $this->render('report/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dateStart' => $dateStart,
            // 'dateEnd' => $dateEnd,
        ]);
    }


        protected function ExportLeave($dataProvider,$searchModel)
        {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->mergeCells('A1:I1');
            $sheet->mergeCells('A2:A3');
            $sheet->mergeCells('B2:B3');
            $sheet->mergeCells('C2:C3');
            $sheet->mergeCells('D2:D3');
            $sheet->mergeCells('E2:H2');
            $sheet->mergeCells('I2:I3');

            
            $rowTitle = 'A1';
            $sheet->setCellValue($rowTitle, 'รายงานวันลาประจำปีงบประมาณ '.$searchModel->thai_year);
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
            $sheet->setCellValue($rowB1, 'ชื่อ-สกุล');
            $sheet->getStyle($rowB1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowB1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowB1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('B')->setWidth(40);

            $rowC1 = 'C2';
            $sheet->setCellValue($rowC1, 'ตำแหน่ง');
            $sheet->getStyle($rowC1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowC1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowC1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('C')->setWidth(50);
            
            $rowD1 = 'D2';
            $sheet->setCellValue($rowD1, 'ฝ่าย/แผนก');
            $sheet->getStyle($rowD1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowD1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowD1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('D')->setWidth(50);
            
            $rowE1 = 'E2';
            $sheet->setCellValue($rowE1, 'ประเภทการลา');
            $sheet->getStyle($rowE1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowE1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowE1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            // $sheet->getColumnDimension('E')->setWidth(20);

            $rowE2 = 'E3';
            $sheet->setCellValue($rowE2, 'ลาป่วย');
            $sheet->getStyle($rowE2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowE2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowE2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('E')->setWidth(15);

            $rowF2 = 'F3';
            $sheet->setCellValue($rowF2, 'ลากิจ');
            $sheet->getStyle($rowF2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowF2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowF2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('F')->setWidth(15);


            $rowG2 = 'G3';
            $sheet->setCellValue($rowG2, 'ลาคลอดบุตร');
            $sheet->getStyle($rowG2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowG2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowG2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('G')->setWidth(15);

            $rowH2 = 'H3';
            $sheet->setCellValue($rowH2, 'ลาพักผ่อน');
            $sheet->getStyle($rowH2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowH2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowH2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('H')->setWidth(15);

            $rowI2 = 'I2';
            $sheet->setCellValue($rowI2, 'รวมได้ลาแล้ว');
            $sheet->getStyle($rowI2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowI2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rowI2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
            $sheet->getColumnDimension('I')->setWidth(20);

            

            // ตั้งชื่อแผ่นงาน
            $sheet->setTitle('รางานวันลา');
            
            $StartRowSheet = 4;
            foreach ($dataProvider->getModels() as $key => $item) {
                $numRow = $StartRowSheet++;
                // $a[] = ['B' => 'B'.$StartRow++];
                $sheet->setCellValue('A' . $numRow, ($key+1));
                $sheet->getStyle('A' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('A' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('A' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');
    
                $sheet->setCellValue('B' . $numRow, $item->employee->fullname);
                $sheet->getStyle('B' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('B' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('B' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('B' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $text = '<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>ไม่ระบุตำแหน่ง';
                $cleaned_text = str_replace('<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>', '', $item->employee->positionName());
                
                $sheet->setCellValue('C' . $numRow, trim($cleaned_text));
                $sheet->getStyle('C' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('C' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('C' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('C' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('D' . $numRow, $item->employee->departmentName());
                $sheet->getStyle('D' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('D' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('D' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('D' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('E' . $numRow, $item->sum_lt1);
                $sheet->getStyle('E' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('E' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('E' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('E' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('F' . $numRow, $item->sum_lt3);
                $sheet->getStyle('F' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('F' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('F' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('F' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('G' . $numRow, $item->sum_lt2);
                $sheet->getStyle('G' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('G' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('G' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('G' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('H' . $numRow, $item->sum_lt4);
                $sheet->getStyle('H' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('H' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('H' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('H' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');

                $sheet->setCellValue('I' . $numRow, ($item->sum_lt1 + $item->sum_lt2 +$item->sum_lt3 +$item->sum_lt4));
                $sheet->getStyle('I' . $numRow, ($key+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I' . $numRow, ($key+1))->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                $sheet->getStyle('I' . $numRow, ($key+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('I' . $numRow, ($key+1))->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle('I' . $numRow, ($key+1))->getFill()->getStartColor()->setRGB('8DB4E2');
            }   
             // set font style ตั้งค่า font
                // $setHeader = 'B1:I3000';
                // $sheet->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
                // $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                // $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                // $sheet->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
                
                // $sheet->getStyle('E1:I3000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // $sheet->getStyle('E3:I3000')->getFont()->setBold(true)->setItalic(false);
                // $sheet->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $writer = new Xlsx($spreadsheet);
            $filePath = Yii::getAlias('@webroot') . '/downloads/report-leave.xlsx';
            $writer->save($filePath);  // สร้าง excel
            if (file_exists($filePath)) {
                return Yii::$app->response->sendFile($filePath);
            } else {
                throw new \yii\web\NotFoundHttpException('The file does not exist.');
            }
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
                'content' => $this->renderAjax('calendar', [
                ]),
            ];
        } else {
            return $this->render('calendar', [
            ]);
        }
    }

    public function actionHoliday()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('holiday', [
                ]),
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
                'content' => $this->renderAjax('type_select', [
                ]),
            ];
        } else {
            return $this->render('type_select', [
            ]);
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
        $me = UserHelper::GetEmployee();
        $leaveTypeId = $this->request->get('leave_type_id');
        $model = new Leave([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'thai_year' => AppHelper::YearBudget(),
            'on_holidays' => 0
        ]);

        $model->data_json = [
            'title' => $this->request->get('title'),
            'address' => $model->CreateBy()->fulladdress,
            'approve_1' => $model->Approve()['approve_1']['id'],
            'approve_2' => $model->Approve()['approve_2']['id'],
            'leave_contact_phone' => $model->CreateBy()->phone,
            'director' => \Yii::$app->site::viewDirector()['id'],
            'director_fullname' => \Yii::$app->site::viewDirector()['fullname'],
        ];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $model->status = 'Pending';
                $model->emp_id = $me->id;
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                
                if($model->save()){
                    $model->createApprove();
                }
                
                return $this->redirect(['view', 'id' => $model->id]);
                // return [
                //     'status' => 'success',
                //     'container' => '#leave'
                // ];
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
        $model = new Leave();
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
            
            if($dateStart > $dateEnd && $dateEnd !=='' ){
                $model->addError('date_start', 'มากกว่าวันสุดท้าย');
                $model->addError('date_end', 'มากกว่าวันเริ่มต้น');
            }

            $model->date_start_type == '' ? $model->addError('date_start_type', $requiredName) : null;
            // $model->data_json['date_end_type'] == '' ? $model->addError('data_json[date_end_type]', $requiredName) : null;
            $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
            $model->data_json['phone'] == '' ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            $model->data_json['leave_work_send_id'] == '' ? $model->addError('data_json[leave_work_send_id]', $requiredName) : null;
            $model->data_json['approve_1'] == '' ? $model->addError('data_json[approve_1]', $requiredName) : null;
            $model->data_json['approve_2'] == '' ? $model->addError('data_json[approve_2]', $requiredName) : null;
            // $model->unit_price == "" ? $model->addError('unit_price', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionCalDays()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $emp_id = (Float) ($this->request->get('emp_id'));
        $date_start_type = (Float) ($this->request->get('date_start_type'));
        $date_end_type = (Float) ($this->request->get('date_end_type'));
        $on_holidays = $this->request->get('on_holidays');

        $date_start = preg_replace('/\D/', '', $this->request->get('date_start'));
        $date_end = preg_replace('/\D/', '', $this->request->get('date_end'));

        $dateStart = $date_start == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_start'));
        $dateEnd = $date_end == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_end'));
        $model = LeaveHelper::CalDay($dateStart, $dateEnd,$emp_id);
        //ถ้าไม่กำหนดวัน OFF ให้นับวันหยุด
        if($model['dayOff'] == 0){
            $total = ($model['allDays']-($date_start_type+$date_end_type) - $model['satsunDays'] - $model['holiday']);
        }else{
            //จำเป็นต้องนับวัน off หรือไม่
            // $total = ($model['allDays']-($date_start_type+$date_end_type) - $model['dayOff']);
            $total = ($model['allDays']-($date_start_type+$date_end_type));
            
           
        }

        return [
            $model,
            'allDays' => $model['allDays'],
            'satsunDays' => $model['satsunDays'],
            'holiday' => $model['holiday'],
            'isDayOff' => $model['dayOff'],
            // 'dayOff' => $holidaysMe,
            'on_holidays' => $on_holidays,
            'type_days' => ($date_start_type+$date_end_type),
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
        return $this->render('dashboard_approve',[
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
        if(!$model)
        {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
           
            
            $approveDate = ["approve_date" => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if($model->level == 3){
                $model->emp_id = $me->id;
            }
            
            if($model->save()){
                $nextApprove = Approve::findOne(["from_id" => $model->from_id,'level' => ($model->level+1)]);
                if($nextApprove){
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }
                
                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if($model->level == 4){
                    $leave->status = 'Allow';
                    $leave->save();
                }else if($model->status == 'Reject')
                {
                    $leave->status = 'Reject';
                    $leave->save();
                }else{                    
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
                'title' => '<i class="bi bi-person-exclamation"></i> '.$this->request->get('title'),
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
        $message = 'ขอยกเลิกวัน'.($model->leaveType->title ?? '-').' วันที่ '.Yii::$app->thaiFormatter->asDate($model->date_start, 'long').' ถึง '.Yii::$app->thaiFormatter->asDate($model->date_end, 'long').'ได้รับการอนุมัติแล้ว';
        LineMsg::sendMsg($lineId, $message);

        return $this->redirect(['/hr/leave','status'=>'ReqCancel']);
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
