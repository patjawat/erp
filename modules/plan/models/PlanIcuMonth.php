<?php

namespace app\modules\plan\models;

use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * ยอดปรับมือรายเดือนของ ICU 3 มิติ — null = ใช้ค่าที่ระบบคำนวณ
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int $month_no 1=ต.ค. ... 12=ก.ย.
 * @property float|null $cash_balance
 * @property float|null $commitment
 * @property string|null $note
 */
class PlanIcuMonth extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%plan_icu_month}}';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'createdAtAttribute' => false],
            ['class' => BlameableBehavior::class, 'createdByAttribute' => false],
        ];
    }

    public function rules(): array
    {
        return [
            [['fiscal_year', 'month_no'], 'required'],
            [['fiscal_year', 'month_no'], 'integer'],
            [['month_no'], 'integer', 'min' => 1, 'max' => 12],
            [['cash_balance', 'commitment'], 'number'],
            [['note'], 'string', 'max' => 255],
        ];
    }
}
