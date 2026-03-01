<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveSearch;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * รายงานการลา — ใช้ models จาก modules/leave/models
 */
class ReportController extends Controller
{
    /**
     * รายงานวันลา
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้ารายงานการลา');
        }
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->joinWith('employee e');
        $dataProvider->query->select([
            'leave.*',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT1" THEN total_days ELSE 0 END), 0) AS sum_lt1',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT2" THEN total_days ELSE 0 END), 0) AS sum_lt2',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT3" THEN total_days ELSE 0 END), 0) AS sum_lt3',
            'IFNULL(SUM(CASE WHEN leave_type_id = "LT4" THEN total_days ELSE 0 END), 0) AS sum_lt4',
        ]);
        $dataProvider->query->andFilterWhere(['leave.status' => 'Approve']);
        $dataProvider->query->andFilterWhere(['NOT', ['e.id' => 1]]);
        $dataProvider->query->andFilterWhere(['e.status' => 1]);

        if ($searchModel->date_start && $searchModel->date_end) {
            $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)]);
            $dataProvider->query->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        }

        if ($searchModel->leave_type_id) {
            $searchModel->leave_type_id = is_array($searchModel->leave_type_id) ? $searchModel->leave_type_id : [$searchModel->leave_type_id];
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        if ($searchModel->position_type_id) {
            $dataProvider->query->andFilterWhere(['position_type' => $searchModel->position_type_id]);
        }

        $org1 = Organization::findOne($searchModel->q_department);
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
            $querys = Yii::$app->db->createCommand($sql)->bindValue(':name', $org1->name)->queryAll();
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

        if (isset($searchModel->export) && $searchModel->export == 'true') {
            $dataProvider->pagination = false;
            return $this->exportReport($dataProvider, $searchModel);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ส่งออก Excel
     */
    protected function exportReport($dataProvider, $searchModel)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:D3');
        $sheet->mergeCells('E2:E3');
        $sheet->mergeCells('F2:F3');
        $sheet->mergeCells('G2:J2');
        $sheet->mergeCells('K2:K3');

        $departmentName = $searchModel->q_department ? '(' . Organization::findOne($searchModel->q_department)->name . ')' : '(ทุกหน่วยงาน)';
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');
        $sheet->setCellValue('A1', 'รายงานวันลาประจำปีงบประมาณ ' . $searchModel->thai_year . ' วันที่ ' . $dateReport . ' ' . $departmentName);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true);

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

        $startRow = 4;
        foreach ($dataProvider->getModels() as $index => $item) {
            $row = $startRow + $index;
            $employee = $item->employee;
            $sheet->setCellValue("A$row", $index + 1);
            $sheet->setCellValue("B$row", $employee ? $employee->fullname : '');
            $sheet->setCellValue("C$row", $employee ? trim(str_replace('<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>', '', $employee->positionName())) : '');
            $sheet->setCellValue("D$row", $employee ? $employee->cid : '');
            $sheet->setCellValueExplicit("D$row", $employee ? $employee->cid : '', DataType::TYPE_STRING);
            $sheet->setCellValue("E$row", $employee ? $employee->departmentName() : '');
            $sheet->setCellValue("F$row", $item->sum_lt1 ?? 0);
            $sheet->setCellValue("G$row", $item->sum_lt3 ?? 0);
            $sheet->setCellValue("H$row", $item->sum_lt2 ?? 0);
            $sheet->setCellValue("I$row", $item->sum_lt4 ?? 0);
            $sheet->setCellValue("K$row", ($item->sum_lt1 ?? 0) + ($item->sum_lt2 ?? 0) + ($item->sum_lt3 ?? 0) + ($item->sum_lt4 ?? 0));
            foreach (range('A', 'K') as $col) {
                $cell = "$col$row";
                $sheet->getStyle($cell)->getFont()->setName('TH Sarabun New')->setSize(16);
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
                $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('8DB4E2');
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/report-leave.xlsx';
        $writer->save($filePath);
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath, 'รายงานวันลา.xlsx');
        }
        throw new \yii\web\NotFoundHttpException('The file does not exist.');
    }

    /**
     * ประวัติการลา (สำหรับ modal ในรายงาน)
     */
    public function actionLeaveHistory()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $empId = Yii::$app->request->get('emp_id');
        $thaiYear = Yii::$app->request->get('thai_year');
        $leaveTypeId = Yii::$app->request->get('leave_type_id');
        $dateStart = Yii::$app->request->get('date_start');
        $dateEnd = Yii::$app->request->get('date_end');
        $status = Yii::$app->request->get('status');

        $query = Leave::find()
            ->andFilterWhere(['status' => $status ?: 'Approve'])
            ->andFilterWhere(['emp_id' => $empId])
            ->andFilterWhere(['thai_year' => $thaiYear])
            ->andFilterWhere(['leave_type_id' => $leaveTypeId])
            ->andFilterWhere(['>=', 'date_start', $dateStart])
            ->andFilterWhere(['<=', 'date_end', $dateEnd]);
        $model = $query->all();

        return [
            'title' => 'ประวัติการลา' . ($thaiYear ? 'ประจำปี ' . $thaiYear : ''),
            'content' => $this->renderAjax('leave_history', ['model' => $model]),
        ];
    }
}
