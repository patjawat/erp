<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;

class PlanAdminAccessTest extends Unit
{
    public function testPlanAdminCanOpenDepartmentPlansThroughMeController(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../../../modules/me/controllers/PlanController.php');

        $this->assertStringContainsString('if (PlanHelper::isPlanAdmin()) {', $controller);
        $this->assertStringContainsString("->from('tree')", $controller);
        $this->assertStringContainsString('if (!PlanHelper::isPlanAdmin()) {', $controller);
        $this->assertStringContainsString("\$query->andWhere(['department_id' => \$this->ledOrgIds]);", $controller);
    }
}
