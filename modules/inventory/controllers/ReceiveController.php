<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockEvent;

class ReceiveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {
        $model = new StockEvent;
        return $this->render('_form',['model' => $model]);
    }

}
