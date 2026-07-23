<?php

namespace app\modules\attendance\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use app\components\UserHelper;
use app\modules\attendance\models\CheckinRecord;
use app\modules\attendance\models\CheckinRecordSearch;
use app\modules\hr\models\Employees;
use app\modules\approveV2\models\Approve;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CheckinController extends Controller
{
    /**
     * ประวัติการลงเวลาของฉัน — แสดงเฉพาะของผู้ใช้ที่ล็อกอินเสมอ
     * (ผู้ดูแลระบบดูของทั้งหน่วยงานที่หน้า report)
     */
    public function actionIndex()
    {
        $searchModel = new CheckinRecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $me = UserHelper::GetEmployee();
        if ($me) {
            $dataProvider->query->andWhere(['checkin_record.emp_id' => $me->id]);
        } else {
            $dataProvider->query->andWhere('1=0'); // ไม่พบพนักงาน → ไม่แสดงของใคร
        }
        $isAdminOrHr = Yii::$app->user->can('admin') || Yii::$app->user->can('hr');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isAdminOrHr' => $isAdminOrHr,
            'me' => $me,
        ]);
    }

    public function actionView($id)
    {
        $model = CheckinRecord::find()->where(['id' => $id])->with(['employee', 'location', 'approver'])->one();
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('ไม่พบรายการ');
        }
        $me = UserHelper::GetEmployee();
        if ($me && $model->emp_id != $me->id && !Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์ดูรายการนี้');
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = 'รายละเอียดการลงเวลา';
            if ($model->employee) {
                $title .= ' — ' . $model->employee->fname . ' ' . $model->employee->lname;
            }
            $title .= ' #' . $model->id;
            return [
                'title' => $title,
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * แก้ไขรายการลงเวลา (admin/hr เท่านั้น)
     */
    public function actionUpdate($id)
    {
        $model = CheckinRecord::find()->where(['id' => $id])->with(['employee'])->one();
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('ไม่พบรายการ');
        }
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์แก้ไข');
        }
        if ($model->load(Yii::$app->request->post())) {
            $raw = $model->checkin_at;
            if (is_string($raw) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $raw)) {
                $model->checkin_at = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', substr($raw, 0, 16))));
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขแล้ว');
                return $this->redirect(['/attendance/default/index']);
            }
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * ลบรายการลงเวลา (admin/hr เท่านั้น, POST เท่านั้น)
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException('อนุญาตเฉพาะ POST');
        }
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์ลบ');
        }
        $model = CheckinRecord::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('ไม่พบรายการ');
        }
        Approve::deleteAll(['name' => 'checkin', 'from_id' => (string)$id]);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการลงเวลาแล้ว');
        return $this->redirect(Yii::$app->request->referrer ?: ['/attendance/default/index']);
    }

    /**
     * นำเข้าข้อมูลจาก CSV
     * รูปแบบ: emp_id หรือ code, checkin_at (Y-m-d H:i:s), method (qrcode/photo/manual), lat, lng, out_of_location_reason
     */
    public function actionImportCsv()
    {
        $saved = 0;
        $errors = [];
        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file) {
            return $this->render('import-csv', ['saved' => 0, 'errors' => ['กรุณาเลือกไฟล์ CSV']]);
        }
        $handle = fopen($file->tempName, 'r');
        if (!$handle) {
            return $this->render('import-csv', ['saved' => 0, 'errors' => ['เปิดไฟล์ไม่ได้']]);
        }
        $header = fgetcsv($handle);
        $lineNo = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            if (count($row) < 2) {
                continue;
            }
            $empIdOrCode = trim($row[0] ?? '');
            $checkinAt = trim($row[1] ?? '');
            $method = isset($row[2]) ? trim($row[2]) : CheckinRecord::METHOD_MANUAL;
            if (!in_array($method, [CheckinRecord::METHOD_QRCODE, CheckinRecord::METHOD_PHOTO, CheckinRecord::METHOD_MANUAL], true)) {
                $method = CheckinRecord::METHOD_MANUAL;
            }
            $lat = isset($row[3]) ? trim($row[3]) : null;
            $lng = isset($row[4]) ? trim($row[4]) : null;
            $outReason = isset($row[5]) ? trim($row[5]) : null;
            $emp = null;
            if (is_numeric($empIdOrCode)) {
                $emp = Employees::findOne((int)$empIdOrCode);
            }
            if (!$emp) {
                $emp = Employees::findOne(['cid' => $empIdOrCode]);
            }
            if (!$emp) {
                $errors[] = "บรรทัด {$lineNo}: ไม่พบพนักงาน {$empIdOrCode}";
                continue;
            }
            $checkinAtTs = strtotime($checkinAt);
            if (!$checkinAtTs) {
                $errors[] = "บรรทัด {$lineNo}: รูปแบบวันเวลาไม่ถูกต้อง {$checkinAt}";
                continue;
            }
            $record = new CheckinRecord();
            $record->emp_id = $emp->id;
            $record->checkin_at = date('Y-m-d H:i:s', $checkinAtTs);
            $record->method = $method;
            $record->check_type = isset($row[6]) && in_array(trim($row[6]), ['in', 'out'], true) ? trim($row[6]) : CheckinRecord::CHECK_TYPE_IN;
            $record->lat = $lat ?: null;
            $record->lng = $lng ?: null;
            $record->is_in_location = ($outReason === '' || $outReason === null) ? 1 : 0;
            $record->out_of_location_reason = $record->is_in_location ? null : $outReason;
            $record->status = CheckinRecord::STATUS_PENDING;
            if ($record->save()) {
                $record->createApproveRecord();
                $saved++;
            } else {
                $errors[] = "บรรทัด {$lineNo}: " . implode(', ', $record->getFirstErrors());
            }
        }
        fclose($handle);
        return $this->render('import-csv', [
            'saved' => $saved,
            'errors' => $errors,
            'lineNo' => $lineNo - 1,
        ]);
    }

    public function actionImportForm()
    {
        return $this->render('import-csv', ['saved' => null, 'errors' => []]);
    }

    /**
     * รายงานการเข้างาน — ฟิลเตอร์ + ตาราง + ส่งออก Excel
     */
    public function actionReport()
    {
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('หน้านี้สำหรับผู้ดูแลระบบเท่านั้น ดูประวัติของคุณได้ที่หน้าประวัติการลงเวลา');
        }
        $searchModel = new CheckinRecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = ['pageSize' => 50];
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ส่งออกรายงานการเข้างานเป็น Excel (ใช้ filter เดียวกับ report)
     */
    public function actionExportExcel()
    {
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('การส่งออกรายงานสำหรับผู้ดูแลระบบเท่านั้น');
        }
        $searchModel = new CheckinRecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        $defaultShiftStart = '08:30';
        $formatLate = function ($checkinAt) use ($defaultShiftStart) {
            if (!$checkinAt) return '-';
            $t = is_string($checkinAt) ? strtotime($checkinAt) : $checkinAt;
            $start = date('Y-m-d', $t) . ' ' . $defaultShiftStart . ':00';
            $startTs = strtotime($start);
            if ($t <= $startTs) return '-';
            $diff = $t - $startTs;
            $h = floor($diff / 3600);
            $m = (int)(($diff % 3600) / 60);
            if ($h > 0 && $m > 0) return $h . ' ชม. ' . $m . ' น.';
            if ($h > 0) return $h . ' ชม.';
            return $m . ' น.';
        };

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานการเข้างาน');

        $dateStart = $searchModel->date_start ?: date('Y-m-d', strtotime('-1 month'));
        $dateEnd = $searchModel->date_end ?: date('Y-m-d');
        $title = 'รายงานการเข้างาน ระหว่าง ' . $dateStart . ' ถึง ' . $dateEnd;
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A' => 'ลำดับ',
            'B' => 'วันที่',
            'C' => 'เวลา',
            'D' => 'ชื่อ-นามสกุล',
            'E' => 'หน่วยงาน',
            'F' => 'ประเภทการลง',
            'G' => 'ประเภทเวร',
            'H' => 'ชื่อเวร',
            'I' => 'เวลาเวร',
            'J' => 'สาย',
            'K' => 'ออกก่อน',
            'L' => 'รูปภาพ',
            'M' => 'สถานะ',
            'N' => 'ผู้อนุมัติ',
            'O' => 'อนุมัติเมื่อ',
        ];
        $row = 2;
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $row, $label);
        }
        $sheet->getStyle('A2:O2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        $row = 3;
        foreach ($models as $idx => $item) {
            $emp = $item->employee;
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item->checkin_at ? date('d/m/Y', strtotime($item->checkin_at)) : '-');
            $sheet->setCellValue('C' . $row, $item->checkin_at ? date('H:i', strtotime($item->checkin_at)) : '-');
            $sheet->setCellValue('D' . $row, $emp ? ($emp->fname . ' ' . $emp->lname) : '-');
            $sheet->setCellValue('E' . $row, $emp ? $emp->departmentName() : '-');
            $sheet->setCellValue('F' . $row, $item->getCheckTypeLabel());
            $sheet->setCellValue('G' . $row, $emp && method_exists($emp, 'viewWorkType') ? ($emp->viewWorkType() ?: '-') : '-');
            $sheet->setCellValue('H' . $row, $emp && !empty($emp->work_shift) ? ($emp->work_shift === 'normal' ? 'ปกติ' : 'เวร') : '-');
            $sheet->setCellValue('I' . $row, '08:30-16:30');
            $sheet->setCellValue('J' . $row, $formatLate($item->checkin_at));
            $sheet->setCellValue('K' . $row, '-');
            $sheet->setCellValue('L' . $row, !empty($item->photo_path) ? 'มี' : '-');
            $sheet->setCellValue('M' . $row, $item->getStatusLabel());
            $approverName = $item->approver ? ($item->approver->fname . ' ' . $item->approver->lname) : '-';
            $sheet->setCellValue('N' . $row, $approverName);
            $sheet->setCellValue('O' . $row, $item->approved_at ? date('d/m/Y H:i', strtotime($item->approved_at)) : '-');
            $row++;
        }
        if ($row > 3) {
            $sheet->getStyle('A3:O' . ($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $dir = Yii::getAlias('@webroot/downloads');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'report-attendance-' . date('Ymd-His') . '.xlsx';
        $filePath = $dir . '/' . $filename;
        (new Xlsx($spreadsheet))->save($filePath);
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath, $filename, ['inline' => false]);
        }
        throw new \yii\web\ServerErrorHttpException('สร้างไฟล์ไม่สำเร็จ');
    }
}
