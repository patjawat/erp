<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * โอนเงินข้ามบัญชี — บันทึก movement สำหรับทะเบียนคุม (ไม่ auto-ปรับยอดคงเหลือรายปี)
 *
 * @property int $id
 * @property int $from_account_id
 * @property int $to_account_id
 * @property string $amount
 * @property string|null $doc_ref
 * @property string $transfer_date
 * @property int $fiscal_year
 * @property string|null $note
 * @property FinanceCashAccount|null $fromAccount
 * @property FinanceCashAccount|null $toAccount
 */
class FinanceCashTransfer extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_cash_transfer}}';
    }

    public function rules()
    {
        return [
            [['from_account_id', 'to_account_id', 'amount', 'transfer_date', 'fiscal_year'], 'required'],
            [['from_account_id', 'to_account_id', 'fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['transfer_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['doc_ref'], 'string', 'max' => 64],
            [['note'], 'string', 'max' => 255],
            [['to_account_id'], 'compare', 'compareAttribute' => 'from_account_id', 'operator' => '!=', 'message' => 'บัญชีปลายทางต้องไม่ใช่บัญชีต้นทาง'],
            [['from_account_id', 'to_account_id'], 'exist', 'targetClass' => FinanceCashAccount::class, 'targetAttribute' => 'id'],
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

    public function getFromAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'from_account_id']);
    }

    public function getToAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'to_account_id']);
    }
}
