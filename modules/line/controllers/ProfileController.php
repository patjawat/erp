<?php

namespace app\modules\line\controllers;
use Yii;
use app\modules\hr\models\Employees;
use app\components\EmployeeHelper;
class ProfileController extends \yii\web\Controller
{

    public function beforeAction($action) {
     

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/line/auth/login']);
        }

        return parent::beforeAction($action);
    }


    public function actionIndex()
    {

        $model = EmployeeHelper::GetEmployee();

        return $this->render('index',[
            'model' => $model
        ]);
    }

}
