<?php

namespace app\modules\hr\models;

class TrainingRoadmapMilestone extends TrainingRoadmapRecord
{
    public static function tableName() { return '{{%training_roadmap_milestone}}'; }
    public function rules()
    {
        return [
            [['roadmap_id', 'title', 'sequence', 'due_offset'], 'required'],
            [['roadmap_id', 'phase_id', 'sequence', 'due_offset', 'created_by', 'updated_by'], 'integer'],
            [['minimum_score'], 'number', 'min' => 0],
            [['requires_signoff'], 'boolean'],
            [['criteria_text', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['offset_unit'], 'string', 'max' => 20],
            [['ref'], 'string', 'max' => 64],
        ];
    }
    public function getRoadmap() { return $this->hasOne(TrainingRoadmap::class, ['id' => 'roadmap_id']); }
    public function getPhase() { return $this->hasOne(TrainingRoadmapPhase::class, ['id' => 'phase_id']); }
}
