<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * เงินประจำงวด — เงินงบประมาณที่ได้รับจัดสรร (1.1)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int|null $period_no
 * @property string $budget_category
 * @property string|null $allotment_no
 * @property string|null $allotment_date
 * @property string $amount
 * @property string|null $note
 */
class FinanceBudgetAllotment extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_budget_allotment}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'budget_category', 'amount'], 'required'],
            [['fiscal_year', 'period_no', 'created_by', 'updated_by'], 'integer'],
            [['budget_category'], 'in', 'range' => array_keys(FinanceBudgetTxn::CATEGORIES)],
            [['allotment_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0],
            [['allotment_no'], 'string', 'max' => 64],
            [['note'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'period_no' => 'งวดที่',
            'budget_category' => 'งบรายจ่าย',
            'allotment_no' => 'เลขที่หนังสือจัดสรร',
            'allotment_date' => 'วันที่จัดสรร',
            'amount' => 'ยอดจัดสรร',
            'note' => 'หมายเหตุ',
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

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }

    public function categoryLabel(): string
    {
        return FinanceBudgetTxn::CATEGORIES[$this->budget_category] ?? $this->budget_category;
    }

    /** ยอดเบิกจ่าย (disburse) ที่ผูกกับเงินประจำงวดนี้ */
    public function getDisbursed(): float
    {
        return (float) FinanceBudgetTxn::find()
            ->where(['allotment_id' => $this->id, 'txn_type' => FinanceBudgetTxn::TYPE_DISBURSE])->sum('amount');
    }

    public function getRemaining(): float
    {
        return (float) $this->amount - $this->getDisbursed();
    }
}
