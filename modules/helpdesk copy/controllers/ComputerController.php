<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class ComputerController extends \yii\web\Controller
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
