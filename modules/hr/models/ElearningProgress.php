<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "hr_elearning_progress".
 *
 * @property int $id
 * @property int $emp_id รหัสพนักงาน
 * @property int $course_id รหัสหลักสูตร
 * @property string $status สถานะการเรียน (not_started, learning, completed)
 * @property string|null $started_at เริ่มเรียนเมื่อ
 * @property string|null $completed_at เรียนเสร็จเมื่อ
 */
class ElearningProgress extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_progress';
    }

    public function rules()
    {
        return [
            [['emp_id', 'course_id'], 'required'],
            [['emp_id', 'course_id'], 'integer'],
            [['started_at', 'completed_at'], 'safe'],
            [['status'], 'string', 'max' => 50],
            [['status'], 'default', 'value' => 'not_started'],
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
            'status' => 'สถานะการเรียน',
            'started_at' => 'วันที่เริ่มเรียน',
            'completed_at' => 'วันที่เรียนเสร็จ',
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
