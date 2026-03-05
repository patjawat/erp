<?php

namespace app\modules\jd\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(['template/index']);
    }
}
