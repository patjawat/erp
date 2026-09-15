<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * ยอดปิดบัญชีประจำปี รายหมวด (แก้ได้/ซิงค์จาก txn)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int $category_id
 * @property string $amount
 */
class FinanceCashYearCloseItem extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_cash_year_close_item}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'category_id', 'amount'], 'required'],
            [['fiscal_year', 'category_id', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['fiscal_year', 'category_id'], 'unique', 'targetAttribute' => ['fiscal_year', 'category_id']],
        ];
    }
}
