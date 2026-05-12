<?php

namespace app\modules\am\models;

use Yii;

/**
 * Annual asset audit detail row.
 *
 * @property int $id
 * @property int $audit_id
 * @property int|null $asset_id
 * @property string $asset_code
 * @property string $asset_name
 * @property string|null $asset_condition
 * @property string|null $note
 * @property int $sort_order
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class AssetAuditItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%asset_audit_items}}';
    }

    public function rules()
    {
        return [
            [['asset_code', 'asset_name'], 'required'],
            [['audit_id', 'asset_id', 'sort_order'], 'integer'],
            [['note', 'created_at', 'updated_at'], 'safe'],
            [['asset_code', 'asset_name'], 'string', 'max' => 255],
            [['asset_condition'], 'string', 'max' => 20],
            [['asset_condition'], 'exist', 'skipOnEmpty' => true, 'targetClass' => AssetCondition::class, 'targetAttribute' => ['asset_condition' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'ใบตรวจนับ',
            'asset_id' => 'ครุภัณฑ์',
            'asset_code' => 'รหัส',
            'asset_name' => 'ชื่อครุภัณฑ์',
            'asset_condition' => 'สภาพ',
            'note' => 'หมายเหตุ',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getAudit()
    {
        return $this->hasOne(AssetAudit::class, ['id' => 'audit_id']);
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
