<?php

namespace app\modules\plan\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * เงินคงเหลือยกมา + เงินคงเหลือแยกประเภท ต่อปีงบ (บล็อกสภาพคล่องหน้าแผนประจำปี)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property float $carry_forward
 * @property float $cash
 * @property float $deposit_treasury
 * @property float $deposit_fixed
 * @property float $deposit_saving
 * @property float $deposit_current
 * @property string|null $note
 */
class PlanAnnualLedger extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%plan_annual_ledger}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['fiscal_year'], 'required'],
            [['fiscal_year'], 'integer'],
            [['fiscal_year'], 'unique'],
            [['carry_forward', 'cash', 'deposit_treasury', 'deposit_fixed', 'deposit_saving', 'deposit_current'], 'number'],
            [['note'], 'string'],
        ];
    }

    /** รวมเงินคงเหลือแยกประเภท = ยอด (2) ไว้ validate กับเงินคงเหลือทั้งสิ้น (1) */
    public function positionTotal(): float
    {
        return (float) $this->cash + (float) $this->deposit_treasury
            + (float) $this->deposit_fixed + (float) $this->deposit_saving + (float) $this->deposit_current;
    }

    public static function forYear(int $fy): self
    {
        return static::findOne(['fiscal_year' => $fy]) ?: new self(['fiscal_year' => $fy]);
    }
}
