<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ทะเบียนคุมเบิกเกินส่งคืนคลัง — 1.4
 *
 * @property int $id
 * @property int $fiscal_year
 * @property string|null $budget_category
 * @property string|null $source_ref
 * @property string|null $return_date
 * @property string|null $return_no
 * @property string $amount
 * @property string|null $note
 */
class FinanceBudgetReturn extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_budget_return}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'amount'], 'required'],
            [['fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['budget_category'], 'in', 'range' => array_keys(FinanceBudgetTxn::CATEGORIES), 'skipOnEmpty' => true],
            [['return_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['source_ref', 'note'], 'string', 'max' => 500],
            [['return_no'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'budget_category' => 'งบรายจ่าย',
            'source_ref' => 'อ้างการเบิกเดิม',
            'return_date' => 'วันที่ส่งคืน',
            'return_no' => 'เลขที่เอกสาร',
            'amount' => 'ยอดส่งคืน',
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
        return $this->budget_category ? (FinanceBudgetTxn::CATEGORIES[$this->budget_category] ?? $this->budget_category) : '-';
    }
}
