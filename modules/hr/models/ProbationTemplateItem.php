<?php

namespace app\modules\hr\models;

class ProbationTemplateItem extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_template_item}}'; }
    public function rules() { return [
        [['template_id', 'category', 'question', 'max_score'], 'required'],
        [['template_id', 'sequence', 'active'], 'integer'],
        [['max_score'], 'number', 'min' => 0.01],
        [['question'], 'string'],
        [['category'], 'string', 'max' => 150],
    ]; }
    public function getTemplate() { return $this->hasOne(ProbationTemplate::class, ['id' => 'template_id']); }
    public function attributeLabels() { return ['category' => 'หมวดการประเมิน', 'question' => 'ข้อประเมิน', 'max_score' => 'คะแนนเต็ม', 'sequence' => 'ลำดับ']; }
}
