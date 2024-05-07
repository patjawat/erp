<?php

namespace app\controllers;
use Yii;
use app\modules\hr\models\Employees;

class MeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        return $this->render('index',[
            'model' => $model ? $model : new Employees()
        ]);
    }

}
