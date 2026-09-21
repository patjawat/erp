<?php

namespace app\modules\accounting\models;

use Yii;
use yii\db\ActiveRecord;

class AccountingChartMapping extends ActiveRecord
{
    public const TYPE_EXACT = 'exact';
    public const TYPE_PARENT = 'parent';
    public const TYPE_MANUAL = 'manual';
    public const TYPE_HOSPITAL_ONLY = 'hospital_only';
    public const STATUS_SUGGESTED = 'suggested';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return '{{%accounting_chart_mapping}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'standard_version_id', 'hospital_version_id', 'hospital_account_id', 'match_type', 'status'], 'required'],
            [['standard_account_id'], 'required', 'when' => fn($model) => $model->match_type !== self::TYPE_HOSPITAL_ONLY],
            [['fiscal_year', 'standard_version_id', 'standard_account_id', 'hospital_version_id', 'hospital_account_id', 'created_by', 'updated_by', 'decided_by'], 'integer'],
            [['decided_at'], 'safe'],
            [['note'], 'string'],
            [['match_type'], 'in', 'range' => [self::TYPE_EXACT, self::TYPE_PARENT, self::TYPE_MANUAL, self::TYPE_HOSPITAL_ONLY]],
            [['status'], 'in', 'range' => [self::STATUS_SUGGESTED, self::STATUS_CONFIRMED, self::STATUS_REJECTED]],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getStandardAccount() { return $this->hasOne(AccountingChartAccount::class, ['id' => 'standard_account_id']); }
    public function getHospitalAccount() { return $this->hasOne(AccountingChartAccount::class, ['id' => 'hospital_account_id']); }
}
