<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * โครงการเงินบำรุง/เงินนอกงบประมาณ (2.4)
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property int|null $fiscal_year
 * @property string|null $fund_source
 * @property string|null $budget_amount
 * @property int $is_active
 * @property string|null $note
 */
class FinanceCashProject extends ActiveRecord
{
    public static function fundSourceOptions(): array
    {
        return [
            'donation' => 'เงินบริจาค',
            'subsidy' => 'เงินอุดหนุน',
            'own' => 'เงินบำรุง',
            'other' => 'อื่น ๆ',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_cash_project}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['fiscal_year', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['budget_amount'], 'number', 'min' => 0],
            [['fund_source'], 'in', 'range' => array_keys(self::fundSourceOptions()), 'skipOnEmpty' => true],
            [['code', 'fund_source'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 500],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสโครงการ',
            'name' => 'ชื่อโครงการ',
            'fiscal_year' => 'ปีงบประมาณ',
            'fund_source' => 'แหล่งเงิน',
            'budget_amount' => 'วงเงินโครงการ',
            'is_active' => 'ใช้งาน',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->budget_amount !== null && $this->budget_amount !== '') {
            $this->budget_amount = (float) str_replace([',', ' '], '', (string) $this->budget_amount);
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

    public function fundSourceLabel(): string
    {
        return $this->fund_source ? (self::fundSourceOptions()[$this->fund_source] ?? $this->fund_source) : '-';
    }

    /** ยอดตามประเภทของรายการที่ผูกกับโครงการนี้ */
    public function sumByType(string $type): float
    {
        return (float) FinanceCashTxn::find()
            ->where(['project_id' => $this->id, 'txn_type' => $type])->sum('amount');
    }

    public function incomeTotal(): float
    {
        return $this->sumByType(FinanceCashTxn::TYPE_IN);
    }

    public function expenseTotal(): float
    {
        return $this->sumByType(FinanceCashTxn::TYPE_OUT);
    }

    public function txnCount(): int
    {
        return (int) FinanceCashTxn::find()->where(['project_id' => $this->id])->count();
    }

    public static function activeList(): array
    {
        return self::find()->where(['is_active' => 1])
            ->select('name')->indexBy('id')
            ->orderBy(['fiscal_year' => SORT_DESC, 'name' => SORT_ASC])->column();
    }
}
