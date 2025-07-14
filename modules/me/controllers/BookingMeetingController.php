<?php

namespace app\modules\me\controllers;
use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use mdm\autonumber\AutoNumber;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Booking;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\RoomSearch;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\MeetingSearch;

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

    public function actionDashboard()
    {
        $searchModel = new MeetingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // รายการจองห้องประชุมที่กำลังจะถึงใน 7 วันข้างหน้า
        $dataProvider->query->andFilterWhere(['between', 'date_start', new Expression('CURDATE()'), new Expression('DATE_ADD(CURDATE(), INTERVAL 7 DAY)')]);
        // $dataProvider->pagination->pageSize = 7;
        return $this->render('dashboard',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionIndex()
    { 
        $me = UserHelper::GetEmployee();

        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new MeetingSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' => ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        
        try {
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        // $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        // $dataProvider->query->andFilterWhere(['between', 'date_start', $dateStart, $dateEnd]);
           
    } catch (\Throwable $th) {
        //throw $th;
    }
        $dataProvider->query->andFilterWhere(['emp_id' => $me->id]);
        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionListMe()
    { 
        $searchModel = new MeetingSearch([
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
                'title' =>$model->getUserReq()['avatar'],
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'action' => false
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'action' => false
            ]);
        }
    }

public function actionConfirm()
{
    \Yii::$app->response->format = Response::FORMAT_JSON;
    if ($this->request->isPost) {
        $model = $this->findModel($this->request->post('id'));
        $model->status = $this->request->post('status');
        if($model->save(false)){
            return [
                'status' => 'success'
            ];
        }
    }
    return [
        'status' => 'error',
        'message' => 'ไม่สามารถบันทึกข้อมูลได้'
    ];
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


    public function actionCancel($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
            $model = $this->findModel($id);
            $model->status = 'Cancel';
            if($model->save(false)){
                return [
                    'status' => 'success'
                ];
            }
            
       
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
    
    public function actionCreate()
    {

        $me = UserHelper::GetEmployee();
        $carType = $this->request->get('type'); 
        $dateStart = $this->request->get('date_start'); 
        $room_id = $this->request->get('room_id'); 
        $model = new Meeting([
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
                $model->code  = \mdm\autonumber\AutoNumber::generate('MEETING' .date('ymd') . '-???');
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                // $model->date_end = AppHelper::convertToGregorian($model->date_end);
                $model->status = 'Pending';
                if($model->save(false)){
                    \Yii::$app->response->format = Response::FORMAT_JSON;
                    $model->SendMeeting();
                    return [
                        'status' => 'success'
                    ];
                    // return $this->redirect(['/me/booking-meeting/index', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                // 'title' => $this->request->get('title'),
                'title' => 'จองห้องประชุม',
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
                    // $model->SendMeeting();
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
    
public function actionGetRoom($id)
{
    $model = Room::find()->where(['code' => $id,'name' => 'meeting_room'])->one();
    \Yii::$app->response->format = Response::FORMAT_JSON;
    return [
        'status' => 'success',
        'title' => $model->title,
        'img' => $model->showImg(),
        'seat' => $model->data_json['seat_capacity'] ?? 0,
        'data' => $model
    ];
}    


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Meeting();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->title == '' ? $model->addError('titlt', $requiredName) : null;
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


    public function actionEvents()
    {
        $start = $this->request->get('start');
        $end = $this->request->get('end');

        // Convert start and end dates to the desired format
        $start = (new DateTime($start))->format('Y-m-d');
        $end = (new DateTime($end))->format('Y-m-d');

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Meeting::find()
            ->andWhere(['between', 'date_start', $start, $end])
            ->andWhere(['or', ['status' => 'Pending'], ['status' => 'Pass']])
            ->orderBy(['id' => SORT_DESC]);

        $bookings = $query->all();
        $data = [];

        foreach ($bookings as $item) {
            $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $item->time_start), "php:Y-m-d\TH:i:s");
            $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end . ' ' . $item->time_end), "php:Y-m-d\TH:i:s");
            $data[] = [
                'id' => $item->id,
                'title' => $item->room->title,
                'start' => $dateStart,
                'end' => $dateStart,
                'extendedProps' => [
                    'title' => $this->renderAjax('@app/modules/booking/views/meeting/view_title', [
                        'model' => $item
                    ]), // Render the content for the event
                    'code' => $item->code,
                ],
                'className' => 'text-truncate px-2 border border-4 border-start border-top-0 border-end-0 border-bottom-0 border-' . $item->viewStatus()['color'],
                'description' => 'description for All Day Event',
                'textColor' => 'black',
                'backgroundColor' => '#3aa3e3',
            ];
        }

        return $data;
    }
        


    public function actionCalendar()
    {
        return $this->render('calendar');
    }
    
    
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    protected function findModel($id)
    {
        if (($model = Meeting::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
