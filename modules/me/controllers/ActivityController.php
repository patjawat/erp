<?php

namespace app\modules\me\controllers;

class ActivityController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
