<?php

namespace app\modules\hr\models;

class ProbationEvaluationScore extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_evaluation_score}}'; }
    public function rules() { return [
        [['evaluation_id', 'template_item_id', 'score'], 'required'], [['evaluation_id', 'template_item_id'], 'integer'], [['score'], 'number', 'min' => 0],
    ]; }
    public function getItem() { return $this->hasOne(ProbationTemplateItem::class, ['id' => 'template_item_id']); }
}
