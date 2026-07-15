<?php

namespace app\modules\am\models;

use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * ผลการคำนวณค่าเสื่อมของทรัพย์สินรายชิ้นต่องวด (ตารางใหม่ asset_depreciations)
 *
 * หมายเหตุ: คนละตัวกับ legacy AmAssetDepreciation (am_asset_depreciations) ที่จะถูกถอดในเฟส 2
 *
 * @property int $id
 * @property int $asset_id
 * @property int $accounting_period_id
 * @property string $transaction_type
 * @property string $opening_cost
 * @property string $depreciable_base
 * @property string $depreciation_amount
 * @property string $adjustment_amount
 * @property string $accumulated_depreciation
 * @property string $closing_net_book_value
 * @property int|null $calculation_days
 * @property string|null $calculation_months
 * @property int|null $depreciation_profile_id
 * @property string|null $method_snapshot
 * @property int|null $useful_life_months_snapshot
 * @property string|null $rate_snapshot
 * @property string|null $salvage_value_snapshot
 * @property string $status
 * @property string|null $calculated_at
 * @property int|null $calculated_by
 * @property string|null $posted_at
 * @property int|null $posted_by
 * @property string|null $note
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Asset $asset
 * @property AccountingPeriod $period
 * @property DepreciationProfile $profile
 */
class AssetDepreciation extends ActiveRecord
{
    /* transaction_type */
    const TX_NORMAL = 'normal';
    const TX_ADJUSTMENT = 'adjustment';
    const TX_REVERSAL = 'reversal';

    /* status */
    const STATUS_DRAFT = 'draft';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_POSTED = 'posted';
    const STATUS_LOCKED = 'locked';
    const STATUS_REVERSED = 'reversed';

    public static function tableName()
    {
        return '{{%asset_depreciations}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['asset_id', 'accounting_period_id'], 'required'],
            [
                ['asset_id', 'accounting_period_id', 'calculation_days',
                    'depreciation_profile_id', 'useful_life_months_snapshot',
                    'calculated_by', 'posted_by'],
                'integer',
            ],
            [['transaction_type'], 'in', 'range' => array_keys(self::txOptions())],
            [['transaction_type'], 'default', 'value' => self::TX_NORMAL],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [
                ['opening_cost', 'depreciable_base', 'depreciation_amount', 'adjustment_amount',
                    'accumulated_depreciation', 'closing_net_book_value', 'calculation_months',
                    'rate_snapshot', 'salvage_value_snapshot'],
                'number',
            ],
            [['method_snapshot'], 'string', 'max' => 30],
            [['note'], 'string', 'max' => 500],
            [
                ['asset_id', 'accounting_period_id', 'transaction_type'],
                'unique',
                'targetAttribute' => ['asset_id', 'accounting_period_id', 'transaction_type'],
                'message' => 'มีรายการค่าเสื่อมของทรัพย์สินนี้ในงวดและชนิดรายการนี้แล้ว',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัส',
            'asset_id' => 'ทรัพย์สิน',
            'accounting_period_id' => 'งวดบัญชี',
            'transaction_type' => 'ชนิดรายการ',
            'opening_cost' => 'ราคาทุน/มูลค่าต้นงวด',
            'depreciable_base' => 'ฐานค่าเสื่อม',
            'depreciation_amount' => 'ค่าเสื่อมประจำงวด',
            'adjustment_amount' => 'ยอดปรับปรุง',
            'accumulated_depreciation' => 'ค่าเสื่อมสะสม',
            'closing_net_book_value' => 'มูลค่าสุทธิปลายงวด',
            'calculation_days' => 'จำนวนวันคำนวณ',
            'calculation_months' => 'จำนวนเดือนคำนวณ',
            'depreciation_profile_id' => 'เกณฑ์ค่าเสื่อม',
            'method_snapshot' => 'วิธีคำนวณ (snapshot)',
            'useful_life_months_snapshot' => 'อายุ (เดือน, snapshot)',
            'rate_snapshot' => 'อัตรา (%, snapshot)',
            'salvage_value_snapshot' => 'มูลค่าซาก (snapshot)',
            'status' => 'สถานะ',
            'calculated_at' => 'คำนวณเมื่อ',
            'calculated_by' => 'ผู้คำนวณ',
            'posted_at' => 'บันทึกบัญชีเมื่อ',
            'posted_by' => 'ผู้บันทึกบัญชี',
            'note' => 'หมายเหตุ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    public function getPeriod()
    {
        return $this->hasOne(AccountingPeriod::class, ['id' => 'accounting_period_id']);
    }

    public function getProfile()
    {
        return $this->hasOne(DepreciationProfile::class, ['id' => 'depreciation_profile_id']);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_POSTED, self::STATUS_LOCKED], true);
    }

    public static function txOptions(): array
    {
        return [
            self::TX_NORMAL => 'ปกติ',
            self::TX_ADJUSTMENT => 'ปรับปรุง',
            self::TX_REVERSAL => 'กลับรายการ',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_CALCULATED => 'คำนวณแล้ว',
            self::STATUS_POSTED => 'บันทึกบัญชีแล้ว',
            self::STATUS_LOCKED => 'ล็อก',
            self::STATUS_REVERSED => 'กลับรายการแล้ว',
        ];
    }
}
