<?php

namespace app\modules\hr\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "hr_elearning_course".
 *
 * @property int $id
 * @property string $title ชื่อหลักสูตร
 * @property string|null $description รายละเอียดหลักสูตร
 * @property string|null $target_departments แผนกเป้าหมาย (JSON หรือ "all")
 * @property int $passing_score_percent เกณฑ์คะแนนสอบผ่าน (%)
 * @property int $is_active สถานะ 1=เปิดใช้งาน, 0=ปิดใช้งาน
 * @property string $created_at วันเวลาสร้าง
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class ElearningCourse extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hr_elearning_course';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['description'], 'string'],
            [['target_departments'], 'safe'],
            [['passing_score_percent', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['passing_score_percent'], 'default', 'value' => 80],
            [['is_active'], 'default', 'value' => 1],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'ชื่อหลักสูตร',
            'description' => 'รายละเอียดหลักสูตร',
            'target_departments' => 'แผนกที่เกี่ยวข้อง',
            'passing_score_percent' => 'เกณฑ์ผ่าน (%)',
            'is_active' => 'สถานะการใช้งาน',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function getMaterials()
    {
        return $this->hasMany(ElearningMaterial::class, ['course_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getQuestions()
    {
        return $this->hasMany(ElearningQuestion::class, ['course_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getProgresses()
    {
        return $this->hasMany(ElearningProgress::class, ['course_id' => 'id']);
    }

    public function getAttempts()
    {
        return $this->hasMany(ElearningAttempt::class, ['course_id' => 'id']);
    }
}
