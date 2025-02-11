<?php

namespace app\modules\line\controllers;


use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\modules\hr\models\EmployeesSearch;

/**
 * Default controller for the `line` module
 */
class HomeController extends Controller
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
