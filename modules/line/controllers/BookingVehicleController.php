<?php

namespace app\modules\line\controllers;
use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetSearch;
use app\modules\approve\models\Approve;
use app\modules\Vehicle\models\Vehicle;
use app\modules\Vehicle\models\VehicleDetail;
use app\modules\Vehicle\models\VehicleSearch;

class BookingVehicleController extends \yii\web\Controller
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
     * Lists all VehicleCar models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $carType = $this->request->get('car_type');
        $status = $this->request->get('status');
        
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id;
        $searchModel = new VehicleSearch([
            'car_type' => $carType,
            'status' => $status
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['created_by' => $userId]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


        //เลือกประเภทของการใช้งานรถ
        public function actionSelectType()
        {
            if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
    
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('select_type'),
                ];
            } else {
                return $this->render('select_type');
            }
        }
    
      
                //เลือกประเภทของการใช้งานรถ
                public function actionListCars()
                {
                    $carType = $this->request->get('car_type');
                    
                    $searchModel = new AssetSearch();
                    $dataProvider = $searchModel->search($this->request->queryParams);
                    $dataProvider->query->andFilterWhere(['not','license_plate',null]);
                    $dataProvider->query->andFilterWhere(['car_type' => $carType]);

                    if ($this->request->isAJax) {
                        \Yii::$app->response->format = Response::FORMAT_JSON;
            
                        return [
                            'title' => $this->request->get('title'),
                            'content' => $this->renderAjax('list_cars',[
                                'searchModel' => $searchModel,
                                'dataProvider' => $dataProvider,
                            ]),
                        ];
                    } else {
                        return $this->render('list_cars',[
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
                        ]);
                    }
                }
    
                

    /**
     * Displays a single VehicleCar model.
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


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Vehicle();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->reason == '' ? $model->addError('reason', $requiredName) : null;
            $model->location == '' ? $model->addError('location', $requiredName) : null;
            $model->urgent == '' ? $model->addError('urgent', $requiredName) : null;
            $model->data_json['total_person_count'] == '' ? $model->addError('data_json[total_person_count]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }
    
    /**
     * Creates a new VehicleCar model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $carType = $this->request->get('type'); 
        $model = new Vehicle([
            'car_type' => $carType
        ]);
        $model->leader_id = $model->Approve()['approve_1']['id'];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                $model->status = 'RECERVE';
                if($model->save(false)){
                    $this->checkLocation($model);
                    $this->createDetail($model);
                    
                    \Yii::$app->response->format = Response::FORMAT_JSON;
                    return $this->redirect(['/me/Vehicle-car']);
                    return [
                        'status' => 'success'
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

    protected function createDetail($model)
    {
        $startDate = new DateTime($model->date_start);
        $endDate = new DateTime($model->date_end);
        $endDate->modify('+1 day'); // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

        $interval = new DateInterval('P1D'); // ระยะห่าง 1 วัน
        $period = new DatePeriod($startDate, $interval, $endDate);

        $dates = [];
        foreach ($period as $date) {
            
            $dates[] = $date->format('Y-m-d');
            $newDetail = new VehicleDetail;
            $newDetail->name = 'driver_detail';
            $newDetail->Vehicle_id = $model->id;
            $newDetail->date_start = $date->format('Y-m-d');
            $newDetail->date_end = $date->format('Y-m-d');
            $newDetail->save(false);
        }
        

    }

    protected function checkLocation($model)
    {
     $location = Categorise::findOne($model->location);  
     if(!$location){
        $maxCode = Categorise::find()
    ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
    ->where(['like', 'name', 'document_org'])
    ->scalar();
        $newLocation = new Categorise;
        $newLocation->code = ($maxCode+1);
        $newLocation->title = $model->location;
        $newLocation->name = 'document_org';
        $newLocation->save(false);
     } 
    }

    /**
     * Updates an existing VehicleCar model.
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

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionApprove($id)
    {
        $model = Approve::findOne($id);

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
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


    public function actionReject($id)
    {
        $model = Approve::findOne($id);

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_reject', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_reject', [
                'model' => $model,
            ]);
        }
    }
    

    /**
     * Deletes an existing VehicleCar model.
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
     * Finds the VehicleCar model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return VehicleCar the loaded model
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