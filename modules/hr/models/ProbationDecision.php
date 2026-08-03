<?php

namespace app\modules\hr\models;

class ProbationDecision extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_decision}}'; }
    public function rules() { return [
        [['case_id', 'average_percent', 'result', 'recommendation', 'summary_comment', 'decided_by_employee_id', 'decided_at'], 'required'],
        [['case_id', 'decided_by_employee_id'], 'integer'], [['average_percent', 'threshold_percent'], 'number'], [['summary_comment'], 'string'], [['decided_at'], 'safe'],
        ['result', 'in', 'range' => ['passed', 'failed']], ['recommendation', 'in', 'range' => ['hire', 'no_hire']],
    ]; }
    public function getCase() { return $this->hasOne(ProbationCase::class, ['id' => 'case_id']); }
}
