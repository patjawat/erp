<?php

namespace app\modules\pm\models;

/** ค่ารายเดือนของตัวชี้วัด รพ. ในปีงบประมาณหนึ่ง (ต.ค.→ก.ย.) */
class KpiIndicatorMonth extends StrategyRecord
{
    /** เดือนปฏิทินเรียงตามปีงบประมาณ ต.ค. → ก.ย. */
    public const FISCAL_MONTHS = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    private const NAMES = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    public static function tableName(): string { return '{{%pm_kpi_indicator_month}}'; }

    public function rules(): array
    {
        return [
            [['kpi_indicator_year_id', 'month'], 'required'],
            [['kpi_indicator_year_id', 'month'], 'integer'],
            ['month', 'in', 'range' => self::FISCAL_MONTHS],
            [['numerator', 'denominator', 'value'], 'number'],
            ['note', 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['month' => 'เดือน', 'numerator' => 'ตัวตั้ง', 'denominator' => 'ตัวหาร', 'value' => 'ผลงาน', 'note' => 'หมายเหตุ'];
    }

    public static function monthName(int $month): string { return self::NAMES[$month] ?? (string) $month; }

    public function getYearRow() { return $this->hasOne(KpiIndicatorYear::class, ['id' => 'kpi_indicator_year_id']); }
}
