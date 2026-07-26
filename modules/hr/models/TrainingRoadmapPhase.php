<?php

namespace app\modules\hr\models;

class TrainingRoadmapPhase extends TrainingRoadmapRecord
{
    public static function tableName() { return '{{%training_roadmap_phase}}'; }

    public function rules()
    {
        return [
            [['roadmap_id', 'title', 'sequence'], 'required'],
            [['roadmap_id', 'sequence', 'start_offset', 'end_offset', 'created_by', 'updated_by'], 'integer'],
            [['description', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['period_label'], 'string', 'max' => 100],
            [['offset_unit', 'color_role'], 'string', 'max' => 20],
            [['ref'], 'string', 'max' => 64],
        ];
    }

    public function getRoadmap() { return $this->hasOne(TrainingRoadmap::class, ['id' => 'roadmap_id']); }
    public function getActivities() { return $this->hasMany(TrainingRoadmapActivity::class, ['phase_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
}
