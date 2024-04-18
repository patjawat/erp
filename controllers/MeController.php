<?php

namespace app\controllers;

class MeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
