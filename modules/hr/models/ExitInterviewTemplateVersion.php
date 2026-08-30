<?php

namespace app\modules\hr\models;

class ExitInterviewTemplateVersion extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_template_version}}'; }
    public function rules() { return [[['template_id', 'version_no'], 'required'], [['template_id', 'version_no', 'published_by'], 'integer'], [['intro_text', 'published_at'], 'safe'], ['status', 'in', 'range' => ['draft', 'published', 'retired']]]; }
    public function getTemplate() { return $this->hasOne(ExitInterviewTemplate::class, ['id' => 'template_id']); }
    public function getSections() { return $this->hasMany(ExitInterviewSection::class, ['version_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
    public static function published() { return self::find()->where(['status' => 'published'])->orderBy(['published_at' => SORT_DESC, 'id' => SORT_DESC])->one(); }
}
