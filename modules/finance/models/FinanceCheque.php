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

    // รูปแบบการเขียนเช็ค (ตามคู่มือ 1-6) — คุมการขีดฆ่า "หรือผู้ถือ" + ขีดคร่อม + ข้อความ
    public const FORM_BEARER = 'bearer';         // ผู้ถือ: ไม่ขีดฆ่า ไม่คร่อม
    public const FORM_NAMED = 'named';           // ระบุชื่อ: ขีดฆ่า "หรือผู้ถือ"
    public const FORM_CROSSED = 'crossed';       // ขีดคร่อมทั่วไป: ขีดฆ่า + คร่อม 2 เส้น
    public const FORM_AC_PAYEE = 'ac_payee';     // A/C PAYEE ONLY: ขีดฆ่า + คร่อม + ข้อความ
    public const FORM_AC_BANK = 'ac_payee_bank'; // คร่อมเฉพาะธนาคาร: ขีดฆ่า + คร่อม + ชื่อธนาคาร

    public static function formTypeOptions(): array
    {
        return [
            self::FORM_AC_PAYEE => 'A/C PAYEE ONLY (โอนเข้าบัญชีผู้รับเท่านั้น)',
            self::FORM_NAMED => 'ระบุชื่อ (ขีดฆ่า "หรือผู้ถือ")',
            self::FORM_CROSSED => 'ขีดคร่อมทั่วไป (เข้าบัญชีเท่านั้น)',
            self::FORM_AC_BANK => 'คร่อมเฉพาะธนาคาร',
            self::FORM_BEARER => 'ผู้ถือ (จ่ายสด/ไม่ขีดฆ่า)',
        ];
    }

    /** คืน [strike(ขีดฆ่าหรือผู้ถือ), cross(ขีดคร่อม), text(ข้อความในคร่อม)] ตาม form_type */
    public static function resolveForm(string $ft, string $bankName = ''): array
    {
        switch ($ft) {
            case self::FORM_BEARER:   return [false, false, ''];
            case self::FORM_NAMED:    return [true, false, ''];
            case self::FORM_CROSSED:  return [true, true, ''];
            case self::FORM_AC_BANK:  return [true, true, trim($bankName) !== '' ? $bankName : 'A/C PAYEE ONLY'];
            case self::FORM_AC_PAYEE:
            default:                  return [true, true, 'A/C PAYEE ONLY'];
        }
    }

    /** ลำดับการเดินสถานะที่อนุญาต (ไม่รวม void ซึ่งทำได้ทุกสถานะที่ยังไม่ยกเลิก) */
    public const FLOW = [
        self::STATUS_DRAFT => [self::STATUS_PRINTED],
        self::STATUS_PRINTED => [self::STATUS_HANDED],
        self::STATUS_HANDED => [self::STATUS_CLEARED, self::STATUS_BOUNCED],
        self::STATUS_BOUNCED => [self::STATUS_HANDED],
        self::STATUS_CLEARED => [],
        self::STATUS_VOID => [],
    ];

    public static function tableName(): string
    {
        return '{{%finance_cheque}}';
    }

    /** สถานะถัดไปที่เดินได้จากสถานะปัจจุบัน */
    public function nextStatuses(): array
    {
        return self::FLOW[$this->status] ?? [];
    }

    /** เดินสถานะไปยัง $status ถ้าเป็นขั้นที่อนุญาต (บันทึกเวลาพิมพ์อัตโนมัติ) */
    public function moveTo(string $status): bool
    {
        if ($this->isVoid() || !in_array($status, $this->nextStatuses(), true)) {
            return false;
        }
        $this->status = $status;
        if ($status === self::STATUS_PRINTED && !$this->printed_at) {
            $this->printed_at = time();
            $this->printed_by = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
        }
        return $this->save(false);
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
            [['form_type'], 'in', 'range' => array_keys(self::formTypeOptions())],
            [['form_type'], 'default', 'value' => self::FORM_AC_PAYEE],
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
            'form_type' => 'รูปแบบเช็ค',
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
        // sync is_ac_payee จาก form_type (เพื่อความเข้ากันได้กับส่วนที่อ้าง is_ac_payee)
        $this->is_ac_payee = in_array($this->form_type, [self::FORM_AC_PAYEE, self::FORM_AC_BANK], true) ? 1 : 0;
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

    /**
     * เลขที่เช็คถัดไปของบัญชีจ่าย = เลขสูงสุดในทะเบียน + 1 (นับทุกสถานะรวม void — เลขเดินตามเล่มจริง)
     * รักษา prefix และจำนวนหลัก (เติมศูนย์หน้า) เช่น 10225123 -> 10225124 , CHB-0009 -> CHB-0010
     */
    public static function nextChequeNo(int $accountId): ?string
    {
        if ($accountId <= 0) {
            return null;
        }
        $nos = self::find()->select('cheque_no')->where(['cash_account_id' => $accountId])->column();
        $bestVal = -1;
        $bestPrefix = '';
        $bestWidth = 0;
        foreach ($nos as $no) {
            if (preg_match('/^(.*?)(\d+)\s*$/u', (string) $no, $m)) {
                $val = (int) $m[2];
                if ($val > $bestVal) {
                    $bestVal = $val;
                    $bestPrefix = $m[1];
                    $bestWidth = strlen($m[2]);
                }
            }
        }
        if ($bestVal < 0) {
            return null;
        }
        return $bestPrefix . str_pad((string) ($bestVal + 1), $bestWidth, '0', STR_PAD_LEFT);
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
        $cheque->form_type = self::FORM_AC_PAYEE;
        $cheque->status = self::STATUS_DRAFT;
        return $cheque;
    }
}
