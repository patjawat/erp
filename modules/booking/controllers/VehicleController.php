<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
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
        $year = (int) $parts[1];
        // Add 543 to convert to Thai Buddhist year
        $thaiYear = $year + 543;
        // Combine the month and Thai year
        $thaiMonthYear = $month . ' ' . $thaiYear;

        $searchModel = new VehicleSearch([
            'status' => 'Pending',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere(['!=', 'car_type_id', 'personal']);
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
         $org1 = Organization::findOne($searchModel->q_department);
         // ถ้ามีกลุ่มย่อย
         if (isset($org1) && $org1->lvl == 1) {
             $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
             $querys = Yii::$app
                 ->db
                 ->createCommand($sql)
                 ->bindValue(':name', $org1->name)
                 ->queryAll();
             $arrDepartment = [];
             foreach ($querys as $tree) {
                 $arrDepartment[] = $tree['id'];
             }
             if (count($arrDepartment) > 0) {
                 $dataProvider->query->andWhere(['in', 'employees.department', $arrDepartment]);
             } else {
                 $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
             }
         } else {
             $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
         }
         

        

        $searchModelDetail = new VehicleDetailSearch();
        $dataProviderDetail = $searchModelDetail->search($this->request->queryParams);
        
        $dataProviderDetail->query->joinWith('vehicle');
        $dataProviderDetail->query->andFilterWhere(['vehicle_detail.status' => 'Pass']);
        $dataProviderDetail->query->andFilterWhere(['!=', 'vehicle.car_type_id', 'personal']);

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

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }

    public function actionWork()
    {
        $searchModel = new VehicleDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['status' => 'Pass']);
        // $dataProvider->query->joinWith('vehicle');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
        ]);

        return $this->render('work', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionWorkUpdate($id)
    {
        $model = VehicleDetail::findOne($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['work']);
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
