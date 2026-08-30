<?php

namespace app\modules\hr\models;

class ExitInterviewAnswer extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_answer}}'; }
    public function rules() { return [[['interview_id', 'question_id', 'question_snapshot'], 'required'], [['interview_id', 'question_id'], 'integer'], [['value_number'], 'number'], [['question_snapshot', 'value_text', 'value_json'], 'string']]; }
    public function decodedValue() { if ($this->value_json !== null && $this->value_json !== '') { $v = json_decode($this->value_json, true); return is_array($v) ? $v : []; } return $this->value_number !== null ? (float) $this->value_number : $this->value_text; }
}
