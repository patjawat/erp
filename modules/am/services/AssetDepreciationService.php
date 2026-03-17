<?php

namespace app\modules\am\services;

use app\modules\am\models\Asset;

/**
 * Thai government accounting standard: Straight Line depreciation with residual value = 1 baht.
 * Supports partial first month (inclusive day counting) and full months; final period adjusted to 1 baht.
 *
 * Annual Depreciation = (Cost - 1) / Useful Life
 * Monthly Depreciation = (Cost - 1) / Useful Life (months)
 * First month (if acquired mid-month): days_used = last_day - acquisition_day + 1; depreciation = daily * days_used
 * Daily depreciation = monthly / 30 (for first-month proration)
 */
class AssetDepreciationService
{
    /** Residual value per Thai government standard (baht) */
    public const RESIDUAL_VALUE_BAHT = 1.0;

    /** Days per month for proration (Thai government practice) */
    public const DAYS_PER_MONTH_FOR_RATE = 30;

    /**
     * Inclusive number of days used in the first month (acquisition month).
     * Formula: days_used = last_day_of_month - acquisition_day + 1
     *
     * @param string|int|null $acquisitionDate Date string (Y-m-d) or timestamp
     * @return int 1–31, or 30 if date invalid (fallback for full month)
     */
    public static function calculateDaysUsedInFirstMonth($acquisitionDate): int
    {
        if ($acquisitionDate === null || $acquisitionDate === '') {
            return self::DAYS_PER_MONTH_FOR_RATE;
        }
        $ts = is_numeric($acquisitionDate) ? (int) $acquisitionDate : strtotime($acquisitionDate);
        if ($ts === false) {
            return self::DAYS_PER_MONTH_FOR_RATE;
        }
        $day = (int) date('j', $ts);
        $lastDay = (int) date('t', $ts);
        $daysUsed = $lastDay - $day + 1;
        return max(1, min($daysUsed, $lastDay));
    }

    /**
     * Daily depreciation for first-month proration: monthly_depreciation / 30 (rounded 2 decimals).
     */
    public static function calculateDailyDepreciation(float $cost, int $usefulLifeYears): ?float
    {
        $monthly = self::calculateMonthlyDepreciation($cost, $usefulLifeYears);
        if ($monthly === null) {
            return null;
        }
        return round($monthly / self::DAYS_PER_MONTH_FOR_RATE, 2);
    }

    /**
     * Full monthly depreciation: (cost - residual) / (useful_life in months), 2 decimals.
     */
    public static function calculateMonthlyDepreciation(float $cost, int $usefulLifeYears): ?float
    {
        if ($usefulLifeYears <= 0) {
            return null;
        }
        $depreciable = $cost - self::RESIDUAL_VALUE_BAHT;
        if ($depreciable <= 0) {
            return null;
        }
        $totalMonths = $usefulLifeYears * 12;
        return round($depreciable / $totalMonths, 2);
    }

    /**
     * Calculate annual depreciation amount (rounded to 2 decimal places).
     *
     * @param float $cost Asset cost (purchase price)
     * @param int $usefulLife Useful life in years
     * @param float $residualValue Residual value (default 1 baht per government standard)
     * @return float|null Annual depreciation or null if invalid
     */
    public static function calculateAnnualDepreciation(float $cost, int $usefulLife, float $residualValue = self::RESIDUAL_VALUE_BAHT): ?float
    {
        if ($usefulLife <= 0) {
            return null;
        }
        $depreciable = $cost - $residualValue;
        if ($depreciable <= 0) {
            return null;
        }
        return round($depreciable / $usefulLife, 2);
    }

