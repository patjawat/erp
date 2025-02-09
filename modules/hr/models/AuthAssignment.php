<?php
namespace app\modules\hr\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;

class AuthAssignment extends ActiveRecord
{
    public static function tableName()
    {
        return 'auth_assignment';
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::className(), ['user_id' => 'user_id']);
    }
}