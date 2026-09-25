<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * รายการในงวดตรวจรับ — ปริมาณที่ตรวจรับงวดนี้ × ราคาต่อหน่วยตามสัญญา
 * ชื่อ/หน่วย/ราคาเป็น snapshot จากบรรทัดใบสั่งซื้อ ณ ตอนบันทึก
 *
 * @property int $id
 * @property int $receipt_id
 * @property int|null $order_item_id
 * @property string|null $asset_item
 * @property string|null $item_name
 * @property string|null $unit_name
 * @property float $qty
 * @property float $unit_price
 * @property float $amount
 */
class ContractReceiptItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'purchase_contract_receipt_item';
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
            [['receipt_id'], 'required'],
            [['receipt_id', 'order_item_id'], 'integer'],
            [['qty', 'unit_price'], 'number', 'min' => 0],
            [['amount'], 'number'],
            [['asset_item', 'item_name'], 'string', 'max' => 255],
            [['unit_name'], 'string', 'max' => 50],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->amount = round((float) $this->qty * (float) $this->unit_price, 2);
        return true;
    }
}
