<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Response;
use app\components\AppHelper;
use app\modules\hr\models\Calendar;
use app\modules\hr\models\Employees;

class HolidaysController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index'),
            ];
        } else {
            return $this->render('index');
        }
    }
    public function actionCreate()
    {
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        if ($this->request->isPost) {
            if ($this->request->post()) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $start = $this->request->post('start');
                $model = new Calendar([
                    'emp_id' => $me->id,
                    'name' => 'holiday_me',
                    'date_start' => $start,
                    'date_end' => $start,
                    'thai_year' => AppHelper::YearBudget(),
                ]);
                $model->save();
                
                return [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionEvents()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        $events = Calendar::find()->where(['emp_id' => $me->id])->all();
        $data = [];
        foreach ($events as $item) {
            $data [] = [
                'id'               => $item->id,
				'title'            => 'OFF',
				'start'            => $item->date_start,
				'end'            => $item->date_end,
                'editable'=> true,
				'startEditable'    => true,
				'durationEditable' => false,
            ];
        }
        return $data;
    }

}
