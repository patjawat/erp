<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Booking;
use app\modules\booking\models\RoomSearch;
use app\modules\booking\models\BookingSearch;

class BookingMeetingController extends \yii\web\Controller
{
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
            'date_end' => $dateStart ? AppHelper::convertToThai($dateStart) : ''
        ]);


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                // $model->status = 'RECERVE';
                if($model->save(false)){
                    return $this->redirect(['index']);

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
       $model->date_end =  AppHelper::convertToThai($model->date_end);
       $old_data_json = $model->data_json;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                // $model->data_json = ArrayHelper::merge($old_data_json['accessory'], $model->data_json['accessory']);
                // return $model->data_json;
                // $model->status = 'RECERVE';
                if($model->save(false)){
                    return $this->redirect(['index']);
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
                // ->andWhere(['between', 'date_start', $start, $end])
                ->all();
                $data = [];

                foreach($bookings as $item)
                {
                    $dateStart = Yii::$app->formatter->asDatetime(($item->date_start.' '.$item->time_start), "php:Y-m-d\TH:i:s");
                    $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end.' '.$item->time_end), "php:Y-m-d\TH:i:s");
                    $data[] = [
                        'id'               => $item->id,
                        'topic' => 'เวลาเริ่ม '.$item->time_start.' สิ้นสุดเวลา '.$item->time_end,  
                        'title'            => $item->reason,
                        'start'            => $dateStart,
                        'end'            => $dateEnd,
                        'data' => $item,
                        'view' => $this->renderAjax('view', ['model' => $item]),
                        'description' => 'description for All Day Event',
                        // 'eventDisplay' => '',
                        'color' => 'yellow',   // an option!
                        'textColor' => 'black', // an option!
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
