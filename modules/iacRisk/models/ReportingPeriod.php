<?php

namespace app\modules\iacRisk\models;

use Yii;
use yii\db\ActiveRecord;

class ReportingPeriod extends ActiveRecord
{
    public const CODE_SIX_MONTH = 'six_month';
    public const CODE_NINE_MONTH = 'nine_month';
    public const CODE_YEAR_END = 'year_end';
    public const STATUS_PENDING = 'pending';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public static function tableName(): string { return '{{%iac_reporting_period}}'; }

    public function rules(): array
    {
        return [
            [['fiscal_year_id', 'code', 'name', 'sequence', 'start_date', 'end_date'], 'required'],
            [['fiscal_year_id', 'sequence'], 'integer'],
            [['start_date', 'end_date', 'due_date', 'opened_at', 'closed_at', 'created_at', 'updated_at'], 'safe'],
            [['code', 'status'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_OPEN, self::STATUS_CLOSED]],
        ];
    }

    public function getFiscalYear() { return $this->hasOne(FiscalYear::class, ['id' => 'fiscal_year_id']); }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
