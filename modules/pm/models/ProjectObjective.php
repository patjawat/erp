<?php

namespace app\modules\pm\models;

use yii\db\ActiveRecord;

/**
 * วัตถุประสงค์ของโครงการ (ข้อ 2)
 *
 * @property int $id
 * @property int $project_id
 * @property int $sort
 * @property string $detail
 */
class ProjectObjective extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%project_objectives}}';
    }

    public function rules()
    {
        return [
            [['detail'], 'required'],
            [['project_id', 'sort'], 'integer'],
            [['detail'], 'string'],
            [['sort'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'detail' => 'วัตถุประสงค์',
        ];
    }
}
