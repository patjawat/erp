<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Booking;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\BookingSearch;

/**
 * CarController implements the CRUD actions for Booking model.
 */
class DriverController extends Controller
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
     * Lists all Booking models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $carType = $this->request->get('car_type');
        $status = $this->request->get('status');
        $searchModel = new BookingSearch([
            'car_type' => $carType,
            'status' => $status
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'driver_service']);
        // $dataProvider->query->andFilterWhere(['car_type' => $carType]);
        // $dataProvider->query->andFilterWhere(['status' => $status]);
        $dataProvider->query->orderBy([
            'id' => SORT_DESC,
            // 'id' => SORT_ASC,
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel = new BookingSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'driver_service']);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    /**
     * Displays a single Booking model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Booking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Booking();

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
     * Updates an existing Booking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['/booking/driver','car_type' => $model->car_type,'status' => $model->status]);
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

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

    /**
     * Deletes an existing Booking model.
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
     * Finds the Booking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Booking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionListCar()
    {
        $id = $this->request->get('id');
        $model = BookingDetail::findOne($id);
        
        if ($this->request->isAJax && $this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = $this->request->post('id');
            $license_plate = $this->request->post('license_plate');
            $model = BookingDetail::findOne($id);
            if($model){
                $model->license_plate = $license_plate;
                if($model->save(false)){
                    return [
                        'status' => 'success',
                    ];
                }else{
                    return [
                     'status' => 'error',
                    ];
                }
            }
          
        }
        
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_cars',[
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('list_cars',[
                'model' => $model
            ]);
        }
    }

    public function actionListDetailItems($id)
    {
        $model = BookingDetail::findOne($id);
        $type = $this->request->get('type');
        if ($this->request->isAJax && $this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
           
            $booking_id = $this->request->post('booking_id');
            $license_plate = $this->request->post('license_plate');
            $driver_id = $this->request->post('driver_id');
            $model = BookingDetail::findOne($id);
            if($model){
                $model->license_plate = $license_plate;
                $model->driver_id = $driver_id;
                if($model->save(false)){
                    return [
                        'status' => 'success',
                        'data' => $model
                    ];
                }else{
                    return [
                     'status' => 'error',
                    ];
                }
            }
          
        }
        
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_detail_items',[
                    'model' => $model,
                    'type' => $type
                ]),
            ];
        } else {
            return $this->render('list_detail_items',[
                'model' => $model,
                'type' => $type
            ]);
        }
    }
    
    public function actionUpdateDetail()
    {
        if ($this->request->isAJax && $this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = $this->request->post('id');
            $license_plate = $this->request->post('license_plate');
            $model = BookingDetail::findOne($id);
            if($model){
                $model->license_plate = $license_plate;
                if($model->save(false)){
                    return [
                        'status' => 'success',
                    ];
                }else{
                    return [
                     'status' => 'error',
                    ];
                }
            }
          
        }
    }
}
