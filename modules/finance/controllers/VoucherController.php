<?php

namespace app\modules\finance\controllers;

use yii\web\Controller;

class VoucherController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
