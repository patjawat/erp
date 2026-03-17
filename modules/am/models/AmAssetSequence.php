<?php

namespace app\modules\am\models;

use yii\db\ActiveRecord;

/**
 * Model for table am_asset_sequences.
 * ลำดับปัจจุบันต่อปีต่อหมวด FSN สำหรับสร้างหมายเลขครุภัณฑ์
 *
 * @property int $id
 * @property string $category_id รหัสหมวด FSN
 * @property int $year ปี พ.ศ.
 * @property int $current_sequence ลำดับล่าสุดที่ใช้
 * @property string|null $updated_at
 */
class AmAssetSequence extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_asset_sequences}}';
    }

    public function rules()
    {
        return [
            [['category_id', 'year', 'current_sequence'], 'required'],
            [['category_id'], 'string', 'max' => 100],
            [['year'], 'integer', 'min' => 2500, 'max' => 2600],
            [['current_sequence'], 'integer', 'min' => 0],
            [['current_sequence'], 'default', 'value' => 0],
            [['category_id', 'year'], 'unique', 'targetAttribute' => ['category_id', 'year'], 'filter' => function ($query) {
                if (!$this->isNewRecord) {
                    $query->andWhere(['<>', 'id', $this->id]);
                }
            }, 'message' => 'รหัสหมวดและปีนี้มีอยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'รหัสหมวด (FSN)',
            'year' => 'ปี พ.ศ.',
            'current_sequence' => 'ลำดับล่าสุด',
            'updated_at' => 'อัปเดตเมื่อ',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
    }
}
