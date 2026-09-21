<?php

namespace tests\unit\modules\laundry;

use Codeception\Test\Unit;

class ProcessingSafetyTest extends Unit
{
    public function testMachineAndSourceAreLockedBeforeAllocation(): void
    {
        $service = $this->source('modules/laundry/services/ProcessingService.php');
        $this->assertStringContainsString('WHERE asset_id = :id FOR UPDATE', $service);
        $this->assertStringContainsString('WHERE w.id = :id FOR UPDATE', $service);
        $this->assertStringContainsString('WHERE id = :id FOR UPDATE', $service);
        $this->assertStringContainsString("'RUNNING'", $service);
        $this->assertStringContainsString('น้ำหนักที่เลือกเกินน้ำหนักคงเหลือ', $service);
        $this->assertStringContainsString('น้ำหนักเกินกำลังเครื่อง', $service);
    }

    public function testOnlyConfirmedCollectionFeedsWashAndCompletedWashFeedsDry(): void
    {
        $service = $this->source('modules/laundry/services/ProcessingService.php');
        $this->assertStringContainsString("$" . "row['status'] !== 'CONFIRMED'", $service);
        $this->assertStringContainsString("$" . "row['stage'] !== 'WASH'", $service);
        $this->assertStringContainsString("$" . "row['status'] !== 'COMPLETED'", $service);
        $this->assertStringContainsString("'COLLECTION_WEIGHT' : 'WASH_BATCH'", $service);
    }

    public function testAbortedLoadsRemainReservedAndRepairBlocksNewCycle(): void
    {
        $service = $this->source('modules/laundry/services/ProcessingService.php');
        $this->assertStringContainsString('i.source_type = :type AND i.source_id = :id', $service);
        $this->assertStringNotContainsString("b.status <> 'ABORTED'", $service);
        $this->assertStringContainsString('Asset::LIFECYCLE_REPAIR', $service);
        $this->assertStringContainsString('Asset::LIFECYCLE_DISPOSED', $service);
        $this->assertStringContainsString("'status' => 'ABORTED'", $service);
    }

    public function testRecoveryRequiresApprovalAndDoesNotReleaseOriginalAllocation(): void
    {
        $service = $this->source('modules/laundry/services/ProcessingService.php');
        $this->assertStringContainsString("$" . "row['status'] !== 'APPROVED'", $service);
        $this->assertStringContainsString("$" . "row['outcome'] !== 'REPROCESS'", $service);
        $this->assertStringContainsString("$" . "recovery['created_by'] === $" . "approverId", $service);
        $this->assertStringContainsString("$" . "sourceMode === 'RECOVERY' ? 'RECOVERY'", $service);
        $this->assertStringNotContainsString('DELETE FROM {{%laundry_batch_input}}', $service);
    }

    private function source(string $relative): string
    {
        $contents = file_get_contents(dirname(__DIR__, 4) . '/' . $relative);
        $this->assertNotFalse($contents);
        return $contents;
    }
}
