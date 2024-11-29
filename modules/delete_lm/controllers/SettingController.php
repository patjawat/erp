<?php

namespace app\modules\lm\controllers;

use yii\web\Controller;

/**
 * Default controller for the `leave` module
 */
class SettingController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionStyle2()
    {
        return $this->render('style2');
    }
}
