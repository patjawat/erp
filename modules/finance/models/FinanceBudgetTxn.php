<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * รายการรับ-จ่ายเงินงบประมาณ (1.2 / งบกลาง 1.5)
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int|null $allotment_id
 * @property string $txn_type
 * @property string $budget_category
 * @property string $doc_date
 * @property string|null $doc_no
 * @property string|null $description
 * @property string|null $payee
 * @property string $amount
 * @property string|null $note
 */
class FinanceBudgetTxn extends ActiveRecord
{
    public const TYPE_RECEIVE = 'receive';   // รับจากคลัง/เบิก
    public const TYPE_DISBURSE = 'disburse'; // จ่าย

    /** งบรายจ่ายตามหมวด สป.สธ. */
    public const CATEGORIES = [
        'personnel' => 'งบบุคลากร',
        'operation' => 'งบดำเนินงาน',
        'investment' => 'งบลงทุน',
        'subsidy' => 'งบเงินอุดหนุน',
        'other' => 'งบรายจ่ายอื่น',
        'central' => 'งบกลาง (สวัสดิการ)',
    ];

    public static function typeOptions(): array
    {
        return [self::TYPE_RECEIVE => 'รับจากคลัง', self::TYPE_DISBURSE => 'จ่าย'];
    }

    public static function tableName(): string
    {
        return '{{%finance_budget_txn}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'txn_type', 'budget_category', 'doc_date', 'amount'], 'required'],
            [['fiscal_year', 'allotment_id', 'created_by', 'updated_by'], 'integer'],
            [['txn_type'], 'in', 'range' => array_keys(self::typeOptions())],
            [['budget_category'], 'in', 'range' => array_keys(self::CATEGORIES)],
            [['doc_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['doc_no'], 'string', 'max' => 64],
            [['description', 'note'], 'string', 'max' => 500],
            [['payee'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'allotment_id' => 'เงินประจำงวด',
            'txn_type' => 'ประเภท',
            'budget_category' => 'งบรายจ่าย',
            'doc_date' => 'วันที่',
            'doc_no' => 'เลขที่เอกสาร',
            'description' => 'รายการ',
            'payee' => 'จ่ายให้',
            'amount' => 'จำนวนเงิน',
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
        return self::CATEGORIES[$this->budget_category] ?? $this->budget_category;
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->txn_type] ?? $this->txn_type;
    }
}
