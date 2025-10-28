<?php

namespace app\modules\helpdesk3\controllers;

use yii\web\Controller;

/**
 * Default controller for the `helpdesk3` module
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
