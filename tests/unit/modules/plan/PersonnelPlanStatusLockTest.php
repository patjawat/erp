<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;

class PersonnelPlanStatusLockTest extends Unit
{
    public function testBothPersonnelControllersLockSubmittedPlans(): void
    {
        $meController = file_get_contents(__DIR__ . '/../../../../modules/me/controllers/PlanController.php');
        $centralController = file_get_contents(__DIR__ . '/../../../../modules/plan/controllers/PersonnelController.php');
        $centralActions = file_get_contents(__DIR__ . '/../../../../modules/plan/views/personnel/action.php');

        $this->assertStringContainsString("in_array(\$model->status, ['draft', 'reject'], true)", $meController);
        $this->assertStringContainsString("if (!in_array(\$model->status, \$editableStatuses, true))", $centralController);
        $this->assertStringContainsString("\$model->status !== 'draft'", $centralController);
        $this->assertStringContainsString("in_array(\$model->status, ['draft', 'reject'], true)", $centralActions);
    }
}
