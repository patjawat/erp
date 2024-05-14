<?php

namespace app\modules\helpdesk\controllers;

class MedicalController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
