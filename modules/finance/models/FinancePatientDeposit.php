<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * เงินมัดจำ / เงินรับฝากผู้ป่วย
 *
 * @property int $id
 * @property string|null $receipt_no
 * @property string $deposit_date
 * @property int|null $fiscal_year
 * @property string|null $hn
 * @property string|null $an
 * @property string|null $patient_name
 * @property string $amount
 * @property string $used_amount
 * @property string $refunded_amount
 * @property string $status
 * @property string|null $note
 */
class FinancePatientDeposit extends ActiveRecord
{
    public const STATUS_HELD = 'held';       // ถือครองอยู่
    public const STATUS_PARTIAL = 'partial'; // หัก/คืนบางส่วน
    public const STATUS_CLOSED = 'closed';   // ปิด (หัก+คืนครบ)

    public static function statusOptions(): array
    {
        return [
            self::STATUS_HELD => 'รับฝากอยู่',
            self::STATUS_PARTIAL => 'ใช้/คืนบางส่วน',
            self::STATUS_CLOSED => 'ปิดรายการ',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_patient_deposit}}';
    }

    public function rules()
    {
        return [
            [['deposit_date', 'amount'], 'required'],
            [['fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['deposit_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount', 'used_amount', 'refunded_amount'], 'number', 'min' => 0],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_HELD],
            [['used_amount', 'refunded_amount'], 'default', 'value' => 0],
            [['receipt_no', 'hn', 'an'], 'string', 'max' => 64],
            [['patient_name'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels()
    {
        return [
            'receipt_no' => 'เลขที่ใบรับเงิน',
            'deposit_date' => 'วันที่รับฝาก',
            'hn' => 'HN',
            'an' => 'AN',
            'patient_name' => 'ชื่อผู้ป่วย',
            'amount' => 'ยอดรับฝาก',
            'used_amount' => 'หักชำระแล้ว',
            'refunded_amount' => 'คืนแล้ว',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        foreach (['amount', 'used_amount', 'refunded_amount'] as $f) {
            if ($this->$f !== null && $this->$f !== '') {
                $this->$f = (float) str_replace([',', ' '], '', (string) $this->$f);
            }
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // คำนวณสถานะจากยอดคงเหลือ
        $remaining = $this->remaining();
        if ($remaining <= 0.005) {
            $this->status = self::STATUS_CLOSED;
        } elseif ((float) $this->used_amount > 0 || (float) $this->refunded_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        } else {
            $this->status = self::STATUS_HELD;
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

    /** ยอดคงเหลือรับฝาก = รับฝาก − ใช้ − คืน */
    public function remaining(): float
    {
        return (float) $this->amount - (float) $this->used_amount - (float) $this->refunded_amount;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }
}
