<?php

namespace app\modules\hr\models;

class ProbationAcknowledgement extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_acknowledgement}}'; }
    public function rules() { return [[['case_id', 'director_employee_id', 'acknowledged_at'], 'required'], [['case_id', 'director_employee_id'], 'integer'], [['acknowledged_at'], 'safe']]; }
}
