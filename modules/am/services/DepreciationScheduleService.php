<?php

namespace app\modules\am\services;

use app\modules\am\models\Asset;

/**
 * Builds depreciation schedule for display (UI).
 * Uses Thai government accounting standard via AssetDepreciationService:
 * residual value = 1 baht, straight line, yearly only, final remaining = 1 baht.
 */
class DepreciationScheduleService
{
    /**
     * @param Asset $asset
     * @return array{can_calculate: bool, annual_amount: float|null, monthly_amount: float|null, schedule: list<array{year_label: string, begin_value: float, annual_depreciation: float, accumulated: float, end_value: float}>}
     */
    public static function buildSchedule(Asset $asset)
    {
        $data = AssetDepreciationService::generateYearlyScheduleFromMonthly($asset);

        $result = [
            'can_calculate' => $data['can_calculate'],
            'annual_amount' => $data['annual_amount'],
            'monthly_amount' => $data['annual_amount'] !== null ? round($data['annual_amount'] / 12, 2) : null,
            'schedule' => [],
        ];

        foreach ($data['schedule'] as $row) {
            $result['schedule'][] = [
                'year_label' => $row['year_label'],
                'begin_value' => $row['beginning_value'],
                'annual_depreciation' => $row['depreciation'],
                'accumulated' => $row['accumulated_depreciation'],
                'end_value' => $row['remaining_value'],
            ];
        }

        return $result;
    }

    /**
     * Build monthly schedule for display (government standard: residual 1 baht, days_used included).
     *
     * @param Asset $asset
     * @return array{can_calculate: bool, monthly_amount: float|null, daily_amount: float|null, schedule: list<array{period_label: string, days_used: int, month: int, is_first_month_of_year: bool, begin_value: float, depreciation: float, accumulated: float, end_value: float}>}
     */
    public static function buildMonthlySchedule(Asset $asset): array
    {
        $data = AssetDepreciationService::generateMonthlySchedule($asset);

        $result = [
            'can_calculate' => $data['can_calculate'],
            'monthly_amount' => $data['monthly_amount'],
            'daily_amount' => $data['daily_amount'] ?? null,
            'schedule' => [],
        ];

        foreach ($data['schedule'] as $row) {
            $month = (int) ($row['month'] ?? 0);
            $result['schedule'][] = [
                'period_label' => $row['period_label'],
                'days_used' => $row['days_used'],
                'month' => $month,
                'is_first_month_of_year' => $month === 1,
                'begin_value' => $row['beginning_value'],
                'depreciation' => $row['depreciation'],
                'accumulated' => $row['accumulated_depreciation'],
                'end_value' => $row['remaining_value'],
            ];
        }

        return $result;
    }

    /**
     * ชื่อวิธีคำนวณสำหรับแสดงผล
     */
    public static function getMethodLabel(?string $method): string
    {
        return match ($method ?? 'straight_line') {
            'straight_line' => 'วิธีเส้นตรง',
            default => $method ?? 'วิธีเส้นตรง',
        };
    }
}