    /**
     * Generate full depreciation schedule for an asset.
     * Final year is adjusted so remaining_value equals exactly 1 baht.
     *
     * @param Asset $asset Asset model (price, useful_life, receive_date)
     * @return array{can_calculate: bool, annual_amount: float|null, schedule: list<array{year: int, year_label: string, beginning_value: float, depreciation: float, accumulated_depreciation: float, remaining_value: float}>}
     */
    public static function generateDepreciationSchedule(Asset $asset): array
    {
        $cost = (float) $asset->price;
        $usefulLife = $asset->useful_life ? (int) $asset->useful_life : 0;

        $result = [
            'can_calculate' => false,
            'annual_amount' => null,
            'schedule' => [],
        ];

        if ($usefulLife <= 0 || $cost <= 0) {
            return $result;
        }

        $annual = self::calculateAnnualDepreciation($cost, $usefulLife, self::RESIDUAL_VALUE_BAHT);
        if ($annual === null) {
            return $result;
        }

        $result['can_calculate'] = true;
        $result['annual_amount'] = $annual;

        $receiveDate = $asset->receive_date;
        $startYear = $receiveDate ? (int) date('Y', strtotime($receiveDate)) : (int) date('Y');

        $beginningValue = $cost;
        $accumulatedDepreciation = 0.0;

        for ($i = 0; $i < $usefulLife; $i++) {
            $year = $startYear + $i;
            $yearLabel = (string) ($year + 543); // พ.ศ.

            $isLastYear = ($i === $usefulLife - 1);

            if ($isLastYear) {
                // Final year: adjust so remaining value = exactly 1 baht
                $depreciation = round($beginningValue - self::RESIDUAL_VALUE_BAHT, 2);
                $remainingValue = self::RESIDUAL_VALUE_BAHT;
            } else {
                $depreciation = $annual;
                $remainingValue = round($beginningValue - $depreciation, 2);
            }

            $accumulatedDepreciation = round($cost - $remainingValue, 2);

            $result['schedule'][] = [
                'year' => $year,
                'year_label' => $yearLabel,
                'beginning_value' => round($beginningValue, 2),
                'depreciation' => round($depreciation, 2),
                'accumulated_depreciation' => $accumulatedDepreciation,
                'remaining_value' => round($remainingValue, 2),
            ];

            $beginningValue = $remainingValue;
        }

        return $result;
    }

    /**
     * Build yearly schedule from monthly schedule so both views use the same logic.
     * Each year = sum of depreciation for that calendar year from monthly schedule.
     *
     * @param Asset $asset
     * @return array{can_calculate: bool, annual_amount: float|null, schedule: list<array{year: int, year_label: string, beginning_value: float, depreciation: float, accumulated_depreciation: float, remaining_value: float}>}
     */
    public static function generateYearlyScheduleFromMonthly(Asset $asset): array
    {
        $monthly = self::generateMonthlySchedule($asset);
        $result = [
            'can_calculate' => $monthly['can_calculate'],
            'annual_amount' => $monthly['monthly_amount'] !== null ? round($monthly['monthly_amount'] * 12, 2) : null,
            'schedule' => [],
        ];
        if (!$monthly['can_calculate'] || empty($monthly['schedule'])) {
            return $result;
        }

        $cost = (float) $asset->price;
        $byYear = [];
        foreach ($monthly['schedule'] as $row) {
            $y = $row['year'];
            if (!isset($byYear[$y])) {
                $byYear[$y] = [
                    'year' => $y,
                    'beginning_value' => $row['beginning_value'],
                    'depreciation_sum' => 0.0,
                    'remaining_value' => $row['remaining_value'],
                ];
            }
            $byYear[$y]['depreciation_sum'] += $row['depreciation'];
            $byYear[$y]['remaining_value'] = $row['remaining_value'];
        }

        foreach ($byYear as $row) {
            $result['schedule'][] = [
                'year' => $row['year'],
                'year_label' => (string) ($row['year'] + 543),
                'beginning_value' => round($row['beginning_value'], 2),
                'depreciation' => round($row['depreciation_sum'], 2),
                'accumulated_depreciation' => round($cost - $row['remaining_value'], 2),
                'remaining_value' => round($row['remaining_value'], 2),
            ];
        }

        return $result;
    }

