<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * กองเงินสดย่อย / วงเงินทดรองจ่าย (imprest fund)
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property string|null $custodian_name
 * @property string|null $unit
 * @property string $float_amount
 * @property int|null $fiscal_year
 * @property int $is_active
 * @property string|null $note
 * @property FinancePettyCashTxn[] $txns
 */
class FinancePettyCash extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_petty_cash}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['fiscal_year', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['float_amount'], 'number', 'min' => 0],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['name', 'custodian_name', 'unit'], 'string', 'max' => 255],
            [['is_active'], 'default', 'value' => 1],
            [['float_amount'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสกอง',
            'name' => 'ชื่อกองเงินสดย่อย',
            'custodian_name' => 'ผู้รับผิดชอบ',
            'unit' => 'จุด/หน่วยงาน',
            'float_amount' => 'วงเงิน',
            'fiscal_year' => 'ปีงบประมาณ',
            'is_active' => 'ใช้งาน',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->float_amount !== null && $this->float_amount !== '') {
            $this->float_amount = (float) str_replace([',', ' '], '', (string) $this->float_amount);
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getTxns()
    {
        return $this->hasMany(FinancePettyCashTxn::class, ['petty_cash_id' => 'id'])
            ->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** เงินสดคงเหลือในมือ = (ตั้งวงเงิน+เบิกชดเชย) − (จ่าย+ส่งคืน) */
    public function balanceOnHand(): float
    {
        $in = (float) FinancePettyCashTxn::find()
            ->where(['petty_cash_id' => $this->id, 'txn_type' => [FinancePettyCashTxn::TYPE_ESTABLISH, FinancePettyCashTxn::TYPE_REPLENISH]])
            ->sum('amount');
        $out = (float) FinancePettyCashTxn::find()
            ->where(['petty_cash_id' => $this->id, 'txn_type' => [FinancePettyCashTxn::TYPE_DISBURSE, FinancePettyCashTxn::TYPE_RETURN]])
            ->sum('amount');
        return $in - $out;
    }

    /** ยอดที่ใช้จ่ายไปแล้วรอเบิกชดเชย = วงเงิน − เงินคงเหลือในมือ (ไม่ต่ำกว่า 0) */
    public function unreimbursed(): float
    {
        return max(0.0, (float) $this->float_amount - $this->balanceOnHand());
    }

    /** กองที่ยังใช้งาน สำหรับ dropdown */
    public static function activeList(): array
    {
        return self::find()->where(['is_active' => 1])
            ->select('name')->indexBy('id')
            ->orderBy(['name' => SORT_ASC])->column();
    }
}
