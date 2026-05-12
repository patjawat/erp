<?php

namespace app\modules\sm\controllers;

class ServiceController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
