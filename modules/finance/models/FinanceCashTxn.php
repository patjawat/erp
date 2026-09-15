<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * รายการรับ-จ่ายจริง — บันทึกละเอียดทีละใบ (ไม่ใช่ยอดก้อนเดียว)
 * เทียบกับแผน (finance_cash_plan) ที่ระดับหมวด ด้วยการ group sum ไม่ผูกบรรทัดแผนตรง ๆ
 *
 * @property int $id
 * @property string $txn_type IN|OUT
 * @property int $fiscal_year พ.ศ.
 * @property int $category_id
 * @property int|null $money_account_id
 * @property string $doc_date
 * @property string|null $doc_no
 * @property string|null $pay_method
 * @property string $amount
 * @property int|null $party_id
 * @property string|null $party_name
 * @property string|null $note
 * @property int $is_closed
 * @property int|null $close_batch_id
 * @property FinanceCashCategory|null $category
 */
class FinanceCashTxn extends ActiveRecord
{
    use LoanAuditTrait;

    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';

    /** วิธีรับ (ฝั่งรายรับ) — เลือกทีละอัน; ฝั่งจ่ายใช้ FinanceCashVoucher::PAY_METHODS แทน */
    public const PAY_METHODS = [
        'cash' => 'เงินสด',
        'transfer' => 'โอน',
        'cheque' => 'เช็ค',
        'promptpay' => 'พร้อมเพย์',
        'credit' => 'บัตรเครดิต',
    ];

    public static function tableName()
    {
        return '{{%finance_cash_txn}}';
    }

    /** ปีงบประมาณปัจจุบัน (พ.ศ.) — เริ่มนับ ต.ค. */
    public static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    public function rules()
    {
        return [
            [['txn_type', 'fiscal_year', 'category_id', 'doc_date', 'amount'], 'required'],
            [['txn_type'], 'in', 'range' => [self::TYPE_IN, self::TYPE_OUT]],
            [['fiscal_year', 'category_id', 'money_account_id', 'party_id', 'is_closed', 'close_batch_id', 'created_by', 'updated_by'], 'integer'],
            [['doc_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['doc_no'], 'string', 'max' => 64],
            [['party_name'], 'string', 'max' => 255],
            [['pay_method'], 'in', 'range' => array_merge(array_keys(self::PAY_METHODS), array_keys(FinanceCashVoucher::PAY_METHODS))],
            [['voucher_id'], 'integer'],
            [['bc_ref'], 'string', 'max' => 64],
            [['note'], 'string'],
            [['is_closed'], 'default', 'value' => 0],
            [['category_id'], 'exist', 'targetClass' => FinanceCashCategory::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'txn_type' => 'ประเภท',
            'fiscal_year' => 'ปีงบประมาณ',
            'category_id' => 'หัวข้อบัญชี',
            'doc_date' => 'วันที่ออกใบเสร็จ/เอกสาร',
            'doc_no' => 'เลขที่ใบเสร็จ/เอกสาร',
            'pay_method' => 'วิธีรับ/จ่าย',
            'amount' => 'จำนวนเงิน',
            'party_name' => 'รับจาก/จ่ายให้',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        // ยอดที่พิมพ์มาอาจติดลูกน้ำจากการคัดลอก ต้องล้างก่อนเสมอ
        if ($this->amount !== null && $this->amount !== '') {
            $this->amount = $this->money($this->amount);
        }
        return true;
    }

    public function getCategory()
    {
        return $this->hasOne(FinanceCashCategory::class, ['id' => 'category_id']);
    }

    public function getVoucher()
    {
        return $this->hasOne(FinanceCashVoucher::class, ['id' => 'voucher_id']);
    }

    public function payMethodLabel(): string
    {
        return self::PAY_METHODS[$this->pay_method] ?? '-';
    }
}
