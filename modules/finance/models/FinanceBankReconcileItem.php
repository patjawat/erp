<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * รายการกระทบยอดในงบพิสูจน์ยอดเงินฝาก
 *
 * @property int $id
 * @property int $reconcile_id
 * @property string $side
 * @property string $direction
 * @property string|null $item_type
 * @property string|null $description
 * @property string $amount
 * @property string|null $ref
 */
class FinanceBankReconcileItem extends ActiveRecord
{
    public const SIDE_BANK = 'bank';
    public const SIDE_BOOK = 'book';
    public const DIR_ADD = 'add';
    public const DIR_SUB = 'sub';

    public static function sideOptions(): array
    {
        return [self::SIDE_BANK => 'ฝั่งธนาคาร (statement)', self::SIDE_BOOK => 'ฝั่งบัญชี รพ. (book)'];
    }

    public static function dirOptions(): array
    {
        return [self::DIR_ADD => 'บวก (+)', self::DIR_SUB => 'ลบ (−)'];
    }

    /** ประเภทรายการกระทบยอดที่พบบ่อย (ช่วยเลือก) */
    public static function typeOptions(): array
    {
        return [
            'deposit_in_transit' => 'เงินฝากระหว่างทาง',
            'outstanding_cheque' => 'เช็คค้างจ่าย',
            'bank_charge' => 'ค่าธรรมเนียมธนาคาร',
            'interest' => 'ดอกเบี้ยรับ',
            'direct_credit' => 'เงินโอนเข้ายังไม่บันทึก',
            'error' => 'แก้ไขข้อผิดพลาด',
            'other' => 'อื่น ๆ',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_bank_reconcile_item}}';
    }

    public function rules()
    {
        return [
            [['reconcile_id', 'side', 'direction', 'amount'], 'required'],
            [['reconcile_id'], 'integer'],
            [['side'], 'in', 'range' => array_keys(self::sideOptions())],
            [['direction'], 'in', 'range' => array_keys(self::dirOptions())],
            [['amount'], 'number', 'min' => 0.01],
            [['item_type', 'ref'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 500],
            [['reconcile_id'], 'exist', 'targetClass' => FinanceBankReconcile::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'side' => 'ฝั่ง',
            'direction' => 'ทิศทาง',
            'item_type' => 'ประเภท',
            'description' => 'รายการ',
            'amount' => 'จำนวนเงิน',
            'ref' => 'อ้างอิง',
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

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->item_type] ?? ($this->item_type ?: '-');
    }

    public function signedLabel(): string
    {
        return $this->direction === self::DIR_ADD ? '+' : '−';
    }
}
