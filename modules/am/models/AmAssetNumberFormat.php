<?php

namespace app\modules\am\models;

use yii\db\ActiveRecord;

/**
 * Model for table am_asset_number_formats.
 * รูปแบบการสร้างหมายเลขครุภัณฑ์ (FSN)
 *
 * @property int $id
 * @property string $name
 * @property string $pattern
 * @property int $is_active
 */
class AmAssetNumberFormat extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_asset_number_formats}}';
    }

    public function rules()
    {
        return [
            [['name', 'pattern'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['pattern'], 'string', 'max' => 255],
            [['is_active'], 'in', 'range' => [0, 1]],
            [['is_active'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อรูปแบบ',
            'pattern' => 'รูปแบบ (Pattern)',
            'is_active' => 'ใช้รูปแบบนี้',
        ];
    }

    /**
     * Set this format as the only active one (others set to 0).
     */
    public function setAsActive()
    {
        static::getDb()->createCommand()->update(
            static::tableName(),
            ['is_active' => 0]
        )->execute();
        $this->is_active = 1;
        return $this->save(false);
    }
}
