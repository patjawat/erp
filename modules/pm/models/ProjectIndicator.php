<?php

namespace app\modules\pm\models;

use yii\db\ActiveRecord;

/**
 * เป้าหมาย/ตัวชี้วัดผลสำเร็จของโครงการ (ข้อ 3)
 *
 * @property int $id
 * @property int $project_id
 * @property int $sort
 * @property string $detail
 * @property float|null $target_percent
 */
class ProjectIndicator extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%project_indicators}}';
    }

    public function rules()
    {
        return [
            [['detail'], 'required'],
            [['project_id', 'sort'], 'integer'],
            [['detail'], 'string'],
            [['target_percent'], 'number', 'min' => 0, 'max' => 100],
            [['sort'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'detail' => 'ตัวชี้วัด',
            'target_percent' => 'เป้าหมาย (ร้อยละ)',
        ];
    }
}
