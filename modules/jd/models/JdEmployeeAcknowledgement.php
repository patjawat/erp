<?php

namespace app\modules\jd\models;

use yii\db\ActiveRecord;

class JdEmployeeAcknowledgement extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_employee_acknowledgement}}';
    }

    public function rules()
    {
        return [
            [['jd_employee_id', 'emp_id', 'user_id', 'employee_name', 'acknowledged_at'], 'required'],
            [['jd_employee_id', 'emp_id', 'user_id'], 'integer'],
            [['acknowledged_at'], 'safe'],
            [['employee_name', 'user_agent'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }
}
