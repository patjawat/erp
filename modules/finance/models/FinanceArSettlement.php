<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * การรับชำระ/ตัดปรับ/ตัดหนี้สูญ ของลูกหนี้ค่ารักษา
 *
 * @property int $id
 * @property int $ar_invoice_id
 * @property string $settle_date
 * @property string $kind
 * @property string $amount
 * @property string|null $doc_no
 * @property string|null $note
 * @property FinanceArInvoice|null $invoice
 */
class FinanceArSettlement extends ActiveRecord
{
    public const KIND_RECEIPT = 'receipt';   // รับชำระ
    public const KIND_ADJUST = 'adjust';     // ตัดปรับ (ส่วนลด/ปรับยอด)
    public const KIND_WRITEOFF = 'writeoff'; // ตัดหนี้สูญ

    public static function kindOptions(): array
    {
        return [
            self::KIND_RECEIPT => 'รับชำระ',
            self::KIND_ADJUST => 'ตัดปรับ',
            self::KIND_WRITEOFF => 'ตัดหนี้สูญ',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_ar_settlement}}';
    }

    public function rules()
    {
        return [
            [['ar_invoice_id', 'settle_date', 'amount'], 'required'],
            [['ar_invoice_id', 'created_by'], 'integer'],
            [['settle_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['kind'], 'in', 'range' => array_keys(self::kindOptions())],
            [['kind'], 'default', 'value' => self::KIND_RECEIPT],
            [['doc_no'], 'string', 'max' => 64],
            [['note'], 'string', 'max' => 500],
            [['ar_invoice_id'], 'exist', 'targetClass' => FinanceArInvoice::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ar_invoice_id' => 'ลูกหนี้',
            'settle_date' => 'วันที่',
            'kind' => 'ประเภท',
            'amount' => 'จำนวนเงิน',
            'doc_no' => 'เลขที่เอกสาร',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->amount !== null && $this->amount !== '') {
            $this->amount = (float) str_replace([',', ' '], '', (string) $this->amount);
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = $this->created_at ?: date('Y-m-d H:i:s');
            $this->created_by = $this->created_by ?: (Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null);
        }
        return true;
    }

    /** ปรับสถานะลูกหนี้แม่หลังบันทึก/ลบ settlement */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->recalcInvoiceStatus();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->recalcInvoiceStatus();
    }

    private function recalcInvoiceStatus(): void
    {
        $inv = FinanceArInvoice::findOne($this->ar_invoice_id);
        if ($inv === null) {
            return;
        }
        $settled = $inv->getSettledAmount();
        $billed = (float) $inv->billed_amount;
        if ($settled <= 0) {
            $status = $inv->status === FinanceArInvoice::STATUS_SUBMITTED ? FinanceArInvoice::STATUS_SUBMITTED : FinanceArInvoice::STATUS_BILLED;
        } elseif ($settled + 0.005 < $billed) {
            $status = FinanceArInvoice::STATUS_PARTIAL;
        } else {
            $status = FinanceArInvoice::STATUS_PAID;
        }
        if ($inv->status !== $status && $inv->status !== FinanceArInvoice::STATUS_WRITTEN_OFF) {
            $inv->status = $status;
            $inv->save(false, ['status', 'updated_at', 'updated_by']);
        }
    }

    public function getInvoice()
    {
        return $this->hasOne(FinanceArInvoice::class, ['id' => 'ar_invoice_id']);
    }
}
