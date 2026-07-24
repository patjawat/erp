<?php

namespace app\modules\hr\models;

class EmployeeTrainingResult extends TrainingRoadmapRecord
{
    public static function tableName() { return '{{%employee_training_result}}'; }
    public function rules()
    {
        return [
            [['plan_id', 'activity_id'], 'required'],
            [['plan_id', 'activity_id', 'competency_level', 'assessed_by', 'created_by', 'updated_by'], 'integer'],
            [['result_value'], 'number'],
            [['result_text', 'evidence_json', 'assessed_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 30],
            [['ref'], 'string', 'max' => 64],
        ];
    }
    public function getPlan() { return $this->hasOne(EmployeeTrainingPlan::class, ['id' => 'plan_id']); }
    public function getActivity() { return $this->hasOne(TrainingRoadmapActivity::class, ['id' => 'activity_id']); }
}
