<?php

namespace app\modules\hr\models;

class EmployeeTrainingPlan extends TrainingRoadmapRecord
{
    public static function tableName() { return '{{%employee_training_plan}}'; }
    public function rules()
    {
        return [
            [['emp_id', 'roadmap_id', 'start_date'], 'required'],
            [['emp_id', 'roadmap_id', 'mentor_emp_id', 'assessor_emp_id', 'assigned_by', 'created_by', 'updated_by'], 'integer'],
            [['progress_percent'], 'number', 'min' => 0, 'max' => 100],
            [['roadmap_snapshot_json', 'start_date', 'target_end_date', 'actual_end_date', 'assigned_at', 'completed_at', 'note', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 30],
            [['ref'], 'string', 'max' => 64],
        ];
    }
    public static function statusOptions()
    {
        return ['assigned' => 'มอบหมายแล้ว', 'in_progress' => 'กำลังดำเนินการ', 'assessment' => 'รอประเมินผล', 'completed' => 'สำเร็จ', 'paused' => 'พักแผน', 'cancelled' => 'ยกเลิก'];
    }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'emp_id']); }
    public function getRoadmap() { return $this->hasOne(TrainingRoadmap::class, ['id' => 'roadmap_id']); }
    public function getMentor() { return $this->hasOne(Employees::class, ['id' => 'mentor_emp_id']); }
    public function getAssessor() { return $this->hasOne(Employees::class, ['id' => 'assessor_emp_id']); }
    public function getResults() { return $this->hasMany(EmployeeTrainingResult::class, ['plan_id' => 'id']); }
}
