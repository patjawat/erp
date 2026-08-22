<?php

namespace tests\unit\views;

use Codeception\Test\Unit;

class PersonnelPlanFormTest extends Unit
{
    public function testFormUsesAnnualBudgetAndReadOnlyMonthlyAllocation(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/me/views/plan/_form_personnel.php');

        $this->assertNotFalse($view);
        $this->assertStringContainsString('[annual_budget]', $view);
        $this->assertStringContainsString('วงเงินประมาณการทั้งปี', $view);
        $this->assertStringContainsString('ระบบเฉลี่ยวงเงินทั้งปีเป็น 12 เดือนอัตโนมัติ', $view);
        $this->assertStringNotContainsString('[days]', $view);
        $this->assertStringNotContainsString('[qty]', $view);
    }

    public function testMobileLayoutTransformsRowsIntoCards(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/me/views/plan/_form_personnel.php');

        $this->assertStringContainsString('@media (max-width: 991.98px)', $view);
        $this->assertStringContainsString('content: attr(data-label)', $view);
        $this->assertStringContainsString('min-width: 44px; min-height: 44px', $view);
    }

    public function testEmployeeRosterCanBeFilteredByMultipleTypesOrAllTypes(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/me/views/plan/_form_personnel.php');

        $this->assertStringContainsString("'name' => 'employee_type_ids'", $view);
        $this->assertStringContainsString("'multiple' => true", $view);
        $this->assertStringContainsString('id="all-employee-types"', $view);
        $this->assertStringContainsString('all_employee_types:', $view);
    }
}
