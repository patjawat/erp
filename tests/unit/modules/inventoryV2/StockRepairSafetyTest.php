<?php

namespace tests\unit\modules\inventoryV2;

use Codeception\Test\Unit;

class StockRepairSafetyTest extends Unit
{
    public function testRepairReplansUnderLockAndRejectsStaleDryRun(): void
    {
        $source = $this->source('modules/inventoryV2/components/StockRepairService.php');
        $execute = $this->between($source, 'public static function execute', 'private static function applyFifo');

        $this->assertStringContainsString('beginTransaction()', $execute);
        $this->assertStringContainsString('InventoryService::lockStockPool(', $execute);
        $this->assertStringContainsString('$fresh = self::plan(', $execute);
        $this->assertStringContainsString('hash_equals(', $execute);
        $this->assertStringContainsString('verifyAfter(', $execute);
        $this->assertLessThan(strpos($execute, '$tx->commit()'), strpos($execute, 'verifyAfter('));
    }

    public function testRepairRequiresReasonAndWritesImmutableAuditSnapshot(): void
    {
        $source = $this->source('modules/inventoryV2/components/StockRepairService.php');

        $this->assertStringContainsString('mb_strlen($reason) < 10', $source);
        $this->assertStringContainsString("insert('{{%stock_repair_audit}}'", $source);
        $this->assertStringContainsString("'before_json'", $source);
        $this->assertStringContainsString("'after_json'", $source);
        $this->assertStringContainsString("'created_by'", $source);
    }

    public function testRepairEndpointIsPermissionProtectedAndPostOnly(): void
    {
        $source = $this->source('modules/inventoryV2/controllers/StockHealthController.php');

        $this->assertStringContainsString("PERMISSION_REPAIR = 'inventoryStockRepair'", $source);
        $this->assertStringContainsString("['actions' => ['repair'], 'allow' => true", $source);
        $this->assertStringContainsString("'repair' => ['post']", $source);
        $this->assertStringContainsString('assertWarehouseAccess($warehouseId)', $source);
    }

    public function testUnsafeEvidenceIsExplicitlyBlocked(): void
    {
        $source = $this->source('modules/inventoryV2/components/StockRepairService.php');
        foreach (['duplicate_balance', 'negative_fifo', 'negative_balance', 'fifo_over_received', 'orphan_balance', 'orphan_allocation', 'missing_allocation', 'history_only_edit'] as $issue) {
            $this->assertStringContainsString("'{$issue}'", $source);
        }
        $this->assertStringContainsString('manual_count_required', $source);
    }

    public function testMatchingBalanceAndFifoCanCreateControlledLedgerRepair(): void
    {
        $source = $this->source('modules/inventoryV2/components/StockRepairService.php');
        $this->assertStringContainsString("'mode' => 'sync_ledger'", $source);
        $this->assertStringContainsString("self::operation('ledger'", $source);
        $this->assertStringContainsString("'history_reconcile' => 1", $source);
        $this->assertStringContainsString('โดยไม่แตะ stock/FIFO', $source);
    }

    private function source(string $relative): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/' . $relative);
        $this->assertNotFalse($source);
        return $source;
    }

    private function between(string $source, string $start, string $end): string
    {
        $startAt = strpos($source, $start);
        $endAt = strpos($source, $end, $startAt + strlen($start));
        $this->assertNotFalse($startAt);
        $this->assertNotFalse($endAt);
        return substr($source, $startAt, $endAt - $startAt);
    }
}
