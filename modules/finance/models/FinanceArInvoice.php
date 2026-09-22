<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ลูกหนี้ค่ารักษารายก้อน (ตั้งเบิกตามสิทธิ+งวด; อาจรายผู้ป่วยหรือยอดรวม)
 *
 * @property int $id
 * @property int $ar_fund_id
 * @property int $fiscal_year
 * @property int|null $period_month
 * @property string|null $service_date
 * @property string|null $doc_no
 * @property string|null $hn
 * @property string|null $an
 * @property string|null $patient_name
 * @property string $billed_amount
 * @property string $status
 * @property string|null $note
 * @property int|null $import_batch_id
 * @property FinanceArFund|null $fund
 * @property FinanceArSettlement[] $settlements
 */
class FinanceArInvoice extends ActiveRecord
{
    public const STATUS_BILLED = 'billed';         // ตั้งลูกหนี้
    public const STATUS_SUBMITTED = 'submitted';   // ส่งเคลม
    public const STATUS_PARTIAL = 'partial';       // รับชำระบางส่วน
    public const STATUS_PAID = 'paid';             // รับครบ
    public const STATUS_WRITTEN_OFF = 'written_off'; // ตัดหนี้สูญ

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BILLED => 'ตั้งลูกหนี้',
            self::STATUS_SUBMITTED => 'ส่งเคลม',
            self::STATUS_PARTIAL => 'รับชำระบางส่วน',
            self::STATUS_PAID => 'รับครบ',
            self::STATUS_WRITTEN_OFF => 'ตัดหนี้สูญ',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_ar_invoice}}';
    }

    public function rules()
    {
        return [
            [['ar_fund_id', 'fiscal_year', 'billed_amount'], 'required'],
            [['ar_fund_id', 'fiscal_year', 'period_month', 'import_batch_id', 'created_by', 'updated_by'], 'integer'],
            [['service_date'], 'date', 'format' => 'php:Y-m-d'],
            [['billed_amount'], 'number'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_BILLED],
            [['doc_no', 'hn', 'an'], 'string', 'max' => 64],
            [['patient_name'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 500],
            [['ar_fund_id'], 'exist', 'targetClass' => FinanceArFund::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ar_fund_id' => 'สิทธิ',
            'fiscal_year' => 'ปีงบประมาณ',
            'period_month' => 'เดือนของงวด',
            'service_date' => 'วันที่บริการ/ตั้งลูกหนี้',
            'doc_no' => 'เลขอ้างอิง/เลขเคลม',
            'hn' => 'HN',
            'an' => 'AN',
            'patient_name' => 'ชื่อผู้ป่วย',
            'billed_amount' => 'ยอดตั้งเบิก',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->billed_amount !== null && $this->billed_amount !== '') {
            $this->billed_amount = (float) str_replace([',', ' '], '', (string) $this->billed_amount);
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getFund()
    {
        return $this->hasOne(FinanceArFund::class, ['id' => 'ar_fund_id']);
    }

    public function getSettlements()
    {
        return $this->hasMany(FinanceArSettlement::class, ['ar_invoice_id' => 'id']);
    }

    /** รับชำระแล้ว (receipt) + ตัดปรับ (adjust) + ตัดหนี้สูญ (writeoff) รวมเป็นยอดที่ปิดหนี้ */
    public function getSettledAmount(): float
    {
        return (float) FinanceArSettlement::find()->where(['ar_invoice_id' => $this->id])->sum('amount');
    }

    /** ยอดคงค้าง = ตั้งเบิก − ที่ปิดหนี้แล้ว (ไม่ต่ำกว่า 0) */
    public function getOutstanding(): float
    {
        return max(0.0, (float) $this->billed_amount - $this->getSettledAmount());
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }
}
