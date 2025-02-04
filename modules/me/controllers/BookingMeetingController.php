<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomSearch;
class BookingMeetingController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
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

}
