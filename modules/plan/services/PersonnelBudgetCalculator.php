<?php

namespace app\modules\plan\services;

/**
 * สูตรกลางสำหรับแผนบุคลากรแบบรวมรายชื่อ
 * จำนวนเงินที่กรอกเป็นวงเงินประมาณการทั้งปี และกระจายเท่ากัน 12 เดือน
 */
final class PersonnelBudgetCalculator
{
    /**
     * @return array{months: array<int,float>, monthly_average: float, total: float}
     */
    public static function allocate(float $annualBudget): array
    {
        $totalCents = max(0, (int) round($annualBudget * 100));
        $baseCents = intdiv($totalCents, 12);
        $months = array_fill(1, 12, $baseCents / 100.0);

        // ปีงบประมาณสิ้นสุดเดือนกันยายน ให้เดือน 9 รับเศษจากการปัดทศนิยม
        $months[9] = ($totalCents - ($baseCents * 11)) / 100.0;

        return [
            'months' => $months,
            'monthly_average' => round($totalCents / 12 / 100.0, 2),
            'total' => $totalCents / 100.0,
        ];
    }
}
