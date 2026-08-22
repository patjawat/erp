<?php

namespace tests\unit\modules\inventoryV2;

use Codeception\Test\Unit;

class InventoryMutationSafetyTest extends Unit
{
    public function testIssueRecordsExactFifoAllocationsAndLocksTheOrder(): void
    {
        $source = $this->source('controllers/IssueController.php');

        $this->assertStringContainsString('InventoryService::lockOrder($model->id)', $source);
        $this->assertStringContainsString('InventoryService::lockStockPool(', $source);
        $this->assertStringContainsString("'fifo_allocations' => \$group['allocations']", $source);
        $this->assertStringContainsString("'source_detail_id'", $source);
        $this->assertStringContainsString("'automatic_multi_lot_fifo' => 1", $source);
        $this->assertStringContainsString('foreach ($lotGroups as $lot => $group)', $source);
        $this->assertStringNotContainsString("'stock_detail.lot_number' => \$selectedLot", $source);
        $this->assertStringNotContainsString('เกินจำนวนที่อนุมัติ', $source);
    }

    public function testReceiptMutationIsBlockedAfterItsSourceWasConsumed(): void
    {
        $source = $this->source('controllers/ReceiveController.php');

        $this->assertGreaterThanOrEqual(2, substr_count($source, 'InventoryService::lockOrder($model->id)'));
        $this->assertStringContainsString('(float) $oldItem->remain_qty + 0.000001 < abs((float) $oldItem->qty)', $source);
        $this->assertStringContainsString('(float) $detail->remain_qty + 0.000001 < abs((float) $detail->qty)', $source);
    }

    public function testReceiptCancelUsesItsExactLotInsteadOfGenericFifo(): void
    {
        $source = $this->methodSource('controllers/ReceiveController.php', 'public function actionCancel', 'public function actionDelete');

        $this->assertStringContainsString('InventoryService::updateBalance(', $source);
        $this->assertStringNotContainsString('InventoryService::moveStock(', $source);
        $this->assertStringContainsString('$detail->lot_number', $source);
    }

    public function testAllocationReturnFailsClosedWhenItsSourceIsMissing(): void
    {
        $source = $this->methodSource('components/InventoryService.php', 'public static function returnFifoAllocation', 'private static function returnToLotSource');

        $this->assertStringContainsString('findEligibleSource(', $source);
        $this->assertStringContainsString('จึงไม่คืน Balance', $source);
        $this->assertLessThan(
            strpos($source, 'self::updateBalance('),
            strpos($source, 'throw new \\Exception("ไม่พบ Lot ต้นทาง')
        );
    }

    public function testIssueChecksReservationsAndInvariantBeforeCommit(): void
    {
        $source = $this->source('controllers/IssueController.php');
        $this->assertStringContainsString('reservedAheadQty(', $source);
        $this->assertStringContainsString('พร้อมจ่ายสำหรับใบนี้', $source);
        $this->assertStringContainsString('assertBalanceMatchesFifo(', $source);
        $this->assertLessThan(strpos($source, '$transaction->commit();'), strrpos($source, 'assertBalanceMatchesFifo('));
    }

    public function testNormalHistoryOnlyMutationIsDisabled(): void
    {
        $source = $this->source('controllers/StockAdjustController.php');
        $this->assertStringContainsString('ปิดการแก้เฉพาะประวัติแล้ว', $source);
        $this->assertStringContainsString('ปิดการลบประวัติใบเบิกโดยตรงแล้ว', $source);
    }

    public function testInventoryServiceHasFailClosedBalanceFifoGuard(): void
    {
        $source = $this->source('components/InventoryService.php');
        $this->assertStringContainsString('public static function assertBalanceMatchesFifo', $source);
        $this->assertStringContainsString('ยกเลิกรายการเพื่อป้องกันสต๊อกคลาดเคลื่อน', $source);
    }

    private function source(string $relativePath): string
    {
        $path = dirname(__DIR__, 4) . '/modules/inventoryV2/' . $relativePath;
        $source = file_get_contents($path);
        $this->assertNotFalse($source, "Unable to read {$path}");
        return $source;
    }

    private function methodSource(string $relativePath, string $start, string $end): string
    {
        $source = $this->source($relativePath);
        $startAt = strpos($source, $start);
        $this->assertNotFalse($startAt, "Missing method marker: {$start}");
        $endAt = strpos($source, $end, $startAt + strlen($start));
        $this->assertNotFalse($endAt, "Missing method end marker: {$end}");
        return substr($source, $startAt, $endAt - $startAt);
    }
}
