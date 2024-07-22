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
        $id = Yii::$app->user->id;
        $model = Employees::findOne(['user_id' => $id]);

        return $this->render('index',[
            'model' => $model
        ]);
    }

}
