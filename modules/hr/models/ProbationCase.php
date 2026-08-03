<?php

namespace app\modules\hr\models;

class ProbationCase extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_case}}'; }
    public function rules() { return [
        [['employee_id', 'template_id', 'supervisor_employee_id', 'group_head_employee_id', 'director_employee_id', 'final_recommender_employee_id', 'start_date'], 'required'],
        [['employee_id', 'template_id', 'supervisor_employee_id', 'group_head_employee_id', 'director_employee_id', 'final_recommender_employee_id'], 'integer'],
        [['start_date', 'cancel_reason'], 'safe'],
        [['status'], 'string', 'max' => 40],
        ['final_recommender_employee_id', 'validateFinalRecommender'],
    ]; }
    public function validateFinalRecommender($attribute): void
    {
        if (!in_array((int) $this->$attribute, [(int) $this->supervisor_employee_id, (int) $this->group_head_employee_id], true)) {
            $this->addError($attribute, 'ผู้สรุปต้องเป็นหัวหน้างานหรือหัวหน้ากลุ่มงาน');
        }
    }
    public static function statusOptions() { return [
        'assigned' => 'รอประเมินตนเอง', 'in_progress' => 'กำลังประเมิน', 'waiting_decision' => 'รอสรุปผล',
        'waiting_acknowledgement' => 'รอ ผอ.รับทราบ', 'completed_hire' => 'เสนอจ้างต่อ',
        'completed_no_hire' => 'เสนอไม่จ้างต่อ', 'cancelled' => 'ยกเลิก',
    ]; }
    public function attributeLabels() { return [
        'employee_id' => 'บุคลากรที่รับการประเมิน', 'template_id' => 'Template ตามวิชาชีพ',
        'supervisor_employee_id' => 'หัวหน้างาน', 'group_head_employee_id' => 'หัวหน้ากลุ่มงาน',
        'director_employee_id' => 'ผอ.ผู้รับทราบ', 'final_recommender_employee_id' => 'ผู้สรุปผล',
        'start_date' => 'วันที่เริ่มงาน',
    ]; }
    public function getStatusLabel() { return self::statusOptions()[$this->status] ?? $this->status; }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'employee_id']); }
    public function getSupervisor() { return $this->hasOne(Employees::class, ['id' => 'supervisor_employee_id']); }
    public function getGroupHead() { return $this->hasOne(Employees::class, ['id' => 'group_head_employee_id']); }
    public function getDirector() { return $this->hasOne(Employees::class, ['id' => 'director_employee_id']); }
    public function getFinalRecommender() { return $this->hasOne(Employees::class, ['id' => 'final_recommender_employee_id']); }
    public function getTemplate() { return $this->hasOne(ProbationTemplate::class, ['id' => 'template_id']); }
    public function getRounds() { return $this->hasMany(ProbationRound::class, ['case_id' => 'id'])->orderBy(['month_no' => SORT_ASC]); }
    public function getDecision() { return $this->hasOne(ProbationDecision::class, ['case_id' => 'id']); }
    public function getAcknowledgement() { return $this->hasOne(ProbationAcknowledgement::class, ['case_id' => 'id']); }
    public function currentRound() { foreach ($this->rounds as $round) if ($round->status !== 'completed') return $round; return null; }
}
