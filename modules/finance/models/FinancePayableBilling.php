<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ทะเบียนรับวางบิล — บริษัทมาวางบิล 1 ครั้ง ครอบคลุมหลายบิล (finance_payable.billing_id)
 *
 * @property int $id
 * @property string $billing_no
 * @property string $billing_date
 * @property int|null $vendor_id
 * @property string $vendor_name
 * @property string|null $vendor_ref เลขที่ใบวางบิลของบริษัท
 * @property string|null $deliverer_name ผู้วางบิล (ตัวแทนบริษัท)
 * @property int|null $receiver_id
 * @property string|null $receiver_name ผู้รับวางบิล
 * @property int $bill_count
 * @property string $total_amount
 * @property string|null $note
 * @property string|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancel_reason
 * @property string|null $created_at
 * @property int|null $created_by
 * @property FinancePayable[] $payables
 */
class FinancePayableBilling extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_payable_billing}}';
    }

    public function rules(): array
    {
        return [
            [['billing_date', 'vendor_name'], 'required'],
            [['billing_date'], 'date', 'format' => 'php:Y-m-d'],
            [['vendor_id', 'receiver_id', 'bill_count'], 'integer'],
            [['total_amount'], 'number'],
            [['vendor_name'], 'string', 'max' => 255],
            [['vendor_ref'], 'string', 'max' => 60],
            [['deliverer_name', 'receiver_name'], 'string', 'max' => 150],
            [['note', 'cancel_reason'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'billing_no' => 'เลขที่ใบรับวางบิล',
            'billing_date' => 'วันที่รับวางบิล',
            'vendor_name' => 'บริษัท',
            'vendor_ref' => 'เลขที่ใบวางบิลของบริษัท',
            'deliverer_name' => 'ผู้วางบิล',
            'receiver_name' => 'ผู้รับวางบิล',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = $this->created_at ?: date('Y-m-d H:i:s');
            $this->created_by = $this->created_by
                ?: (Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null);
        }
        return true;
    }

    public function getPayables()
    {
        return $this->hasMany(FinancePayable::class, ['billing_id' => 'id'])->orderBy(['invoice_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function isCancelled(): bool
    {
        return !empty($this->cancelled_at);
    }

    /** เลขที่ใบรับวางบิลถัดไป BL-ปีงบ พ.ศ.-ลำดับ 4 หลัก (นับต่อปีงบ) */
    public static function nextNo(string $billingDate): string
    {
        $ts = strtotime($billingDate) ?: time();
        $fy = (int) date('Y', $ts) + 543 + ((int) date('n', $ts) >= 10 ? 1 : 0);
        $prefix = 'BL-' . $fy . '-';
        $last = (string) self::find()->select('billing_no')->where(['like', 'billing_no', $prefix . '%', false])
            ->orderBy(['billing_no' => SORT_DESC])->scalar();
        $seq = $last !== '' ? (int) substr($last, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
