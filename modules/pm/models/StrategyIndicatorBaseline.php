<?php

namespace app\modules\pm\models;

/** ข้อมูลพื้นฐานย้อนหลัง (Baseline Data) ของตัวชี้วัดในปีหนึ่ง */
class StrategyIndicatorBaseline extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_strategy_indicator_baseline}}'; }

    public function rules(): array
    {
        return [
            [['indicator_year_id', 'fiscal_year'], 'required'],
            [['indicator_year_id', 'fiscal_year'], 'integer'],
            ['fiscal_year', 'integer', 'min' => 2500, 'max' => 2700, 'message' => 'ระบุปีเป็น พ.ศ.'],
            ['value', 'number'],
        ];
    }

    public function attributeLabels(): array { return ['fiscal_year' => 'ปีงบประมาณ', 'value' => 'ผลการดำเนินงาน']; }

    public function getYear() { return $this->hasOne(StrategyIndicatorYear::class, ['id' => 'indicator_year_id']); }
}
