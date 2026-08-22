<?php

namespace tests\unit\modules\plan;

use Codeception\Test\Unit;

class PlanRbacSeparationTest extends Unit
{
    public function testMigrationSeparatesPlannerFromApprover(): void
    {
        $migration = file_get_contents(__DIR__ . '/../../../../migrations/m260815_130000_separate_plan_and_approval_permissions.php');

        $this->assertStringContainsString("['parent' => 'plan', 'child' => 'planApprove']", $migration);
        $this->assertStringContainsString("['parent' => 'planApprove', 'child' => '/plan/*']", $migration);
        $this->assertStringContainsString("ensureChild('planApprove', '/plan/approve/*')", $migration);
        $this->assertStringContainsString("ensureChild('director', 'planApprove')", $migration);
    }

    public function testPlanPeriodRequiresPlannerOrAdminRole(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../../../modules/plan/controllers/PlanPeriodController.php');

        $this->assertStringContainsString("user->can('plan')", $controller);
        $this->assertStringContainsString("user->can('admin')", $controller);
        $this->assertStringContainsString('ForbiddenHttpException', $controller);
    }
}
