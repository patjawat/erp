<?php

namespace app\modules\serviceProfile\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

class ServiceProfileQualityReviewer extends ActiveRecord
{
    public static function tableName() { return '{{%service_profile_quality_reviewer}}'; }
    public function rules() { return [[['owner_type', 'owner_id', 'employee_id'], 'required'], [['owner_id', 'employee_id', 'created_by'], 'integer'], [['is_lead', 'active'], 'boolean'], [['effective_from', 'effective_to', 'created_at'], 'safe'], [['owner_type'], 'string', 'max' => 30]]; }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'employee_id']); }
}
