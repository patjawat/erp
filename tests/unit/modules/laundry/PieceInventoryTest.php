<?php

namespace tests\unit\modules\laundry;

use app\modules\laundry\services\PieceInventoryService;
use Codeception\Test\Unit;
use yii\db\Connection;

class PieceInventoryTest extends Unit
{
    public function testBalanceUsesConfirmedPieceMovementsNotPendingOrWeight(): void
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $db->open();
        $db->createCommand('CREATE TABLE laundry_piece_event (id INTEGER PRIMARY KEY, item_id INTEGER, qty INTEGER, from_location TEXT, from_department_id INTEGER, to_location TEXT, to_department_id INTEGER, status TEXT)')->execute();
        $db->createCommand()->batchInsert('laundry_piece_event',
            ['item_id', 'qty', 'from_location', 'to_location', 'status'], [
                [1, 100, 'EXTERNAL', 'CLEAN', 'CONFIRMED'],
                [1, 30, 'CLEAN', 'WARD', 'CONFIRMED'],
                [1, 7, 'WARD', 'DIRTY', 'CONFIRMED'],
                [1, 5, 'CLEAN', 'DISPOSED', 'PENDING'],
            ])->execute();
        $inventory = new PieceInventoryService($db);
        $this->assertSame(70, $inventory->balance(1, 'CLEAN'));
        $this->assertSame(7, $inventory->balance(1, 'DIRTY'));
        $db->close();
    }

    public function testProcurementIsIdempotentAndAuditAdjustmentsRequireApproval(): void
    {
        $root = dirname(__DIR__, 4);
        $migration = file_get_contents($root . '/migrations/m260917_120000_create_laundry_piece_inventory.php');
        $inventory = file_get_contents($root . '/modules/laundry/services/PieceInventoryService.php');
        $audit = file_get_contents($root . '/modules/laundry/services/AnnualCountService.php');
        $this->assertStringContainsString("'source_stock_detail_id' => " . '$this->integer()->null()->unique()', $migration);
        $this->assertStringContainsString('receiving_warehouse_id', $migration);
        $this->assertStringContainsString("$" . "detail['sub_warehouse_id'] !== (int) $" . "config['receiving_warehouse_id']", $inventory);
        $this->assertStringNotContainsString("->update('stock_balance'", $inventory);
        $this->assertStringNotContainsString("->delete('stock_balance'", $inventory);
        $this->assertStringContainsString("'PENDING', $" . 'userId, null', $inventory);
        $this->assertStringContainsString('created_by', $audit);
        $this->assertStringContainsString('cutoff_event_id', $audit);
        $this->assertStringContainsString('ยอดบัญชีเปลี่ยนหลังเปิดรอบสอบ', $audit);
        $this->assertStringContainsString("'status' => 'APPROVED'", $audit);
    }

    public function testAnnualCountKeepsVerifiedTransitSeparateAndPreservesCancelledHistory(): void
    {
        $root = dirname(__DIR__, 4);
        $migration = file_get_contents($root . '/migrations/m260917_120000_create_laundry_piece_inventory.php');
        $audit = file_get_contents($root . '/modules/laundry/services/AnnualCountService.php');
        $this->assertStringContainsString('verified_in_transit_qty', $migration);
        $this->assertStringContainsString('transit_evidence', $migration);
        $this->assertStringContainsString('ผ้าระหว่างซักต้องระบุหลักฐาน', $audit);
        $this->assertStringContainsString('cutoff_event_id', $audit);
        $this->assertStringContainsString("'status' => 'CANCELLED'", $audit);
        $this->assertStringContainsString('LIMIT 1 FOR UPDATE', $audit);
    }

    public function testDryCountMustBeApprovedBeforeBatchQcAndCannotBeReused(): void
    {
        $root = dirname(__DIR__, 4);
        $migration = file_get_contents($root . '/migrations/m260917_140000_create_laundry_batch_piece_count.php');
        $inventory = file_get_contents($root . '/modules/laundry/services/PieceInventoryService.php');
        $this->assertStringContainsString("['dry_batch_id', 'item_id'], true", $migration);
        $this->assertStringContainsString("$" . "count['status'] !== 'APPROVED'", $inventory);
        $this->assertStringContainsString("$" . "count['created_by'] === $" . "approverId", $inventory);
        $this->assertStringContainsString("$" . "used + $" . "qty > (int) $" . "count['qty']", $inventory);
        $this->assertStringContainsString("'BATCH_COUNT'", $inventory);
        $this->assertStringContainsString("'QC_HOLD'", $inventory);
    }

    public function testFailedQcBranchesRequireStockAndDisposalApproval(): void
    {
        $inventory = file_get_contents(dirname(__DIR__, 4) . '/modules/laundry/services/PieceInventoryService.php');
        $this->assertStringContainsString("'REWASH_RETURN', $" . 'itemId, $qty, ' . "'REWORK', null, 'DIRTY'", $inventory);
        $this->assertStringContainsString("'REPAIR_COMPLETE', $" . 'itemId, $qty, ' . "'REPAIR', null, 'CLEAN'", $inventory);
        $this->assertStringContainsString("'QC_DISPOSAL'", $inventory);
        $this->assertStringContainsString("$" . "event['created_by'] === $" . "approverId", $inventory);
        $this->assertStringContainsString("$" . "event['status'] !== 'PENDING'", $inventory);
        $this->assertStringContainsString("$" . "reserved < $" . "qty", $inventory);
        $this->assertStringContainsString("'DISPOSAL_PENDING', null, true", $inventory);
    }

    public function testLotBalanceCountsOnlyConfirmedCleanMovements(): void
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $db->open();
        $db->createCommand('CREATE TABLE laundry_piece_event (id INTEGER PRIMARY KEY, clean_lot_id INTEGER, qty INTEGER, from_location TEXT, to_location TEXT, status TEXT)')->execute();
        $db->createCommand()->batchInsert('laundry_piece_event',
            ['clean_lot_id', 'qty', 'from_location', 'to_location', 'status'], [
                [3, 40, 'QC_HOLD', 'CLEAN', 'CONFIRMED'],
                [3, 12, 'CLEAN', 'WARD', 'CONFIRMED'],
                [3, 10, 'CLEAN', 'WARD', 'PENDING'],
                [4, 80, 'QC_HOLD', 'CLEAN', 'CONFIRMED'],
            ])->execute();
        $this->assertSame(28, (new PieceInventoryService($db))->lotBalance(3));
        $db->close();
    }

    public function testIroningAndLotIssueHaveDatabaseGuards(): void
    {
        $root = dirname(__DIR__, 4);
        $migration = file_get_contents($root . '/migrations/m260917_150000_create_laundry_ironing_and_clean_lot.php');
        $inventory = file_get_contents($root . '/modules/laundry/services/PieceInventoryService.php');
        $this->assertStringContainsString('uq_laundry_clean_lot_production', $migration);
        $this->assertStringContainsString("'source_type' => 'LEGACY'", $migration);
        $this->assertStringContainsString('$ironed + $qty > (int) $count', $inventory);
        $this->assertStringContainsString('$used + $qty > $ironed', $inventory);
        $this->assertStringContainsString('$this->lotBalance($lotId) < $qty', $inventory);
        $this->assertStringContainsString('public function issueFromLot(', $inventory);
    }
}
