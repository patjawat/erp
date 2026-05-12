<?php

namespace app\modules\am\models;

/**
 * Asset disposal request item row.
 */
class AssetDisposalItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%asset_disposal_items}}';
    }

    public function rules()
    {
        return [
            [['asset_code', 'asset_name'], 'required'],
            [['disposal_id', 'asset_id', 'sort_order'], 'integer'],
            [['reason', 'created_at', 'updated_at'], 'safe'],
            [['asset_code', 'asset_name'], 'string', 'max' => 255],
            [['asset_condition'], 'string', 'max' => 20],
            [['asset_condition'], 'exist', 'skipOnEmpty' => true, 'targetClass' => AssetCondition::class, 'targetAttribute' => ['asset_condition' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'disposal_id' => 'ใบขอจำหน่าย',
            'asset_id' => 'ครุภัณฑ์',
            'asset_code' => 'รหัส',
            'asset_name' => 'ชื่อ',
            'asset_condition' => 'สภาพ',
            'reason' => 'เหตุผล',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getDisposal()
    {
        return $this->hasOne(AssetDisposal::class, ['id' => 'disposal_id']);
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    public function getCondition()
    {
        return $this->hasOne(AssetCondition::class, ['id' => 'asset_condition']);
    }
}
