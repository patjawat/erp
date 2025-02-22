<?php

namespace app\modules\me\controllers;
use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Booking;
use app\modules\booking\models\RoomSearch;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\BookingSearch;

class BookingMeetingController extends \yii\web\Controller
{

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
    
    public function actionIndex()
    { 
        $searchModel = new BookingSearch([
            'date_start' => $this->request->get('date_start') ?? date('Y-m-d')
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'conference_room']);
        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionListMe()
    { 
        $searchModel = new BookingSearch([
            'created_by' => Yii::$app->user->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'meeting']);
        $dataProvider->query->andWhere(['<>', 'status', 'cancel']);

        return $this->render('list_me', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


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

    public function actionSelectFormDepartment($id)
    {
            $model = $this->findModel($id);

            if ($this->request->isPost) {
                if ($model->load($this->request->post())) {
                    \Yii::$app->response->format = Response::FORMAT_JSON;



                    $listEmployees = Employees::find()
                    ->where(['department' => $model->tags_department])
                    ->all();
                    $data = [];
                    foreach($listEmployees as $item)
                    {
                        $newModel = new BookingDetail();
                        $newModel->name = 'meeting_menber';
                        $newModel->emp_id = $item->id;
                        $newModel->booking_id = $model->id;
                        $newModel->save(false);

                    }
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
            
            if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_department', [
                    'model' => $model
                ]),
            ];
        } 
    }

    public function actionFormMember($id)
    {
            $model = $this->findModel($id);

            if ($this->request->isPost) {
                if ($model->load($this->request->post())) {
                    \Yii::$app->response->format = Response::FORMAT_JSON;

                        $newModel = new BookingDetail();
                        $newModel->name = 'meeting_menber';
                        $newModel->emp_id = $model->data_json['emp_id'];
                        $newModel->booking_id = $model->id;
                        $newModel->save(false);

                    
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
            
            if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_member', [
                    'model' => $model
                ]),
            ];
        } 
    }
    

    public function actionDeleteMenber($id)
    {
        $model = BookingDetail::findOne($id);
        $model->delete();
        return $this->redirect(['view', 'id' => $model->booking_id]);
    }

    public function actionCancelOrder($id)
    {
            $model = $this->findModel($id);
            $old = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $model->status = 'cancel';
                $model->data_json = ArrayHelper::merge($old, $model->data_json);
                if($model->save(false)){
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
            'content' => $this->renderAjax('_form_cancel', [
                'model' => $model
            ]),
        ];
    }
    }
    
    public function actionCreate($room_id)
    {

        $me = UserHelper::GetEmployee();
        $carType = $this->request->get('type'); 
        $dateStart = $this->request->get('date_start'); 
        $room_id = $this->request->get('room_id'); 
        $model = new Booking([
            'name' => 'meeting',
            'emp_id' => $me->id,
            'room_id' => $room_id,
            'date_start' => $dateStart ? AppHelper::convertToThai($dateStart) : '',
            // 'date_end' => $dateStart ? AppHelper::convertToThai($dateStart) : '',
            'data_json' => [
                'phone' => $me->phone,
            ]
        ]);


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                // $model->date_end = AppHelper::convertToGregorian($model->date_end);
                $model->status = 'pending';
                if($model->save(false)){
                    return $this->redirect(['view', 'id' => $model->id]);
                    // return $this->redirect(['index']);

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
    

    public function actionUpdate($id)
    {
        $carType = $this->request->get('type'); 
        $dateStart = $this->request->get('date_start'); 
        $room_id = $this->request->get('room_id'); 
        $model = $this->findModel($id);
        
       $model->date_start =  AppHelper::convertToThai($model->date_start);

       $old_data_json = $model->data_json;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                if($model->save(false)){
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
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
    
    


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Booking();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->reason == '' ? $model->addError('reason', $requiredName) : null;
            // $model->location == '' ? $model->addError('location', $requiredName) : null;
            // $model->urgent == '' ? $model->addError('urgent', $requiredName) : null;
            $model->data_json['employee_point'] == '' ? $model->addError('data_json[employee_point]', $requiredName) : null;
            $model->data_json['employee_total'] == '' ? $model->addError('data_json[employee_total]', $requiredName) : null;
            $model->data_json['phone'] == '' ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['period_time'] == '' ? $model->addError('data_json[period_time]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }
    


    // ตรวจสอบความถูกต้องยกเลิก
    public function actionValidatorCancel()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Booking();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->reason == '' ? $model->addError('reason', $requiredName) : null;
            $model->data_json['cancel_note'] == '' ? $model->addError('data_json[cancel_note]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }
    

    public function actionListRoom()
    {
        $searchModel = new RoomSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'conference_room']);

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
   
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_room', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]),
            ];
        } else {
            return $this->render('list_room', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        }
    }

    // public function actionEvents($id, $start, $end)
    public function actionEvents()
	{
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;


            $bookings = Booking::find()
                ->where(['name' => 'meeting'])
                ->andWhere(['<>', 'status', 'cancel'])
                ->all();
                $data = [];

                foreach($bookings as $item)
                {
                    $dateStart = Yii::$app->formatter->asDatetime(($item->date_start.' '.$item->time_start), "php:Y-m-d\TH:i:s");
                    $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end.' '.$item->time_end), "php:Y-m-d\TH:i:s");
                    $data[] = [
                        'id'               => $item->id,
                        'title'            => $item->reason,
                        'start'            => $dateStart,
                        'end'            => $dateEnd,
                        'data' => $item,
                        'view' => $this->renderAjax('view', ['model' => $item]),
                        'description' => 'description for All Day Event',
                        // 'eventDisplay' => '',
                        'color' => 'danger',   // an option!
                        'textColor' => 'black', // an option!
                         'rendering' => 'background',
        'color'=> '#ff9f89'
                    ];
                }

            return  $data;
       
	}
    
    
    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
