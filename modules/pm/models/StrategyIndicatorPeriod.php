<?php

namespace app\modules\pm\models;

/** รอบการประเมินของตัวชี้วัดในปีหนึ่ง — is_selected คือช่อง "ระยะเวลาการประเมินผล" ในแบบฟอร์ม */
class StrategyIndicatorPeriod extends StrategyRecord
{
    public const MONTHS = [1, 3, 6, 9, 12];

    public static function tableName(): string { return '{{%pm_strategy_indicator_period}}'; }

    public function rules(): array
    {
        return [
            [['indicator_year_id', 'period_month'], 'required'],
            [['indicator_year_id', 'period_month', 'score_level'], 'integer'],
            ['period_month', 'in', 'range' => self::MONTHS],
            ['is_selected', 'boolean'],
            [['target_value', 'actual_value'], 'number'],
            ['note', 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['period_month' => 'รอบ', 'is_selected' => 'ประเมินรอบนี้', 'target_value' => 'เกณฑ์',
            'actual_value' => 'ผลงาน', 'score_level' => 'ระดับคะแนน', 'note' => 'หมายเหตุ'];
    }

    public function label(): string { return "รอบ {$this->period_month} เดือน"; }

    public function getYear() { return $this->hasOne(StrategyIndicatorYear::class, ['id' => 'indicator_year_id']); }
}
