<?php

namespace app\modules\roster\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(['/roster/period/index']);
    }
}
