<?php

namespace app\modules\booking\controllers;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use yii\web\Response;
use yii\web\Controller;
use yii\db\Query;
use app\models\Categorise;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\models\VehicleSearch;
use app\modules\booking\models\VehicleDetailSearch;
use app\modules\booking\components\VehicleTelegramNotify;
use app\modules\pdfTemplate\models\PdfTemplate;
use app\modules\pdfTemplate\services\PdfTemplateService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * VehicleController implements the CRUD actions for Vehicle model.
 */
class VehicleController extends Controller
{
    /**
     * @inheritDoc
     */
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
     * Lists all Vehicle models.
     *
     * @return string
     */
    public function actionDashboard($date = null)
    {
        $params = $this->request->queryParams;
        $searchModel = new VehicleSearch();

        // ค่าเริ่มต้น: ปีงบประมาณปัจจุบัน (ถ้าผู้ใช้ไม่ได้ส่ง filter มา)
        if (!isset($params['VehicleSearch']['thai_year'])) {
            $searchModel->thai_year = AppHelper::YearBudget();
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ตารางการใช้รถยนต์รายคันในช่วง 06:00-18:00 ของวันที่ระบุ
     */
    public function actionSchedule($date = null, $type = null)
    {
        $todayIso = date('Y-m-d');
        $tomorrowIso = date('Y-m-d', strtotime('+1 day'));

        $targetIso = $todayIso;
        if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $targetIso = $date;
        }

        // ประเภทรถที่อนุญาตให้ filter
        $allowedTypes = ['official', 'ambulance'];
        $activeType = in_array($type, $allowedTypes, true) ? $type : null;

        // เวลาเริ่ม-สิ้นสุดของหน้าต่าง schedule
        $timelineStartMin = 6 * 60;
        $timelineEndMin = 18 * 60;
        $timelineSpan = $timelineEndMin - $timelineStartMin;

        $parseTime = function ($t) {
            if (preg_match('/^(\d{1,2}):(\d{2})/', (string) $t, $m)) {
                return ((int) $m[1]) * 60 + ((int) $m[2]);
            }
            return null;
        };

        // === STEP 1: ดึง Asset ของรถที่จะแสดง ===
        // - official (รถยนต์ทั่วไป): asset_type_id='VEH' AND asset_category_id='TV'
        // - ambulance (รถฉุกเฉิน):  asset_type_id='VEH' AND asset_category_id='VM'
        // - null (ทั้งหมด):         asset_type_id='VEH'
        $assetQuery = Asset::find()
            ->where(['asset_type_id' => 'VEH'])
            ->andWhere(['IS NOT', 'license_plate', null])
            ->andWhere(['<>', 'license_plate', ''])
            ->andWhere(['deleted_at' => null])
            ->andWhere([
                'or',
                ['lifecycle_status' => null],
                ['<>', 'lifecycle_status', Asset::LIFECYCLE_DISPOSED],
            ]);

        if ($activeType === 'official') {
            $assetQuery->andWhere(['asset_category_id' => 'TV']);
        } elseif ($activeType === 'ambulance') {
            $assetQuery->andWhere(['asset_category_id' => 'VM']);
        }

        $assets = $assetQuery
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();

        // === STEP 2: ดึง booking ของวันนี้สำหรับรถเหล่านี้ ===
        $plates = array_values(array_filter(array_map(fn($a) => $a->license_plate, $assets)));
        $bookingsByLicense = [];

        if (!empty($plates)) {
            $details = VehicleDetail::find()
                ->joinWith('vehicle')
                ->andFilterWhere(['vehicle_detail.date_start' => $targetIso])
                ->andWhere(['vehicle_detail.license_plate' => $plates])
                ->andFilterWhere(['NOT IN', 'vehicle.status', ['Cancel']])
                ->orderBy(['vehicle_detail.time_start' => SORT_ASC])
                ->all();

            foreach ($details as $d) {
                $plate = trim((string) ($d->license_plate ?: $d->vehicle?->license_plate));
                if ($plate === '') {
                    continue;
                }
                $bookingsByLicense[$plate][] = $d;
            }
        }

        // === STEP 3: Preload รูปรถทั้งหมดด้วย query เดียว (แก้ N+1 จาก showImg() แต่ละ row) ===
        $assetImages = [];
        $refs = array_values(array_filter(array_map(fn($a) => $a->ref, $assets)));
        if (!empty($refs)) {
            $uploads = Uploads::find()
                ->where(['ref' => $refs, 'name' => 'asset'])
                ->all();
            foreach ($uploads as $up) {
                // เก็บ id ของ upload ตัวแรกของแต่ละ ref
                if (!isset($assetImages[$up->ref])) {
                    $assetImages[$up->ref] = FileManagerHelper::getImg($up->id);
                }
            }
        }

        // === STEP 4: สร้าง vehicles list สำหรับ rendering (1 Asset = 1 row) ===
        $vehicles = [];
        foreach ($assets as $a) {
            $vehicles[] = ['asset' => $a, 'row_key' => $a->license_plate];
        }

        $rows = [];
        $fmtClock = fn(int $m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        foreach ($vehicles as $entry) {
            $v = $entry['asset'];
            $rowKey = $entry['row_key'];
            $isMaintenance = ($v->lifecycle_status === Asset::LIFECYCLE_REPAIR);
            $vehicleBookings = $bookingsByLicense[$rowKey] ?? [];

            $blocks = [];
            foreach ($vehicleBookings as $d) {
                // ใช้เวลาของ vehicle_detail ก่อน fallback เป็นของ vehicle (parent booking)
                // เพราะ user กรอกเวลาผ่านฟอร์มใบจอง (Vehicle) เป็นหลัก vehicle_detail
                // อาจถูกสร้างหลัง assign และอาจ inherit เวลาเดียวกัน
                $timeStartStr = !empty($d->time_start) ? $d->time_start : ($d->vehicle?->time_start ?? null);
                $timeEndStr = !empty($d->time_end) ? $d->time_end : ($d->vehicle?->time_end ?? null);

                $startMin = $parseTime($timeStartStr);
                $endMin = $parseTime($timeEndStr);
                if ($startMin === null || $endMin === null) {
                    // ถ้าทั้ง detail และ parent ไม่มีเวลา ถือเป็นทั้งวัน
                    $startMin = $timelineStartMin;
                    $endMin = $timelineEndMin;
                }
                $clampedStart = max($timelineStartMin, min($timelineEndMin, $startMin));
                $clampedEnd = max($timelineStartMin, min($timelineEndMin, $endMin));
                if ($clampedEnd <= $clampedStart) {
                    continue;
                }
                $startLabel = !empty($timeStartStr) ? substr((string) $timeStartStr, 0, 5) : $fmtClock($clampedStart);
                $endLabel = !empty($timeEndStr) ? substr((string) $timeEndStr, 0, 5) : $fmtClock($clampedEnd);
                $driverName = trim((string) ($d->driver?->fullname ?? ''));
                $blocks[] = [
                    'left' => ($clampedStart - $timelineStartMin) / $timelineSpan * 100,
                    'width' => ($clampedEnd - $clampedStart) / $timelineSpan * 100,
                    'label' => sprintf('%s-%s', $startLabel, $endLabel),
                    'reason' => trim((string) ($d->vehicle?->reason ?? '')),
                    'location' => trim((string) ($d->vehicle?->locationOrg?->title ?: ($d->vehicle?->location ?? ''))),
                    'driver' => $driverName,
                    'detail_id' => $d->id,
                    'vehicle_id' => $d->vehicle_id,
                ];
            }

            $rows[] = [
                'asset' => $v,
                'is_maintenance' => $isMaintenance,
                'booking_count' => count($vehicleBookings),
                'blocks' => $blocks,
            ];
        }

        return $this->render('schedule', [
            'rows' => $rows,
            'targetIso' => $targetIso,
            'todayIso' => $todayIso,
            'tomorrowIso' => $tomorrowIso,
            'timelineStartMin' => $timelineStartMin,
            'timelineEndMin' => $timelineEndMin,
            'activeType' => $activeType,
            'assetImages' => $assetImages,
        ]);
    }

    /**
     * แสดงรายการภารกิจของพนักงานขับรถใน modal
     */
    public function actionDriverWork($driver_id)
    {
        $thaiYear = $this->request->get('thai_year');

        $query = Vehicle::find()
            ->where(['driver_id' => $driver_id])
            ->andWhere(['IN', 'status', ['Approve', 'Pass', 'Success']])
            ->andFilterWhere(['thai_year' => $thaiYear])
            ->orderBy(['date_start' => SORT_DESC, 'time_start' => SORT_DESC]);

        $trips = $query->all();
        $driver = !empty($trips) ? $trips[0]->driver : null;
        $driverName = $driver?->fullname ?: 'พนักงานขับรถ #' . $driver_id;

        \Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => '<i class="fa-solid fa-user-tie"></i> รายการภารกิจ — ' . $driverName,
            'content' => $this->renderAjax('_driver_work', [
                'trips' => $trips,
                'driverName' => $driverName,
            ]),
        ];
    }

    public function actionIndex()
    {
        $type = 'official';
        $searchModel = new VehicleSearch([
            'vehicle_type_id' => $type
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;

        // ลด N+1: preload relations ที่ใช้ใน list.php
        $query->with([
            'employee',
            'locationOrg',
            'vehicleStatus',
            'vehicleDetails.driver',
            'vehicleDetails.car',
        ]);

        // select เฉพาะ field ที่ใช้จริงบนหน้ารายการ
        $query->select([
            'id',
            'code',
            'vehicle_type_id',
            'urgent',
            'location',
            'reason',
            'status',
            'date_start',
            'time_start',
            'date_end',
            'time_end',
            'go_type',
            'emp_id',
            'is_shared',
            'created_at',
        ]);

        $q = trim((string) $searchModel->q);
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'code', $q],
                ['like', 'reason', $q],
            ]);
        }


