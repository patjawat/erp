<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\web\Controller;
use app\modules\sm\components\SmHelper;

/**
 * Default controller for the `sm` module
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

    public function actionDemo()
    {
        SmHelper::InitailSm();
    }

    public function actionClear()
    {
        SmHelper::Clear();
    }
    
}
