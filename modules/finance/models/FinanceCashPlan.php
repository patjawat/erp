<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * ยอดแผนรับ-จ่าย ต่อหมวด/ปีงบ (baseline) — UI ทำจริงเฟส 3
 * เก็บ 1 แถวต่อ (fiscal_year, category_id) เอาไว้เทียบกับยอดจริงจาก finance_cash_txn
 *
 * @property int $id
 * @property string $txn_type IN|OUT
 * @property int $fiscal_year พ.ศ.
 * @property int $category_id
 * @property string $amount
 * @property string|null $note
 * @property FinanceCashCategory|null $category
 */
class FinanceCashPlan extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_cash_plan}}';
    }

    public function rules()
    {
        return [
            [['txn_type', 'fiscal_year', 'category_id', 'amount'], 'required'],
            [['txn_type'], 'in', 'range' => [FinanceCashCategory::TYPE_IN, FinanceCashCategory::TYPE_OUT]],
            [['fiscal_year', 'category_id', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number', 'min' => 0],
            [['note'], 'string', 'max' => 255],
            [['category_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['fiscal_year', 'category_id'],
                'message' => 'ตั้งแผนของหมวดนี้ในปีงบนี้ไว้แล้ว'],
            [['category_id'], 'exist', 'targetClass' => FinanceCashCategory::class, 'targetAttribute' => 'id'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->amount !== null && $this->amount !== '') {
            $this->amount = $this->money($this->amount);
        }
        return true;
    }

    public function getCategory()
    {
        return $this->hasOne(FinanceCashCategory::class, ['id' => 'category_id']);
    }
}
