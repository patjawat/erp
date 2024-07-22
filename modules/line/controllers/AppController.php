<?php

namespace app\modules\line\controllers;

class AppController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
