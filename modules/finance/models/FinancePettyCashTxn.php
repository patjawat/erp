<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * การเคลื่อนไหวเงินสดย่อย/เงินทดรองจ่าย
 *
 * @property int $id
 * @property int $petty_cash_id
 * @property string $txn_type
 * @property string $doc_date
 * @property string|null $doc_no
 * @property string|null $description
 * @property string|null $payee
 * @property string $amount
 * @property string|null $note
 * @property FinancePettyCash|null $fund
 */
class FinancePettyCashTxn extends ActiveRecord
{
    public const TYPE_ESTABLISH = 'establish'; // ตั้งวงเงิน (+)
    public const TYPE_DISBURSE = 'disburse';   // จ่าย (-)
    public const TYPE_REPLENISH = 'replenish'; // เบิกชดเชย (+)
    public const TYPE_RETURN = 'return';       // ส่งคืนวงเงิน (-)

    public const TYPES = [
        self::TYPE_ESTABLISH => 'ตั้งวงเงิน',
        self::TYPE_DISBURSE => 'จ่าย',
        self::TYPE_REPLENISH => 'เบิกชดเชย',
        self::TYPE_RETURN => 'ส่งคืนวงเงิน',
    ];

    /** ประเภทที่ทำให้เงินในมือเพิ่ม */
    public const INFLOW = [self::TYPE_ESTABLISH, self::TYPE_REPLENISH];

    public static function tableName(): string
    {
        return '{{%finance_petty_cash_txn}}';
    }

    public function rules()
    {
        return [
            [['petty_cash_id', 'txn_type', 'doc_date', 'amount'], 'required'],
            [['petty_cash_id', 'created_by', 'updated_by'], 'integer'],
            [['txn_type'], 'in', 'range' => array_keys(self::TYPES)],
            [['doc_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0.01],
            [['note'], 'string'],
            [['doc_no'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 500],
            [['payee'], 'string', 'max' => 255],
            [['petty_cash_id'], 'exist', 'targetClass' => FinancePettyCash::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'txn_type' => 'ประเภทรายการ',
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
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getFund()
    {
        return $this->hasOne(FinancePettyCash::class, ['id' => 'petty_cash_id']);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->txn_type] ?? $this->txn_type;
    }

    public function isInflow(): bool
    {
        return in_array($this->txn_type, self::INFLOW, true);
    }
}
