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
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeePosition;
use app\modules\leave\models\Leave;
use app\modules\filemanager\models\Uploads;
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
     * เวลาเข้างานมาตรฐานของคนเวรปกติ (normal) — ใช้เกณฑ์เดียวกับรายงาน export เดิม
     */
    const SHIFT_START_NORMAL = '08:30';

    /**
     * สถานะใบไปราชการ (development) ที่ถือว่า "ได้ไปจริง" — ผ่านการตรวจสอบอย่างน้อยชั้นที่ 1
     * ตัด Pending / Reject / Cancel / Checking (ยังไม่ผ่านชั้นใดเลย) ออก
     */
    const TRIP_STATUSES = ['Approve', 'Pass', 'Checkup_pass', 'Checking2_pass', 'Checking1_pass'];

    /**
     * สรุปการลงเวลารายเดือน (matrix) — บุคลากรปฏิบัติราชการ × วันที่ 1..สิ้นเดือน
     * เลือกเดือน/ปี (พ.ศ.) + กรองกลุ่มงาน/ฝ่ายงาน + สรุปรวมมาสาย + ส่งออก Excel
     * admin/hr เท่านั้น
     */
    public function actionMonthly($month = null, $year = null, $group = null, $unit = null)
    {
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
        }
        $month = $this->clampMonth($month);
        $yearCE = $this->clampYearCE($year);
        $data = $this->buildMonthlyMatrix($month, $yearCE, $group, $unit);

        return $this->render('monthly', array_merge($data, [
            'groups' => $this->orgOptions(1),
            'units' => $this->orgOptions(2),
            'selGroup' => $group !== null && $group !== '' ? (int)$group : null,
            'selUnit' => $unit !== null && $unit !== '' ? (int)$unit : null,
        ]));
    }

    /**
     * ส่งออกสรุปรายเดือน (matrix) เป็น Excel — ใช้ข้อมูลชุดเดียวกับ actionMonthly
     */
    public function actionMonthlyExcel($month = null, $year = null, $group = null, $unit = null)
    {
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr')) {
            throw new \yii\web\ForbiddenHttpException('การส่งออกรายงานสำหรับผู้ดูแลระบบเท่านั้น');
        }
        $month = $this->clampMonth($month);
        $yearCE = $this->clampYearCE($year);
        $data = $this->buildMonthlyMatrix($month, $yearCE, $group, $unit);
        $rows = $data['rows'];
        $days = $data['daysInMonth'];
        $monthName = $data['monthName'];
        $yearBE = $data['yearBE'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปลงเวลา');

        $tripColIdx = 3 + $days + 1;       // รวมไปราชการ
        $leaveColIdx = 3 + $days + 2;      // รวมลา
        $absentColIdx = 3 + $days + 3;     // รวมขาด
        $lastColIdx = 3 + $days + 4;       // รวมสาย
        $tripCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($tripColIdx);
        $leaveCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($leaveColIdx);
        $absentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($absentColIdx);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIdx);

        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->setCellValue('A1', 'สรุปการลงเวลาเข้างาน เดือน' . $monthName . ' พ.ศ. ' . $yearBE);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // header
        $sheet->setCellValue('A2', 'ลำดับ');
        $sheet->setCellValue('B2', 'ชื่อ-นามสกุล');
        $sheet->setCellValue('C2', 'ตำแหน่ง');
        for ($d = 1; $d <= $days; $d++) {
            $sheet->setCellValue([3 + $d, 2], $d);
        }
        $sheet->setCellValue($tripCol . '2', 'รวมไปราชการ');
        $sheet->setCellValue($leaveCol . '2', 'รวมลา');
        $sheet->setCellValue($absentCol . '2', 'รวมขาด');
        $sheet->setCellValue($lastCol . '2', 'รวมสาย');
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $r = 3;
        foreach ($rows as $idx => $row) {
            $sheet->setCellValue('A' . $r, $idx + 1);
            $sheet->setCellValue('B' . $r, $row['name']);
            $sheet->setCellValue('C' . $r, $row['position']);
            for ($d = 1; $d <= $days; $d++) {
                $cell = $row['cells'][$d];
                $col = 3 + $d;
                $val = '';
                switch ($cell['state']) {
                    case 'ontime': $val = $cell['time']; break;
                    case 'late': $val = $cell['time']; break;
                    case 'shift': $val = $cell['time']; break;
                    case 'leave': $val = ($cell['lv']['ab'] ?? 'ล'); break;
                    case 'trip': $val = 'ร'; break;
                    case 'absent': $val = '-'; break;
                    default: $val = ''; break; // weekend / holiday / future / nodata
                }
                $sheet->setCellValue([$col, $r], $val);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                if ($cell['state'] === 'late') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FDE7C7');
                    $sheet->getStyle($colLetter . $r)->getFont()->setBold(true)->getColor()->setRGB('B45309');
                } elseif ($cell['state'] === 'leave') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDE7F6');
                    $sheet->getStyle($colLetter . $r)->getFont()->setBold(true)->getColor()->setRGB('6D28D9');
                } elseif ($cell['state'] === 'trip') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D7F0EC');
                    $sheet->getStyle($colLetter . $r)->getFont()->setBold(true)->getColor()->setRGB('0F766E');
                } elseif ($cell['state'] === 'holiday') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBE4EC');
                } elseif ($cell['state'] === 'weekend') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                }
            }
            $sheet->setCellValue($tripCol . $r, $row['tripCount']);
            if ($row['tripCount'] > 0) {
                $sheet->getStyle($tripCol . $r)->getFont()->setBold(true)->getColor()->setRGB('0F766E');
            }
            $sheet->setCellValue($leaveCol . $r, $row['leaveCount']);
            if ($row['leaveCount'] > 0) {
                $sheet->getStyle($leaveCol . $r)->getFont()->setBold(true)->getColor()->setRGB('6D28D9');
            }
            $sheet->setCellValue($absentCol . $r, $row['absentCount']);
            if ($row['absentCount'] > 0) {
                $sheet->getStyle($absentCol . $r)->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
            }
            $sheet->setCellValue($lastCol . $r, $row['lateCount']);
            if ($row['lateCount'] > 0) {
                $sheet->getStyle($lastCol . $r)->getFont()->setBold(true)->getColor()->setRGB('B45309');
            }
            $r++;
        }
        if ($r > 3) {
            $sheet->getStyle('A2:' . $lastCol . ($r - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getStyle('D3:' . $lastCol . ($r - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(22);
        for ($d = 1; $d <= $days; $d++) {
            $sheet->getColumnDimensionByColumn(3 + $d)->setWidth(6);
        }
        $sheet->getColumnDimension($tripCol)->setWidth(13);
        $sheet->getColumnDimension($leaveCol)->setWidth(9);
        $sheet->getColumnDimension($absentCol)->setWidth(9);
        $sheet->getColumnDimension($lastCol)->setWidth(9);
        $sheet->freezePane('D3');

        $dir = Yii::getAlias('@webroot/downloads');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'attendance-monthly-' . $yearCE . sprintf('%02d', $month) . '-' . date('His') . '.xlsx';
        $filePath = $dir . '/' . $filename;
        (new Xlsx($spreadsheet))->save($filePath);
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath, $filename, ['inline' => false]);
        }
        throw new \yii\web\ServerErrorHttpException('สร้างไฟล์ไม่สำเร็จ');
    }

    /** เดือน 1-12 (default = เดือนปัจจุบัน) */
    private function clampMonth($month): int
    {
        $m = (int)$month;
        return ($m >= 1 && $m <= 12) ? $m : (int)date('n');
    }

    /** ปี ค.ศ. จาก input พ.ศ. (default = ปีปัจจุบัน); รับ พ.ศ. เท่านั้น */
    private function clampYearCE($year): int
    {
        $y = (int)$year;
        if ($y >= 2500 && $y <= 2600) {
            return $y - 543;
        }
        return (int)date('Y');
    }

    /** รายการ organization node ตาม lvl (1 = กลุ่มงาน, 2 = ฝ่าย/หน่วยงาน) => [id => name] */
    private function orgOptions(int $lvl): array
    {
        $out = [];
        try {
            $nodes = Organization::find()->where(['lvl' => $lvl])->orderBy(['lft' => SORT_ASC])->all();
            foreach ($nodes as $n) {
                $out[(int)$n->id] = $n->name;
            }
        } catch (\Throwable $e) {
        }
        return $out;
    }

    /** id ของ node ที่เลือก + ลูกหลานทั้งหมด (nested set) สำหรับกรอง department */
    private function orgSubtreeIds($nodeId): ?array
    {
        $node = Organization::findOne((int)$nodeId);
        if (!$node) {
            return null;
        }
        return Organization::find()
            ->select('id')
            ->where(['root' => $node->root])
            ->andWhere(['>=', 'lft', $node->lft])
            ->andWhere(['<=', 'rgt', $node->rgt])
            ->column();
    }

    /**
     * สร้างข้อมูล matrix สรุปรายเดือน (ใช้ร่วมกันระหว่าง view และ Excel)
     * @return array{rows:array,daysInMonth:int,month:int,monthName:string,yearCE:int,yearBE:int,weekends:array,totalLate:int}
     */
    private function buildMonthlyMatrix(int $month, int $yearCE, $groupId, $unitId): array
    {
        $monthStart = sprintf('%04d-%02d-01 00:00:00', $yearCE, $month);
        $daysInMonth = (int)date('t', strtotime($monthStart));
        $monthEnd = sprintf('%04d-%02d-%02d 23:59:59', $yearCE, $month, $daysInMonth);
        $monthStartDate = sprintf('%04d-%02d-01', $yearCE, $month);
        $monthEndDate = sprintf('%04d-%02d-%02d', $yearCE, $month, $daysInMonth);
        $today = date('Y-m-d');

        // วันหยุดเสาร์-อาทิตย์
        $weekends = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $w = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $yearCE, $month, $d)));
            $weekends[$d] = ($w === 0 || $w === 6);
        }

        // วันหยุดนักขัตฤกษ์ (calendar name='holiday', date_start/date_end = Y-m-d) — day => ชื่อวันหยุด
        $holidays = [];
        try {
            $hs = (new \yii\db\Query())->select(['title', 'date_start', 'date_end'])->from('calendar')
                ->where(['name' => 'holiday'])
                ->andWhere(['deleted_at' => null])
                ->andWhere(['<=', 'date_start', $monthEndDate])
                ->andWhere(['or', ['date_end' => null], ['>=', 'date_end', $monthStartDate]])
                ->all();
            $mLo = strtotime($monthStartDate);
            $mHi = strtotime($monthEndDate);
            foreach ($hs as $h) {
                if (empty($h['date_start'])) {
                    continue;
                }
                $lo = max(strtotime($h['date_start']), $mLo);
                $hi = min(strtotime($h['date_end'] ?: $h['date_start']), $mHi);
                for ($t = $lo; $t <= $hi; $t += 86400) {
                    $holidays[(int)date('j', $t)] = (string)$h['title'];
                }
            }
        } catch (\Throwable $e) {
        }

        // department ที่ต้องกรอง (ฝ่ายชนะกลุ่ม ถ้าเลือกทั้งคู่)
        $deptIds = null;
        if ($unitId !== null && $unitId !== '') {
            $deptIds = $this->orgSubtreeIds($unitId);
        } elseif ($groupId !== null && $groupId !== '') {
            $deptIds = $this->orgSubtreeIds($groupId);
        }

        // ดึงเป็น array ไม่ใช่ ActiveRecord — Employees::afterFind() ทำงานหนักต่อ record
        // (UpdateFormDetail/joinDate/Age ยิง query ต่อคน) รายงานนี้ใช้แค่ 8 คอลัมน์ จึงไม่ต้อง hydrate
        $empQuery = Employees::find()
            ->select(['id', 'prefix', 'fname', 'lname', 'ref', 'work_shift', 'department', 'employee_position_id'])
            ->andWhere(['branch' => 'MAIN', 'status' => '1'])
            ->andWhere(['not', ['id' => 1]]);
        if ($deptIds !== null) {
            $empQuery->andWhere(['department' => $deptIds]);
        }
        $emps = $empQuery->orderBy(['department' => SORT_ASC, 'fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->asArray()->all();
        $empIds = array_map(static fn($e) => (int)$e['id'], $emps);

        // batch lookup ชื่อตำแหน่ง / ชื่อหน่วยงาน (แทนการอ่านผ่าน relation ต่อแถว)
        $posTitles = [];
        try {
            $posIds = array_values(array_unique(array_filter(array_map(static fn($e) => $e['employee_position_id'], $emps))));
            if (!empty($posIds)) {
                foreach (EmployeePosition::find()->select(['id', 'title'])->where(['id' => $posIds])->asArray()->all() as $p) {
                    $posTitles[(int)$p['id']] = (string)$p['title'];
                }
            }
        } catch (\Throwable $e) {
        }
        $deptNames = [];
        try {
            $dIds = array_values(array_unique(array_filter(array_map(static fn($e) => $e['department'], $emps))));
            if (!empty($dIds)) {
                foreach (Organization::find()->select(['id', 'name'])->where(['id' => $dIds])->asArray()->all() as $o) {
                    $deptNames[(int)$o['id']] = (string)$o['name'];
                }
            }
        } catch (\Throwable $e) {
        }

        // batch prefetch avatar (กัน N+1) — ref => upload id
        $avatarByRef = [];
        try {
            $refs = array_values(array_filter(array_map(static fn($e) => $e['ref'], $emps)));
            if (!empty($refs)) {
                $ups = Uploads::find()->select(['id', 'ref'])
                    ->where(['name' => 'avatar'])->andWhere(['ref' => $refs])
                    ->asArray()->all();
                foreach ($ups as $u) {
                    $avatarByRef[$u['ref']] = (int)$u['id'];
                }
            }
        } catch (\Throwable $e) {
        }
        $placeholderAvatar = \Yii::getAlias('@web') . '/img/placeholder_cid.png';

        // วันที่แต่ละคน "เริ่มใช้ระบบลงเวลา" = วันลงเวลาครั้งแรกของคนนั้น (ทุกช่วงเวลา ไม่จำกัดเดือนนี้)
        // ก่อนวันนั้น = ยังไม่มีข้อมูล (no-data) ไม่ใช่ขาดงาน — ตั้ง params['attendanceStartDate'] เพื่อกำหนดวันเริ่มใช้ระดับองค์กรทับได้
        $orgStart = Yii::$app->params['attendanceStartDate'] ?? null;
        $startByEmp = [];
        if (!empty($empIds)) {
            try {
                $firsts = CheckinRecord::find()
                    ->select(['emp_id', 'first_at' => 'MIN(checkin_at)'])
                    ->where(['emp_id' => $empIds])
                    ->andWhere(['<>', 'status', CheckinRecord::STATUS_REJECTED])
                    ->groupBy(['emp_id'])
                    ->asArray()->all();
                foreach ($firsts as $f) {
                    $d = substr((string)$f['first_at'], 0, 10);
                    $startByEmp[(int)$f['emp_id']] = ($orgStart && $orgStart > $d) ? $orgStart : $d;
                }
            } catch (\Throwable $e) {
            }
        }

        // prefetch การลงเวลา (เข้า) ของทั้งเดือน — เก็บเวลาเข้าเร็วสุดต่อวัน
        $map = [];
        if (!empty($empIds)) {
            $recs = CheckinRecord::find()
                ->select(['emp_id', 'checkin_at'])
                ->where(['check_type' => CheckinRecord::CHECK_TYPE_IN])
                ->andWhere(['emp_id' => $empIds])
                ->andWhere(['between', 'checkin_at', $monthStart, $monthEnd])
                ->andWhere(['<>', 'status', CheckinRecord::STATUS_REJECTED])
                ->orderBy(['checkin_at' => SORT_ASC])
                ->asArray()->all();
            foreach ($recs as $rec) {
                $ts = strtotime($rec['checkin_at']);
                $d = (int)date('j', $ts);
                $eid = (int)$rec['emp_id'];
                if (!isset($map[$eid][$d])) {
                    $map[$eid][$d] = date('H:i', $ts); // เร็วสุดของวัน (ordered ASC)
                }
            }
        }

        // map ประเภทการลา code => [ตัวย่อ, ชื่อเต็ม] (ย่อจาก title ด้วย keyword)
        $leaveTypeAbbr = [];
        $leaveTypeTitle = [];
        try {
            $lts = (new \yii\db\Query())->select(['code', 'title'])->from('categorise')
                ->where(['name' => 'leave_type'])->all();
            foreach ($lts as $lt) {
                $title = (string)$lt['title'];
                $leaveTypeTitle[$lt['code']] = $title;
                $ab = 'ล';
                if (mb_strpos($title, 'ป่วย') !== false) {
                    $ab = 'ป';
                } elseif (mb_strpos($title, 'กิจ') !== false) {
                    $ab = 'ก';
                } elseif (mb_strpos($title, 'พักผ่อน') !== false) {
                    $ab = 'พ';
                } elseif (mb_strpos($title, 'คลอด') !== false) {
                    $ab = 'ค';
                }
                $leaveTypeAbbr[$lt['code']] = $ab;
            }
        } catch (\Throwable $e) {
        }

        // prefetch ใบลาที่อนุมัติแล้ว (status = Approve, ยังไม่ถูกลบ) — map เป็นรายวัน + ประเภท
        $leaveMap = [];
        if (!empty($empIds)) {
            try {
                $leaves = Leave::find()
                    ->select(['emp_id', 'date_start', 'date_end', 'leave_type_id', 'total_days', 'data_json'])
                    ->where(['status' => 'Approve'])
                    ->andWhere(['emp_id' => $empIds])
                    ->andWhere(['deleted_at' => null])
                    ->andWhere(['<=', 'date_start', $monthEndDate])
                    ->andWhere(['>=', 'date_end', $monthStartDate])
                    ->asArray()->all();
                $mLo = strtotime($monthStartDate);
                $mHi = strtotime($monthEndDate);
                foreach ($leaves as $lv) {
                    if (empty($lv['date_start'])) {
                        continue;
                    }
                    $code = (string)($lv['leave_type_id'] ?? '');
                    $reason = '';
                    $raw = $lv['data_json'] ?? null;
                    $json = is_array($raw) ? $raw : (is_string($raw) && $raw !== '' ? json_decode($raw, true) : null);
                    if (is_array($json) && !empty($json['reason'])) {
                        $reason = (string)$json['reason'];
                    }
                    $info = [
                        'ab' => $leaveTypeAbbr[$code] ?? 'ล',
                        'title' => $leaveTypeTitle[$code] ?? 'ลา',
                        'from' => (string)$lv['date_start'],
                        'to' => (string)($lv['date_end'] ?: $lv['date_start']),
                        'days' => $lv['total_days'] !== null ? (float)$lv['total_days'] : null,
                        'reason' => $reason,
                    ];
                    $lo = max(strtotime($lv['date_start']), $mLo);
                    $hi = min(strtotime($lv['date_end'] ?: $lv['date_start']), $mHi);
                    for ($t = $lo; $t <= $hi; $t += 86400) {
                        $leaveMap[(int)$lv['emp_id']][(int)date('j', $t)] = $info;
                    }
                }
            } catch (\Throwable $e) {
                // ตาราง leave ยังไม่มี / โครงสร้างต่าง — ข้ามการแสดงลา
            }
        }

        // prefetch การไปราชการ (development + development_detail name='member') — map เป็นรายวัน
        // ช่วงวันที่ยึด vehicle_date_* (วันเดินทาง) และ fallback เป็น date_* ถ้าไม่ได้ระบุรถ
        $tripMap = [];
        if (!empty($empIds)) {
            try {
                $dsExpr = 'COALESCE(d.vehicle_date_start, d.date_start)';
                $deExpr = 'COALESCE(d.vehicle_date_end, d.date_end, d.vehicle_date_start, d.date_start)';
                $trips = (new \yii\db\Query())
                    ->select(['emp_id' => 'dd.emp_id', 'topic' => 'd.topic', 'status' => 'd.status', 'ds' => $dsExpr, 'de' => $deExpr])
                    ->from(['dd' => 'development_detail'])
                    ->innerJoin(['d' => 'development'], 'd.id = dd.development_id')
                    ->where(['dd.name' => 'member'])
                    ->andWhere(['dd.deleted_at' => null])
                    ->andWhere(['d.deleted_at' => null])
                    ->andWhere(['d.status' => self::TRIP_STATUSES])
                    ->andWhere(['dd.emp_id' => array_map('strval', $empIds)])
                    ->andWhere(['<=', $dsExpr, $monthEndDate])
                    ->andWhere(['>=', $deExpr, $monthStartDate])
                    ->all();
                $mLo = strtotime($monthStartDate);
                $mHi = strtotime($monthEndDate);
                foreach ($trips as $tp) {
                    if (empty($tp['ds'])) {
                        continue;
                    }
                    $info = [
                        'topic' => (string)$tp['topic'],
                        'from' => (string)$tp['ds'],
                        'to' => (string)($tp['de'] ?: $tp['ds']),
                        'status' => (string)$tp['status'],
                    ];
                    $eid = (int)$tp['emp_id'];
                    $lo = max(strtotime($tp['ds']), $mLo);
                    $hi = min(strtotime($tp['de'] ?: $tp['ds']), $mHi);
                    for ($t = $lo; $t <= $hi; $t += 86400) {
                        $tripMap[$eid][(int)date('j', $t)][] = $info;
                    }
                }
            } catch (\Throwable $e) {
                // ตาราง development ยังไม่มี / โครงสร้างต่าง — ข้ามการแสดงไปราชการ
            }
        }

        $rows = [];
        $totalLate = 0;
        $totalLeave = 0;
        $totalTrip = 0;
        $totalAbsent = 0;
        $coveredCount = 0;                         // จำนวนคนที่เริ่มใช้ระบบแล้ว (มีวันเริ่มนับในหรือก่อนเดือนนี้)
        $dayLate = array_fill(1, $daysInMonth, 0); // ยอดรวมต่อวัน (แถวท้ายตาราง)
        $dayAbsent = array_fill(1, $daysInMonth, 0);
        foreach ($emps as $emp) {
            $empId = (int)$emp['id'];
            $shift = $emp['work_shift'] ?: 'normal';
            $empStart = $startByEmp[$empId] ?? null;   // null = ยังไม่เคยลงเวลาเลย
            if ($empStart !== null && $empStart <= $monthEndDate) {
                $coveredCount++;
            }
            $cells = [];
            $lateCount = 0;
            $leaveCount = 0;
            $tripCount = 0;
            $absentCount = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $yearCE, $month, $d);
                $time = $map[$empId][$d] ?? null;
                $lv = $leaveMap[$empId][$d] ?? null;
                $tp = $tripMap[$empId][$d] ?? null;
                if ($tp !== null && !$weekends[$d] && !isset($holidays[$d])) {
                    $tripCount++;
                }
                if ($time !== null) {
                    if ($shift === 'shift') {
                        $state = 'shift'; // เวรหมุน — ไม่ประเมินสาย
                    } else {
                        $state = ($time > self::SHIFT_START_NORMAL) ? 'late' : 'ontime';
                        if ($state === 'late') {
                            $lateCount++;
                            $dayLate[$d]++;
                        }
                    }
                } else {
                    if ($weekends[$d]) {
                        $state = 'weekend';
                    } elseif (isset($holidays[$d])) {
                        $state = 'holiday'; // วันหยุดนักขัตฤกษ์ — ไม่นับขาด
                    } elseif ($lv !== null) {
                        $state = 'leave';
                        $leaveCount++;
                    } elseif ($tp !== null) {
                        $state = 'trip'; // ไปราชการ — ไม่นับขาด
                    } elseif ($dateStr > $today) {
                        $state = 'future';
                    } elseif ($empStart === null || $dateStr < $empStart) {
                        $state = 'nodata'; // ยังไม่เริ่มใช้ระบบลงเวลา — ไม่ใช่ขาดงาน จึงไม่นับ
                    } else {
                        $state = 'absent';
                        $absentCount++;
                        $dayAbsent[$d]++;
                    }
                }
                $cells[$d] = ['state' => $state, 'time' => $time, 'lv' => $lv, 'trip' => $tp];
            }
            $pos = $posTitles[(int)$emp['employee_position_id']] ?? '';
            $avatar = (!empty($emp['ref']) && isset($avatarByRef[$emp['ref']]))
                ? \yii\helpers\Url::to(['/filemanager/uploads/get-image', 'id' => $avatarByRef[$emp['ref']]])
                : $placeholderAvatar;
            $totalLate += $lateCount;
            $totalLeave += $leaveCount;
            $totalTrip += $tripCount;
            $totalAbsent += $absentCount;
            $rows[] = [
                'id' => $empId,
                'name' => trim($emp['fname'] . ' ' . $emp['lname']),
                'position' => $pos,
                'avatar' => $avatar,
                'dept' => $deptNames[(int)$emp['department']] ?? 'ไม่ระบุ',
                'shift' => $shift,
                'cells' => $cells,
                'lateCount' => $lateCount,
                'leaveCount' => $leaveCount,
                'tripCount' => $tripCount,
                'absentCount' => $absentCount,
                'covered' => $empStart !== null && $empStart <= $monthEndDate,
            ];
        }

        $thaiMonths = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        return [
            'rows' => $rows,
            'daysInMonth' => $daysInMonth,
            'month' => $month,
            'monthName' => $thaiMonths[$month] ?? '',
            'yearCE' => $yearCE,
            'yearBE' => $yearCE + 543,
            'weekends' => $weekends,
            'holidays' => $holidays,
            'totalLate' => $totalLate,
            'totalLeave' => $totalLeave,
            'totalTrip' => $totalTrip,
            'totalAbsent' => $totalAbsent,
            'coveredCount' => $coveredCount,
            'dayLate' => $dayLate,
            'dayAbsent' => $dayAbsent,
            'shiftStart' => self::SHIFT_START_NORMAL,
        ];
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
