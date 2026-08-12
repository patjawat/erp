<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * งวดงานของสัญญา
 *
 * แยกเป็นตารางลูกเพราะสัญญาจ้าง/ก่อสร้างแบ่งงวดได้ไม่จำกัด และค่าปรับของสัญญา
 * ที่แบ่งงวดต้องคิดจากวงเงินของงวดที่ส่งล่าช้า ไม่ใช่วงเงินทั้งสัญญา
 *
 * @property int $id
 * @property int $contract_id
 * @property int $seq ลำดับงวด
 * @property string|null $detail
 * @property float|null $percent สัดส่วนของวงเงิน (%)
 * @property float $amount วงเงินของงวด
 * @property string|null $due_date กำหนดส่งมอบ
 * @property string|null $delivered_date วันที่ส่งมอบจริง
 * @property string|null $receive_date วันที่ตรวจรับ
 * @property string $status pending|delivered|received
 * @property int $fine_days
 * @property float $fine_amount
 */
class ContractMilestone extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RECEIVED = 'received';

    public static function tableName()
    {
        return 'purchase_contract_milestone';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['contract_id'], 'required'],
            [['contract_id', 'seq', 'fine_days'], 'integer'],
            [['percent', 'amount', 'fine_amount'], 'number', 'min' => 0],
            [['due_date', 'delivered_date', 'receive_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['detail', 'note'], 'string', 'max' => 500],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['amount', 'fine_amount', 'fine_days'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'seq' => 'งวดที่',
            'detail' => 'รายละเอียดงาน',
            'percent' => 'สัดส่วน (%)',
            'amount' => 'วงเงินงวด',
            'due_date' => 'กำหนดส่งมอบ',
            'delivered_date' => 'ส่งมอบจริง',
            'receive_date' => 'วันตรวจรับ',
            'status' => 'สถานะ',
            'fine_days' => 'วันล่าช้า',
            'fine_amount' => 'ค่าปรับ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }

    public static function statusList()
    {
        return [
            self::STATUS_PENDING => 'รอส่งมอบ',
            self::STATUS_DELIVERED => 'ส่งมอบแล้ว',
            self::STATUS_RECEIVED => 'ตรวจรับแล้ว',
        ];
    }

    public static function statusBadge($status)
    {
        $map = [
            self::STATUS_PENDING => ['label' => 'รอส่งมอบ', 'color' => 'secondary'],
            self::STATUS_DELIVERED => ['label' => 'ส่งมอบแล้ว', 'color' => 'info'],
            self::STATUS_RECEIVED => ['label' => 'ตรวจรับแล้ว', 'color' => 'success'],
        ];
        return $map[$status] ?? ['label' => $status ?: '-', 'color' => 'secondary'];
    }

    public function statusName()
    {
        return self::statusBadge($this->status)['label'];
    }

    /**
     * วันที่ใช้ปิดงวดสำหรับคิดค่าปรับ
     * ยึดวันส่งมอบจริงก่อน ถ้ายังไม่กรอกจึงถอยไปใช้วันตรวจรับ — เพราะค่าปรับ
     * หยุดนับตั้งแต่วันที่ผู้ขายส่งมอบครบถ้วน ไม่ใช่วันที่คณะกรรมการตรวจรับเสร็จ
     * ซึ่งอาจช้ากว่าหลายวันด้วยเหตุที่ไม่ใช่ความผิดของผู้ขาย
     */
    public function closingDate(): ?string
    {
        return $this->delivered_date ?: ($this->receive_date ?: null);
    }
}
