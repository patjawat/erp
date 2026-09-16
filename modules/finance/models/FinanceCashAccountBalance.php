<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * ยอดคงเหลือบัญชีต่อปีงบประมาณ (กรอกเอง — ปิดงบปีแล้วตั้งยอดใหม่ ยกไปปีถัดไป)
 *
 * @property int $id
 * @property int $account_id
 * @property int $fiscal_year
 * @property string $amount
 * @property string|null $note
 * @property FinanceCashAccount|null $account
 */
class FinanceCashAccountBalance extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_cash_account_balance}}';
    }

    public function rules()
    {
        return [
            [['account_id', 'fiscal_year', 'amount'], 'required'],
            [['account_id', 'fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['note'], 'string', 'max' => 255],
            [['account_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['account_id', 'fiscal_year'],
                'message' => 'ตั้งยอดของบัญชีนี้ในปีงบนี้ไว้แล้ว'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->amount !== null && $this->amount !== '') {
            $this->amount = $this->money($this->amount);
        }
        return true;
    }

    public function getAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'account_id']);
    }
}
