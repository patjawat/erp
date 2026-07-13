<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "hr_elearning_question".
 *
 * @property int $id
 * @property int $course_id รหัสหลักสูตร
 * @property string $question_text โจทย์คำถาม
 * @property int $sort_order ลำดับคำถาม
 */
class ElearningQuestion extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_question';
    }

    public function rules()
    {
        return [
            [['course_id', 'question_text'], 'required'],
            [['course_id', 'sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['question_text'], 'string'],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElearningCourse::class, 'targetAttribute' => ['course_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'หลักสูตร',
            'question_text' => 'โจทย์คำถาม',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(ElearningCourse::class, ['id' => 'course_id']);
    }

    public function getAnswers()
    {
        return $this->hasMany(ElearningAnswer::class, ['question_id' => 'id']);
    }
}
