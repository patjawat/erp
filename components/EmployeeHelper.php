<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\bootstrap5\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;

// ใช้ง
// ใช้งานเกี่ยวกับ user
class EmployeeHelper extends Component
{
   // คำนวน วัน เวลา ที่ผ่านมา
   //  public static function Duration($end){
   //     return round(abs(strtotime(date('Y-m-d 00:00:00')) - strtotime($model->created_at))/60/60/24);
   // }

   public static function Initail($id)
   {
       $model = self::GetEmployee($id);
       \Yii::$app->session->set('employee', $model);
   }

   public static function GetEmployee($ids = null)
   {
$id = $ids ? \Yii::$app->session->set('employee')['id'] : null;
       if (($model = Employees::findOne(['id' => $id])) !== null) {
           return $model;
       } else {
           return null;
       }

   }


   public static function isDirector($user_id){
   
      $sql = "SELECT * FROM `auth_assignment` WHERE item_name = 'director' AND user_id = :user_id;";
      $query = Yii::$app->db->createCommand($sql)
         ->bindValue(':user_id', $user_id)
         ->queryOne();
      if($query){
         return true;
      }else{
         return false;
      }
   }
   
   public static function checkNull(){
      $education = Employees::find()->where(['education' => null])->count('id');
      $position = Employees::find()->where(['position_name' => null])->count('id');
      $status = Employees::find()->where(['status' => 0])->count('id');
          return [
              'education' => $education,
              'position'=> $position,
              'status' => $status
          ];
  }


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

   public static function Summary()
   {
      $totalEmployee = Employees::find()->where(['NOT',['status'=>[5,7,8]]])->count();
      $retire = Employees::find()->where(['status' => '3'])->count();
      return [
         'total' => $totalEmployee,
         'retire' => $retire
      ];
   }



}