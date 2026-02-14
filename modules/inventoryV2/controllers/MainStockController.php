<?php

namespace app\modules\inventoryV2\controllers;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
