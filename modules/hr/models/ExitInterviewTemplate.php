<?php

namespace app\modules\hr\models;

class ExitInterviewTemplate extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_template}}'; }
    public function rules() { return [[['code', 'title'], 'required'], [['description'], 'string'], [['code'], 'string', 'max' => 64], [['title'], 'string', 'max' => 255], ['status', 'in', 'range' => ['active', 'retired']]]; }
    public function getVersions() { return $this->hasMany(ExitInterviewTemplateVersion::class, ['template_id' => 'id'])->orderBy(['version_no' => SORT_DESC]); }
}
