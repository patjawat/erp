<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class GeneralController extends \yii\web\Controller
{
    public function actionIndex()
    {

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index')
                ];
            }else{
                return $this->render('index');
        }
    }



}
