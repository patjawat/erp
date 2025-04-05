<?php

namespace app\modules\booking\controllers;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
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


    public function actionCalendar($date = null)
    {
        // ถ้าไม่มีค่า date ที่ส่งมา ใช้วันที่ปัจจุบัน
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $currentDate = new \DateTime($date);
        
        // สร้างข้อมูลสำหรับแสดงในตาราง 5 วัน
        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $day = clone $currentDate;
            $day->modify("+$i day");
            $days[] = $day->format('Y-m-d');
        }
        
        // ดึงข้อมูลรถทั้งหมด
        $vehicles = Vehicle::find()->all();
        
        // ดึงข้อมูลการจองในช่วงวันที่แสดง
        $startDate = $days[0];
        $endDate = $days[4];
        
        // ดึงข้อมูลการจองที่มีช่วงเวลาทับกับวันที่แสดง
        $bookings = Vehicle::find()
            ->where(['id' => array_map(function($v) { return $v->id; }, $vehicles)])
            ->andWhere(['or',
                ['between', 'date_start', $startDate, $endDate],
                ['between', 'date_end', $startDate, $endDate],
                ['and', ['<=', 'date_start', $startDate], ['>=', 'date_end', $endDate]]
            ])
            ->orderBy('date_start')
            ->all();
        
        // จัดเรียงข้อมูลการจองตามรถและวันที่
        $bookingData = [];
        foreach ($bookings as $booking) {
            $bookingData[$booking->id][$booking->date_start] = $booking;
        }
        
        // สร้างข้อมูลสำหรับปฏิทิน
        $previousDate = clone $currentDate;
        $previousDate->modify('-5 days');
        
        $nextDate = clone $currentDate;
        $nextDate->modify('+5 days');
        
        $month = Yii::$app->formatter->asDate($currentDate, 'MMMM yyyy');
        
        return $this->render('calendar', [
            'days' => $days,
            'vehicles' => $vehicles,
            'bookings' => $bookings,
            'bookingData' => $bookingData,
            'previousDate' => $previousDate->format('Y-m-d'),
            'nextDate' => $nextDate->format('Y-m-d'),
            'currentDate' => $currentDate->format('Y-m-d'),
            'month' => $month,
        ]);
    }
    
    /**
     * Lists all Vehicle models.
     *
     * @return string
     */
    public function actionIndex($date = null)
    {

         // ถ้าไม่มีค่า date ที่ส่งมา ใช้วันที่ปัจจุบัน
         if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $currentDate = new \DateTime($date);
        
        // สร้างข้อมูลสำหรับแสดงในตาราง 5 วัน
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = clone $currentDate;
            $day->modify("+$i day");
            $days[] = $day->format('Y-m-d');
        }
        
        // ดึงข้อมูลรถทั้งหมด
        $vehicles = Vehicle::find()->all();
        
        // ดึงข้อมูลการจองในช่วงวันที่แสดง
        $startDate = $days[0];
        $endDate = $days[4];
         // สร้างข้อมูลสำหรับปฏิทิน
         $previousDate = clone $currentDate;
         $previousDate->modify('-5 days');
         $nextDate = clone $currentDate;
         $nextDate->modify('+5 days');
         // Get the current date formatted as "MMMM yyyy"
        $monthYear = Yii::$app->formatter->asDate($currentDate, 'MMMM yyyy');
        // Split the string to get the month and year separately
        $parts = explode(' ', $monthYear);
        $month = $parts[0];
        $year = (int)$parts[1];
        // Add 543 to convert to Thai Buddhist year
        $thaiYear = $year + 543;
        // Combine the month and Thai year
        $thaiMonthYear = $month . ' ' . $thaiYear;
         

        $searchModel = new VehicleSearch([
            'status' => 'Pending',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);

        $searchModelDetail = new VehicleDetailSearch();
        $dataProviderDetail = $searchModelDetail->search($this->request->queryParams);
        $dataProviderDetail->query->andFilterWhere(['status' => 'Pass']);

        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderDetail' => $dataProviderDetail,
            'days' => $days,
            'vehicles' => $vehicles,
            'previousDate' => $previousDate->format('Y-m-d'),
            'nextDate' => $nextDate->format('Y-m-d'),
            'currentDate' => $currentDate->format('Y-m-d'),
            'thaiMonthYear' => $thaiMonthYear,
        ]);
    }

    /**
     * Displays a single Vehicle model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Vehicle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Vehicle();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
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
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'Pass';
            $post = Yii::$app->request->post();

            // Yii::info('POST data: ' . print_r($post, true), 'booking');
        
            // ตรวจสอบและแสดงข้อมูล
            $dates = Yii::$app->request->post('dates', []);
            $cars = Yii::$app->request->post('cars', []);
            $drivers = Yii::$app->request->post('drivers', []);
            
            // $transaction = Yii::$app->db->beginTransaction();
            try {
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
                    }
                    
                    if (!$bookingDetail->save()) {
                        throw new \Exception('ไม่สามารถบันทึกรายละเอียดการจองได้');
                    }
                }
                
                // $transaction->commit();
                return [
                    'status' => 'success'
                ];
                // return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_approve', [
                'model' => $model,
            ]),
        ];
    
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
