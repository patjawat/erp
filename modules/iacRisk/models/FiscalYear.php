<?php

namespace app\modules\iacRisk\models;

use app\components\AppHelper;
use Yii;
use yii\db\ActiveRecord;

class FiscalYear extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public static function tableName(): string { return '{{%iac_fiscal_year}}'; }

    public function rules(): array
    {
        return [
            [['hospital_id', 'fiscal_year', 'name', 'start_date', 'end_date'], 'required'],
            [['hospital_id', 'fiscal_year'], 'integer'],
            [['is_current'], 'boolean'],
            [['start_date', 'end_date', 'opened_at', 'closed_at', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['fiscal_year'], 'unique', 'targetAttribute' => ['hospital_id', 'fiscal_year'], 'message' => 'โรงพยาบาลนี้มีปีงบประมาณดังกล่าวแล้ว'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>', 'type' => 'date'],
        ];
    }

    public static function statusLabels(): array
    {
        return [self::STATUS_DRAFT => 'ฉบับร่าง', self::STATUS_OPEN => 'เปิดใช้งาน', self::STATUS_CLOSED => 'ปิดรอบปี'];
    }

    public function attributeLabels(): array
    {
        return ['fiscal_year' => 'ปีงบประมาณ', 'name' => 'ชื่อรอบปี', 'start_date' => 'วันที่เริ่มต้น', 'end_date' => 'วันที่สิ้นสุด'];
    }

    public function getHospital() { return $this->hasOne(Hospital::class, ['id' => 'hospital_id']); }
    public function getPeriods() { return $this->hasMany(ReportingPeriod::class, ['fiscal_year_id' => 'id'])->orderBy(['sequence' => SORT_ASC]); }

    public function applyDefaultDates(): void
    {
        $range = self::defaultDateRange((int) $this->fiscal_year);
        $this->name = $this->name ?: 'ปีงบประมาณ ' . (int) $this->fiscal_year;
        $this->start_date = $this->start_date ?: $range['start'];
        $this->end_date = $this->end_date ?: $range['end'];
    }

    public static function defaultDateRange(int $fiscalYear): array
    {
        return AppHelper::BudgetYearRange($fiscalYear);
    }

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
