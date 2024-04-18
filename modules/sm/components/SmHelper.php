<?php

namespace app\modules\sm\components;

use app\models\Categorise;
use app\models\Hospcode;
use app\models\Profile;
use app\models\Visit;
use Yii;
use yii\helpers\ArrayHelper;
use yii\base\Component;
use app\modules\usermanager\models\User;

// รวม function ตที่ใช้งานบ่อยๆ
class SmHelper extends Component
{
   
    public static function InitailSm()
    {

        $idbuy = [
            'id' => 1,
            'orsernum' => 'SMR-660001'
        ];

       return  \Yii::$app->session->set('sm', $idbuy);
    }

    public static function GetInitailSm()
    {

       return \Yii::$app->session->get('sm');
    }

    public static function Clear()
    {

       return \Yii::$app->session->remove('sm');
    }

}