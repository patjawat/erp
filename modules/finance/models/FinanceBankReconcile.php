<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * งบพิสูจน์ยอดเงินฝากธนาคาร (หัว)
 *
 * @property int $id
 * @property int $cash_account_id
 * @property int $fiscal_year
 * @property int|null $period_month
 * @property string|null $statement_date
 * @property string $statement_balance
 * @property string $book_balance
 * @property string $status
 * @property string|null $note
 * @property FinanceCashAccount|null $account
 * @property FinanceBankReconcileItem[] $items
 */
class FinanceBankReconcile extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DONE = 'done';

    public static function statusOptions(): array
    {
        return [self::STATUS_DRAFT => 'ร่าง', self::STATUS_DONE => 'ยืนยันแล้ว'];
    }

    public static function tableName(): string
    {
        return '{{%finance_bank_reconcile}}';
    }

    public function rules()
    {
        return [
            [['cash_account_id', 'fiscal_year'], 'required'],
            [['cash_account_id', 'fiscal_year', 'period_month', 'created_by', 'updated_by'], 'integer'],
            [['statement_date'], 'date', 'format' => 'php:Y-m-d'],
            [['statement_balance', 'book_balance'], 'number'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['statement_balance', 'book_balance'], 'default', 'value' => 0],
            [['note'], 'string'],
            [['cash_account_id'], 'exist', 'targetClass' => FinanceCashAccount::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cash_account_id' => 'บัญชีเงินฝาก',
            'fiscal_year' => 'ปีงบประมาณ',
            'period_month' => 'เดือน',
            'statement_date' => 'วันที่ตาม statement',
            'statement_balance' => 'ยอดตาม statement ธนาคาร',
            'book_balance' => 'ยอดตามบัญชี รพ.',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        foreach (['statement_balance', 'book_balance'] as $f) {
            if ($this->$f !== null && $this->$f !== '') {
                $this->$f = (float) str_replace([',', ' '], '', (string) $this->$f);
            }
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

    public function getAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'cash_account_id']);
    }

    public function getItems()
    {
        return $this->hasMany(FinanceBankReconcileItem::class, ['reconcile_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    private function sideSum(string $side, string $direction): float
    {
        return (float) FinanceBankReconcileItem::find()
            ->where(['reconcile_id' => $this->id, 'side' => $side, 'direction' => $direction])->sum('amount');
    }

    /** ยอดถูกต้องฝั่งธนาคาร = statement + บวก − ลบ */
    public function adjustedBank(): float
    {
        return (float) $this->statement_balance
            + $this->sideSum(FinanceBankReconcileItem::SIDE_BANK, FinanceBankReconcileItem::DIR_ADD)
            - $this->sideSum(FinanceBankReconcileItem::SIDE_BANK, FinanceBankReconcileItem::DIR_SUB);
    }

    /** ยอดถูกต้องฝั่งบัญชี รพ. = book + บวก − ลบ */
    public function adjustedBook(): float
    {
        return (float) $this->book_balance
            + $this->sideSum(FinanceBankReconcileItem::SIDE_BOOK, FinanceBankReconcileItem::DIR_ADD)
            - $this->sideSum(FinanceBankReconcileItem::SIDE_BOOK, FinanceBankReconcileItem::DIR_SUB);
    }

    public function difference(): float
    {
        return $this->adjustedBank() - $this->adjustedBook();
    }

    public function isMatched(): bool
    {
        return abs($this->difference()) < 0.01;
    }
}
