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
use app\modules\am\models\AssetSearch;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\models\VehicleSearch;
use app\modules\booking\models\VehicleDetailSearch;

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

        $searchModel = new VehicleSearch([
            // 'thai_year' => AppHelper::YearBudget(),
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
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
            $transaction = Yii::$app->db->beginTransaction();
            $model->status = 'Pass';
            $post = Yii::$app->request->post();

            // try {
            // บันทึกข้อมูลหลักการจอง
            if (!$model->save()) {
                throw new \Exception('ไม่สามารถบันทึกข้อมูลการจองได้');
            }

            foreach ($post['vehicleDetails'] as $key => $detail) {
                $bookingDetail = VehicleDetail::findOne($detail['id']);
                if ($bookingDetail) {
                    $bookingDetail->driver_id = $detail['driver'];
                    $bookingDetail->license_plate = $detail['car'];
                    $bookingDetail->status = 'Pass';
                    $bookingDetail->save(false);
                    $this->sendMessage($model);
                }

                if (!$bookingDetail->save()) {
                    throw new \Exception('ไม่สามารถบันทึกรายละเอียดการจองได้');
                }
            }

            $transaction->commit();
            $this->sendApprove($model);
            return [
                'status' => 'success'
            ];
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
