<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "project_status".
 *
 * @property int $id คีย์หลักที่เป็น Auto Increment
 * @property int $project_id ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects
 * @property string $name สถานะของโปรเจกต์
 */
class ProjectStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'name'], 'required'],
            [['project_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'สถานะของโปรเจกต์',
        ];
    }
}
