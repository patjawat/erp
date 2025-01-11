<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "project_details".
 *
 * @property int $id
 * @property string $name ชื่อของการก็บข้อมูลเพิ่มเติมที่เกี่ยวข้องกับโปรเจกต์
 * @property int|null $emp_id ไอดีของผู้ใช้งาน ซึ่งเชื่อมโยงกับตาราง users
 * @property int|null $project_id ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects
 */
class ProjectDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['emp_id', 'project_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อของการก็บข้อมูลเพิ่มเติมที่เกี่ยวข้องกับโปรเจกต์',
            'emp_id' => 'ไอดีของผู้ใช้งาน ซึ่งเชื่อมโยงกับตาราง users',
            'project_id' => 'ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects',
        ];
    }
}
