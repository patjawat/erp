<?php
namespace app\modules\gdoc\controllers;

use yii\web\Controller;

class SettingController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

        public function actionTemplate()
    {
        return $this->render('template');
    }
}
