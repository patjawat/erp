<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * การส่งใช้เงินยืมหนึ่งครั้ง — ตรงกับหนึ่งบรรทัดในตารางหน้า 2 ของแบบ 8500
 *
 * seq คือ “ครั้งที่” บนกระดาษ และ balance_after คือช่อง “คงค้าง” ที่ลดลงทีละครั้ง
 *
 * balance_after เก็บไว้กับแถวเพื่อให้พิมพ์และทำรายงานได้โดยไม่ต้องไล่คำนวณใหม่ทุกครั้ง
 * แต่ไม่ใช่ค่าที่แช่แข็ง — FinanceLoanSettlementService จะไล่คำนวณยอดคงค้างใหม่ทั้งสาย
 * ทุกครั้งที่มีการเพิ่ม แก้ หรือลบรายการใดรายการหนึ่ง เพราะถ้าแก้ยอดของครั้งที่ 1
 * แล้วคงค้างของครั้งที่ 2 ยังเป็นตัวเลขเดิม ตารางหน้า 2 จะอ่านแล้วขัดกันเอง
 *
 * @property FinanceLoan $loan
 */
class FinanceLoanSettlement extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_loan_settlement}}';
    }

    public function rules()
    {
        return [
            [['loan_id', 'settled_at'], 'required'],
            [['loan_id', 'seq', 'created_by', 'updated_by'], 'integer'],
            [['settled_at', 'evidence_sent_at'], 'date', 'format' => 'php:Y-m-d'],
            [['voucher_amount', 'cash_amount', 'balance_after'], 'number', 'min' => 0],
            [['voucher_amount', 'cash_amount', 'balance_after'], 'default', 'value' => 0],
            [['receipt_no', 'document_no'], 'string', 'max' => 100],
            [['receipt_book_no', 'receipt_number'], 'string', 'max' => 50],
            [['late_reason', 'note'], 'string', 'max' => 255],
            [['voucher_amount'], 'validateHasAmount'],
            [['settled_at'], 'validateNotBeforeBorrow'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'seq' => 'ครั้งที่',
            'settled_at' => 'วันที่ส่งใช้',
            'voucher_amount' => 'ใบสำคัญ',
            'cash_amount' => 'เงินสด',
            'balance_after' => 'คงค้างหลังส่งใช้',
            'receipt_no' => 'เลขที่ บร./บค.',
            'document_no' => 'เลขที่บันทึกนำส่ง',
            'receipt_book_no' => 'ใบเสร็จ เล่มที่',
            'receipt_number' => 'ใบเสร็จ เลขที่',
            'evidence_sent_at' => 'วันที่ส่งหลักฐานให้หน่วยงานผู้เบิก',
            'late_reason' => 'เหตุผลกรณีล่าช้า',
            'note' => 'หมายเหตุ',
        ];
    }

    public function validateHasAmount(): void
    {
        if ($this->money($this->voucher_amount) + $this->money($this->cash_amount) <= 0) {
            $this->addError('voucher_amount', 'ระบุยอดใบสำคัญหรือเงินสดอย่างน้อยหนึ่งช่อง');
        }
    }

    public function validateNotBeforeBorrow(): void
    {
        $loan = $this->loan;
        if ($loan && $this->settled_at && $this->settled_at < $loan->borrowed_at) {
            $this->addError('settled_at', 'วันที่ส่งใช้ต้องไม่ก่อนวันที่ยืม (' . Yii::$app->formatter->asDate($loan->borrowed_at, 'php:d/m/Y') . ')');
        }
    }

    public function getLoan()
    {
        return $this->hasOne(FinanceLoan::class, ['id' => 'loan_id']);
    }

    public function totalAmount(): float
    {
        return round($this->money($this->voucher_amount) + $this->money($this->cash_amount), 2);
    }

    /** ครั้งถัดไปของใบยืมนี้ */
    public static function nextSeq(int $loanId): int
    {
        return 1 + (int) self::find()->where(['loan_id' => $loanId])->max('seq');
    }
}
