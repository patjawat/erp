<?php

namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\components\StockHealthService as Service;
use Codeception\Test\Unit;

class StockHealthServiceTest extends Unit
{
    public function testHealthyWhenAllThreeViewsMatch(): void
    {
        $row = Service::classify($this->row(45, 45, 45));
        $this->assertSame('healthy', $row['status']);
        $this->assertSame('none', $row['repair_mode']);
        $this->assertSame([], $row['issues']);
    }

    public function testM2246PatternIsCritical(): void
    {
        $row = Service::classify($this->row(45, 45, 0));
        $this->assertSame('critical', $row['status']);
        $this->assertContains('balance_without_fifo', $row['issues']);
        $this->assertSame('dry_run_sync_fifo', $row['repair_mode']);
    }

    public function testConflictingThreeWayTotalsRequirePhysicalCount(): void
    {
        $row = Service::classify($this->row(45, 44, 0));
        $this->assertSame('critical', $row['status']);
        $this->assertSame('manual_count_required', $row['repair_mode']);
    }

    public function testLedgerAndFifoCanRecommendBalanceDryRun(): void
    {
        $row = Service::classify($this->row(45, 44, 45));
        $this->assertSame('mismatch', $row['status']);
        $this->assertSame('dry_run_sync_balance', $row['repair_mode']);
    }

    public function testOrphanBalanceIsAlwaysCritical(): void
    {
        $input = $this->row(10, 10, 0);
        $input['source_rows'] = 0;
        $row = Service::classify($input);
        $this->assertSame('critical', $row['status']);
        $this->assertContains('orphan_balance', $row['issues']);
    }

    public function testDuplicateBalanceRowsAreReported(): void
    {
        $input = $this->row(5, 5, 5);
        $input['scope'] = 'lot';
        $input['ledger_qty'] = null;
        $input['balance_rows'] = 2;
        $row = Service::classify($input);
        $this->assertSame('mismatch', $row['status']);
        $this->assertContains('duplicate_balance', $row['issues']);
    }

    public function testMissingLedgerIsNotTreatedAsZeroMovement(): void
    {
        $input = $this->row(0, 9, 9);
        $input['ledger_qty'] = null;
        $row = Service::classify($input);
        $this->assertSame('review', $row['status']);
        $this->assertContains('ledger_unavailable', $row['issues']);
        $this->assertNotContains('ledger_balance_mismatch', $row['issues']);
        $this->assertNull($row['ledger_qty']);
        $this->assertSame('manual_count_required', $row['repair_mode']);
    }

    private function row(float $ledger, float $balance, float $fifo): array
    {
        return [
            'scope' => 'item',
            'warehouse_id' => 3,
            'item_code' => 'M22-46',
            'lot_number' => 'LOT69-06298',
            'ledger_qty' => $ledger,
            'balance_qty' => $balance,
            'fifo_qty' => $fifo,
            'received_qty' => 50,
            'balance_rows' => 1,
            'source_rows' => 1,
        ];
    }
}
