<?php

namespace app\modules\am\services;

use app\modules\am\models\DepreciationProfile;

/**
 * เครื่องคำนวณค่าเสื่อม (pure calculation engine)
 *
 * ออกแบบให้ไม่ผูกกับ ActiveRecord/DB เพื่อให้ unit test ได้เต็มรูปแบบ:
 *   - รับ input เป็น array ของ snapshot เกณฑ์ + ราคาทุน + วันได้มา
 *   - คืน schedule รายเดือน พร้อมรายละเอียดสูตร (formula) เพื่อ audit
 *   - รายไตรมาส/ปีงบประมาณให้รวมจาก schedule รายเดือนชุดเดียวกัน (aggregateByFiscal)
 *
 * รองรับ:
 *   - วิธีเส้นตรง (straight_line)
 *   - ฐานคำนวณ monthly / daily (ตามจำนวนวัน) / full_period
 *   - อัตราหลายช่วง (rate_tiers)
 *   - ป้องกันค่าเสื่อมเกินฐาน และมูลค่าสุทธิไม่ต่ำกว่ามูลค่าซาก
 *   - ปัดเศษสม่ำเสมอตาม rounding_scale
 *
 * หน่วยเงินคำนวณด้วย float ภายใน แต่ปัดเศษทุกงวดตาม scale และเก็บลง DECIMAL(20,4)
 */
class DepreciationCalculator
{
    public const DAYS_PER_MONTH = 30;

    /**
     * สร้างพารามิเตอร์คำนวณจาก snapshot ของทรัพย์สิน + เกณฑ์
     *
     * @param array $overrides key ที่ต้องการ override ค่า default
     * @return array
     */
    public static function params(array $overrides = []): array
    {
        return array_merge([
            'cost' => 0.0,
            'salvage_value' => 0.0,
            'salvage_value_type' => DepreciationProfile::SALVAGE_AMOUNT,
            'minimum_book_value' => null,    // null = ใช้มูลค่าซาก; ใช้แยกกรณีราชการที่คิดจากราคาทุนแต่คงมูลค่า 1 บาท
            'method' => DepreciationProfile::METHOD_STRAIGHT_LINE,
            'useful_life_months' => 0,
            'annual_rate' => null,          // อัตราต่อปี (%) กรณีคิดด้วยอัตราเดียว
            'calculation_basis' => DepreciationProfile::BASIS_MONTHLY,
            'start_rule' => DepreciationProfile::START_READY_DATE,
            'rounding_scale' => 2,
            'acquisition_date' => null,     // Y-m-d
            'rate_tiers' => [],             // [['start_month'=>1,'end_month'=>36,'rate_percent'=>5.0], ...]
            'disposal_date' => null,        // Y-m-d วันจำหน่าย — หยุดคิดค่าเสื่อมหลังวันนี้
        ], $overrides);
    }

    /**
     * มูลค่าซากตามชนิด
     */
    public static function resolveSalvage(float $cost, string $type, float $value): float
    {
        switch ($type) {
            case DepreciationProfile::SALVAGE_ONE_BAHT:
                return 1.0;
            case DepreciationProfile::SALVAGE_PERCENT:
                return $cost * ($value / 100.0);
            case DepreciationProfile::SALVAGE_AMOUNT:
            default:
                return $value;
        }
    }

    /**
     * วันเริ่มคิดค่าเสื่อมตามกฎ start_rule
     *
     * @return string|null Y-m-d
     */
    public static function resolveStartDate(?string $acquisitionDate, string $startRule): ?string
    {
        if (empty($acquisitionDate)) {
            return null;
        }
        $ts = strtotime($acquisitionDate);
        if ($ts === false) {
            return null;
        }
        switch ($startRule) {
            case DepreciationProfile::START_READY_MONTH:
                return date('Y-m-01', $ts);
            case DepreciationProfile::START_NEXT_MONTH:
                return date('Y-m-01', strtotime('first day of next month', $ts));
            case DepreciationProfile::START_DAY_15_CUTOFF:
                return (int) date('j', $ts) <= 15
                    ? date('Y-m-01', $ts)
                    : date('Y-m-01', strtotime('first day of next month', $ts));
            case DepreciationProfile::START_READY_DATE:
            default:
                return date('Y-m-d', $ts);
        }
    }

    /**
     * จำนวนวันใช้งานในเดือนแรก (นับแบบ inclusive: วันสิ้นเดือน - วันเริ่ม + 1)
     */
    public static function daysUsedInFirstMonth(string $date): int
    {
        $ts = strtotime($date);
        if ($ts === false) {
            return self::DAYS_PER_MONTH;
        }
        $day = (int) date('j', $ts);
        $lastDay = (int) date('t', $ts);
        return max(1, min($lastDay - $day + 1, $lastDay));
    }

