<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `me` module
 */
class MenuController extends Controller
{
    public function actionIndex() {
        return $this->render('index');
    }
}
