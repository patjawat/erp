<?php

namespace app\modules\helpdesk\controllers;

class ComputerController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
