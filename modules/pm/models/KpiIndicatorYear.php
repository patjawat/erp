<?php

namespace app\modules\pm\models;

use app\modules\pm\components\KpiStatus;

/**
 * ค่าตัวชี้วัดนอกแผนของปีงบประมาณหนึ่ง (เป้า + ผลจริง + สถานะที่คำนวณไว้)
 */
class KpiIndicatorYear extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_kpi_indicator_year}}'; }

    public function rules(): array
    {
        return [
            [['kpi_indicator_id', 'fiscal_year'], 'required'],
            [['kpi_indicator_id', 'fiscal_year'], 'integer'],
            [['target_value', 'actual_value'], 'number'],
            [['note'], 'string'],
            ['status', 'string', 'max' => 20],
            [['kpi_indicator_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['kpi_indicator_id', 'fiscal_year'], 'message' => 'ตัวชี้วัดนี้มีข้อมูลของปีดังกล่าวแล้ว'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ (พ.ศ.)', 'target_value' => 'ค่าเป้าหมาย',
            'actual_value' => 'ผลงานจริง', 'status' => 'สถานะ', 'note' => 'หมายเหตุ',
        ];
    }

    public function getIndicator() { return $this->hasOne(KpiIndicator::class, ['id' => 'kpi_indicator_id']); }

    /**
     * คำนวณสถานะ pass/gap/nodata จาก operator ของตัวชี้วัดแม่ แล้วเก็บลง status
     * เรียกก่อนบันทึกเสมอ เพื่อให้ dashboard อ่าน status ได้ตรงโดยไม่ต้องคำนวณซ้ำ
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $operator = $this->indicator?->operator;
        $this->status = KpiStatus::evaluate($this->target_value, $this->actual_value, $operator);
        return true;
    }
}
