<?php

namespace app\modules\am\models;

use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * ประวัติการเปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สินรายชิ้น
 *
 * @property int $id
 * @property int $asset_id
 * @property int|null $old_depreciation_profile_id
 * @property int|null $new_depreciation_profile_id
 * @property int|null $old_useful_life_months
 * @property int|null $new_useful_life_months
 * @property string|null $old_rate
 * @property string|null $new_rate
 * @property string $effective_date
 * @property string $change_scope
 * @property string|null $reason
 * @property string|null $document_reference
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property string|null $created_at
 *
 * @property Asset $asset
 */
class AssetDepreciationChange extends ActiveRecord
{
    /* change_scope */
    const SCOPE_FUTURE = 'future_periods';
    const SCOPE_UNPOSTED = 'unposted_periods';
    const SCOPE_WITH_ADJUSTMENT = 'with_adjustment';

    public static function tableName()
    {
        return '{{%asset_depreciation_changes}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['asset_id', 'effective_date', 'change_scope'], 'required'],
            [
                ['asset_id', 'old_depreciation_profile_id', 'new_depreciation_profile_id',
                    'old_useful_life_months', 'new_useful_life_months', 'approved_by', 'created_by'],
                'integer',
            ],
            [['old_rate', 'new_rate'], 'number'],
            [['effective_date'], 'date', 'format' => 'php:Y-m-d'],
            [['change_scope'], 'in', 'range' => array_keys(self::scopeOptions())],
            [['reason'], 'string', 'max' => 500],
            [['document_reference'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัส',
            'asset_id' => 'ทรัพย์สิน',
            'old_depreciation_profile_id' => 'เกณฑ์เดิม',
            'new_depreciation_profile_id' => 'เกณฑ์ใหม่',
            'old_useful_life_months' => 'อายุเดิม (เดือน)',
            'new_useful_life_months' => 'อายุใหม่ (เดือน)',
            'old_rate' => 'อัตราเดิม (%)',
            'new_rate' => 'อัตราใหม่ (%)',
            'effective_date' => 'วันที่มีผล',
            'change_scope' => 'ขอบเขตการเปลี่ยน',
            'reason' => 'เหตุผล',
            'document_reference' => 'เลขที่เอกสารอ้างอิง',
            'approved_by' => 'ผู้อนุมัติ',
            'created_by' => 'ผู้บันทึก',
            'created_at' => 'บันทึกเมื่อ',
        ];
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_FUTURE => 'เฉพาะงวดอนาคต',
            self::SCOPE_UNPOSTED => 'งวดที่ยังไม่บันทึกบัญชี',
            self::SCOPE_WITH_ADJUSTMENT => 'ปรับย้อนหลังด้วยรายการปรับปรุง',
        ];
    }
}
