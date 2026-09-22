<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ทะเบียนคุมการรับและนำส่งเงินรายได้แผ่นดิน (นส.02) — 1.3
 *
 * @property int $id
 * @property int $fiscal_year
 * @property string|null $revenue_type
 * @property string|null $collect_date
 * @property string $collected_amount
 * @property string|null $remit_date
 * @property string|null $remit_no
 * @property string|null $note
 */
class FinanceTreasuryRemit extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_treasury_remit}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'collected_amount'], 'required'],
            [['fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['collect_date', 'remit_date'], 'date', 'format' => 'php:Y-m-d'],
            [['collected_amount'], 'number', 'min' => 0],
            [['revenue_type', 'note'], 'string', 'max' => 500],
            [['remit_no'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'revenue_type' => 'ประเภทรายได้แผ่นดิน',
            'collect_date' => 'วันที่จัดเก็บ',
            'collected_amount' => 'ยอดจัดเก็บ',
            'remit_date' => 'วันที่นำส่งคลัง',
            'remit_no' => 'เลขที่ นส.02',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if ($this->collected_amount !== null && $this->collected_amount !== '') {
            $this->collected_amount = (float) str_replace([',', ' '], '', (string) $this->collected_amount);
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

    public function isRemitted(): bool
    {
        return !empty($this->remit_date);
    }
}