    /**
     * จำนวนวันใช้งานในเดือนสุดท้ายเมื่อเริ่มกลางเดือน (ส่วนที่เหลือให้ครบเดือน)
     * เริ่มวันที่ 15 → เดือนสุดท้ายคิดวันที่ 1-14 = 14 วัน
     */
    public static function daysUsedInFinalMonth(string $startDate): int
    {
        $ts = strtotime($startDate);
        if ($ts === false) {
            return 0;
        }
        return max(0, (int) date('j', $ts) - 1);
    }

    /** ปี-เดือน (Ym) ของวันที่ — ใช้เทียบว่าอยู่เดือนเดียวกันหรือไม่ */
    private static function ym(?string $date): ?int
    {
        if (empty($date)) {
            return null;
        }
        $ts = strtotime($date);

        return $ts === false ? null : (int) date('Ym', $ts);
    }

    /**
     * หาอัตรา (% ต่อปี) ของเดือนลำดับที่ $monthIndex จากช่วงอัตราหลายช่วง
     *
     * @param array $tiers list ของ ['start_month','end_month','rate_percent']
     * @return float|null null ถ้าไม่มีช่วงครอบคลุม
     */
    public static function tierRateForMonth(array $tiers, int $monthIndex): ?float
    {
        foreach ($tiers as $t) {
            $start = (int) $t['start_month'];
            $end = isset($t['end_month']) && $t['end_month'] !== null ? (int) $t['end_month'] : PHP_INT_MAX;
            if ($monthIndex >= $start && $monthIndex <= $end) {
                return (float) $t['rate_percent'];
            }
        }
        return null;
    }

