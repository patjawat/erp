<?php

namespace app\modules\serviceProfile\models;

use yii\db\ActiveRecord;

class ServiceProfileActivity extends ActiveRecord
{
    public static function tableName() { return '{{%service_profile_activity}}'; }
    public function rules() { return [[['service_profile_id', 'action'], 'required'], [['service_profile_id', 'section_id', 'created_by'], 'integer'], [['message'], 'string'], [['data_json', 'created_at'], 'safe'], [['action'], 'string', 'max' => 50], [['from_status', 'to_status'], 'string', 'max' => 30]]; }
}