        $query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)]);
        $query->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        // Filter: ยังไม่บันทึกการเดินทาง (distance_km/oil_price/oil_liter ยังไม่มี > 0)
        if ((string) $searchModel->not_logged === '1') {
            $recordedExistsSubQuery = (new Query())
                ->select(new \yii\db\Expression('1'))
                ->from('vehicle_detail vd')
                ->where('vd.vehicle_id = vehicle.id')
                ->andWhere([
                    'or',
                    ['and', ['IS NOT', 'vd.distance_km', null], ['>', 'vd.distance_km', 0]],
                    ['and', ['IS NOT', 'vd.oil_price', null], ['>', 'vd.oil_price', 0]],
                    ['and', ['IS NOT', 'vd.oil_liter', null], ['>', 'vd.oil_liter', 0]],
                ]);

            $query->andWhere(['not exists', $recordedExistsSubQuery]);
        }

        $query->orderBy([
            'date_start' => SORT_DESC,
            'location' =>  SORT_DESC,
        ]);

        // สรุปสถานะสำหรับงานจัดสรร (ใช้ query เดียวกับรายการหลัก)
        $summaryBaseQuery = clone $query;

        $statusSummary = (clone $summaryBaseQuery)
            ->select(['status', 'COUNT(*) AS total'])
            ->groupBy(['status'])
            ->asArray()
            ->all();

        $assignedExistsSubQuery = (new Query())
            ->select(new \yii\db\Expression('1'))
            ->from('vehicle_detail vd')
            ->where('vd.vehicle_id = vehicle.id')
            ->andWhere([
                'or',
                ['IS NOT', 'vd.driver_id', null],
                [
                    'and',
                    ['IS NOT', 'vd.license_plate', null],
                    ['<>', 'vd.license_plate', ''],
                    ['<>', 'vd.license_plate', ' '],
                ],
            ]);

        $waitingAllocationCount = (int) (clone $summaryBaseQuery)
            ->andWhere(['IN', 'status', ['Pending', 'Pass', 'Approve']])
            ->andWhere(['not exists', $assignedExistsSubQuery])
            ->count();

        $allocatedCount = (int) (clone $summaryBaseQuery)
            ->andWhere(['exists', $assignedExistsSubQuery])
            ->count();

        if ($this->request->get('export') === 'excel') {
            return $this->exportOfficialVehicleExcel($dataProvider);
        }

        return $this->render('index', [
            'type' => $type,
            'icon' => '<i class="fa-solid fa-car-on"></i>',
            'title' => 'ทะเบียนขอใช้รถยนต์ทั่วไป',
            'icon' => '',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => '',
            'statusSummary' => $statusSummary,
            'waitingAllocationCount' => $waitingAllocationCount,
            'allocatedCount' => $allocatedCount,
        ]);
    }

    /**
     * ส่งออกทะเบียนขอใช้รถยนต์ทั่วไปเป็นไฟล์ Excel
     */
    private function exportOfficialVehicleExcel($dataProvider): Response
    {
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ทะเบียนรถทั่วไป');

        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'รหัสขอใช้รถ',
            'C1' => 'ผู้ขอ',
            'D1' => 'หน่วยงาน',
            'E1' => 'ความเร่งด่วน',
            'F1' => 'สถานที่ไป',
            'G1' => 'เหตุผล',
            'H1' => 'วันที่เริ่ม',
            'I1' => 'เวลาเริ่ม',
            'J1' => 'วันที่สิ้นสุด',
            'K1' => 'เวลาสิ้นสุด',
            'L1' => 'ประเภทการเดินทาง',
            'M1' => 'สถานะ',
            'N1' => 'จัดสรรร่วม',
            'O1' => 'ทะเบียนรถ',
            'P1' => 'พนักงานขับ',
            'Q1' => 'บันทึกเมื่อ',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $headerStyle = 'A1:Q1';
        $sheet->getStyle($headerStyle)->getFont()->setBold(true);
        $sheet->getStyle($headerStyle)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerStyle)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerStyle)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
        $sheet->getStyle($headerStyle)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerStyle)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));

        $columnWidths = [
            'A' => 8,
            'B' => 18,
            'C' => 24,
            'D' => 22,
            'E' => 18,
            'F' => 22,
            'G' => 30,
            'H' => 18,
            'I' => 12,
            'J' => 18,
            'K' => 12,
            'L' => 16,
            'M' => 16,
            'N' => 14,
            'O' => 18,
            'P' => 22,
            'Q' => 20,
        ];
        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $row = 2;
        foreach ($models as $index => $vehicle) {
            $requester = $vehicle->userRequest();
            $assignedPlates = [];
            $assignedDrivers = [];

            foreach ($vehicle->vehicleDetails as $detail) {
                $plate = trim((string) ($detail->license_plate ?? ''));
                if ($plate !== '') {
                    $assignedPlates[$plate] = true;
                }

                $driverName = $detail->driver?->fullname ?? '';
                if ($driverName !== '') {
                    $assignedDrivers[$driverName] = true;
                }
            }

            $statusLabel = $vehicle->viewStatus()['title'] ?? ($vehicle->status ?? '-');
            $sharedLabel = ((int) ($vehicle->is_shared ?? 0) === 1) ? 'จัดสรรร่วม' : 'เดี่ยว';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, (string) ($vehicle->code ?? '-'));
            $sheet->setCellValue('C' . $row, (string) ($requester['fullname'] ?? '-'));
            $sheet->setCellValue('D' . $row, (string) ($requester['department'] ?? '-'));
            $sheet->setCellValue('E' . $row, (string) ($vehicle->viewUrgent() ?? '-'));
            $sheet->setCellValue('F' . $row, (string) ($vehicle->locationOrg?->title ?? $vehicle->location ?? '-'));
            $sheet->setCellValue('G' . $row, (string) ($vehicle->reason ?? '-'));
            $sheet->setCellValue('H' . $row, $vehicle->date_start ? (string) \app\components\ThaiDateHelper::formatThaiDate($vehicle->date_start) : '-');
            $sheet->setCellValue('I' . $row, (string) ($vehicle->viewTime()['start'] ?? '-'));
            $sheet->setCellValue('J' . $row, $vehicle->date_end ? (string) \app\components\ThaiDateHelper::formatThaiDate($vehicle->date_end) : '-');
            $sheet->setCellValue('K' . $row, (string) ($vehicle->viewTime()['end'] ?? '-'));
            $sheet->setCellValue('L' . $row, (string) ($vehicle->viewGoType() ?? '-'));
            $sheet->setCellValue('M' . $row, (string) $statusLabel);
            $sheet->setCellValue('N' . $row, $sharedLabel);
            $sheet->setCellValue('O' . $row, !empty($assignedPlates) ? implode(', ', array_keys($assignedPlates)) : '-');
            $sheet->setCellValue('P' . $row, !empty($assignedDrivers) ? implode(', ', array_keys($assignedDrivers)) : '-');
            $sheet->setCellValue('Q' . $row, (string) ($vehicle->viewCreated()['full'] ?? '-'));

            $sheet->getStyle('A' . $row . ':Q' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $row . ':Q' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $row++;
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:Q1');

        $fileName = 'ทะเบียนขอใช้รถยนต์ทั่วไป.xlsx';
        $tmpFile = tempnam(sys_get_temp_dir(), 'vehicle_official_');
        if ($tmpFile === false) {
            throw new \RuntimeException('Unable to create temporary file for export.');
        }

        $xlsxFile = $tmpFile . '.xlsx';
        rename($tmpFile, $xlsxFile);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxFile);

        $response = Yii::$app->response;
        $response->on(Response::EVENT_AFTER_SEND, static function () use ($xlsxFile) {
            @unlink($xlsxFile);
        });

        return $response->sendFile($xlsxFile, $fileName);
    }



    public function actionAmbulance()
    {
        $status = $this->request->get('status');

        $type = 'ambulance';
        $searchModel = new VehicleSearch([
            'status' =>   $status ? [$status] : ['Pending'],
            'vehicle_type_id' => $type
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        // Filter: ยังไม่บันทึกการเดินทาง (distance_km/oil_price/oil_liter ไม่มีค่า > 0)
        if ((string) $searchModel->not_logged === '1') {
            $recordedExistsSubQuery = (new Query())
                ->select(new \yii\db\Expression('1'))
                ->from('vehicle_detail vd')
                ->where('vd.vehicle_id = vehicle.id')
                ->andWhere([
                    'or',
                    ['and', ['IS NOT', 'vd.distance_km', null], ['>', 'vd.distance_km', 0]],
                    ['and', ['IS NOT', 'vd.oil_price', null], ['>', 'vd.oil_price', 0]],
                    ['and', ['IS NOT', 'vd.oil_liter', null], ['>', 'vd.oil_liter', 0]],
                ]);

            $query->andWhere(['not exists', $recordedExistsSubQuery]);
        }


        return $this->render('index', [
            'type' => $type,
            'icon' => '<i class="fa-solid fa-truck-medical text-danger"></i>',
            'title' => 'ทะเบียนขอใช้รถพยาบาล',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => ['ambulance'],
            // 'dataProviderDetail' => $dataProviderDetail,
        ]);
    }



    //ทะเบียนจัดสรรรถทั่วไป
    public function actionWorkOfficial()
    {
        $searchModel = new VehicleDetailSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith('vehicle');
        $query->andFilterWhere(['vehicle.thai_year' => $searchModel->thai_year]);
        $query->andFilterWhere(['vehicle.location' => $searchModel->location]);
        $query->andFilterWhere(['vehicle.vehicle_type_id' => 'official']);

        $query->andFilterWhere([
            'or',
            ['like', 'reason', $searchModel->q],
        ]);


        $query->andFilterWhere(['>=', 'vehicle_detail.date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'vehicle_detail.date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        // Filter: ยังไม่บันทึกการเดินทาง (vehicle_detail ไม่มี distance_km/oil_price/oil_liter > 0)
        if ((string) $searchModel->not_logged === '1') {
            $query->andWhere([
                'and',
                ['or', ['vehicle_detail.distance_km' => null], ['<=', 'vehicle_detail.distance_km', 0]],
                ['or', ['vehicle_detail.oil_price' => null], ['<=', 'vehicle_detail.oil_price', 0]],
                ['or', ['vehicle_detail.oil_liter' => null], ['<=', 'vehicle_detail.oil_liter', 0]],
            ]);
        }

        // UI parity กับหน้า /booking/vehicle/index: summary card ต้องมี status/จำนวนรอจัดสรร/จำนวนจัดสรรแล้ว
        $statusSummary = (clone $query)
            ->select(['vehicle_detail.status AS status', 'COUNT(*) AS total'])
            ->groupBy(['vehicle_detail.status'])
            ->asArray()
            ->all();

        $waitingAllocationCount = (int) (clone $query)
            ->andWhere(['IN', 'vehicle_detail.status', ['Pending', 'Pass', 'Approve']])
            ->andWhere(['vehicle_detail.driver_id' => null])
            ->andWhere([
                'or',
                ['vehicle_detail.license_plate' => null],
                ['vehicle_detail.license_plate' => ''],
                ['vehicle_detail.license_plate' => ' '],
            ])
            ->count();

        $allocatedCount = (int) (clone $query)
            ->andWhere([
                'or',
                ['IS NOT', 'vehicle_detail.driver_id', null],
                [
                    'and',
                    ['IS NOT', 'vehicle_detail.license_plate', null],
                    ['<>', 'vehicle_detail.license_plate', ''],
                    ['<>', 'vehicle_detail.license_plate', ' '],
                ],
            ])
            ->count();

        // เพิ่ม eager load เพื่อให้ card-based list โหลดข้อมูลที่ใช้จริงได้ลื่นขึ้น
        $query->with([
            'vehicle.employee',
            'vehicle.locationOrg',
            'vehicle',
            'driver',
            'vehicleDetailStatus',
        ]);

        return $this->render('work-official/index', [
            'vehicle_type' => 'official',
            'icon' => '<i class="fa-solid fa-car-on"></i>',
            'title' => 'ทะเบียนการจัดสรรรถทั่วไป (พขร.)',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusSummary' => $statusSummary,
            'waitingAllocationCount' => $waitingAllocationCount,
            'allocatedCount' => $allocatedCount,
        ]);
    }

    //ทะเบียนจจัดสรรรถ Refer
    public function actionWorkAmbulance()
    {
        $searchModel = new VehicleDetailSearch([]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith('vehicle');
        $query->andFilterWhere(['vehicle.thai_year' => $searchModel->thai_year]);
        $query->andFilterWhere(['vehicle.location' => $searchModel->location]);
        $query->andFilterWhere(['vehicle.vehicle_type_id' => 'ambulance']);

        $query->andFilterWhere([
            'or',
            ['like', 'reason', $searchModel->q],
        ]);

        $query->andFilterWhere(['>=', 'vehicle_detail.date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'vehicle_detail.date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        return $this->render('work', [
            'vehicle_type' => 'ambulance',
            'title' => 'ทะเบียนการจัดสรรรถพยาบาล (พขร.)',
            'icon' => '<i class="fa-solid fa-truck-medical text-danger"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    //ลงบันทึกการใช่รถยนต์
    public function actionWorkUpdate($id)
    {
        $model = VehicleDetail::findOne($id);
        $previousDetailDriverId = $model->driver_id;
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);
        if (!$model->ref) {
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);

            // if ($model->status == 'Success') {

            // }
            if ($model->save()) {
                $model->vehicle->status = $model->status;
                $model->vehicle->save();
                if ($model->vehicle) {
                    VehicleTelegramNotify::notifyVehicleDetailDriverChanged($model->vehicle, $model, $previousDetailDriverId);
                }
                return ['status' => 'success',];
            } else {
                return ['status' => 'error',];
            }
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_work_update', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('_form_work_update', [
                'model' => $model
            ]);
        }
    }


    //แสดงการจองรถวันนี้
    public function actionListEventTodays()
    {

        $todays =  date('Y-m-d');
        $vehicle_type = $this->request->get('vehicle_type');

        $searchModel = new VehicleDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith('vehicle');
        $query->andFilterWhere(['vehicle_detail.date_start' => $todays]);
        $query->andWhere(['vehicle_type_id' => $vehicle_type]);
        $query->andFilterWhere(['NOT IN', 'vehicle.status', ['Cancel']]);
        $dataProvider->pagination->pageSize = 7;

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_vehicle_events', [
                    'showDate' => ThaiDateHelper::formatThaiDate($todays),
                    'container' => 'EventTodays',
                    'title' => 'การใช้รถวันนี้',
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list_vehicle_events', [
                'showDate' => ThaiDateHelper::formatThaiDate($todays),
                'container' => 'EventTodays',
                'title' => 'การใช้รถวันนี้',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    //แสดงการจองรถวันพรุ่งนี้
    public function actionListEventTomorrow()
    {

        $todays =  date('Y-m-d');
        $vehicle_type = $this->request->get('vehicle_type');

        $nextDate = date('Y-m-d', strtotime($todays . ' +1 day'));
        $searchModel = new VehicleDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith('vehicle');
        $query->andFilterWhere(['vehicle_detail.date_start' => $nextDate]);
        $query->andFilterWhere(['vehicle.vehicle_type_id' => $vehicle_type]);
        $query->andFilterWhere(['NOT IN', 'vehicle.status', ['Cancel']]);
        $dataProvider->pagination->pageSize = 7;


        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_vehicle_events', [
                    'showDate' => ThaiDateHelper::formatThaiDate($nextDate),
                    'container' => 'EventTomorrow',
                    'title' => 'การใช้รถพรุ่งนี้',
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list_vehicle_events', [
                'showDate' => ThaiDateHelper::formatThaiDate($nextDate),
                'container' => 'EventTomorrow',
                'title' => 'การใช้รถพรุ่งนี้',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    public function actionCalendarDev()
    {
        return $this->render('calendar_dev', [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car-icon lucide-car"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>',
            'title' => 'รถยนต์ทั้วไป',
            'vehicle_type' => 'official'
        ]);
    }


    //ปฎิทินการขอใช้รถยนต์ราชการ
    public function actionCalendar()
    {
        return $this->render('calendar', [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car-icon lucide-car"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>',
            'title' => 'รถยนต์ทั้วไป',
            'vehicle_type' => 'official'
        ]);
    }

    // ปฏิทินการขอใช้รถยนต์ทั่วไป
    public function actionCalendarAmbulance()
    {
        return $this->render('calendar', [
            'title' => 'รถฉุกเฉิน',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ambulance-icon lucide-ambulance"><path d="M10 10H6"/><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.28a1 1 0 0 0-.684-.948l-1.923-.641a1 1 0 0 1-.578-.502l-1.539-3.076A1 1 0 0 0 16.382 8H14"/><path d="M8 8v4"/><path d="M9 18h6"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
            'vehicle_type' => 'ambulance'
        ]);
    }


    public function actionGetEvents($start, $end)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // ดึงข้อมูลการใช้รถในช่วงวันที่กำหนด
        $models = Vehicle::find()
            ->andWhere(['<>', 'status', 'Cancel'])
            // ใช้การเช็คแบบคาบเกี่ยว (Overlap) เพื่อความแม่นยำ
            ->andWhere(['<=', 'date_start', $end])
            ->andWhere(['>=', 'date_end', $start])
            ->orderBy(['date_start' => SORT_ASC])
            ->all();

        $events = [];
        foreach ($models as $model) {
            // แปลงวันที่เริ่มและจบเป็นก้อน Object เพื่อวนลูป
            $begin = new \DateTime($model->date_start);
            $terminate = new \DateTime($model->date_end);

            // วนลูปเพื่อนำกิจกรรมไปใส่ในทุกวันที่ที่จองรถ (กรณีจองข้ามวัน)
            // หากต้องการโชว์แค่ "วันเริ่ม" ให้ลบส่วน loop นี้แล้วใช้ $events[$model->date_start][] แทน
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($begin, $interval, $terminate->modify('+1 day'));

            foreach ($period as $dt) {
                $currentDate = $dt->format("Y-m-d");
                // ป้องกันไม่ให้กิจกรรมไปโชว์นอกช่วงเดือนที่เลือก (เพื่อความสะอาดของข้อมูล)
                if ($currentDate >= $start && $currentDate <= $end) {
                    // กำหนดสีตามสถานะที่นี่ (หรือดึงจากฐานข้อมูล)
                    $color = '#050505';
                    $bgColor = $model->vehicleStatus->data_json['color'] ?? '';


                    // เก็บข้อความที่จะโชว์ เช่น ทะเบียนรถ หรือ เหตุผลการใช้รถ
                    $events[$currentDate][] = [
                        'title' => $model->locationOrg?->title ?? '-',
                        'color' => $color,
                        'bg_color' => $bgColor
                    ];
                }
            }
        }

        return $events;
    }

    public function actionViewDetail($date)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // ดึงข้อมูลกิจกรรมทั้งหมดของวันนั้นๆ
        $models = Vehicle::find()
            ->where(['<=', 'date_start', $date])
            ->andWhere(['>=', 'date_end', $date])
            ->andWhere(['<>', 'status', 'Cancel'])
            ->all();

        // Render เป็นไฟล์ View เพื่อส่งเป็น HTML content
        return [
            'title' => 'รายละเอียดกิจกรรมวันที่ ' . \Yii::$app->formatter->asDate($date, 'medium'),
            'content' => $this->renderPartial('_view_event_list', ['models' => $models]),
            'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>'
        ];
    }

    public function actionEvents()
    {
        $start = Yii::$app->formatter->asDate($this->request->get('start'), 'php:Y-m-d');
        $end =  Yii::$app->formatter->asDate($this->request->get('end'), 'php:Y-m-d');
        $vehicleType  = $this->request->get('vehicle_type');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $bookings = Vehicle::find()
            ->andWhere(['vehicle_type_id' =>  $vehicleType])
            ->andWhere(['<>', 'status', 'Cancel'])
            ->andWhere(['>=', 'date_start', $start])->andWhere(['<', 'date_end', $end])
            ->orderBy(['date_start' => SORT_ASC])
            ->all();
        $data = [];
        // return [
        //     'date_start' => $start,
        //     'date_end' => $end,
        //     'vehicle_type' => $vehicleType
        // ];


        foreach ($bookings as $item) {
            try {

                $timeStart = $item->time_start;
                $timeEnd = $item->time_end;
                $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $timeStart), "php:Y-m-d\TH:i");
                $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end . ' ' . $timeEnd), "php:Y-m-d\TH:i");
                $data[] = [
                    'id'               => $item->id,
                    'title'            => $item->reason,
                    'start'            => $dateStart,
                    'time_start' => $timeStart,
                    'end'            => $dateEnd,
                    'time_end' => $timeEnd,
                    'allDay' => false,
                    'source' => 'vehicle',
                    'extendedProps' => [
                        'title' => $this->renderAjax('@app/modules/booking/views/vehicle/view_title', ['model' => $item]),
                        'code' => $item->code,
                        'color' => (isset($item->vehicleStatus) && isset($item->vehicleStatus->data_json['color'])) ? $item->vehicleStatus->data_json['color'] : '',
                    ],
                ];
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        $summaryStatus = Vehicle::find()
            ->select(['status', 'COUNT(*) AS count'])
            ->andWhere(['vehicle_type_id' =>  $vehicleType])
            ->andWhere(['>=', 'date_start', $start])
            ->andWhere(['<', 'date_end', $end])
            ->groupBy('status')
            ->orderBy(['status' => SORT_ASC])
            ->asArray()
            ->all();

        return  [
            'date_start' =>  $start,
            'date_end' => $end,
            'vehicle_type' => $vehicleType,
            'summary_status' => $summaryStatus,
            'events' => $data
        ];
    }

    /**
     * Displays a single Vehicle model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-solid fa-car"></i> แสดงข้อมูลการขอใช้ยานพาหนะ',
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

    public function actionShow($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('show', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('show', [
                'model' => $model
            ]);
        }
    }

    /**
     * Creates a new Vehicle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $vehicleType = $this->request->get('vehicle_type');
        $dateStart = $this->request->get('date_start');
        $dateEnd = $this->request->get('date_end');
        // กำหนดค่า Default ไว้ที่นี่
        $model = new Vehicle([
            'vehicle_type_id' => $vehicleType,
            'go_type' => $vehicleType ? 1 : '',
            'date_start' => $dateStart ? AppHelper::convertToThai($dateStart) : '',
            'date_end' => $dateStart ? AppHelper::convertToThai($dateEnd) : '',
            'data_json' => [
                'phone' => $me->phone
            ]
        ]);
        $model->leader_id = isset($model->Approve()['approve_1']['id']) ? $model->Approve()['approve_1']['id'] : '';

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                $model->status =  $model->vehicle_type_id == "personal" ? 'Pass' : 'Pending';
                $model->emp_id = $me->id;
                // $model->code  = CARREQ-20250101-001
                $model->code  = \mdm\autonumber\AutoNumber::generate('REQ-CAR' . date('ymd') . '-???');

                if ($model->save(false)) {
                    // ตรวจสอบหากมีการเพิ่มสถานที่ไปแห่งใหม่ให้สร้าง
                    $this->checkLocation($model);

                    // ถ้าเป็นการไปกลับสร้างตารางจรรสรรของแต่ละวัน

                    $this->createDetail($model);
                    $model->sendMessage();
                    //สร้างการอนุมัติ

                    // $this->createApprove($model);
                    return [
                        'status' => 'success',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }


    /**
     * Updates an existing Vehicle model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $previousDriverId = $model->driver_id;
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);
        // เก็บค่าเดิมไว้ก่อน
        $old_json = is_array($model->data_json) ? $model->data_json : json_decode($model->data_json, true) ?? [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);

            $new_json = $model->data_json;
            $model->data_json = ArrayHelper::merge($old_json, $new_json);

            $model->save();
            VehicleTelegramNotify::notifyDriverChanged($model, $previousDriverId);
            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
                'reload' => true
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model
            ]);
        }
    }

    /**
     * Deletes an existing Vehicle model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionApprove($id)
    {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->status = 'Pass';
                if (!$model->save()) {
                    throw new \Exception('ไม่สามารถบันทึกข้อมูลการจองได้');
                }

                $driverNotifyRows = [];
                foreach ($post['vehicleDetails'] as $key => $detail) {
                    $bookingDetail = VehicleDetail::findOne($detail['id']);
                    if (!$bookingDetail) {
                        throw new \Exception('ไม่พบรายละเอียดการจอง');
                    }
                    $prevDriver = $bookingDetail->driver_id;
                    $bookingDetail->driver_id = $detail['driver'];
                    $bookingDetail->license_plate = $detail['car'];
                    $bookingDetail->status = 'Pass';
                    if (!$bookingDetail->save(false)) {
                        throw new \Exception('ไม่สามารถบันทึกรายละเอียดการจองได้');
                    }
                    $driverNotifyRows[] = ['detail' => $bookingDetail, 'prev' => $prevDriver];
                }

                $transaction->commit();

                foreach ($driverNotifyRows as $row) {
                    VehicleTelegramNotify::notifyVehicleDetailDriverChanged($model, $row['detail'], $row['prev']);
                }
                VehicleTelegramNotify::notifyRequesterAllocated($model);
                $this->sendApprove($model);

                return [
                    'status' => 'success'
                ];
            } catch (\Throwable $e) {
                $transaction->rollBack();
                return [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-circle-info"></i> แสดงข้อมูลการจองรถ',
                // 'title' => 'เลขที่#' . $model->code,
                'content' => $this->renderAjax('_form_approve', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_approve', [
                'model' => $model,
            ]);
        }
    }

    //ส่งข้อความหาพนักงานขับรถที่จัดสรร
    public function sendMessage($model)
    {
        $message = 'ภาระกิจไป' . ($model->locationOrg?->title ?? '-') . ($model->showDateRange() . ' ' . $model->viewTime()['full']) . "\n ผู้ขอ" . $model->userRequest()['fullname'];
        $data = [];
        if (isset($this->listMembers) && is_array($this->listMembers)) {
            foreach ($this->listMembers as $item) {
                if (isset($item->driver, $item->driver->employee, $item->driver->employee->user, $item->driver->employee->user->line_id)) {
                    $lineId = $item->driver->employee->user->line_id;
                    LineMsg::sendMsg($lineId, $message);
                }
            }
        }
        return $data;
    }


    //ส่งการอนุมัติไปยังผู้อำนวยการและแจ้งเตือผู้ขอใช้ยานพาหนะ
    private function sendApprove($model)
    {
        $info = SiteHelper::getInfo();
        $emp_id = $info['director']?->id ?? 0;

        // Check if an approval already exists for this vehicle and employee
        $existingApproval = Approve::find()
            ->where(['from_id' => $model->id, 'emp_id' => $emp_id, 'name' => 'vehicle'])
            ->one();

        if (!$existingApproval) {
            $newApproval = new Approve();
            $newApproval->from_id = $model->id;
            $newApproval->title = 'ขออนุมัติใช้รถ';
            $newApproval->name = 'vehicle';
            $newApproval->status = 'Pending';
            $newApproval->emp_id = $emp_id;
            $newApproval->level = 1;
            $newApproval->data_json = ['label' => 'อนุมัติ'];
            $newApproval->save(false);
        }
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = 'Cancel';
        if ($model->save(false)) {

            $msg  = "
        🚫 <b>ยกเลิกการจองรถ</b>
        👤 <b>ผู้ยกเลิก:</b> " . $model->userRequest()['fullname'] . "
        📍 <b>สถานที่:</b> " . ($model->locationOrg?->title ?? '-') . "
        📅 <b>วันที่:</b> " . $model->showDateRange() . "
        🕘 <b>เวลา:</b> " . $model->viewTime()['full'];
            $model->sendMessage($msg);
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 'success',
            'message' => 'ยกเลิกการจองเรียบร้อยแล้ว',
        ];
    }



    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $model->ref = $model->ref ? $model->ref : substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $model->save(false);

        $template = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_BOOKING_VEHICLE_OFFICIAL])->one();
        if (!$template) {
            $template = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_BOOKING_VEHICLE_CENTRAL])->one();
        }
        if ($template) {
            $data = $this->buildBookingTemplateData($model);
            if ((string) Yii::$app->request->get('debug', '') === '1') {
                $templateService = new PdfTemplateService();
                $layout = $templateService->loadLayout((int) $template->id);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'template_id' => (int) $template->id,
                    'template_name' => (string) $template->name,
                    'data_source_id' => (string) ($template->data_source_id ?? ''),
                    'layout_count' => count($layout),
                    'layout' => $layout,
                    'data' => $data,
                ];
            }
            $templateService = new PdfTemplateService();
            $pdfBinary = $templateService->generatePdfWithData((int) $template->id, $data);
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->headers->set('Content-Type', 'application/pdf');
            Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="booking-vehicle-' . (int) $model->id . '.pdf"');
            Yii::$app->response->content = $pdfBinary;
            return Yii::$app->response;
        }

        $info = SiteHelper::getInfo();
        $modelData = [
            'director' => $info['company_name'],
            'fullname' => $model->employee?->fullname,
            'fullname_' => $model->employee?->fullname,
            'date' => ThaiDateHelper::formatThaiDate($model->date_start),
            'position' => $model->employee?->positionName(),
            'department' => $model->employee?->departmentName(),
            'location' => $model->locationOrg?->title ?? '-',
            'passenger' => '2',
            'phone' => $model->employee?->phone ?? '-',
            'reason' => $model->reason,
            'date_start' => ThaiDateHelper::formatThaiDate($model->date_start),
            'date_end' => ThaiDateHelper::formatThaiDate($model->date_end),
            'time_start' => $model->time_start,
            'time_end' => $model->time_start,
            'vehicle_type' => $model->vehicle_type_id,
            'license_plate' => $model->license_plate,
            'driver_name' => $model->driver?->fullname ?? '-',
            'driver_name_' => $model->driver?->fullname ?? '-',
            //หัวหน้างาน
            'leader_name' => $model->leader?->getInfo()['fullname'],
            'leader_signature' => $model->leader?->getInfo()['signature'],
            'driver_leader_name' => 'นายหัวหน้า พขร.',
            'mileage_start' => '10000',
            'mileage_end' => '10100',
            'emp_signature' => $model->userRequest()['signature'],
            'driver_signature' => $model->driver?->getInfo()['signature'],
            'director_signature' => Yii::getAlias('@web') . '/images/signature.png',

        ];
        if ($model) {
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $content = $this->renderAjax('print', [
                    'model' => $model,
                    'modelData' => $modelData,
                ]);
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'content' => $content,
                ];
            } else {
                return $this->render('print', [
                    'model' => $model,
                    'modelData' => $modelData,
                ]);
            }
        } else {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลการจอง'
            ];
        }
    }

    /**
     * คืนข้อมูลจริงสำหรับ editor ของ pdf-template (source: booking.vehicle.central)
     */
    public function actionPrintData($id)
    {
        $model = Vehicle::find()->where(['id' => (int) $id])->one();
        if (!$model) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'ไม่พบรายการ'];
        }
        $data = $this->buildBookingTemplateData($model);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

    private function buildBookingTemplateData(Vehicle $model): array
    {
        $employee = $model->employee;
        $leader = $model->leader;
        $driver = $model->driver;
        $vehicleStatus = $model->vehicleStatus;
        $carType = $model->carType;

        $approverData = [];
        $approveRows = Approve::find()
            ->where(['name' => 'vehicle'])
            ->andWhere([
                'or',
                ['from_id' => (string) $model->id],
                ['from_id' => (int) $model->id],
            ])
            ->with('employee')
            ->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $index = 1;
        foreach ($approveRows as $approveRow) {
            if ($index > 4) {
                break;
            }
            $approveEmp = $approveRow->employee;
            $approveSignature = '';
            if ($approveEmp && method_exists($approveEmp, 'SignatureFilePath')) {
                $approveSigPath = (string) ($approveEmp->SignatureFilePath() ?? '');
                if ($approveSigPath !== '' && is_file($approveSigPath)) {
                    $approveSignature = $approveSigPath;
                }
            }
            $approveDataJson = $approveRow->data_json;
            if (is_string($approveDataJson)) {
                $decodedApproveJson = json_decode($approveDataJson, true);
                $approveDataJson = is_array($decodedApproveJson) ? $decodedApproveJson : [];
            }
            if (!is_array($approveDataJson)) {
                $approveDataJson = [];
            }
            $approveDate = '';
            if (!empty($approveDataJson['approve_date'])) {
                $approveDate = substr((string) $approveDataJson['approve_date'], 0, 10);
            } elseif (in_array((string) $approveRow->status, ['Pass', 'Approve'], true) && !empty($approveRow->updated_at)) {
                $approveDate = substr((string) $approveRow->updated_at, 0, 10);
            }
            $approverData['approver_' . $index . '_fullname'] = (string) ($approveEmp->fullname ?? '');
            $approverData['approver_' . $index . '_position'] = $approveEmp && method_exists($approveEmp, 'positionName') ? (string) ($approveEmp->positionName() ?? '') : '';
            $approverData['approver_' . $index . '_approve_date'] = $approveDate;
            $approverData['approver_' . $index . '_signature'] = $approveSignature;
            $approverData['approver_' . $index . '_status'] = (string) ($approveRow->status ?? '');
            $index++;
        }
        if (!empty($approverData['approver_1_fullname'])) {
            $approverData['approver_fullname'] = (string) ($approverData['approver_1_fullname'] ?? '');
            $approverData['approver_position'] = (string) ($approverData['approver_1_position'] ?? '');
            $approverData['approver_approve_date'] = (string) ($approverData['approver_1_approve_date'] ?? '');
            $approverData['approver_signature'] = (string) ($approverData['approver_1_signature'] ?? '');
            $approverData['approval_status'] = (string) ($approverData['approver_1_status'] ?? '');
        } elseif ($leader) {
            $leaderSignature = '';
            if (method_exists($leader, 'SignatureFilePath')) {
                $leaderSigPath = (string) ($leader->SignatureFilePath() ?? '');
                if ($leaderSigPath !== '' && is_file($leaderSigPath)) {
                    $leaderSignature = $leaderSigPath;
                }
            }
            $approverData['approver_1_fullname'] = (string) ($leader->fullname ?? '');
            $approverData['approver_1_position'] = method_exists($leader, 'positionName') ? (string) ($leader->positionName() ?? '') : '';
            $approverData['approver_1_approve_date'] = '';
            $approverData['approver_1_signature'] = $leaderSignature;
            $approverData['approver_1_status'] = '';
            $approverData['approver_fullname'] = (string) ($approverData['approver_1_fullname'] ?? '');
            $approverData['approver_position'] = (string) ($approverData['approver_1_position'] ?? '');
            $approverData['approver_approve_date'] = '';
            $approverData['approver_signature'] = $leaderSignature;
            $approverData['approval_status'] = '';
        }

        $employeeSignature = '';
        if ($employee && method_exists($employee, 'SignatureFilePath')) {
            $sigPath = (string) ($employee->SignatureFilePath() ?? '');
            if ($sigPath !== '' && is_file($sigPath)) {
                $employeeSignature = $sigPath;
            }
        }
        if ($employeeSignature === '' && !empty($model->userRequest()['signature'])) {
            $fallbackSignature = (string) $model->userRequest()['signature'];
            if (is_file($fallbackSignature)) {
                $employeeSignature = $fallbackSignature;
            }
        }

        $driverSignature = '';
        if ($driver && method_exists($driver, 'SignatureFilePath')) {
            $driverSigPath = (string) ($driver->SignatureFilePath() ?? '');
            if ($driverSigPath !== '' && is_file($driverSigPath)) {
                $driverSignature = $driverSigPath;
            }
        }

        $dataJson = is_array($model->data_json) ? $model->data_json : [];
        return [
            'id' => (int) $model->id,
            'code' => (string) ($model->code ?? ''),
            'thai_year' => (string) ($model->thai_year ?? ''),
            'created_at' => (string) ($model->created_at ?? ''),
            'status' => (string) ($model->status ?? ''),
            'vehicle_type_id' => (string) ($model->vehicle_type_id ?? ''),
            'go_type' => (string) ($model->go_type ?? ''),
            'urgent' => (string) ($model->urgent ?? ''),
            'license_plate' => (string) ($model->license_plate ?? ''),
            'location' => (string) ($model->location ?? ''),
            'reason' => (string) ($model->reason ?? ''),
            'date_start' => (string) ($model->date_start ?? ''),
            'time_start' => (string) ($model->time_start ?? ''),
            'date_end' => (string) ($model->date_end ?? ''),
            'time_end' => (string) ($model->time_end ?? ''),
            // legacy keys for older templates
            'date' => (string) ($model->date_start ?? ''),
            'vehicle_type' => (string) ($model->vehicle_type_id ?? ''),
            'passenger' => (string) ($dataJson['passenger_total'] ?? ''),
            'phone' => (string) (($employee->phone ?? '') ?: ($dataJson['phone'] ?? '')),
            'driver_name' => (string) ($driver->fullname ?? ''),
            'driver_name_' => (string) ($driver->fullname ?? ''),
            'leader_name' => (string) ($leader->fullname ?? ''),
            'leader_signature' => (string) ($approverData['approver_signature'] ?? ''),
            'driver_signature' => $driverSignature,
            // aliases for custom templates
            'fullname' => (string) ($employee->fullname ?? ''),
            'position' => $employee && method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?? '') : '',
            'department' => $employee && method_exists($employee, 'departmentName') ? (string) ($employee->departmentName() ?? '') : '',
            'officer_name' => (string) ($employee->fullname ?? ''),
            'officer_position' => $employee && method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?? '') : '',
            'officer_department' => $employee && method_exists($employee, 'departmentName') ? (string) ($employee->departmentName() ?? '') : '',
            'requester_fullname' => (string) ($employee->fullname ?? ''),
            'requester_position' => $employee && method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?? '') : '',
            'requester_department' => $employee && method_exists($employee, 'departmentName') ? (string) ($employee->departmentName() ?? '') : '',
            'employee_fullname' => (string) ($employee->fullname ?? ''),
            'employee_position' => $employee && method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?? '') : '',
            'employee_department' => $employee && method_exists($employee, 'departmentName') ? (string) ($employee->departmentName() ?? '') : '',
            'time_go' => (string) ($model->time_start ?? ''),
            'time_back' => (string) ($model->time_end ?? ''),
            'emp_id' => (string) ($model->emp_id ?? ''),
            'leader_id' => (string) ($model->leader_id ?? ''),
            'driver_id' => (string) ($model->driver_id ?? ''),
            'emp_signature' => $employeeSignature,
            'requester_signature' => $employeeSignature,
            'vehicleStatus' => [
                'title' => (string) ($vehicleStatus->title ?? ''),
            ],
            'carType' => [
                'title' => (string) ($carType->title ?? ''),
            ],
            'employee' => [
                'fullname' => (string) ($employee->fullname ?? ''),
                'positionName' => $employee && method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?? '') : '',
                'departmentName' => $employee && method_exists($employee, 'departmentName') ? (string) ($employee->departmentName() ?? '') : '',
            ],
            'leader' => [
                'fullname' => (string) ($leader->fullname ?? ''),
                'positionName' => $leader && method_exists($leader, 'positionName') ? (string) ($leader->positionName() ?? '') : '',
            ],
            'driver' => [
                'fullname' => (string) ($driver->fullname ?? ''),
            ],
            'data_json' => [
                'phone' => (string) ($dataJson['phone'] ?? ''),
                'passenger_total' => (string) ($dataJson['passenger_total'] ?? ''),
                'passenger_name' => (string) ($dataJson['passenger_name'] ?? ''),
                'note' => (string) ($dataJson['note'] ?? ''),
                'req_driver_id' => (string) ($dataJson['req_driver_id'] ?? ''),
            ],
        ] + $approverData;
    }


    // เลือกประเภทของการใช้งานรถ
    public function actionListCars()
    {
        $carType = $this->request->get('vehicle_type_id');

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['asset_status' => 1]);
        $dataProvider->query->andWhere([
            'AND',
            ['IS NOT', 'license_plate', null],
            ['<>', 'license_plate', ''],
            ['<>', 'license_plate', ' ']
        ]);

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_cars', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list_cars', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    protected function createDetail($model)
    {
        $startDate = new DateTime($model->date_start);
        $endDate = new DateTime($model->date_end);
        $endDate->modify('+1 day');  // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

        $interval = new DateInterval('P1D');  // ระยะห่าง 1 วัน
        $period = new DatePeriod($startDate, $interval, $endDate);
        //ถ้าเป็นรถยนต์ส่วนตัว
        if ($model->vehicle_type_id == "personal") {
            $me = UserHelper::GetEmployee();
            if ($model->go_type == "1") {
                $dates = [];
                foreach ($period as $date) {
                    $newDetail = new VehicleDetail;
                    $newDetail->date_start = $date->format('Y-m-d');
                    $newDetail->date_end = $date->format('Y-m-d');
                    $newDetail->vehicle_id = $model->id;
                    $newDetail->driver_id = $me->id;
                    $newDetail->license_plate = $model->license_plate;
                    $newDetail->status = 'Pass';
                    $newDetail->save(false);
                }
            } else {
                $newDetail = new VehicleDetail;
                $newDetail->date_start = $model->date_start;
                $newDetail->date_end = $model->date_end;
                $newDetail->vehicle_id = $model->id;
                $newDetail->driver_id = $me->id;
                $newDetail->license_plate = $model->license_plate;
                $newDetail->status = 'Pass';
                $newDetail->save(false);
            }



            $info = SiteHelper::getInfo();
            $newAprove = new Approve();
            $newAprove->from_id = $model->id;
            $newAprove->name = 'vehicle';
            $newAprove->emp_id = $info['director']->id;
            $newAprove->title = 'ขออนุมัติใช้รถ';
            $newAprove->data_json = ['label' => 'อนุมัติ'];
            $newAprove->level = 1;
            $newAprove->status = 'Pending';
            $newAprove->save(false);
        } else {

            if ($model->go_type == "1") {
                $dates = [];
                foreach ($period as $date) {
                    $dates[] = $date->format('Y-m-d');
                    $newDetail = new VehicleDetail;
                    $newDetail->vehicle_id = $model->id;
                    $newDetail->date_start = $date->format('Y-m-d');
                    $newDetail->date_end = $date->format('Y-m-d');
                    $newDetail->save(false);
                }
            } else {
                $newDetail = new VehicleDetail;
                $newDetail->vehicle_id = $model->id;
                $newDetail->date_start = $model->date_start;
                $newDetail->date_end = $model->date_end;
                $newDetail->save(false);
            }
        }
    }



    // ตรวจสอบหากมีการเพิ่มสถานที่ไปแห่งใหม่ให้สร้าง

    protected function checkLocation($model)
    {
        $location = Categorise::findOne($model->location);
        if (!$location) {
            $maxCode = Categorise::find()
                ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
                ->where(['like', 'name', 'document_org'])
                ->scalar();
            $newLocation = new Categorise;
            $newLocation->code = ($maxCode + 1);
            $newLocation->title = $model->location;
            $newLocation->name = 'document_org';
            $newLocation->save(false);
        }
    }


    /**
     * Finds the Vehicle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vehicle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vehicle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
