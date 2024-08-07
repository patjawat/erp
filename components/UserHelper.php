<?php

namespace app\components;

use app\models\Categorise;
use app\modules\hr\models\Employees;
use Yii;
use yii\base\Component;
use app\modules\usermanager\models\User;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

// ใช้ง
// ใช้งานเกี่ยวกับ user
class UserHelper extends Component
{
   // คำนวน วัน เวลา ที่ผ่านมา
   //  public static function Duration($end){
   //     return round(abs(strtotime(date('Y-m-d 00:00:00')) - strtotime($model->created_at))/60/60/24);
   // }

   // ####  function เกี่ยวกับระบบ
// ดึงรายละเอียดของ role
   public static function userAssignment()
   {
      try {
         $id = Yii::$app->user->id;
         $sql = "SELECT * FROM `auth_assignment` LEFT JOIN auth_item ON auth_item.name = auth_assignment.item_name WHERE user_id = :id";
         $query = Yii::$app->db->createCommand($sql)
            ->bindParam(':id', $id)
            ->queryOne();

         return $query['description'];
         //code...
      } catch (\Throwable $th) {
         return null;
      }
   }
   public static function GetUser()
   {
      return User::findOne(Yii::$app->user->identity->id);
   }


   public static function GetEmployee()
   {
      return Employees::findOne(['user_id' => Yii::$app->user->id]);
   }



    // Avatar ของฉัน
    public static function getMe($msg=null)
    {
        try {
            $employee = self::GetEmployee();
            return [
                'avatar' => $employee->getAvatar(false,$msg),
                'emp_id' => $employee->id,
                'user_id' => $employee->user_id,
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'emp_id' => '',
                'user_id' => '',
                'department' => '',
                'fullname' => '',
            ];
        }
    }

   public static function phone()
   {
      return self::GetEmployee()->phone;
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