    /**
     * Get annual depreciation for an asset (government standard: residual = 1 baht).
     * Convenience method for reports and single-year display.
     */
    public static function getAnnualDepreciationForAsset(Asset $asset): ?float
    {
        $cost = (float) $asset->price;
        $years = $asset->useful_life ? (int) $asset->useful_life : 0;
        return self::calculateAnnualDepreciation($cost, $years, self::RESIDUAL_VALUE_BAHT);
    }

    /**
     * Generate monthly depreciation schedule with inclusive first-month days and days_used per period.
     * First month: depreciation = daily_depreciation * days_used (inclusive).
     * Full months: 30 days_used, full monthly depreciation.
     * Final month: adjusted so remaining_value = 1 baht.
     *
     * @param Asset $asset
     * @return array{can_calculate: bool, monthly_amount: float|null, daily_amount: float|null, schedule: list<array{year: int, month: int, days_used: int, period_label: string, beginning_value: float, depreciation: float, accumulated_depreciation: float, remaining_value: float}>}
     */
    public static function generateMonthlySchedule(Asset $asset): array
    {
        $cost = (float) $asset->price;
        $usefulLife = $asset->useful_life ? (int) $asset->useful_life : 0;

        $result = [
            'can_calculate' => false,
            'monthly_amount' => null,
            'daily_amount' => null,
            'schedule' => [],
        ];

        if ($usefulLife <= 0 || $cost <= 0) {
            return $result;
        }

        $monthlyFull = self::calculateMonthlyDepreciation($cost, $usefulLife);
        $daily = self::calculateDailyDepreciation($cost, $usefulLife);
        if ($monthlyFull === null || $daily === null) {
            return $result;
        }

        $totalMonths = $usefulLife * 12;
        $result['can_calculate'] = true;
        $result['monthly_amount'] = $monthlyFull;
        $result['daily_amount'] = $daily;

        $receiveDate = $asset->receive_date;
        $startDate = $receiveDate ? strtotime($receiveDate) : time();
        $startYear = (int) date('Y', $startDate);
        $startMonth = (int) date('n', $startDate);

        $daysUsedFirstMonth = self::calculateDaysUsedInFirstMonth($receiveDate);

        $beginningValue = $cost;
        $accumulatedDepreciation = 0.0;

        $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        for ($i = 0; $i < $totalMonths; $i++) {
            $month = $startMonth + $i;
            $year = $startYear;
            while ($month > 12) {
                $month -= 12;
                $year++;
            }
            $yearThai = $year + 543;
            $periodLabel = $thaiMonths[$month] . ' ' . $yearThai;

            $isFirstMonth = ($i === 0);
            $isLastMonth = ($i === $totalMonths - 1);

            if ($isLastMonth) {
                $depreciation = round($beginningValue - self::RESIDUAL_VALUE_BAHT, 2);
                $remainingValue = self::RESIDUAL_VALUE_BAHT;
                $daysUsed = self::DAYS_PER_MONTH_FOR_RATE;
            } elseif ($isFirstMonth) {
                $daysUsed = $daysUsedFirstMonth;
                $depreciation = round($daily * $daysUsed, 2);
                $remainingValue = round($beginningValue - $depreciation, 2);
            } else {
                $daysUsed = self::DAYS_PER_MONTH_FOR_RATE;
                $depreciation = $monthlyFull;
                $remainingValue = round($beginningValue - $depreciation, 2);
            }

            $accumulatedDepreciation = round($cost - $remainingValue, 2);

            $result['schedule'][] = [
                'year' => $year,
                'month' => $month,
                'days_used' => $daysUsed,
                'period_label' => $periodLabel,
                'beginning_value' => round($beginningValue, 2),
                'depreciation' => round($depreciation, 2),
                'accumulated_depreciation' => $accumulatedDepreciation,
                'remaining_value' => round($remainingValue, 2),
            ];

            $beginningValue = $remainingValue;
        }

        return $result;
    }
}
