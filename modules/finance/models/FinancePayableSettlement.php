<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * การตัดหนี้/จ่ายชำระเจ้าหนี้ (AP settlement)
 *
 * @property int $id
 * @property int $payable_id
 * @property int|null $cash_voucher_id
 * @property string $amount
 * @property string $settle_date
 * @property string|null $note
 */
class FinancePayableSettlement extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_payable_settlement}}';
    }

    public function rules(): array
    {
        return [
            [['payable_id', 'amount', 'settle_date'], 'required'],
            [['payable_id', 'cash_voucher_id', 'created_by'], 'integer'],
            [['amount'], 'number', 'min' => 0],
            [['settle_date'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string', 'max' => 255],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = $this->created_at ?: time();
            $this->created_by = $this->created_by
                ?: (Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null);
        }
        return true;
    }

    public function getPayable()
    {
        return $this->hasOne(FinancePayable::class, ['id' => 'payable_id']);
    }

    public function getCashVoucher()
    {
        return $this->hasOne(FinanceCashVoucher::class, ['id' => 'cash_voucher_id']);
    }
}
