<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * ใบสำคัญจ่าย (voucher) — 1 ใบมีหลายบรรทัด (finance_cash_txn ฝั่ง OUT ที่ voucher_id = ใบนี้)
 * ถือข้อมูลหัว: วิธีจ่าย/เลขเช็ค/บัญชีจ่าย/ผู้รับ/VAT/WHT/ยอดสุทธิ-จ่ายจริง
 * บรรทัดเงินอยู่ใน finance_cash_txn เพื่อให้ปิดบัญชี/ภาพรวมอ่านตารางเดียวได้ทั้งรับ-จ่าย
 *
 * @property int $id
 * @property int $fiscal_year
 * @property string $pay_date
 * @property string|null $doc_no เลขใบสำคัญ
 * @property string|null $pay_method
 * @property string|null $cheque_no
 * @property int|null $account_id
 * @property string|null $payee_name
 * @property string $subtotal
 * @property string $vat_amount
 * @property string $total_amount
 * @property string|null $wht_type
 * @property string $wht_amount
 * @property string $net_amount
 * @property string|null $note
 * @property int $is_closed
 * @property FinanceCashAccount|null $account
 * @property FinanceCashTxn[] $items
 */
class FinanceCashVoucher extends ActiveRecord
{
    use LoanAuditTrait;

    /** วิธีจ่าย (ต่างจากฝั่งรับ) */
    public const PAY_METHODS = [
        'cheque' => 'เช็ค',
        'treasury_deposit' => 'เงินฝากคลัง',
        'cash' => 'เงินสด',
        'ktb_corporate' => 'KTB Corporate Online',
    ];

    /** ประเภทหักภาษี ณ ที่จ่าย + อัตรา (%) ที่ใช้คำนวณ (90/91 ไม่มีอัตรา = กรอก/0) */
    public const WHT_TYPES = [
        'pnd1_5' => ['label' => 'ภ.ง.ด.1 (5%)', 'rate' => 5.0],
        'pnd1_10' => ['label' => 'ภ.ง.ด.1 (10%)', 'rate' => 10.0],
        'pnd3_1' => ['label' => 'ภ.ง.ด.3 (1%)', 'rate' => 1.0],
        'pnd53_1' => ['label' => 'ภ.ง.ด.53 (1%)', 'rate' => 1.0],
        'pnd90' => ['label' => 'ภ.ง.ด.90', 'rate' => 0.0],
        'pnd91' => ['label' => 'ภ.ง.ด.91', 'rate' => 0.0],
    ];

    public static function tableName()
    {
        return '{{%finance_cash_voucher}}';
    }

    public static function currentFiscalYear(): int
    {
        return FinanceCashTxn::currentFiscalYear();
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'pay_date'], 'required'],
            [['fiscal_year', 'account_id', 'payee_id', 'is_closed', 'close_batch_id', 'created_by', 'updated_by'], 'integer'],
            [['pay_date'], 'date', 'format' => 'php:Y-m-d'],
            [['pay_method'], 'in', 'range' => array_keys(self::PAY_METHODS)],
            [['wht_type'], 'in', 'range' => array_keys(self::WHT_TYPES)],
            [['subtotal', 'vat_amount', 'total_amount', 'wht_amount', 'net_amount'], 'number'],
            [['doc_no', 'cheque_no'], 'string', 'max' => 64],
            [['payee_name'], 'string', 'max' => 255],
            [['note'], 'string'],
            [['account_id'], 'exist', 'targetClass' => FinanceCashAccount::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'pay_date' => 'วันที่จ่าย',
            'doc_no' => 'เลขใบสำคัญ',
            'pay_method' => 'การจ่าย',
            'cheque_no' => 'เลขที่เช็ค',
            'account_id' => 'จ่ายจากบัญชี',
            'payee_name' => 'จ่ายให้',
            'vat_amount' => 'ภาษีมูลค่าเพิ่ม',
            'wht_type' => 'หักภาษี ณ ที่จ่าย',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'account_id']);
    }

    public function getItems()
    {
        return $this->hasMany(FinanceCashTxn::class, ['voucher_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    public function payMethodLabel(): string
    {
        return self::PAY_METHODS[$this->pay_method] ?? '-';
    }

    public function whtLabel(): string
    {
        return isset(self::WHT_TYPES[$this->wht_type]) ? self::WHT_TYPES[$this->wht_type]['label'] : 'ไม่หักภาษี';
    }

    /**
     * คำนวณยอดจาก subtotal (ผลรวมบรรทัด) + vat + ประเภท WHT
     * WHT คิดจากฐานก่อน VAT (รวมสุทธิที่เสียภาษี) ตามหลักปฏิบัติ
     */
    public function applyTotals(float $subtotal, float $vat, ?string $whtType): void
    {
        $this->subtotal = round($subtotal, 2);
        $this->vat_amount = round($vat, 2);
        $this->total_amount = round($subtotal + $vat, 2);
        $rate = ($whtType && isset(self::WHT_TYPES[$whtType])) ? (float) self::WHT_TYPES[$whtType]['rate'] : 0.0;
        $this->wht_type = $whtType ?: null;
        $this->wht_amount = round($subtotal * $rate / 100, 2);
        $this->net_amount = round($this->total_amount - $this->wht_amount, 2);
    }
}
