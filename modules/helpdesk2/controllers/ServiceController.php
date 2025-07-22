<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use DateTime;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

class ServiceController extends \yii\web\Controller
{
        public function actionView($id)
    {
        $model = $this->findModel($id);
                if($this->request->isAjax){
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                'model' => $model,
        ])
            ];
        }else{
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }
public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->request_repair_date = AppHelper::convertToThai($model->request_repair_date);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                try {
                    $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
                } catch (\Throwable $th) {
                }
                
                if($model->save()){
                    return [
                        'status' => 'success' 
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if($this->request->isAjax){
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
            'model' => $model,
        ])
            ];
        }else{
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    
    public function actionTechnician()
    {
        return $this->render('technician/index');
    }


    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
