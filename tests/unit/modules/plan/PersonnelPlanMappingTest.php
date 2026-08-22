<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;
use app\modules\me\controllers\PlanController;

class PersonnelPlanMappingTest extends Unit
{
    public function testPersonnelExpenseTypesMapToExpectedEmployeeTypes(): void
    {
        $this->assertSame([3], PlanController::employeeTypesForPlanItem('P1'));
        $this->assertSame([4], PlanController::employeeTypesForPlanItem('P2'));
        $this->assertSame([5], PlanController::employeeTypesForPlanItem('P3'));
        $this->assertSame([1, 2], PlanController::employeeTypesForPlanItem('P10'));
    }

    public function testChorElevenAppliesToEveryActiveEmployeeType(): void
    {
        $this->assertTrue(PlanController::planItemAppliesToAllEmployees('P9'));
        $this->assertTrue(PlanController::planItemAppliesToAllEmployees('PER_02_04'));
        $this->assertSame([], PlanController::employeeTypesForPlanItem('P9'));
        $this->assertFalse(PlanController::planItemAppliesToAllEmployees('P1'));
    }

    public function testUnknownExpenseTypeDoesNotFallBackToAllEmployees(): void
    {
        $this->assertSame([], PlanController::employeeTypesForPlanItem('UNKNOWN'));
    }
}
