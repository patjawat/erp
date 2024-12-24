<?php

namespace app\modules\dms\controllers;

class DashboardController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
