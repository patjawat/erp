<?php

namespace app\modules\booking\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\Organization;
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
            'thai_year' => AppHelper::YearBudget(),
            // 'status' => 'Pending',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
       
        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
          
        ]);
    }

    public function actionIndex()
    {
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new VehicleSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' =>   $status ? [$status] : ['Pending'],
            'vehicle_type_id' => 'official'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);


        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        } catch (\Throwable $th) {
            // throw $th;
        }

        // search employee department
         // ค้นหาคามกลุ่มโครงสร้าง
        //  $org1 = Organization::findOne($searchModel->q_department);
        //  // ถ้ามีกลุ่มย่อย
        //  if (isset($org1) && $org1->lvl == 1) {
        //      $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
        //      FROM tree t1
        //      JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
        //      WHERE t2.name = :name;';
        //      $querys = Yii::$app
        //          ->db
        //          ->createCommand($sql)
        //          ->bindValue(':name', $org1->name)
        //          ->queryAll();
        //      $arrDepartment = [];
        //      foreach ($querys as $tree) {
        //          $arrDepartment[] = $tree['id'];
        //      }
        //      if (count($arrDepartment) > 0) {
        //          $dataProvider->query->andWhere(['in', 'employees.department', $arrDepartment]);
        //      } else {
        //          $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        //      }
        //  } else {
        //      $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        //  }
         

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dataProviderDetail' => $dataProviderDetail,
        ]);
    }



    public function actionAmbulance()
    {
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new VehicleSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' =>   $status ? [$status] : ['Pending'],
            'vehicle_type_id' => 'ambulance'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        // $dataProvider->query->andFilterWhere(['!=', 'vehicle_type_id', 'personal']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);


        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        } catch (\Throwable $th) {
            // throw $th;
        }

        // search employee department
         // ค้นหาคามกลุ่มโครงสร้าง
        //  $org1 = Organization::findOne($searchModel->q_department);
        //  // ถ้ามีกลุ่มย่อย
        //  if (isset($org1) && $org1->lvl == 1) {
        //      $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
        //      FROM tree t1
        //      JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
        //      WHERE t2.name = :name;';
        //      $querys = Yii::$app
        //          ->db
        //          ->createCommand($sql)
        //          ->bindValue(':name', $org1->name)
        //          ->queryAll();
        //      $arrDepartment = [];
        //      foreach ($querys as $tree) {
        //          $arrDepartment[] = $tree['id'];
        //      }
        //      if (count($arrDepartment) > 0) {
        //          $dataProvider->query->andWhere(['in', 'employees.department', $arrDepartment]);
        //      } else {
        //          $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        //      }
        //  } else {
        //      $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        //  }
         

        return $this->render('ambulance', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dataProviderDetail' => $dataProviderDetail,
        ]);
    }

    

    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    public function actionEvents()
	{
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $bookings = Vehicle::find()
                ->andWhere(['<>', 'status', 'Cancel'])
                ->andWhere(['>=', 'date_start', $start])->andFilterWhere(['<=', 'date_end', $end])
                ->orderBy(['id' => SORT_DESC])
                ->all();
                $data = [];

                foreach($bookings as $item)
                {
                    $timeStart = (preg_match('/^\d{2}:\d{2}$/', $item->time_start) && strtotime($item->time_start)) ? $item->time_start : '00:00';
                    $timeEnd = (preg_match('/^\d{2}:\d{2}$/', $item->time_end) && strtotime($item->time_end)) ? $item->time_end : '00:00';
                    $dateStart = Yii::$app->formatter->asDatetime(($item->date_start.' '.$timeStart), "php:Y-m-d\TH:i");
                    $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end.' '.$timeEnd), "php:Y-m-d\TH:i");
                    $title = 'ขอใช้'.$item->carType?->title.' ไป'.($item->locationOrg?->title ?? '-');
                    $data[] = [
                        'id'               => $item->id,
                        'title'            => $item->reason,
                        'start'            => $dateStart,
                        'end'            => $dateEnd,
                        // 'display' => 'auto',
                        'allDay' => false,
                        'source' => 'vehicle',
                        'extendedProps' => [
                            'title' => $title,
                            // 'avatar' => $item->employee?->getAvatar(false,($title)),
                            'avatar' => $this->renderAjax('@app/modules/booking/views/vehicle/avatar', ['model' => $item]),
                            'fullname' => $item->employee?->fullname,
                            'dateTime' => $item->viewTime(),
                            // 'dateTime' => $item->viewMeetingTime(),
                            'status' => $item->viewStatus()['view'],
                            'view' => $this->renderAjax('@app/modules/booking/views/vehicle/view', ['model' => $item,'action' => true]),
                            'description' => 'คำอธิบาย',
                        ],
                         'className' =>  'border border-4 border-start border-top-0 border-end-0 border-bottom-0 border-'.$item->viewStatus()['color'],
                        'description' => 'description for All Day Event',
                        'textColor' => 'black',
                        'backgroundColor' => '#eee',
                    ];
                }

            return  $data;
       
	}


    public function actionWork()
    {
        $me = UserHelper::GetEmployee();
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new VehicleDetailSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            // 'status' =>   $status ? [$status] : ['Pending']
        ]);
      
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('vehicle');
        $dataProvider->query->andFilterWhere(['vehicle_detail.driver_id' => $me->id]);
        $dataProvider->query->andFilterWhere(['vehicle_detail.driver_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere(['vehicle.thai_year' => $searchModel->thai_year]);
        // $dataProvider->query->joinWith('vehicle');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'reason', $searchModel->q],
        ]);

        // if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
        //     $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
        //     $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        // }

        // try {
        //     $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        //     $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        //     $dataProvider->query->andFilterWhere(['>=', 'vehicle_detail.date_start', $dateStart])->andFilterWhere(['<=', 'vehicle_detail.date_end', $dateEnd]);
        // } catch (\Throwable $th) {
        //     // throw $th;
        // }
        
        return $this->render('work', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionWorkUpdate($id)
    {
        $model = VehicleDetail::findOne($id);
        if(!$model->ref){
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }
        
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('work_update', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('work_update', [
                'model' => $model
            ]);
        }
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
     
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $transaction = Yii::$app->db->beginTransaction();
            $model->status = 'Pass';
            $post = Yii::$app->request->post();

            // Yii::info('POST data: ' . print_r($post, true), 'booking');

            // ตรวจสอบและแสดงข้อมูล
            $dates = Yii::$app->request->post('dates', []);
            $cars = Yii::$app->request->post('cars', []);
            $drivers = Yii::$app->request->post('drivers', []);
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
                // return $this->redirect(['view', 'id' => $model->id]);
            // } catch (\Exception $e) {
            //     $transaction->rollBack();
            //     Yii::$app->session->setFlash('error', $e->getMessage());
            // }
        }
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_approve', [
                    'model' => $model,
                ]),
            ];
        }else{
            return $this->render('_form_approve', [
                'model' => $model,
            ]);
        }
    }

    //ส่งข้อความหาพนักงานขับรถที่จัดสรร
    public function sendMessage($model)
    {
        $message = 'ภาระกิจไป'.($model->locationOrg?->title ?? '-').($model->showDateRange().' '.$model->viewTime()) ."\n ผู้ขอ".$model->userRequest()['fullname'];
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

    public function actionCancel($id){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($this->request->isPost) {
            $model->status = 'Cancel';
            if ($model->save()) {
                return [
                    'status' => 'success'
                ];
            }
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
