<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

class FinanceVoucher extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    public static function tableName() { return '{{%finance_voucher}}'; }

    public function rules()
    {
        return [
            [['finance_payable_id', 'vendor_id', 'vendor_name_snapshot', 'payable_no_snapshot', 'invoice_no_snapshot', 'gross_amount', 'net_amount', 'funding_source', 'requested_payment_date', 'payment_method', 'status'], 'required'],
            [['finance_payable_id', 'vendor_id', 'created_by', 'updated_by'], 'integer'],
            [['gross_amount', 'withholding_tax_amount', 'net_amount'], 'number', 'min' => 0],
            [['requested_payment_date'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string'],
            [['voucher_no', 'payable_no_snapshot'], 'string', 'max' => 50],
            [['vendor_code_snapshot', 'invoice_no_snapshot'], 'string', 'max' => 100],
            [['vendor_name_snapshot', 'funding_source'], 'string', 'max' => 255],
            [['payment_method'], 'in', 'range' => array_keys(self::paymentMethodOptions())],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['withholding_tax_amount'], 'default', 'value' => 0],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public static function paymentMethodOptions(): array
    {
        return [self::METHOD_CHEQUE => 'เช็ค', self::METHOD_BANK_TRANSFER => 'โอนเงิน'];
    }

    public function getPayable() { return $this->hasOne(FinancePayable::class, ['id' => 'finance_payable_id']); }
}
