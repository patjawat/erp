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
    public function getMonths() { return $this->hasMany(KpiIndicatorMonth::class, ['kpi_indicator_year_id' => 'id']); }

    /** ผลงานรายเดือนครบ 12 เดือน เรียง ต.ค.→ก.ย. เติมเดือนที่ยังไม่มีเป็นรายการว่าง */
    public function monthRows(): array
    {
        $existing = [];
        foreach ($this->months as $m) $existing[(int) $m->month] = $m;
        $rows = [];
        foreach (KpiIndicatorMonth::FISCAL_MONTHS as $month) {
            $rows[] = $existing[$month] ?? new KpiIndicatorMonth(['kpi_indicator_year_id' => $this->id, 'month' => $month]);
        }
        return $rows;
    }

    /**
     * คำนวณค่าจริงรายปีจากค่ารายเดือนตามวิธีสรุปของตัวชี้วัด (avg/sum/latest)
     * ถ้าไม่มีค่ารายเดือนเลย จะไม่แตะ actual_value (ให้คงค่าที่กรอกเองไว้)
     */
    public function recomputeActualFromMonths(): void
    {
        $months = KpiIndicatorMonth::find()->where(['kpi_indicator_year_id' => $this->id])->all();
        $vals = [];
        foreach (KpiIndicatorMonth::FISCAL_MONTHS as $m) {
            foreach ($months as $row) {
                if ((int) $row->month === $m && $row->value !== null) $vals[] = (float) $row->value;
            }
        }
        if (!$vals) return;
        $agg = $this->indicator?->aggregation ?: 'avg';
        $this->actual_value = match ($agg) {
            'sum' => array_sum($vals),
            'latest' => end($vals),
            default => round(array_sum($vals) / count($vals), 4),
        };
        $this->save(false); // beforeSave คำนวณ status ใหม่
    }

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
