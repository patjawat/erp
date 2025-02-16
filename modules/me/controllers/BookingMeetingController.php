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
        $searchModel = new BookingSearch();
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
        $model = new Booking([
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

    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
