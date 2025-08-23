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
use app\modules\inventory\models\Product;

// รวม function ตที่ใช้งานบ่อยๆ
class ProductHelper extends Component
{
    //ตรวจสอบรหัสซ้ำ
public static function checkCodeDuplicate($categoryId,$code)
{
    $sqlRunNumber="SELECT (number+1) FROM `auto_number` WHERE `group` = :group;";
    $codeNumber = Yii::$app->db->createCommand($sqlRunNumber)
    ->bindValue(':group',($categoryId.'-?'))->queryScalar();
    //ถ้าสร้าง code อัตโนมัติ
    if($code ==''){
        $checkCode1 = Product::findOne(['code' => $code,'name' => 'asset_item']);
        // ซ้ำ   
        if($checkCode1){
            return [
                'status' => false,
                'data' => $checkCode1,
                'msg' => 'สร้างอัตโนมัติ ซ้ำ'
            ];
        }
    }else{
        $newCode = $categoryId.'-'.$codeNumber;
        $checknewCode = Product::findOne(['code' => $newCode,'name' => 'asset_item']);  
        // ซ้ำ   
        if($checknewCode){
            return [
                'status' => false,
                'data' => $checknewCode,
                'msg' => 'ซ้ำระหัสเดิม'
            ];
        } 

    }
    return [
        'status' => true,
        'data' => []
    ];
}
}