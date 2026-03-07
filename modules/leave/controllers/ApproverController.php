<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\components\AppHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveSearch;
use app\modules\leave\models\LeaveType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * ผู้ตรวจสอบวันลา — แสดงทะเบียนวันลาทั้งหมด
 */
class ApproverController extends Controller
{
    /**
     * ทะเบียนวันลาทั้งหมด
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าผู้ตรวจสอบวันลา');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/default/index']);
        }

        $status = $this->request->get('status');
        $searchModel = new LeaveSearch();
        $params = $this->request->queryParams;
        if (!isset($params['LeaveSearch']['status']) || $params['LeaveSearch']['status'] === '') {
            $params['LeaveSearch']['status'] = 'Checking2_pass'; // หน.กลุ่มงานเห็นชอบ — default หน้าแรก
        }
        $dataProvider = $searchModel->search($params);
        $query = $dataProvider->query;
        $query->joinWith([
            'employee',
            'leaveType',
            'leaveStatus',
        ]);

        if ($dataProvider->pagination !== false) {
            $dataProvider->pagination->pageSize = 20;
        }

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }

        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }

        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }

        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        // สรุปตามตัวกรอง: ตามสถานะ และตามประเภทการลา (ใช้ query ชุดเดียวกัน)
        $summaryByStatus = (clone $query)->select(['leave.status', 'COUNT(*) as cnt'])->groupBy('leave.status')->asArray()->all();
        $summaryByLeaveType = (clone $query)->select(['leave.leave_type_id', 'COUNT(*) as cnt'])->groupBy('leave.leave_type_id')->asArray()->all();

        $query->with([
            'approves' => function ($q) {
                $q->andWhere(['!=', 'approve.status', 'None'])->orderBy(['level' => SORT_ASC]);
            },
        ]);
        $dataProvider->setSort(['defaultOrder' => [
            'date_start' => SORT_DESC,
        ]]);

        $leaveTypes = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['title' => SORT_ASC])
            ->all();
        $listLeaveType = ArrayHelper::map($leaveTypes, 'code', 'title');
        $allowedColors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
        $leaveTypeColors = [];
        foreach ($leaveTypes as $lt) {
            $data = is_array($lt->data_json) ? $lt->data_json : (is_string($lt->data_json) ? json_decode($lt->data_json, true) : []);
            $c = isset($data['color']) ? $data['color'] : 'info';
            $leaveTypeColors[$lt->code] = in_array($c, $allowedColors, true) ? $c : 'info';
        }
        $listLeaveStatus = (new Leave())->listStatus();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'listLeaveType' => $listLeaveType,
            'listLeaveStatus' => $listLeaveStatus,
            'summaryByStatus' => $summaryByStatus,
            'summaryByLeaveType' => $summaryByLeaveType,
            'leaveTypeColors' => $leaveTypeColors,
        ]);
    }

    /**
     * คืนค่า emp_id ที่อยู่ในหน่วยงานที่กำหนด (ถ้ามี inject จาก config)
     */
    protected function getEmpIdsByDepartment($q_department)
    {
        if (isset(Yii::$app->params['leave.empIdsByDepartment']) && is_callable(Yii::$app->params['leave.empIdsByDepartment'])) {
            return call_user_func(Yii::$app->params['leave.empIdsByDepartment'], $q_department);
        }
        return null;
    }

