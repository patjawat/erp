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

        $lastColIdx = 3 + $days + 1; // ลำดับ, ชื่อ, ตำแหน่ง, วัน 1..N, รวมสาย
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
            $sheet->setCellValueByColumnAndRow(3 + $d, 2, $d);
        }
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
                    case 'absent': $val = '-'; break;
                    default: $val = ''; break; // weekend / future
                }
                $sheet->setCellValueByColumnAndRow($col, $r, $val);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                if ($cell['state'] === 'late') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FDE7C7');
                    $sheet->getStyle($colLetter . $r)->getFont()->setBold(true)->getColor()->setRGB('B45309');
                } elseif ($cell['state'] === 'weekend') {
                    $sheet->getStyle($colLetter . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                }
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
        $today = date('Y-m-d');

        // วันหยุดเสาร์-อาทิตย์
        $weekends = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $w = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $yearCE, $month, $d)));
            $weekends[$d] = ($w === 0 || $w === 6);
        }

        // department ที่ต้องกรอง (ฝ่ายชนะกลุ่ม ถ้าเลือกทั้งคู่)
        $deptIds = null;
        if ($unitId !== null && $unitId !== '') {
            $deptIds = $this->orgSubtreeIds($unitId);
        } elseif ($groupId !== null && $groupId !== '') {
            $deptIds = $this->orgSubtreeIds($groupId);
        }

        $empQuery = Employees::find()
            ->andWhere(['branch' => 'MAIN', 'status' => '1'])
            ->andWhere(['not', ['id' => 1]]);
        if ($deptIds !== null) {
            $empQuery->andWhere(['department' => $deptIds]);
        }
        $emps = $empQuery->orderBy(['department' => SORT_ASC, 'fname' => SORT_ASC, 'lname' => SORT_ASC])->all();
        $empIds = array_map(static fn($e) => (int)$e->id, $emps);

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

        $rows = [];
        $totalLate = 0;
        foreach ($emps as $emp) {
            $shift = $emp->work_shift ?: 'normal';
            $cells = [];
            $lateCount = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $yearCE, $month, $d);
                $time = $map[(int)$emp->id][$d] ?? null;
                if ($time !== null) {
                    if ($shift === 'shift') {
                        $state = 'shift'; // เวรหมุน — ไม่ประเมินสาย
                    } else {
                        $state = ($time > self::SHIFT_START_NORMAL) ? 'late' : 'ontime';
                        if ($state === 'late') {
                            $lateCount++;
                        }
                    }
                } else {
                    if ($weekends[$d]) {
                        $state = 'weekend';
                    } elseif ($dateStr > $today) {
                        $state = 'future';
                    } else {
                        $state = 'absent';
                    }
                }
                $cells[$d] = ['state' => $state, 'time' => $time];
            }
            $pos = '';
            try {
                $pos = $emp->positionName ? (string)$emp->positionName->title : '';
            } catch (\Throwable $e) {
            }
            $totalLate += $lateCount;
            $rows[] = [
                'id' => (int)$emp->id,
                'name' => trim($emp->fname . ' ' . $emp->lname),
                'position' => $pos,
                'dept' => $emp->departmentName(),
                'shift' => $shift,
                'cells' => $cells,
                'lateCount' => $lateCount,
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
            'totalLate' => $totalLate,
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
