<?php

namespace app\modules\accounting\models;

use Yii;
use yii\db\ActiveRecord;

class AccountingChartVersion extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';
    public const SCOPE_STANDARD = 'standard';
    public const SCOPE_HOSPITAL = 'hospital';

    public static function tableName()
    {
        return '{{%accounting_chart_version}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'version_code', 'title', 'scope', 'status'], 'required'],
            [['fiscal_year', 'account_count', 'activated_by', 'created_by', 'updated_by'], 'integer'],
            [['fiscal_year'], 'integer', 'min' => 2500, 'max' => 2700],
            [['note'], 'string'],
            [['version_code', 'scope'], 'string', 'max' => 30],
            [['title', 'source_file_name'], 'string', 'max' => 255],
            [['source_file_hash', 'ref'], 'string', 'max' => 64],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['scope'], 'in', 'range' => array_keys(self::scopeOptions())],
            [['version_code'], 'unique', 'targetAttribute' => ['fiscal_year', 'scope', 'version_code']],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
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

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับรอตรวจสอบ',
            self::STATUS_ACTIVE => 'ใช้งานอยู่',
            self::STATUS_ARCHIVED => 'เก็บประวัติ',
        ];
    }

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_STANDARD => 'ผังมาตรฐาน',
            self::SCOPE_HOSPITAL => 'ผังโรงพยาบาล',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'bg-success-subtle text-success-emphasis',
            self::STATUS_ARCHIVED => 'bg-secondary-subtle text-secondary-emphasis',
            default => 'bg-warning-subtle text-warning-emphasis',
        };
    }

    public function getAccounts()
    {
        return $this->hasMany(AccountingChartAccount::class, ['version_id' => 'id'])->orderBy(['code' => SORT_ASC]);
    }
}
