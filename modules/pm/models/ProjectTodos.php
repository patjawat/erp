<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "project_todos".
 *
 * @property int $id คีย์หลักที่เป็น Auto Increment
 * @property int $project_id ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects
 * @property int|null $emp_id ไอดีของผู้ใช้งาน ซึ่งเชื่อมโยงกับตาราง users
 * @property int $task_id ขั้ตอนการทำงานของโปรเจกต์
 * @property string $name สถานะของโปรเจกต์
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 *
 * @property Projects $project
 * @property ProjectTasks $task
 */
class ProjectTodos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_todos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'task_id', 'name'], 'required'],
            [['project_id', 'emp_id', 'task_id', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Projects::class, 'targetAttribute' => ['project_id' => 'id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectTasks::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'คีย์หลักที่เป็น Auto Increment',
            'project_id' => 'ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects',
            'emp_id' => 'ไอดีของผู้ใช้งาน ซึ่งเชื่อมโยงกับตาราง users',
            'task_id' => 'ขั้ตอนการทำงานของโปรเจกต์',
            'name' => 'สถานะของโปรเจกต์',
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
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Projects::class, ['id' => 'project_id']);
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(ProjectTasks::class, ['id' => 'task_id']);
    }
}
