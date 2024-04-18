<?php

namespace app\components;

use app\models\Categorise;
use app\modules\employees\models\Employees;
use Yii;
use yii\base\Component;
use app\modules\usermanager\models\User;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

// ใช้ง
// ใช้งานเกี่ยวกับ user
class ProfileHelper extends Component
{



   public static function GetEmployee()
   {
      return Employees::findOne(['user_id' => Yii::$app->user->id]);
   }


   public static function UserImg()
   {
      $model = self::GetEmployee();
      if (isset($model->avatar)) {
         return Html::img('@web/avatar/' . $model->avatar, ['class' => 'view-avatar']);
      } else {
         return Html::img('@web/img/profiles/avatar-01.jpg', ['class' => 'view-avatar']);
      }
   }

   public static function Avatar()
   {
      return '<div class="profile-img-wrap edit-img">' . self::UserImg() . '
                              <div class="fileupload btn">
                                 <span class="btn-text"><i class="fa-regular fa-pen-to-square"></i></span>
                                 <input class="upload edit-avatar" type="file" id="avatarxx">
                              </div>
                           </div>';

   }


   public static function Fullname()
   {
      $model = self::GetEmployee();
      if($model){

         return $model->fullname;
      }
   }


   public static function Prefix()
   {
      return ArrayHelper::map(Categorise::find()->where(['name' => 'prefix'])->asArray()->all(), 'title', 'title');
   }

   public static function CateTream()
   {
      $sql = "SELECT p.filename,p.prefix,p.fname,p.lname,p.hospcode,h.name,h.province_name,auth_item.description 
      FROM profile p 
      INNER JOIN user u ON u.id = p.user_id 
      INNER JOIN auth_assignment ass ON ass.user_id = p.user_id 
      INNER JOIN auth_item ON auth_item.name = ass.item_name 
      INNER JOIN hospcode h ON h.hospcode = p.hospcode;";

      $query = Yii::$app->db->createCommand($sql)
         // ->bindParam(':id', $id)
         ->queryAll();
      return $query;

   }



}