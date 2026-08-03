<?php

namespace app\modules\hr\models;

class ProbationRound extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_round}}'; }
    public function rules() { return [
        [['case_id', 'month_no', 'due_date'], 'required'], [['case_id', 'month_no'], 'integer'],
        ['month_no', 'in', 'range' => [1, 2, 3]], [['due_date', 'opened_at', 'completed_at'], 'safe'], [['status'], 'string', 'max' => 40],
    ]; }
    public static function statusOptions() { return ['scheduled' => 'ยังไม่เปิดรอบ', 'waiting_self' => 'รอประเมินตนเอง', 'waiting_supervisor' => 'รอหัวหน้างาน', 'waiting_group_head' => 'รอหัวหน้ากลุ่มงาน', 'completed' => 'เสร็จสิ้น']; }
    public function getStatusLabel() { return self::statusOptions()[$this->status] ?? $this->status; }
    public function getCase() { return $this->hasOne(ProbationCase::class, ['id' => 'case_id']); }
    public function getEvaluations() { return $this->hasMany(ProbationEvaluation::class, ['round_id' => 'id'])->orderBy(['id' => SORT_ASC]); }
}
