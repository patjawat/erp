<?php

namespace app\modules\pm\models;

/** เกณฑ์การให้คะแนน 1 ระดับ (ระดับ 1–5) ของตัวชี้วัดในปีหนึ่ง */
class StrategyIndicatorScore extends StrategyRecord
{
    public const LEVELS = [5, 4, 3, 2, 1];

    public static function tableName(): string { return '{{%pm_strategy_indicator_score}}'; }

    public function rules(): array
    {
        return [
            [['indicator_year_id', 'level'], 'required'],
            [['indicator_year_id', 'level'], 'integer'],
            ['level', 'in', 'range' => self::LEVELS],
            [['min_value', 'max_value'], 'number'],
            ['description', 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['level' => 'ระดับ', 'description' => 'เกณฑ์', 'min_value' => 'ค่าต่ำสุด', 'max_value' => 'ค่าสูงสุด'];
    }

    public function richTextAttributes(): array { return ['description']; }

    public function getYear() { return $this->hasOne(StrategyIndicatorYear::class, ['id' => 'indicator_year_id']); }
}
