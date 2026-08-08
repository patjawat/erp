<?php

namespace app\modules\pm\models;

/**
 * กลยุทธ์ — วิธีการที่จะทำให้บรรลุเป้าประสงค์
 * อยู่ใต้เป้าประสงค์ และเป็นที่รวมของมาตรการที่ใช้ขับเคลื่อน
 */
class StrategyTactic extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_strategy_tactic}}'; }

    public function rules(): array
    {
        return [
            [['goal_id', 'name'], 'required'],
            [['goal_id', 'sort_order'], 'integer'],
            ['is_active', 'boolean'],
            ['name', 'string'],
            ['code', 'string', 'max' => 50],
        ];
    }

    public function attributeLabels(): array
    {
        return ['code' => 'รหัสกลยุทธ์', 'name' => 'กลยุทธ์', 'sort_order' => 'ลำดับ', 'is_active' => 'ใช้งาน'];
    }

    public function getGoal() { return $this->hasOne(StrategyGoal::class, ['id' => 'goal_id']); }
    public function getMeasures() { return $this->hasMany(StrategyMeasure::class, ['tactic_id' => 'id'])->orderBy(['fiscal_year' => SORT_ASC, 'sort_order' => SORT_ASC]); }

    public function label(): string { return trim(($this->code ? $this->code . ' — ' : '') . $this->name); }
}
