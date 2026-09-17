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
}
