<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\finance\components\BahtText;

/**
 * ทะเบียนคุมเช็ค — 1 เช็ค (โดยปกติผูก 1 รอบจ่ายเจ้าหนี้)
 *
 * วงจรสถานะ: draft → printed → handed → cleared | bounced ; ยกเลิกได้ตลอดเป็น void
 *
 * @property int $id
 * @property int|null $payment_id
 * @property int|null $cash_account_id
 * @property int|null $template_id
 * @property string|null $cheque_book_no
 * @property string $cheque_no
 * @property string|null $cheque_date
 * @property string $payee_name
 * @property string $amount
 * @property string|null $amount_text
 * @property int $is_ac_payee
 * @property string $status
 */
class FinanceCheque extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';       // สร้างแล้ว รอพิมพ์
    public const STATUS_PRINTED = 'printed';   // พิมพ์ลงเช็คแล้ว
    public const STATUS_HANDED = 'handed';     // ส่งมอบผู้รับแล้ว
    public const STATUS_CLEARED = 'cleared';   // ขึ้นเงินแล้ว
    public const STATUS_BOUNCED = 'bounced';   // เช็คคืน/เด้ง
    public const STATUS_VOID = 'void';         // ยกเลิก/เช็คเสีย

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'รอพิมพ์',
            self::STATUS_PRINTED => 'พิมพ์แล้ว',
            self::STATUS_HANDED => 'ส่งมอบแล้ว',
            self::STATUS_CLEARED => 'ขึ้นเงินแล้ว',
            self::STATUS_BOUNCED => 'เช็คคืน',
            self::STATUS_VOID => 'ยกเลิก',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_cheque}}';
    }

    public function rules(): array
    {
        return [
            [['cheque_no', 'payee_name'], 'required'],
            [['payment_id', 'cash_account_id', 'template_id', 'is_ac_payee'], 'integer'],
            [['amount'], 'number'],
            [['cheque_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['is_ac_payee'], 'default', 'value' => 1],
            [['cheque_book_no', 'cheque_no'], 'string', 'max' => 50],
            [['payee_name', 'amount_text', 'void_reason', 'note'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'cheque_book_no' => 'เล่มเช็ค',
            'cheque_no' => 'เลขที่เช็ค',
            'cheque_date' => 'วันที่สั่งจ่าย',
            'payee_name' => 'จ่ายให้',
            'amount' => 'จำนวนเงิน',
            'amount_text' => 'จำนวนเงินตัวอักษร',
            'is_ac_payee' => 'ขีดคร่อม A/C PAYEE ONLY',
            'status' => 'สถานะ',
            'void_reason' => 'เหตุผลยกเลิก',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // เก็บ snapshot จำนวนเงินตัวอักษรเสมอเมื่อมียอด
        if ($this->amount !== null && $this->amount !== '') {
            $this->amount_text = BahtText::convert($this->amount);
        }
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
        $now = time();
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }

    public function getPayment()
    {
        return $this->hasOne(FinancePayablePayment::class, ['id' => 'payment_id']);
    }

    public function getCashAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'cash_account_id']);
    }

    public function getTemplate()
    {
        return $this->hasOne(FinanceChequeTemplate::class, ['id' => 'template_id']);
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function isVoid(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    /** ยกเลิกเช็ค (เช็คเสีย/พิมพ์ผิด) โดยคงเลขไว้ในทะเบียน */
    public function void(string $reason): bool
    {
        $this->status = self::STATUS_VOID;
        $this->void_reason = $reason;
        $this->voided_at = time();
        $this->voided_by = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
        return $this->save(false, ['status', 'void_reason', 'voided_at', 'voided_by', 'updated_at', 'updated_by']);
    }

    /** สร้างเช็คร่างจากรอบจ่ายเจ้าหนี้ (payment batch) */
    public static function fromPayment(FinancePayablePayment $pay): self
    {
        $cheque = new self();
        $cheque->payment_id = $pay->id;
        $cheque->cash_account_id = $pay->cash_account_id ?? null;
        $cheque->cheque_no = (string) ($pay->cheque_no ?? '');
        $cheque->cheque_date = $pay->pay_date;
        $cheque->payee_name = $pay->vendor_name_snapshot;
        $cheque->amount = $pay->net_total;
        $cheque->status = self::STATUS_DRAFT;
        return $cheque;
    }
}
