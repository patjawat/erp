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

    
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => Yii::$app->request->get('title'),
            'content' => $this->renderAjax('view',['model' => $model])
        ];
    }
    public function actionCreate()
    {
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        if ($this->request->isPost) {
            if ($this->request->post()) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $start = $this->request->post('start');
                $check = Calendar::findOne(['emp_id' => $me->id, 'date_start' => $start, 'name' => 'holiday_me']);
                if ($check) {
                    return [
                        'status' => 'error',
                        'container' => '#leave',
                        'massages' => 'วันที่ทำการเลือกมีการบันทึกไปแล้ว'
                    ];
                }   
                }
                $model = new Calendar([
                    'emp_id' => $me->id,
                    'title' => 'OFF',
                    'name' => 'holiday_me',
                    'date_start' => $start,
                    'date_end' => $start,
                    'thai_year' => AppHelper::YearBudget(),
                ]);
                $model->save();
                
                return [
                    'status' => 'success',
                    'container' => '#leave',
                    'massages' => 'บันทึกข้อมูลสำเร็จ!',
                    'data' => $model
                ];

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

    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id =  $this->request->post('id');
        $start =  $this->request->post('start');
        $end =  $this->request->post('end');
        $model = $this->findModel($id);
        $model->date_start = $start;
        $model->date_end = $end;
        $model->save();
        return [
            'status' => 'success',
            'container' => '#leave'
        ];
    }
    
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findModel($id)->delete();
        return [
            'status' => 'success',
            'container' => '#leave'
        ];
       
    }
    
    protected function findModel($id)
    {
        if (($model = Calendar::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