    /**
     * ส่งออก Excel ทะเบียนวันลา (ใช้ตัวกรองเดียวกับ actionIndex)
     */
    public function actionExport()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ส่งออกข้อมูล');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/approver/index']);
        }

        $status = $this->request->get('status');
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $query->joinWith(['employee', 'leaveType', 'leaveStatus']);
        $dataProvider->pagination = false;

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }
        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }
        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }
        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        $query->orderBy(['leave.date_start' => SORT_DESC]);

        return $this->exportToExcelLeave($dataProvider);
    }

    /**
     * พิมพ์ทะเบียนวันลาเป็น PDF เปิดในแท็บใหม่ (ใช้ตัวกรองเดียวกับ actionIndex)
     */
    public function actionPrint()
    {
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์พิมพ์ข้อมูล');
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/approver/index']);
        }

        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $query->joinWith(['employee', 'leaveType', 'leaveStatus']);
        $dataProvider->pagination = false;

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        $query->andFilterWhere(['>=', 'leave.date_start', $start])
            ->andFilterWhere(['<=', 'leave.date_end', $end]);

        if (!empty($searchModel->leave_type_id)) {
            $query->andFilterWhere(['in', 'leave.leave_type_id', $searchModel->leave_type_id]);
        }
        $status = $this->request->get('status');
        if ($status) {
            $query->andFilterWhere(['leave.status' => $searchModel->status]);
        }
        $position_type_id = $this->request->get('LeaveSearch')['position_type_id'] ?? null;
        if ($position_type_id) {
            $query->andFilterWhere(['employees.position_type' => $position_type_id]);
        }
        if ($searchModel->q_department) {
            $empIds = $this->getEmpIdsByDepartment($searchModel->q_department);
            if ($empIds !== null) {
                $query->andWhere(['in', 'leave.emp_id', $empIds]);
            }
        }
        if (!empty($searchModel->q)) {
            $query->andFilterWhere([
                'or',
                ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ]);
        }

        $query->orderBy(['leave.date_start' => SORT_DESC]);
        $models = $dataProvider->getModels();

        $html = $this->renderPartial('print-pdf', [
            'models' => $models,
            'searchModel' => $searchModel,
        ]);

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ];
        $fontPathTh = Yii::getAlias('@webroot') . '/fonts';
        $ttfR = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew.ttf';
        if (is_dir($fontPathTh) && file_exists($ttfR)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $defaultFont = (new \Mpdf\Config\FontVariables())->getDefaults();
            $config['fontDir'] = array_merge($defaultConfig['fontDir'], [$fontPathTh]);
            $ttfB = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew Bold.ttf';
            $ttfBAlt = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew-Bold.ttf';
            $fontdata = [
                'R' => 'THSarabunNew.ttf',
                'B' => file_exists($ttfB) ? 'THSarabunNew Bold.ttf' : (file_exists($ttfBAlt) ? 'THSarabunNew-Bold.ttf' : 'THSarabunNew.ttf'),
            ];
            $config['fontdata'] = array_merge($defaultFont['fontdata'], [
                'thsarabun' => $fontdata,
            ]);
            $config['default_font'] = 'thsarabun';
        }

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('ทะเบียนวันลา');
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $filename = 'ทะเบียนวันลา_' . date('Y-m-d_His') . '.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
        return Yii::$app->response;
    }

    /**
     * ส่งออก DataProvider (Leave) เป็นไฟล์ Excel
     */
    protected function exportToExcelLeave($dataProvider)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ทะเบียนวันลา');

        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'ผู้ขออนุมัติ',
            'C1' => 'ประเภทการลา',
            'D1' => 'จำนวนวัน',
            'E1' => 'เหตุผลการลา',
            'F1' => 'วันที่เริ่ม',
            'G1' => 'วันที่สิ้นสุด',
            'H1' => 'หน่วยงาน',
            'I1' => 'สถานะใบลา',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $colWidths = ['A' => 8, 'B' => 28, 'C' => 18, 'D' => 10, 'E' => 36, 'F' => 14, 'G' => 14, 'H' => 24, 'I' => 16];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $row = 2;
        foreach ($dataProvider->getModels() as $index => $item) {
            $emp = $item->employee ?? null;
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $emp ? $emp->fullname : '-');
            $sheet->setCellValue('C' . $row, $item->leaveType->title ?? '-');
            $sheet->setCellValue('D' . $row, (float) $item->total_days);
            $dataJson = is_array($item->data_json) ? $item->data_json : (is_string($item->data_json) ? json_decode($item->data_json, true) : []);
            $sheet->setCellValue('E' . $row, isset($dataJson['reason']) ? $dataJson['reason'] : '-');
            $sheet->setCellValue('F' . $row, $item->date_start ? ThaiDateHelper::formatThaiDate($item->date_start) : '-');
            $sheet->setCellValue('G' . $row, $item->date_end ? ThaiDateHelper::formatThaiDate($item->date_end) : '-');
            $sheet->setCellValue('H' . $row, $emp ? $emp->departmentName() : '-');
            $sheet->setCellValue('I' . $row, $item->leaveStatus ? $item->leaveStatus->title : $item->status);
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A1:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ทะเบียนวันลา_' . date('Y-m-d_His') . '.xlsx';
        $filePath = Yii::getAlias('@runtime') . '/' . $filename;
        $writer->save($filePath);

        return Yii::$app->response->sendFile($filePath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ]);
    }
}
