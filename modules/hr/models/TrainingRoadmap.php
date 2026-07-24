<?php

namespace app\modules\hr\models;

class TrainingRoadmap extends TrainingRoadmapRecord
{
    public static function tableName()
    {
        return '{{%training_roadmap}}';
    }

    public function rules()
    {
        return [
            [['code', 'title', 'duration_value', 'duration_unit'], 'required'],
            [['version_no', 'duration_value', 'supersedes_id', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['description', 'target_json', 'effective_from', 'effective_to', 'approved_at', 'created_at', 'updated_at'], 'safe'],
            [['code', 'ref'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 255],
            [['roadmap_type'], 'string', 'max' => 40],
            [['duration_unit', 'status'], 'string', 'max' => 20],
            [['code', 'version_no'], 'unique', 'targetAttribute' => ['code', 'version_no']],
            ['duration_value', 'integer', 'min' => 1],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['duration_unit', 'in', 'range' => array_keys(self::durationUnitOptions())],
        ];
    }

    public static function statusOptions()
    {
        return ['draft' => 'ฉบับร่าง', 'review' => 'รอตรวจทาน', 'active' => 'ใช้งาน', 'retired' => 'ยกเลิกใช้'];
    }

    public static function typeOptions()
    {
        return [
            'onboarding' => 'ปฐมนิเทศบุคลากรใหม่',
            'professional' => 'พัฒนาตามวิชาชีพ',
            'position' => 'พัฒนาตามตำแหน่ง',
            'unit' => 'พัฒนาตามหน่วยงาน',
            'leadership' => 'เตรียมความพร้อมก่อนรับบทบาท',
            'renewal' => 'ทบทวนหรือรักษาสมรรถนะ',
            'gap' => 'แก้ไขช่องว่างจากการประเมิน',
        ];
    }

    public static function durationUnitOptions()
    {
        return ['day' => 'วัน', 'week' => 'สัปดาห์', 'month' => 'เดือน'];
    }

    public function getPhases()
    {
        return $this->hasMany(TrainingRoadmapPhase::class, ['roadmap_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getMilestones()
    {
        return $this->hasMany(TrainingRoadmapMilestone::class, ['roadmap_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getAssignments()
    {
        return $this->hasMany(EmployeeTrainingPlan::class, ['roadmap_id' => 'id']);
    }

    public function getStatusLabel()
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }
}
