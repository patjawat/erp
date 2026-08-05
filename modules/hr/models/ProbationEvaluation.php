<?php

namespace app\modules\hr\models;

class ProbationEvaluation extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_evaluation}}'; }
    public function rules() { return [
        [['round_id', 'evaluator_employee_id', 'role'], 'required'], [['round_id', 'evaluator_employee_id'], 'integer'],
        [['total_score', 'max_score', 'percent_score'], 'number'], [['submitted_at', 'reopened_at', 'reopen_reason'], 'safe'],
        [['comment'], 'string'],
        ['role', 'in', 'range' => ['self', 'supervisor', 'group_head']], ['status', 'in', 'range' => ['pending', 'open', 'submitted']],
    ]; }
    public static function roleOptions() { return ['self' => 'ประเมินตนเอง', 'supervisor' => 'หัวหน้างานประเมิน', 'group_head' => 'หัวหน้ากลุ่มงานประเมิน']; }
    public function getRoleLabel() { return self::roleOptions()[$this->role] ?? $this->role; }
    public function getRound() { return $this->hasOne(ProbationRound::class, ['id' => 'round_id']); }
    public function getEvaluator() { return $this->hasOne(Employees::class, ['id' => 'evaluator_employee_id']); }
    public function getScores() { return $this->hasMany(ProbationEvaluationScore::class, ['evaluation_id' => 'id']); }
}
