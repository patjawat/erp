<?php

namespace app\modules\hr\models;

class ExitInterviewQuestion extends ExitInterviewRecord
{
    public $options_text;
    public static function tableName() { return '{{%exit_interview_question}}'; }
    public function rules() { return [[['section_id', 'code', 'prompt', 'question_type'], 'required'], [['section_id', 'sequence', 'is_required', 'is_hr_only'], 'integer'], [['prompt', 'config_json', 'condition_json', 'options_text'], 'string'], [['code', 'analytics_key'], 'string', 'max' => 80], ['question_type', 'in', 'range' => ['short_text', 'long_text', 'single_choice', 'multi_choice', 'ranking', 'rating', 'date', 'number', 'display']]]; }
    public function getOptions() { return $this->hasMany(ExitInterviewQuestionOption::class, ['question_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
    public function getSection() { return $this->hasOne(ExitInterviewSection::class, ['id' => 'section_id']); }
    public function config(): array { $value = json_decode((string) $this->config_json, true); return is_array($value) ? $value : []; }
    public function condition(): array { $value = json_decode((string) $this->condition_json, true); return is_array($value) ? $value : []; }
}
