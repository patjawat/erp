<?php

namespace app\modules\hr\models;

class TrainingRoadmapActivity extends TrainingRoadmapRecord
{
    public static function tableName() { return '{{%training_roadmap_activity}}'; }

    public function rules()
    {
        return [
            [['phase_id', 'title', 'sequence', 'activity_type', 'requirement_type'], 'required'],
            [['phase_id', 'sequence', 'competency_level', 'created_by', 'updated_by'], 'integer'],
            [['target_value'], 'number', 'min' => 0],
            [['is_required', 'evidence_required'], 'boolean'],
            [['description', 'checklist_json', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['competency_code', 'development_method'], 'string', 'max' => 100],
            [['activity_type'], 'string', 'max' => 40],
            [['requirement_type'], 'string', 'max' => 30],
            [['ref'], 'string', 'max' => 64],
        ];
    }

    public static function typeOptions()
    {
        return [
            'orientation' => 'ปฐมนิเทศ', 'training' => 'เข้าอบรม', 'self_learning' => 'ศึกษาด้วยตนเอง',
            'practice' => 'ฝึกปฏิบัติงาน', 'coaching' => 'Coaching / Mentoring',
            'case' => 'กรณีศึกษา/สะสม Case', 'exam' => 'สอบ', 'simulation' => 'Simulation',
            'demonstration' => 'สาธิตย้อนกลับ', 'checklist' => 'ผ่าน Checklist', 'portfolio' => 'ส่งผลงาน',
        ];
    }

    public static function requirementOptions()
    {
        return ['complete' => 'ทำให้ครบ', 'pass_fail' => 'ผ่าน/ไม่ผ่าน', 'score' => 'คะแนน', 'count' => 'จำนวนครั้ง/Case', 'level' => 'ระดับความสามารถ', 'checklist' => 'Checklist'];
    }

    public function getPhase() { return $this->hasOne(TrainingRoadmapPhase::class, ['id' => 'phase_id']); }
}
