<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "project_tasks".
 *
 * @property int $id
 * @property string $task_name ชื่องาน
 *
 * @property ProjectToTasks[] $projectToTasks
 * @property Projects[] $projects
 */
class ProjectTasks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_name'], 'required'],
            [['task_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_name' => 'ชื่องาน',
        ];
    }

    /**
     * Gets query for [[ProjectToTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectToTasks()
    {
        return $this->hasMany(ProjectToTasks::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Projects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Projects::class, ['id' => 'project_id'])->viaTable('project_to_tasks', ['task_id' => 'id']);
    }
}
