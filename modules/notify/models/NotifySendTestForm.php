<?php

namespace app\modules\notify\models;

use yii\base\Model;

/**
 * Model สำหรับฟอร์มส่งแจ้งเตือนทดสอบ (ใช้กับ input_emp)
 */
class NotifySendTestForm extends Model
{
    public $recipient_emp_id;

    public function rules()
    {
        return [
            [['recipient_emp_id'], 'safe'],
        ];
    }

    public function formName()
    {
        return 'NotifySendTest';
    }
}
