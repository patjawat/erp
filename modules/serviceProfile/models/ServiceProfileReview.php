<?php

namespace app\modules\serviceProfile\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

class ServiceProfileReview extends ActiveRecord
{
    public const DECISION_COMMENTED = 'commented';
    public const DECISION_ENDORSED = 'endorsed';
    public const DECISION_RETURNED = 'returned';
    public static function tableName() { return '{{%service_profile_review}}'; }
    public function rules() { return [[['service_profile_id', 'reviewer_employee_id'], 'required'], [['service_profile_id', 'reviewer_employee_id'], 'integer'], [['comment'], 'string'], [['decision'], 'in', 'range' => [self::DECISION_COMMENTED, self::DECISION_ENDORSED, self::DECISION_RETURNED]], [['decided_at', 'created_at', 'updated_at'], 'safe']]; }
    public function getReviewer() { return $this->hasOne(Employees::class, ['id' => 'reviewer_employee_id']); }
}
