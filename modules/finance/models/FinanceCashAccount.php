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

    public static function tableName()
    {
        return '{{%finance_cash_account}}';
    }

    public function rules()
    {
        return [
            [['name', 'account_type'], 'required'],
            [['account_type'], 'in', 'range' => [self::TYPE_CASH, self::TYPE_TREASURY, self::TYPE_BANK]],
            [['sort_order', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'เลขที่บัญชี',
            'name' => 'ชื่อบัญชี',
            'account_type' => 'ประเภท',
            'is_active' => 'ใช้งาน',
        ];
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
