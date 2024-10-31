<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;


class StockController extends \yii\web\Controller
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
