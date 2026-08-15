<?php

namespace app\modules\finance\controllers;

use yii\web\Controller;

class PaymentController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
