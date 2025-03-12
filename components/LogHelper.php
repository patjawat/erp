<?php

namespace app\components;

use Yii;
use DateTime;
use app\models\Visit;
use yii\helpers\Html;
use app\models\Profile;
use yii\base\Component;
use app\models\Hospcode;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\modules\usermanager\models\User;

// รวม function ตที่ใช้งานบ่อยๆ
class LogHelper extends Component
{
   public static function log($name,$data = [])
   {
       $model = new \app\models\Logs();
       $model->id = Yii::$app->security->generateRandomString(36);
       $model->name = $name;
       $model->data_json = $data;
       $model->save();
   }
}
