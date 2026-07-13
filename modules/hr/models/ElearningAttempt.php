<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "hr_elearning_attempt".
 *
 * @property int $id
 * @property int $emp_id รหัสพนักงาน
 * @property int $course_id รหัสหลักสูตร
 * @property int $attempt_number ครั้งที่ทำแบบทดสอบ
 * @property int $score คะแนนที่ทำได้
 * @property int $total_questions จำนวนคำถามทั้งหมด
 * @property float $percentage เปอร์เซ็นต์คะแนนที่ได้
 * @property int $is_passed 1=ผ่านเกณฑ์, 0=ไม่ผ่านเกณฑ์
 * @property string $created_at วันเวลาที่บันทึก
 */
class ElearningAttempt extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_attempt';
    }

    public function rules()
    {
        return [
            [['emp_id', 'course_id', 'attempt_number', 'score', 'total_questions', 'percentage', 'is_passed', 'created_at'], 'required'],
            [['emp_id', 'course_id', 'attempt_number', 'score', 'total_questions', 'is_passed'], 'integer'],
            [['percentage'], 'number'],
            [['created_at'], 'safe'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElearningCourse::class, 'targetAttribute' => ['course_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'บุคลากร',
            'course_id' => 'หลักสูตร',
            'attempt_number' => 'ครั้งที่สอบ',
            'score' => 'คะแนนที่ได้',
            'total_questions' => 'จำนวนข้อสอบ',
            'percentage' => 'คะแนนคิดเป็น (%)',
            'is_passed' => 'สถานะผลสอบ',
            'created_at' => 'วันเวลาที่ทำข้อสอบ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getCourse()
    {
        return $this->hasOne(ElearningCourse::class, ['id' => 'course_id']);
    }
}
