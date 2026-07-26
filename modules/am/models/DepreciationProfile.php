<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * เกณฑ์ค่าเสื่อม (depreciation profile)
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $method
 * @property int|null $useful_life_months
 * @property string|null $annual_rate
 * @property string $salvage_value_type
 * @property string $salvage_value
 * @property string $calculation_basis
 * @property string $start_rule
 * @property int $rounding_scale
 * @property string|null $effective_from
 * @property string|null $effective_to
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property DepreciationProfileRate[] $rates
 */
class DepreciationProfile extends ActiveRecord
{
    /* method */
    const METHOD_STRAIGHT_LINE = 'straight_line';

    /* salvage_value_type */
    const SALVAGE_AMOUNT = 'amount';
    const SALVAGE_PERCENT = 'percent';
    const SALVAGE_ONE_BAHT = 'one_baht';

    /* calculation_basis */
    const BASIS_MONTHLY = 'monthly';
    const BASIS_DAILY = 'daily';
    const BASIS_FULL_PERIOD = 'full_period';

    /* start_rule */
    const START_READY_DATE = 'ready_date';   // เริ่มวันที่พร้อมใช้งาน
    const START_READY_MONTH = 'ready_month';  // เริ่มเดือนที่พร้อมใช้งาน
    const START_NEXT_MONTH = 'next_month';   // เริ่มเดือนถัดไป

    /* status */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DRAFT = 'draft';

    public static function tableName()
    {
        return '{{%depreciation_profiles}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
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
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['method'], 'in', 'range' => array_keys(self::methodOptions())],
            [['method'], 'default', 'value' => self::METHOD_STRAIGHT_LINE],
            [['useful_life_months', 'rounding_scale', 'created_by', 'updated_by'], 'integer'],
            [['useful_life_months'], 'integer', 'min' => 1],
            [['annual_rate', 'salvage_value'], 'number'],
            [['salvage_value_type'], 'in', 'range' => array_keys(self::salvageTypeOptions())],
            [['salvage_value_type'], 'default', 'value' => self::SALVAGE_AMOUNT],
            [['salvage_value'], 'default', 'value' => 0],
            [['calculation_basis'], 'in', 'range' => array_keys(self::basisOptions())],
            [['calculation_basis'], 'default', 'value' => self::BASIS_MONTHLY],
            [['start_rule'], 'in', 'range' => array_keys(self::startRuleOptions())],
            [['start_rule'], 'default', 'value' => self::START_READY_DATE],
            [['rounding_scale'], 'default', 'value' => 2],
            [['rounding_scale'], 'integer', 'min' => 0, 'max' => 6],
            [['effective_from', 'effective_to'], 'date', 'format' => 'php:Y-m-d'],
            [['effective_to'], 'compareEffectiveRange'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * effective_to ต้องไม่ก่อน effective_from
     */
    public function compareEffectiveRange($attribute)
    {
        if (!empty($this->effective_from) && !empty($this->effective_to)
            && strtotime($this->effective_to) < strtotime($this->effective_from)) {
            $this->addError($attribute, 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มมีผลบังคับใช้');
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัส',
            'code' => 'รหัสเกณฑ์',
            'name' => 'ชื่อเกณฑ์',
            'method' => 'วิธีคำนวณ',
            'useful_life_months' => 'อายุการใช้งาน (เดือน)',
            'annual_rate' => 'อัตราค่าเสื่อมต่อปี (%)',
            'salvage_value_type' => 'ชนิดมูลค่าซาก',
            'salvage_value' => 'มูลค่าซาก',
            'calculation_basis' => 'ฐานการคำนวณ',
            'start_rule' => 'กฎการเริ่มคิดค่าเสื่อม',
            'rounding_scale' => 'ทศนิยมการปัดเศษ',
            'effective_from' => 'มีผลตั้งแต่',
            'effective_to' => 'มีผลถึง',
            'status' => 'สถานะ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function getRates()
    {
        return $this->hasMany(DepreciationProfileRate::class, ['depreciation_profile_id' => 'id'])
            ->orderBy(['sequence' => SORT_ASC, 'start_month' => SORT_ASC]);
    }

    public static function methodOptions(): array
    {
        return [self::METHOD_STRAIGHT_LINE => 'เส้นตรง (Straight Line)'];
    }

    public static function salvageTypeOptions(): array
    {
        return [
            self::SALVAGE_AMOUNT => 'จำนวนเงิน',
            self::SALVAGE_PERCENT => 'ร้อยละของราคาทุน',
            self::SALVAGE_ONE_BAHT => 'มูลค่าซาก 1 บาท',
        ];
    }

    public static function basisOptions(): array
    {
        return [
            self::BASIS_MONTHLY => 'รายเดือน',
            self::BASIS_DAILY => 'ตามจำนวนวัน',
            self::BASIS_FULL_PERIOD => 'เต็มงวด',
        ];
    }

    public static function startRuleOptions(): array
    {
        return [
            self::START_READY_DATE => 'เริ่มวันที่พร้อมใช้งาน',
            self::START_READY_MONTH => 'เริ่มเดือนที่พร้อมใช้งาน',
            self::START_NEXT_MONTH => 'เริ่มเดือนถัดไป',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_INACTIVE => 'ยกเลิกใช้งาน',
            self::STATUS_DRAFT => 'ร่าง',
        ];
    }

    /**
     * คืนค่าแสดงสถานะให้ทุกหน้าของเกณฑ์ค่าเสื่อมใช้รูปแบบเดียวกัน
     *
     * @return array{class:string,label:string,icon:string}
     */
    public static function getStatusBadgeConfigFor(string $status): array
    {
        $map = [
            self::STATUS_ACTIVE => [
                'class' => 'dp-status dp-status--active',
                'label' => 'ใช้งาน',
                'icon' => 'circle-check',
            ],
            self::STATUS_DRAFT => [
                'class' => 'dp-status dp-status--draft',
                'label' => 'ร่าง',
                'icon' => 'file-pen-line',
            ],
            self::STATUS_INACTIVE => [
                'class' => 'dp-status dp-status--inactive',
                'label' => 'ยกเลิกใช้งาน',
                'icon' => 'archive',
            ],
        ];

        return $map[$status] ?? [
            'class' => 'dp-status dp-status--inactive',
            'label' => self::statusOptions()[$status] ?? $status,
            'icon' => 'circle',
        ];
    }
}
