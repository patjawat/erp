<?php

namespace app\modules\serviceProfile\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

class ServiceProfileApproval extends ActiveRecord
{
    public const STAGE_QUALITY = 'quality_review';
    public const STAGE_DIRECTOR = 'director_approval';
    public const STAGE_HEAD = 'head_acknowledgement';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PASSED = 'passed';
    public const STATUS_RETURNED = 'returned';
    public static function tableName() { return '{{%service_profile_approval}}'; }
    public function rules() { return [[['service_profile_id', 'stage', 'employee_id', 'employee_name_snapshot'], 'required'], [['service_profile_id', 'employee_id', 'acted_by_user_id'], 'integer'], [['comment'], 'string'], [['data_json', 'acted_at', 'created_at', 'updated_at'], 'safe'], [['stage'], 'in', 'range' => [self::STAGE_QUALITY, self::STAGE_DIRECTOR, self::STAGE_HEAD]], [['status'], 'in', 'range' => [self::STATUS_WAITING, self::STATUS_PENDING, self::STATUS_PASSED, self::STATUS_RETURNED]]]; }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'employee_id']); }
}
