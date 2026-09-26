<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * รอบจ่ายเจ้าหนี้รายบริษัท (payment batch) = 1 เช็ค + 1 หนังสือนำส่ง
 *
 * @property int $id
 * @property int|null $vendor_id
 * @property string $vendor_name_snapshot
 * @property string $pay_date
 * @property string $pay_method
 * @property string|null $bank_name
 * @property string|null $bank_branch
 * @property string|null $cheque_no
 * @property string|null $doc_no
 * @property string|null $subject
 * @property string $gross_total
 * @property string $wht_total
 * @property string $net_total
 * @property string|null $note
 */
class FinancePayablePayment extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_payable_payment}}';
    }

    public function rules(): array
    {
        return [
            [['vendor_name_snapshot', 'pay_date'], 'required'],
            [['vendor_id', 'created_by'], 'integer'],
            [['pay_date'], 'date', 'format' => 'php:Y-m-d'],
            [['gross_total', 'wht_total', 'net_total'], 'number'],
            [['pay_method'], 'string', 'max' => 20],
            [['bank_name', 'bank_branch', 'cheque_no'], 'string', 'max' => 100],
            [['doc_no'], 'string', 'max' => 60],
            [['vendor_name_snapshot', 'subject', 'note'], 'string', 'max' => 255],
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

    public function getSettlements()
    {
        return $this->hasMany(FinancePayableSettlement::class, ['payment_id' => 'id']);
    }

    public function isCancelled(): bool
    {
        return !empty($this->cancelled_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** ป้ายสถานะรอบจ่าย [ข้อความ, class badge] */
    public function statusBadge(): array
    {
        if ($this->isCancelled()) {
            return ['ยกเลิกแล้ว', 'bg-danger-subtle text-danger-emphasis'];
        }
        return match ($this->status) {
            'pending' => ['รออนุมัติ', 'bg-warning-subtle text-warning-emphasis'],
            'rejected' => ['ไม่อนุมัติ', 'bg-secondary-subtle text-secondary-emphasis'],
            default => ['จ่ายแล้ว', 'bg-success-subtle text-success-emphasis'],
        };
    }

    /** บิล/ยอดที่ขอจ่าย (ก่อนอนุมัติ) — [['payable' => FinancePayable, 'amount' => float]] */
    public function requestedLines(): array
    {
        $out = [];
        foreach ((array) ((json_decode((string) $this->request_json, true) ?: [])['lines'] ?? []) as $ln) {
            if ($p = FinancePayable::findOne((int) $ln['payable_id'])) {
                $out[] = ['payable' => $p, 'amount' => (float) $ln['amount']];
            }
        }
        return $out;
    }

    /** ใบสำคัญจ่ายเงินบำรุงที่ออกจากรอบนี้ (รอบจ่ายเก่าก่อนเชื่อม mophcash จะไม่มี) */
    public function getVoucher(): ?FinanceCashVoucher
    {
        $vid = FinancePayableSettlement::find()->select('cash_voucher_id')
            ->where(['payment_id' => $this->id])->andWhere(['not', ['cash_voucher_id' => null]])->scalar();
        return $vid ? FinanceCashVoucher::findOne((int) $vid) : null;
    }

    public function getCheque(): ?FinanceCheque
    {
        return FinanceCheque::find()->where(['payment_id' => $this->id])->orderBy(['id' => SORT_DESC])->one();
    }

    /** บิลที่เคยจ่ายก่อนยกเลิก (จาก snapshot) — [[payable_id, payable_no, invoice_no, amount]] */
    public function cancelledLines(): array
    {
        $rows = json_decode((string) $this->cancel_snapshot, true);
        return is_array($rows) ? $rows : [];
    }

    /** บิลที่จ่ายในรอบนี้ พร้อมยอดที่จ่าย — คืน list ของ [payable, amount] */
    public function paidLines(): array
    {
        $out = [];
        $rows = FinancePayableSettlement::find()->where(['payment_id' => $this->id])->orderBy(['id' => SORT_ASC])->all();
        foreach ($rows as $s) {
            $p = FinancePayable::findOne($s->payable_id);
            if ($p) {
                $out[] = ['payable' => $p, 'amount' => (float) $s->amount];
            }
        }
        return $out;
    }
}
