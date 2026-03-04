<?php

namespace app\modules\approveV3\controllers;

use Yii;
use yii\web\Controller;

/**
 * Default controller for the `approveV3` module
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