    /**
     * สร้าง schedule รายเดือน
     *
     * @param array $p ผลจาก params()
     * @return array{
     *   can_calculate: bool,
     *   cost: float, salvage: float, depreciable_base: float,
     *   schedule: list<array>,
     *   message: string
     * }
     */
    public static function buildMonthlySchedule(array $p): array
    {
        $p = self::params($p);
        $scale = (int) $p['rounding_scale'];
        $cost = (float) $p['cost'];
        $salvage = self::resolveSalvage($cost, (string) $p['salvage_value_type'], (float) $p['salvage_value']);
        $minimumBookValue = $p['minimum_book_value'] === null
            ? $salvage
            : max(0.0, (float) $p['minimum_book_value']);
        $base = round($cost - $salvage, $scale);
        $lifeMonths = (int) $p['useful_life_months'];
        $basis = (string) $p['calculation_basis'];
        $tiers = is_array($p['rate_tiers']) ? $p['rate_tiers'] : [];

        $result = [
            'can_calculate' => false,
            'cost' => round($cost, $scale),
            'salvage' => round($salvage, $scale),
            'depreciable_base' => $base,
            'schedule' => [],
            'message' => '',
        ];

        if ($cost <= 0) {
            $result['message'] = 'ราคาทุนต้องมากกว่า 0';
            return $result;
        }
        if ($base <= 0) {
            $result['message'] = 'ฐานค่าเสื่อม (ทุน - มูลค่าซาก) ต้องมากกว่า 0';
            return $result;
        }

        $hasTiers = !empty($tiers);
        $annualRate = $p['annual_rate'] !== null ? (float) $p['annual_rate'] : null;

        // จำนวนงวดที่จะเดิน
        $maxMonths = $lifeMonths > 0 ? $lifeMonths : 0;
        if ($hasTiers) {
            foreach ($tiers as $t) {
                if (isset($t['end_month']) && $t['end_month'] !== null) {
                    $maxMonths = max($maxMonths, (int) $t['end_month']);
                }
            }
        }
        if ($maxMonths <= 0) {
            $result['message'] = 'ต้องระบุอายุการใช้งาน (เดือน) หรือช่วงอัตราค่าเสื่อม';
            return $result;
        }

        $startDate = self::resolveStartDate($p['acquisition_date'], (string) $p['start_rule']);
        $walkTs = $startDate ? strtotime($startDate) : null;

        $accumulated = 0.0;
        // คิดตามจำนวนวันจริงเมื่อเริ่มกลางเดือน — ใช้ได้เฉพาะกฎ "เริ่มวันที่พร้อมใช้งาน"
        // (กฎอื่น resolve เป็นวันที่ 1 อยู่แล้ว จึงเป็นเดือนเต็มทุกเดือน)
        $prorate = ($basis === DepreciationProfile::BASIS_DAILY)
            && $startDate !== null
            && (string) $p['start_rule'] === DepreciationProfile::START_READY_DATE
            && (int) date('j', (int) $walkTs) > 1;

        // เริ่มกลางเดือน → เดือนแรกไม่เต็ม จึงต้องมีเดือนท้ายอีก 1 งวดเก็บส่วนที่เหลือ
        $finalPartialDays = $prorate ? self::daysUsedInFinalMonth($startDate) : 0;
        if ($prorate && $finalPartialDays > 0) {
            $maxMonths++;
        }

        $disposalYm = self::ym($p['disposal_date'] ?? null);

        for ($i = 0; $i < $maxMonths; $i++) {
            $monthIndex = $i + 1;
            $currentYm = $walkTs ? (int) date('Ym', $walkTs) : null;

            // จำหน่ายแล้ว — หยุดก่อนเดือนถัดจากเดือนที่จำหน่าย
            if ($disposalYm !== null && $currentYm !== null && $currentYm > $disposalYm) {
                break;
            }

            // อัตรา/ค่าเสื่อมเต็มเดือน
            if ($hasTiers) {
                $rate = self::tierRateForMonth($tiers, $monthIndex);
                $monthlyFull = $rate !== null ? $base * ($rate / 100.0) / 12.0 : 0.0;
            } elseif ($annualRate !== null && $annualRate > 0) {
                $rate = $annualRate;
                $monthlyFull = $base * ($annualRate / 100.0) / 12.0;
            } else {
                $rate = null;
                $monthlyFull = $lifeMonths > 0 ? $base / $lifeMonths : 0.0;
            }

            // การนับวัน / proration — คิดตามสัดส่วนวันจริงของเดือนนั้น (ไม่ใช่ฐาน 30 วันตายตัว
            // ซึ่งทำให้เดือน 31 วันคิดเกินเดือนเต็มไป 3%)
            $daysInMonth = $walkTs ? (int) date('t', $walkTs) : self::DAYS_PER_MONTH;
            $daysUsed = $daysInMonth;
            $dep = $monthlyFull;

            if ($basis !== DepreciationProfile::BASIS_FULL_PERIOD && $prorate) {
                if ($i === 0) {
                    $daysUsed = self::daysUsedInFirstMonth($startDate);       // เดือนแรก: ตั้งแต่วันที่พร้อมใช้ถึงสิ้นเดือน
                } elseif ($finalPartialDays > 0 && $monthIndex === $maxMonths) {
                    $daysUsed = min($finalPartialDays, $daysInMonth);         // เดือนท้าย: ส่วนที่เหลือให้ครบเดือน
                }
                if ($daysUsed !== $daysInMonth) {
                    $dep = $monthlyFull * ($daysUsed / $daysInMonth);
                }
            }

            // เดือนที่จำหน่าย: คิดถึงวันที่จำหน่ายเท่านั้น (เฉพาะฐานคิดตามวัน)
            if ($disposalYm !== null && $currentYm === $disposalYm
                && $basis === DepreciationProfile::BASIS_DAILY) {
                $disposalDay = (int) date('j', strtotime((string) $p['disposal_date']));
                $daysUsed = max(0, min($daysUsed, $disposalDay));
                $dep = $monthlyFull * ($daysUsed / $daysInMonth);
            }

            $dep = round($dep, $scale);

            // งวดสุดท้ายแบบเส้นตรงอัตราเดียว: บังคับให้ปิดพอดีที่มูลค่าซาก
            // (ต้องเป็นงวดสุดท้ายจริง ๆ ของ schedule ไม่ใช่แค่ครบจำนวนเดือนตามอายุ
            //  เพราะเมื่อเริ่มกลางเดือนจะมีเดือนท้ายเพิ่มมาอีก 1 งวด)
            $isFinalMonth = (!$hasTiers && $annualRate === null && $lifeMonths > 0 && $monthIndex === $maxMonths);
            if ($isFinalMonth && $disposalYm === null) {
                $dep = round($base - $accumulated, $scale);
            }

            // ป้องกันค่าเสื่อมเกินฐาน
            if ($accumulated + $dep > $base) {
                $dep = round($base - $accumulated, $scale);
            }
            if ($dep < 0) {
                $dep = 0.0;
            }

            $openingAccumulated = $accumulated;
            $beginningNbv = round($cost - $accumulated, $scale);
            $accumulated = round($accumulated + $dep, $scale);
            $closingNbv = round($cost - $accumulated, $scale);
            // มูลค่าสุทธิไม่ต่ำกว่ามูลค่าซาก
            if ($closingNbv < round($minimumBookValue, $scale)) {
                $closingNbv = round($minimumBookValue, $scale);
                $accumulated = round($cost - $closingNbv, $scale);
                $dep = round($accumulated - $openingAccumulated, $scale);
            }

            $calYear = $walkTs ? (int) date('Y', $walkTs) : 0;
            $calMonth = $walkTs ? (int) date('n', $walkTs) : 0;
            $fiscal = self::fiscalInfo($calYear, $calMonth);

            $result['schedule'][] = [
                'month_index' => $monthIndex,
                'period_date' => $walkTs ? date('Y-m-d', $walkTs) : null,
                'calendar_year' => $calYear,
                'calendar_month' => $calMonth,
                'fiscal_year' => $fiscal['fiscal_year'],       // พ.ศ.
                'fiscal_month' => $fiscal['fiscal_month'],     // 1..12 (ต.ค.=1)
                'quarter' => $fiscal['quarter'],               // 1..4
                'days_used' => $daysUsed,
                'rate_percent' => $rate,
                'beginning_value' => $beginningNbv,
                'depreciation' => $dep,
                'accumulated_depreciation' => $accumulated,
                'remaining_value' => $closingNbv,
                'formula' => self::formulaText($hasTiers, $rate, $base, $lifeMonths, $basis, $daysUsed, $dep, $scale),
            ];

            if ($walkTs) {
                $walkTs = strtotime('+1 month', mktime(0, 0, 0, (int) date('n', $walkTs), 1, (int) date('Y', $walkTs)));
            }

            // ปิดเมื่อค่าเสื่อมสะสมถึงฐานแล้ว
            if ($accumulated >= $base) {
                break;
            }
        }

        $result['can_calculate'] = true;
        return $result;
    }

