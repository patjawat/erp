<?php

namespace app\modules\am\models;

use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * งวดบัญชี — รายเดือน/ไตรมาส/ปีงบประมาณ (ปีงบไทย 1 ต.ค. - 30 ก.ย.)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int $period_no
 * @property string $period_type
 * @property string|null $name
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property string|null $closed_at
 * @property int|null $closed_by
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property AssetDepreciation[] $assetDepreciations
 */
class AccountingPeriod extends ActiveRecord
{
    /* period_type */
    const TYPE_MONTH = 'month';
    const TYPE_QUARTER = 'quarter';
    const TYPE_FISCAL_YEAR = 'fiscal_year';
    const TYPE_ADJUSTMENT = 'adjustment';

    /* status */
    const STATUS_OPEN = 'open';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_POSTED = 'posted';
    const STATUS_LOCKED = 'locked';

    public static function tableName()
    {
        return '{{%accounting_periods}}';
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
            [['fiscal_year', 'period_no', 'period_type', 'start_date', 'end_date'], 'required'],
            [['fiscal_year', 'period_no', 'closed_by'], 'integer'],
            [['period_type'], 'in', 'range' => array_keys(self::typeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
            [['name'], 'string', 'max' => 100],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['end_date'], 'compareDateRange'],
            [['fiscal_year', 'period_type', 'period_no'], 'unique', 'targetAttribute' => ['fiscal_year', 'period_type', 'period_no']],
        ];
    }

    public function compareDateRange($attribute)
    {
        if (!empty($this->start_date) && !empty($this->end_date)
            && strtotime($this->end_date) < strtotime($this->start_date)) {
            $this->addError($attribute, 'วันสิ้นงวดต้องไม่ก่อนวันเริ่มงวด');
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัส',
            'fiscal_year' => 'ปีงบประมาณ',
            'period_no' => 'ลำดับงวด',
            'period_type' => 'ชนิดงวด',
            'name' => 'ชื่องวด',
            'start_date' => 'วันเริ่มงวด',
            'end_date' => 'วันสิ้นงวด',
            'status' => 'สถานะ',
            'closed_at' => 'ปิดงวดเมื่อ',
            'closed_by' => 'ผู้ปิดงวด',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getAssetDepreciations()
    {
        return $this->hasMany(AssetDepreciation::class, ['accounting_period_id' => 'id']);
    }

    /**
     * งวดที่ปิดแล้ว (posted/locked) ห้ามคำนวณทับหรือแก้ไข
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_POSTED, self::STATUS_LOCKED], true);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MONTH => 'รายเดือน',
            self::TYPE_QUARTER => 'รายไตรมาส',
            self::TYPE_FISCAL_YEAR => 'ปีงบประมาณ',
            self::TYPE_ADJUSTMENT => 'ปรับปรุง',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'เปิด',
            self::STATUS_CALCULATED => 'คำนวณแล้ว',
            self::STATUS_POSTED => 'บันทึกบัญชีแล้ว',
            self::STATUS_LOCKED => 'ล็อก',
        ];
    }
}
