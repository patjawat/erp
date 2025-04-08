<?php

namespace app\modules\me\controllers;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use mdm\autonumber\AutoNumber;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetSearch;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\models\VehicleSearch;

/**
 * BookingVehicleController implements the CRUD actions for Vehicle model.
 */
class BookingVehicleController extends Controller
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
    public function actionIndex()
    {
        $searchModel = new VehicleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
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

    /**
     * Creates a new Vehicle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $model = new Vehicle([
            // 'time_start' => '08:00',
            // 'time_end' => '16:30',
        ]);
        $model->leader_id = $model->Approve()['approve_1']['id'];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                $model->status =  $model->car_type_id == "personal" ? 'Pass' : 'None';
                $model->emp_id = $me->id;
                // $model->code  = CARREQ-20250101-001
                $model->code  = \mdm\autonumber\AutoNumber::generate('REQ-CAR' .date('ymd') . '-???');
               
                if ($model->save(false)) {
                    // ตรวจสอบหากมีการเพิ่มสถานที่ไปแห่งใหม่ให้สร้าง
                    $this->checkLocation($model);

                    // ถ้าเป็นการไปกลับสร้างตารางจรรสรรของแต่ละวัน
                   
                        $this->createDetail($model);
                        
                        
                        //สร้างการอนุมัติ
                        
                        $this->createApprove($model);
                        
                    return $this->redirect(['/me/booking-vehicle/index', 'id' => $model->id]);
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
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
 public function actionTest(){
    \Yii::$app->response->format = Response::FORMAT_JSON;

   return  LineMsg::BookVehicle(1060,1);
 }

    protected function createApprove($model)
    {
        // try {
            // หัวหน้างาน
            $leader = new Approve();
            $leader->from_id = $model->id;
            $leader->title = 'ขออนุมัติใช้รถ';
            $leader->name = 'vehicle';
            $leader->status = 'Pending';
            $leader->emp_id = $model->leader_id;
            $leader->level = 1;
            $leader->data_json = ['label' => 'อนุมัติ'];
            $leader->save(false);
            $id = $model->id;
            LineMsg::BookVehicle($id,1);
            
        // } catch (\Throwable $th) {
        // }
        // ผู้อำนวยการอนุมัติ
        $getDirector = SiteHelper::viewDirector();
        // try {
            $director = new Approve();
            $director->from_id = $model->id;
            $director->name = 'vehicle';
            $director->emp_id = $director['id'];
            $director->title = 'ขออนุมัติใช้รถ';
            $director->data_json = ['label' => 'อนุมัติ'];
            $director->level = 2;
            $director->status = 'None';
            $director->save(false);
        // } catch (\Throwable $th) {
        // }

        
    }

    protected function createDetail($model)
    {
        $startDate = new DateTime($model->date_start);
        $endDate = new DateTime($model->date_end);
        $endDate->modify('+1 day');  // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

        $interval = new DateInterval('P1D');  // ระยะห่าง 1 วัน
        $period = new DatePeriod($startDate, $interval, $endDate);
        //ถ้าเป็นรถยนต์ส่วนตัว
        if($model->car_type_id == "personal"){
            $me = UserHelper::GetEmployee();
            if($model->go_type == "1"){
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
            }else{
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

        }else{

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
            }else{
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

    // เลือกประเภทของการใช้งานรถ
    public function actionListCars()
    {
        $carType = $this->request->get('car_type_id');

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['AND',
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
    
    
}