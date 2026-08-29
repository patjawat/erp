<?php

namespace app\modules\hr\models;

class ExitInterviewQuestionOption extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_question_option}}'; }
    public function rules() { return [[['question_id', 'value', 'label'], 'required'], [['question_id', 'sequence', 'is_other'], 'integer'], [['score'], 'number'], [['value'], 'string', 'max' => 100], [['label'], 'string', 'max' => 255]]; }
}
