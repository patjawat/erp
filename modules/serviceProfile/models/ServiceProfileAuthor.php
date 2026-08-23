<?php

namespace app\modules\serviceProfile\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

class ServiceProfileAuthor extends ActiveRecord
{
    public const ROLE_COORDINATOR = 'coordinator';
    public const ROLE_AUTHOR = 'author';
    public static function tableName() { return '{{%service_profile_author}}'; }
    public function rules() { return [[['service_profile_id', 'employee_id', 'role'], 'required'], [['service_profile_id', 'employee_id', 'assigned_by'], 'integer'], [['assigned_at'], 'safe'], [['role'], 'in', 'range' => [self::ROLE_COORDINATOR, self::ROLE_AUTHOR]]]; }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'employee_id']); }
    public function getProfile() { return $this->hasOne(ServiceProfile::class, ['id' => 'service_profile_id']); }
}
