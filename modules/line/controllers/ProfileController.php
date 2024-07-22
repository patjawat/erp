<?php

namespace app\modules\line\controllers;
use Yii;
use app\modules\hr\models\Employees;

class ProfileController extends \yii\web\Controller
{

    public function beforeAction($action) {
     
        // $visit_ = TCDSHelper::getVisit();
        // $hn = $visit_['hn'];
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/line/register']);
        }
        // if(empty($vn)){
        //     return $this->redirect(['/nursescreen']);
        // }
        // return parent::beforeAction($action);
    }


    public function actionIndex()
    {

        $model = Employees::findOne(8);

        return $this->render('index',[
            'model' => $model
        ]);
    }

}
