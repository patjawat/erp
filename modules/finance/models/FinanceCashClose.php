<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * งวดปิดบัญชี — 1 แถวต่อการปิด 1 ครั้ง (ประจำวัน/ประจำปี) เก็บ snapshot ยอด
 * รายการที่ถูกปิดชี้กลับมาที่แถวนี้ผ่าน finance_cash_txn.close_batch_id / voucher.close_batch_id
 *
 * @property int $id
 * @property string $close_type daily|yearly
 * @property string|null $close_date
 * @property int $fiscal_year
 * @property string $total_in
 * @property string $total_out
 * @property int $in_count
 * @property int $out_count
 * @property string|null $note
 */
class FinanceCashClose extends ActiveRecord
{
    use LoanAuditTrait;

    public const TYPE_DAILY = 'daily';
    public const TYPE_YEARLY = 'yearly';

    public static function tableName()
    {
        return '{{%finance_cash_close}}';
    }

    public function rules()
    {
        return [
            [['close_type', 'fiscal_year'], 'required'],
            [['close_type'], 'in', 'range' => [self::TYPE_DAILY, self::TYPE_YEARLY]],
            [['fiscal_year', 'in_count', 'out_count', 'created_by', 'updated_by'], 'integer'],
            [['close_date'], 'date', 'format' => 'php:Y-m-d'],
            [['total_in', 'total_out'], 'number'],
            [['note'], 'string', 'max' => 255],
        ];
    }
}
