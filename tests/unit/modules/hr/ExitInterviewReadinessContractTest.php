<?php

namespace tests\unit\modules\hr;

use PHPUnit\Framework\TestCase;

class ExitInterviewReadinessContractTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 4);
    }

    public function testImportAndPublishUseTransactions(): void
    {
        $controller = file_get_contents($this->root() . '/modules/hr/controllers/ExitInterviewController.php');
        $this->assertGreaterThanOrEqual(2, substr_count($controller, 'beginTransaction()'));
        $this->assertStringContainsString('validateTemplateVersion($version)', $controller);
        $this->assertStringContainsString('if ($transaction->isActive) $transaction->rollBack()', $controller);
    }

    public function testAnalyticsApplyMinimumCohortToEveryMetricFamily(): void
    {
        $service = file_get_contents($this->root() . '/modules/hr/services/ExitInterviewService.php');
        $this->assertStringContainsString('$total < self::MINIMUM_ANALYTICS_GROUP', $service);
        $this->assertStringContainsString('(int) $row[\'cnt\'] < self::MINIMUM_ANALYTICS_GROUP', $service);
        $this->assertStringContainsString('$rehireTotal >= self::MINIMUM_ANALYTICS_GROUP', $service);
        $this->assertStringContainsString('count($reasonAnswers) >= self::MINIMUM_ANALYTICS_GROUP', $service);
    }

    public function testReadOnlyViewerGetsDisabledControlsAndNoSubmitActions(): void
    {
        $questionnaire = file_get_contents($this->root() . '/modules/hr/views/exit-interview/_questionnaire.php');
        $form = file_get_contents($this->root() . '/modules/hr/views/exit-interview/form.php');
        $this->assertStringContainsString("'disabled' => !\$canEdit", $questionnaire);
        $this->assertStringContainsString('if ($canEdit)', $questionnaire);
        $this->assertStringContainsString('คุณมีสิทธิ์ดูข้อมูลเท่านั้น', $form);
    }

    public function testNavigationAndDashboardActionsFollowGranularPermissions(): void
    {
        $nav = file_get_contents($this->root() . '/modules/hr/views/exit-interview/_nav.php');
        $dashboard = file_get_contents($this->root() . '/modules/hr/views/exit-interview/index.php');
        $this->assertStringContainsString("can('exitInterviewViewAnalytics')", $nav);
        $this->assertStringContainsString("can('exitInterviewManage')", $nav);
        $this->assertStringContainsString("can('exitInterviewManageTemplate')", $nav);
        $this->assertStringContainsString("can('exitInterviewImport')", $dashboard);
        $this->assertStringContainsString("can('exitInterviewExportIdentified')", $dashboard);
    }

    public function testSubmittedOrCancelledInterviewCannotReceiveNewPublicLink(): void
    {
        $service = file_get_contents($this->root() . '/modules/hr/services/ExitInterviewService.php');
        $this->assertStringContainsString("in_array(\$interview->status, ['pending', 'draft'], true)", $service);
        $this->assertStringContainsString('$days = min(90, max(1, $days))', $service);
    }

    public function testMigrationCreatesTokenIndexAuditLogAndGranularPermissions(): void
    {
        $migration = file_get_contents($this->root() . '/migrations/m260824_100000_create_exit_interview_tables.php');
        foreach (['uq-exit-link-token', 'exit_interview_audit_log', 'exitInterviewManage', 'exitInterviewViewIdentified', 'exitInterviewViewAnalytics', 'exitInterviewManageTemplate'] as $contract) {
            $this->assertStringContainsString($contract, $migration);
        }
    }

    public function testExitInterviewViewsAvoidHardCodedColorsAndInlineStyles(): void
    {
        foreach (glob($this->root() . '/modules/hr/views/exit-interview/*.php') as $file) {
            $contents = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression('/#[0-9a-f]{3,8}\b|rgba?\(|hsla?\(/i', $contents, $file);
            $this->assertStringNotContainsString('style=', $contents, $file);
        }
    }
}
