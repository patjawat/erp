<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * หัว worksheet ปิดบัญชีประจำปี — 1 แถวต่อปีงบ (reconciliation + องค์ประกอบเงินคงเหลือ)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property string $carried_forward
 * @property string $fund_pending
 * @property string $obligation
 * @property string $purchase_obligation
 * @property string $cash_amount
 * @property string $treasury_amount
 * @property string $bank_fixed
 * @property string $bank_savings
 * @property string $bank_current
 * @property string|null $note
 */
class FinanceCashYearClose extends ActiveRecord
{
    use LoanAuditTrait;

    private const MONEY_FIELDS = ['carried_forward', 'fund_pending', 'obligation', 'purchase_obligation',
        'cash_amount', 'treasury_amount', 'bank_fixed', 'bank_savings', 'bank_current'];

    public static function tableName()
    {
        return '{{%finance_cash_year_close}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year'], 'required'],
            [['fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [self::MONEY_FIELDS, 'number'],
            [['note'], 'string', 'max' => 255],
            [['fiscal_year'], 'unique'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        foreach (self::MONEY_FIELDS as $f) {
            if ($this->$f !== null && $this->$f !== '') {
                $this->$f = $this->money($this->$f);
            }
        }
        return true;
    }
}
