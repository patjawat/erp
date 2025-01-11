<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "projects".
 *
 * @property int $id คีย์หลักที่เป็น Auto Increment
 * @property string $name ชื่อของโปรเจกต์
 * @property string|null $status สถานะของโปรเจกต์ เช่น Pending, In Progress, Completed
 * @property string|null $dead_line_date วันที่ครบกำหนดโปรเจกต์
 * @property string|null $start_date วันที่เริ่มต้นโปรเจกต์
 * @property string|null $end_date วันที่สิ้นสุดโปรเจกต์
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 *
 * @property ProjectToTasks[] $projectToTasks
 * @property ProjectTasks[] $tasks
 */
class Projects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['dead_line_date', 'start_date', 'end_date', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'คีย์หลักที่เป็น Auto Increment',
            'name' => 'ชื่อของโปรเจกต์',
            'status' => 'สถานะของโปรเจกต์ เช่น Pending, In Progress, Completed',
            'dead_line_date' => 'วันที่ครบกำหนดโปรเจกต์',
            'start_date' => 'วันที่เริ่มต้นโปรเจกต์',
            'end_date' => 'วันที่สิ้นสุดโปรเจกต์',
            'data_json' => 'Data Json',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    /**
     * Gets query for [[ProjectToTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTodos()
    {
        return $this->hasMany(ProjectDetails::class, ['project_id' => 'id'])->andOnCondition(['name' => 'todo']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(ProjectTasks::class, ['id' => 'task_id'])->viaTable('project_to_tasks', ['project_id' => 'id']);
    }
}
