<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;
use app\modules\plan\services\PersonnelBudgetCalculator;

class PersonnelBudgetCalculatorTest extends Unit
{
    public function testAllocatesAnnualBudgetAcrossTwelveMonthsWithoutLosingSatang(): void
    {
        $result = PersonnelBudgetCalculator::allocate(1000.00);

        $this->assertSame(1000.00, $result['total']);
        $this->assertSame(83.33, $result['monthly_average']);
        $this->assertSame(12, count($result['months']));
        $this->assertEqualsWithDelta(1000.00, array_sum($result['months']), 0.001);
        $this->assertSame(83.37, $result['months'][9]);
    }

    public function testNegativeBudgetBecomesZero(): void
    {
        $result = PersonnelBudgetCalculator::allocate(-500.00);

        $this->assertSame(0.00, $result['total']);
        $this->assertSame(0.00, array_sum($result['months']));
    }

    public function testLargeBudgetKeepsExactTotal(): void
    {
        $result = PersonnelBudgetCalculator::allocate(3600000.00);

        $this->assertSame(300000.00, $result['monthly_average']);
        $this->assertSame(3600000.00, array_sum($result['months']));
    }
}
