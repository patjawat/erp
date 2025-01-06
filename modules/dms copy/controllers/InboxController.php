<?php

namespace app\modules\dms\controllers;

class InboxController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