    /**
     * รวม schedule รายเดือนเป็นรายไตรมาส/ปีงบประมาณ (ยอดต้องตรงกับรายเดือนชุดเดียวกัน)
     *
     * @param array $schedule schedule จาก buildMonthlySchedule()['schedule']
     * @param string $groupBy 'quarter' | 'fiscal_year'
     * @param int $scale ทศนิยมของเกณฑ์ — ต้องใช้ค่าเดียวกับตอนสร้าง schedule
     *                   ไม่งั้นยอดรวมรายไตรมาส/ปีจะไม่ตรงกับผลรวมรายเดือน
     * @return array<string,array> key = "{fiscal_year}" หรือ "{fiscal_year}-Q{n}"
     */
    public static function aggregateByFiscal(array $schedule, string $groupBy, int $scale = 2): array
    {
        $out = [];
        foreach ($schedule as $row) {
            $key = $groupBy === 'quarter'
                ? $row['fiscal_year'] . '-Q' . $row['quarter']
                : (string) $row['fiscal_year'];

            if (!isset($out[$key])) {
                $out[$key] = [
                    'fiscal_year' => $row['fiscal_year'],
                    'quarter' => $groupBy === 'quarter' ? $row['quarter'] : null,
                    'beginning_value' => $row['beginning_value'],
                    'depreciation' => 0.0,
                    'accumulated_depreciation' => $row['accumulated_depreciation'],
                    'remaining_value' => $row['remaining_value'],
                    'months' => 0,
                ];
            }
            $out[$key]['depreciation'] = round($out[$key]['depreciation'] + $row['depreciation'], $scale);
            // ค่าปลายงวดใช้ค่าล่าสุดของกลุ่ม
            $out[$key]['accumulated_depreciation'] = $row['accumulated_depreciation'];
            $out[$key]['remaining_value'] = $row['remaining_value'];
            $out[$key]['months']++;
        }
        return $out;
    }

    /**
     * ข้อมูลปีงบประมาณไทย (เริ่ม 1 ต.ค.)
     *
     * @return array{fiscal_year:int, fiscal_month:int, quarter:int}
     */
    public static function fiscalInfo(int $calYear, int $calMonth): array
    {
        if ($calYear === 0 || $calMonth === 0) {
            return ['fiscal_year' => 0, 'fiscal_month' => 0, 'quarter' => 0];
        }
        // ปีงบ (ค.ศ.): ต.ค.-ธ.ค. นับเป็นปีถัดไป
        $fyCe = $calMonth >= 10 ? $calYear + 1 : $calYear;
        // เดือนในปีงบ: ต.ค.=1 ... ก.ย.=12
        $fiscalMonth = $calMonth >= 10 ? $calMonth - 9 : $calMonth + 3;
        $quarter = (int) ceil($fiscalMonth / 3);
        return [
            'fiscal_year' => $fyCe + 543, // พ.ศ.
            'fiscal_month' => $fiscalMonth,
            'quarter' => $quarter,
        ];
    }

    private static function formulaText(bool $hasTiers, ?float $rate, float $base, int $lifeMonths, string $basis, int $days, float $dep, int $scale): string
    {
        if ($hasTiers || $rate !== null) {
            $r = $rate !== null ? number_format($rate, 2) : '0.00';
            $f = "ฐาน {$base} × {$r}% ÷ 12";
        } else {
            $f = "ฐาน {$base} ÷ {$lifeMonths} เดือน";
        }
        if ($basis === DepreciationProfile::BASIS_DAILY) {
            $f .= " × {$days}/" . self::DAYS_PER_MONTH . ' วัน';
        }
        return $f . ' = ' . number_format($dep, $scale);
    }
}
