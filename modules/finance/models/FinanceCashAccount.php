<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * บัญชีเงิน/แหล่งเงิน (เงินสด/เงินฝากคลัง/บัญชีธนาคาร)
 * ยอดคงเหลือ + เคลื่อนไหว ทำเต็มในเฟส 4 — ตอนนี้ใช้เป็นตัวเลือก "จ่ายจากบัญชี"
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property string $account_type cash|treasury|bank
 * @property int $sort_order
 * @property int $is_active
 */
class FinanceCashAccount extends ActiveRecord
{
    use LoanAuditTrait;

    public const TYPE_CASH = 'cash';
    public const TYPE_TREASURY = 'treasury';
    public const TYPE_BANK = 'bank';

    /** รายชื่อธนาคาร (dropdown) */
    public const BANKS = [
        'ธนาคารกรุงไทย', 'ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร', 'ธนาคารออมสิน',
        'ธนาคารไทยพาณิชย์', 'ธนาคารกสิกรไทย', 'ธนาคารกรุงเทพ', 'ธนาคารกรุงศรีอยุธยา',
        'ธนาคารทหารไทยธนชาต', 'ธนาคารอาคารสงเคราะห์', 'ธนาคารซีไอเอ็มบีไทย',
        'ธนาคารยูโอบี', 'ธนาคารเกียรตินาคินภัทร', 'ธนาคารทิสโก้', 'ธนาคารไอซีบีซี (ไทย)',
    ];

    /** ประเภทบัญชีเงินฝาก */
    public const DEPOSIT_TYPES = ['ออมทรัพย์', 'กระแสรายวัน', 'ฝากประจำ'];

    public static function tableName()
    {
        return '{{%finance_cash_account}}';
    }

    public function rules()
    {
        return [
            [['name', 'account_type'], 'required'],
            [['account_type'], 'in', 'range' => [self::TYPE_CASH, self::TYPE_TREASURY, self::TYPE_BANK]],
            [['sort_order', 'is_active', 'is_promptpay', 'is_credit', 'created_by', 'updated_by'], 'integer'],
            [['name', 'bank_name', 'branch'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['deposit_type'], 'string', 'max' => 24],
            [['sort_order', 'is_promptpay', 'is_credit'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'เลขที่บัญชี',
            'name' => 'ชื่อบัญชี',
            'bank_name' => 'ธนาคาร',
            'branch' => 'สาขา',
            'deposit_type' => 'ประเภทบัญชี',
            'account_type' => 'ชนิดบัญชี',
            'is_promptpay' => 'บัญชีพร้อมเพย์',
            'is_credit' => 'รับเงินบัตรเครดิต',
            'is_active' => 'ใช้งาน',
        ];
    }

    public function getBalances()
    {
        return $this->hasMany(FinanceCashAccountBalance::class, ['account_id' => 'id']);
    }

    /** ยอดคงเหลือของปีงบที่ระบุ (กรอกเอง) */
    public function balanceFor(int $fiscalYear): float
    {
        $row = FinanceCashAccountBalance::find()->where(['account_id' => $this->id, 'fiscal_year' => $fiscalYear])->one();
        return $row ? (float) $row->amount : 0.0;
    }

    public function label(): string
    {
        return $this->code ? $this->code . ' (' . $this->name . ')' : $this->name;
    }

    /** id => label สำหรับ dropdown */
    public static function activeList(): array
    {
        $rows = self::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        return ArrayHelper::map($rows, 'id', fn ($a) => $a->label());
    }
}
