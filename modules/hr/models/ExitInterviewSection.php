<?php

namespace app\modules\hr\models;

class ExitInterviewSection extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_section}}'; }
    public function rules() { return [[['version_id', 'code', 'title'], 'required'], [['version_id', 'sequence'], 'integer'], [['description', 'condition_json'], 'safe'], [['code'], 'string', 'max' => 64], [['title'], 'string', 'max' => 255]]; }
    public function getQuestions() { return $this->hasMany(ExitInterviewQuestion::class, ['section_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
}
