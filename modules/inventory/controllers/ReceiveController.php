<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockEvent;
use app\modules\inventory\components\FrozenWriteGuard;

class ReceiveController extends \yii\web\Controller
{
    use FrozenWriteGuard;

    protected function frozenWriteActions(): array
    {
        return ['create'];
    }

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
