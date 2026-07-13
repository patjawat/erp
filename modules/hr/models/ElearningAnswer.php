<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "hr_elearning_answer".
 *
 * @property int $id
 * @property int $question_id รหัสโจทย์ข้อถาม
 * @property string $answer_text ข้อความตัวเลือก
 * @property int $is_correct 1=คำตอบที่ถูกต้อง, 0=คำตอบผิด
 */
class ElearningAnswer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_answer';
    }

    public function rules()
    {
        return [
            [['question_id', 'answer_text'], 'required'],
            [['question_id', 'is_correct'], 'integer'],
            [['is_correct'], 'default', 'value' => 0],
            [['answer_text'], 'string'],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElearningQuestion::class, 'targetAttribute' => ['question_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question_id' => 'คำถาม',
            'answer_text' => 'ตัวเลือกคำตอบ',
            'is_correct' => 'เฉลยว่าถูกต้อง',
        ];
    }

    public function getQuestion()
    {
        return $this->hasOne(ElearningQuestion::class, ['id' => 'question_id']);
    }
}
