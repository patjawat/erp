<?php

namespace app\modules\iacRisk\models;

use yii\db\ActiveRecord;

class ServiceProcess extends ActiveRecord
{
    public static function tableName(): string { return '{{%iac_service_process}}'; }
    public function getVersions() { return $this->hasMany(ServiceProcessVersion::class, ['process_id' => 'id']); }
}
