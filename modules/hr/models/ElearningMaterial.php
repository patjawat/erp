<?php

namespace app\modules\hr\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "hr_elearning_material".
 *
 * @property int $id
 * @property int $course_id รหัสหลักสูตร
 * @property string $title ชื่อสื่อการสอน
 * @property string $type ประเภทสื่อ (video_url, pdf_file, slide_link)
 * @property string $file_path ลิงก์หรือที่อยู่ไฟล์
 * @property int $sort_order ลำดับการแสดงผล
 * @property string $created_at วันเวลาสร้าง
 * @property string|null $updated_at วันเวลาแก้ไข
 */
class ElearningMaterial extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_material';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['course_id', 'title', 'type', 'file_path'], 'required'],
            [['course_id', 'sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'type'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElearningCourse::class, 'targetAttribute' => ['course_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'หลักสูตร',
            'title' => 'ชื่อสื่อการสอน',
            'type' => 'ประเภทสื่อ',
            'file_path' => 'ที่อยู่ไฟล์/ลิงก์',
            'sort_order' => 'ลำดับ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(ElearningCourse::class, ['id' => 'course_id']);
    }
}
