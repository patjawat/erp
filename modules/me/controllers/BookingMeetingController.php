<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
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
        $carType = $this->request->get('type'); 
        $dateStart = $this->request->get('date_start'); 
        $room_id = $this->request->get('room_id'); 
        $model = new Booking([
            'name' => 'meeting',
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
                    return $this->redirect(['view','id' => $model->id]);

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
    public function actionEvents($start, $end)
	{
        
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;


            $bookings = Booking::find()
                ->where(['name' => 'meeting'])
                ->andWhere(['between', 'date_start', $start, $end])
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
                        'end'              => $dateEnd,
                        'editable'         => true,
                        'startEditable'    => true,
                        'durationEditable' => true,
                    ];
                }

                

return  $data;
        // $models = Booking::find()->where()->all();
        
		return [
			[
				'id'               => uniqid(),
				'title'            => 'Appointment #' . rand(1, 999),
				'start'            => '2025-02-17T12:30:00',
				'end'              => '2025-02-17T13:30:00',
				'editable'         => true,
				'startEditable'    => true,
				'durationEditable' => true,
			],
			// No overlap
			[
				'id'               => uniqid(),
				'title'            => 'Appointment #' . rand(1, 999),
				'start'            => '2025-02-17T15:30:00',
				'end'              => '2025-02-17T19:30:00',
				'overlap'          => false, // Overlap is default true
				'editable'         => true,
				'startEditable'    => true,
				'durationEditable' => true,
			],
			// Only duration editable
			[
				'id'               => uniqid(),
				'title'            => 'Appointment #' . rand(1, 999),
				'start'            => '2025-02-16T11:00:00',
				'end'              => '2025-02-16T11:30:00',
				'startEditable'    => false,
				'durationEditable' => true,
			],
			// Only start editable
			[
				'id'               => uniqid(),
				'title'            => 'Appointment #' . rand(1, 999),
				'start'            => '2025-02-15T14:00:00',
				'end'              => '2025-02-15T15:30:00',
				'startEditable'    => true,
				'durationEditable' => false,
			],
		];
	}
    
    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
