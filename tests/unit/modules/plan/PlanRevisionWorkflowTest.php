<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;
use app\modules\plan\services\PlanRevisionService;

class PlanRevisionWorkflowTest extends Unit
{
    public function testRevisionTypesRepresentFullPlanSnapshots(): void
    {
        $this->assertSame('initial_approved', PlanRevisionService::INITIAL);
        $this->assertSame('before_adjust', PlanRevisionService::BEFORE_ADJUST);
        $this->assertSame('adjusted_approved', PlanRevisionService::ADJUSTED);

        $migration = file_get_contents(__DIR__ . '/../../../../migrations/m260815_120000_create_plan_order_revision_table.php');
        foreach (range(1, 12) as $month) {
            $this->assertStringContainsString("'month_{$month}'", $migration);
        }
        $this->assertStringContainsString("'items_json'", $migration);
    }

    public function testHeadWorkflowRequiresAdjustPhaseAndLocksAfterSubmit(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../../../modules/me/controllers/PlanController.php');
        $this->assertStringContainsString('PlanHelper::canAdjust($model->thai_year)', $controller);
        $this->assertStringContainsString("in_array(\$model->status, ['renew', 'reject'], true)", $controller);
        $this->assertStringContainsString("\$model->status = 'submit'", $controller);

        $centralController = file_get_contents(__DIR__ . '/../../../../modules/plan/controllers/PlanOrderController.php');
        $this->assertStringContainsString("\$currentStatus !== 'submit'", $centralController);
        $this->assertStringContainsString("if (\$status === 'approve')", $centralController);
        $this->assertStringContainsString('PlanRevisionService::capture', $centralController);
    }
}
