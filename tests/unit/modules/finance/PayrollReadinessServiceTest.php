<?php

namespace tests\unit\modules\finance;

use app\modules\finance\services\PayrollReadinessService;
use Codeception\Test\Unit;

class PayrollReadinessServiceTest extends Unit
{
    public function testValidMonthIsPreserved(): void
    {
        $this->assertSame('2026-08', PayrollReadinessService::normalizeMonth('2026-08', '2026-01'));
    }

    /** @dataProvider invalidMonthProvider */
    public function testInvalidMonthFallsBackSafely(string $month): void
    {
        $this->assertSame('2026-01', PayrollReadinessService::normalizeMonth($month, '2026-01'));
    }

    public static function invalidMonthProvider(): array
    {
        return [
            'empty' => [''],
            'missing leading zero' => ['2026-8'],
            'month zero' => ['2026-00'],
            'month thirteen' => ['2026-13'],
            'non numeric' => ['abcd-ef'],
        ];
    }

    public function testEmployeeWhoseEmploymentOverlapsPeriodIsIncluded(): void
    {
        $this->assertTrue(PayrollReadinessService::overlaps('2026-01-01', '2026-08-15', '2026-08-01', '2026-08-31'));
    }

    public function testEmployeeWhoLeftBeforePeriodIsExcluded(): void
    {
        $this->assertFalse(PayrollReadinessService::overlaps('2025-01-01', '2026-07-31', '2026-08-01', '2026-08-31'));
    }

    public function testEmployeeWhoStartsAfterPeriodIsExcluded(): void
    {
        $this->assertFalse(PayrollReadinessService::overlaps('2026-09-01', null, '2026-08-01', '2026-08-31'));
    }

    public function testEmployeeWithBlankEmploymentDatesIsIncluded(): void
    {
        $this->assertTrue(PayrollReadinessService::overlaps('', '', '2026-08-01', '2026-08-31'));
    }

    public function testActiveEmployeeWithoutEndDateIsEligible(): void
    {
        $this->assertTrue(PayrollReadinessService::eligibleForPeriod('1', null, '2026-08-01'));
    }

    public function testInactiveEmployeeWithEndDateInPeriodIsEligibleForFinalPayroll(): void
    {
        $this->assertTrue(PayrollReadinessService::eligibleForPeriod('3', '2026-08-31', '2026-08-01'));
    }

    public function testInactiveEmployeeWithoutEndDateIsNotEligible(): void
    {
        $this->assertFalse(PayrollReadinessService::eligibleForPeriod('3', null, '2026-08-01'));
    }

    public function testInactiveEmployeeWhoseEmploymentEndedBeforePeriodIsNotEligible(): void
    {
        $this->assertFalse(PayrollReadinessService::eligibleForPeriod('2', '2026-07-31', '2026-08-01'));
    }

    public function testEffectiveDatedSalaryUsesTheRecordCoveringPeriodEnd(): void
    {
        $old = (object) ['data_json' => ['date_start' => '2025-01-01', 'date_end' => '2026-07-31', 'salary' => 15000]];
        $current = (object) ['data_json' => ['date_start' => '2026-08-01', 'date_end' => null, 'salary' => 16500, 'employee_position_text' => 'นักวิชาการ']];

        $result = PayrollReadinessService::effectivePosition([$current, $old], '2026-08-31');

        $this->assertSame(16500.0, $result['salary']);
        $this->assertSame('นักวิชาการ', $result['title']);
    }
}
