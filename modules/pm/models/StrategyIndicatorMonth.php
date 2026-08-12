<?php

namespace app\modules\pm\models;

/** ผลงานจริงรายเดือนของตัวชี้วัดในปีงบประมาณหนึ่ง */
class StrategyIndicatorMonth extends StrategyRecord
{
    /** เดือนปฏิทินเรียงตามปีงบประมาณ ต.ค. → ก.ย. */
    public const FISCAL_MONTHS = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    private const NAMES = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    public static function tableName(): string { return '{{%pm_strategy_indicator_month}}'; }

    public function rules(): array
    {
        return [
            [['indicator_year_id', 'month'], 'required'],
            [['indicator_year_id', 'month'], 'integer'],
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

    /** ปี พ.ศ. ปฏิทินของเดือนนี้ — เดือน ต.ค.–ธ.ค. อยู่ในปีปฏิทินก่อนหน้าปีงบประมาณ */
    public static function calendarYear(int $fiscalYear, int $month): int { return $month >= 10 ? $fiscalYear - 1 : $fiscalYear; }

    public function label(int $fiscalYear): string
    {
        return self::monthName((int) $this->month) . ' ' . (self::calendarYear($fiscalYear, (int) $this->month) % 100);
    }

    public function getYear() { return $this->hasOne(StrategyIndicatorYear::class, ['id' => 'indicator_year_id']); }
}
