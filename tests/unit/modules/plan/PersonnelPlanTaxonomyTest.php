<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;
use app\modules\plan\components\PersonnelPlanTaxonomy;

class PersonnelPlanTaxonomyTest extends Unit
{
    protected function _before(): void
    {
        PersonnelPlanTaxonomy::clearCache();
    }

    public function testUnknownPlanItemSelectsNobody(): void
    {
        $this->assertSame([], PersonnelPlanTaxonomy::employeeTypeIds('ไม่มีรหัสนี้'));
        $this->assertFalse(PersonnelPlanTaxonomy::appliesToAllEmployees('ไม่มีรหัสนี้'));
        $this->assertSame([], PersonnelPlanTaxonomy::employeeTypeIds(''));
    }

    public function testLegacyCodesStillMapWhenPlanItemHasNoConfiguration(): void
    {
        // รหัสชุดเก่าและชุดใหม่ต้องได้ผลเหมือนกัน เครื่องที่ยังไม่ได้อัปเดตชุดข้อมูลจึงใช้งานได้
        $this->assertSame([3], PersonnelPlanTaxonomy::employeeTypeIds('P1'));
        $this->assertSame([3], PersonnelPlanTaxonomy::employeeTypeIds('PER_01_01'));
        $this->assertSame([1, 2], PersonnelPlanTaxonomy::employeeTypeIds('P10'));
        $this->assertTrue(PersonnelPlanTaxonomy::appliesToAllEmployees('P9'));
        $this->assertSame([], PersonnelPlanTaxonomy::employeeTypeIds('P9'));
    }

    public function testDailyEmployeeBudgetUsesWorkingDaysPerMonth(): void
    {
        $daily = PersonnelPlanTaxonomy::payBasis(PersonnelPlanTaxonomy::LEGACY_DAILY_TYPE_ID);
        $this->assertSame('daily', $daily['basis']);

        $expected = 500.0 * $daily['work_days'] * 12;
        $this->assertSame(
            $expected,
            PersonnelPlanTaxonomy::annualBudget(PersonnelPlanTaxonomy::LEGACY_DAILY_TYPE_ID, 500)
        );
    }

    public function testMonthlyEmployeeBudgetIsTwelveTimesSalary(): void
    {
        $this->assertSame('monthly', PersonnelPlanTaxonomy::payBasis(4)['basis']);
        $this->assertSame(180000.0, PersonnelPlanTaxonomy::annualBudget(4, 15000));
        $this->assertSame(0.0, PersonnelPlanTaxonomy::annualBudget(4, -100));
    }

    public function testPersonnelFormWarnsWhenTaxonomyIsNotConfigured(): void
    {
        $form = file_get_contents(__DIR__ . '/../../../../modules/me/views/plan/_form_personnel.php');

        $this->assertStringContainsString('$setupIssues', $form);
        $this->assertStringContainsString('ระบบยังตั้งค่าข้อมูลพื้นฐานของแผนบุคลากรไม่ครบ', $form);
        $this->assertStringContainsString('php yii migrate', $form);
    }
}
