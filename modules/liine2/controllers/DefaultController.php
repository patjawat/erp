<?php

namespace app\modules\liine2\controllers;

use yii\web\Controller;

/**
 * Default controller for the `liine2` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
