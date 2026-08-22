<?php

namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\components\StockHealthService;
use app\modules\inventoryV2\components\StockRepairService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use Codeception\Test\Unit;
use Yii;
use yii\db\Query;

class StockRepairIntegrationTest extends Unit
{
    public function testRepairableCaseWritesAuditAndVerifiesThenOuterTransactionRollsBack(): void
    {
        $beforeAudit = $this->auditCount();
        $outer = Yii::$app->db->beginTransaction();
        try {
            [$row, $plan] = $this->syntheticRepairablePlan();
            $result = StockRepairService::execute(
                (int) $row['warehouse_id'], $row['item_code'], $row['scope'], $row['lot_number'],
                $plan['plan']['fingerprint'], 'integration test controlled repair', 1
            );
            $this->assertTrue($result['success']);
            $this->assertTrue($result['after']['verified']);
            $this->assertSame($beforeAudit + 1, $this->auditCount());
        } finally {
            if ($outer->isActive) $outer->rollBack();
        }
        $this->assertSame($beforeAudit, $this->auditCount());
    }

    public function testStaleFingerprintRejectsAndLeavesNoAudit(): void
    {
        $beforeAudit = $this->auditCount();
        $outer = Yii::$app->db->beginTransaction();
        try {
            [$row] = $this->syntheticRepairablePlan();
            StockRepairService::execute(
                (int) $row['warehouse_id'], $row['item_code'], $row['scope'], $row['lot_number'],
                str_repeat('0', 64), 'integration test stale fingerprint', 1
            );
            $this->fail('A stale fingerprint must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ข้อมูลเปลี่ยนหลัง Dry-run', $e->getMessage());
        } finally {
            if ($outer->isActive) $outer->rollBack();
        }
        $this->assertSame($beforeAudit, $this->auditCount());
    }

    public function testInsufficientEvidenceIsRefused(): void
    {
        $result = StockRepairService::plan(999999, 'NOT-FOUND', 'lot', '-');
        $this->assertFalse($result['allowed']);
        $this->assertNull($result['plan']);
    }

    public function testLotBalanceCanBeRepairedWhenLedgerAndFifoAgree(): void
    {
        $beforeAudit = $this->auditCount();
        $outer = Yii::$app->db->beginTransaction();
        try {
            [$row, $plan] = $this->syntheticBalanceRepairPlan();
            $this->assertSame('sync_balance', $plan['plan']['mode']);
            $result = StockRepairService::execute(
                (int) $row['warehouse_id'], $row['item_code'], 'lot', $row['lot_number'],
                $plan['plan']['fingerprint'], 'integration test lot balance repair', 1
            );
            $this->assertTrue($result['success']);
            $this->assertTrue($result['after']['verified']);
        } finally {
            if ($outer->isActive) $outer->rollBack();
        }
        $this->assertSame($beforeAudit, $this->auditCount());
    }

    public function testPhysicalCountCanRepairFifoWhileKeepingConfirmedBalance(): void
    {
        $beforeAudit = $this->auditCount();
        $outer = Yii::$app->db->beginTransaction();
        try {
            [$row, $physicalQty, $plan] = $this->syntheticPhysicalCountPlan();
            $this->assertSame('physical_count_to_balance', $plan['plan']['mode']);
            $this->assertSame($physicalQty, (float) $plan['plan']['physical_qty']);
            $result = StockRepairService::execute(
                (int) $row['warehouse_id'], $row['item_code'], 'item', '',
                $plan['plan']['fingerprint'], 'ตรวจนับจริงและยืนยันยอด Balance ในการทดสอบ', 1, $physicalQty
            );
            $this->assertTrue($result['success']);
            $this->assertTrue($result['after']['verified']);
        } finally {
            if ($outer->isActive) $outer->rollBack();
        }
        $this->assertSame($beforeAudit, $this->auditCount());
    }

    public function testPhysicalCountMovesOrphanBalanceIntoARealSourceLot(): void
    {
        $beforeAudit = $this->auditCount();
        $outer = Yii::$app->db->beginTransaction();
        try {
            [$row, $physicalQty, $plan] = $this->syntheticOrphanLotPlan();
            $targets = array_column($plan['plan']['operations'], 'target');
            $this->assertContains('balance', $targets);
            $this->assertContains('fifo', $targets);
            $result = StockRepairService::execute(
                (int) $row['warehouse_id'], $row['item_code'], 'item', '',
                $plan['plan']['fingerprint'], 'ตรวจนับจริงและย้ายยอด Lot ลอยในการทดสอบ', 1, $physicalQty
            );
            $this->assertTrue($result['success']);
            $this->assertTrue($result['after']['verified']);
        } finally {
            if ($outer->isActive) $outer->rollBack();
        }
        $this->assertSame($beforeAudit, $this->auditCount());
    }

    private function syntheticRepairablePlan(): array
    {
        $warehouseIds = array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());
        $scan = StockHealthService::scan($warehouseIds, ['include_healthy' => true]);
        $itemEvidence = [];
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] === 'item') $itemEvidence[$row['warehouse_id'] . '|' . $row['item_code']] = $row;
        }
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] !== 'lot' || $row['status'] !== 'healthy' || $row['fifo_qty'] < 1) continue;
            $item = $itemEvidence[$row['warehouse_id'] . '|' . $row['item_code']] ?? null;
            if (!$item || $item['ledger_qty'] === null || abs($item['ledger_qty'] - $item['balance_qty']) > StockHealthService::EPSILON) continue;
            $source = StockDetail::find()->alias('sd')->joinWith(['stockOrder so'])->where([
                'sd.item_code' => $row['item_code'], 'sd.lot_number' => $row['lot_number'],
                'so.status' => StockOrder::STATUS_CONFIRMED, 'so.main_warehouse_id' => (int) $row['warehouse_id'],
            ])->andWhere(['or', ['so.order_type' => StockOrder::ORDER_TYPE_IN], ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]]])
                ->andWhere(['>=', 'sd.remain_qty', 1])->orderBy(['sd.id' => SORT_ASC])->one();
            if (!$source) continue;
            $source->remain_qty = (float) $source->remain_qty - 1;
            $this->assertTrue($source->save(false, ['remain_qty']));
            $plan = StockRepairService::plan((int) $row['warehouse_id'], $row['item_code'], $row['scope'], $row['lot_number']);
            if ($plan['allowed']) return [$row, $plan];
        }
        $this->fail('Unable to construct a safe repairable fixture from the isolated test snapshot.');
    }

    private function auditCount(): int
    {
        return (int) (new Query())->from('{{%stock_repair_audit}}')->count();
    }

    private function syntheticBalanceRepairPlan(): array
    {
        $warehouseIds = array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());
        $scan = StockHealthService::scan($warehouseIds, ['include_healthy' => true]);
        $itemEvidence = [];
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] === 'item') $itemEvidence[$row['warehouse_id'] . '|' . $row['item_code']] = $row;
        }
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] !== 'lot' || $row['status'] !== 'healthy' || $row['balance_qty'] < 1 || $row['balance_rows'] !== 1) continue;
            $item = $itemEvidence[$row['warehouse_id'] . '|' . $row['item_code']] ?? null;
            if (!$item || $item['ledger_qty'] === null || abs($item['ledger_qty'] - $item['fifo_qty']) > StockHealthService::EPSILON) continue;
            $balance = StockBalance::findOne(['warehouse_id' => $row['warehouse_id'], 'item_code' => $row['item_code'], 'lot_number' => $row['lot_number']]);
            if (!$balance) continue;
            $balance->balance_qty = (float) $balance->balance_qty - 1;
            $this->assertTrue($balance->save(false, ['balance_qty']));
            $plan = StockRepairService::plan((int) $row['warehouse_id'], $row['item_code'], 'lot', $row['lot_number']);
            if ($plan['allowed'] && $plan['plan']['mode'] === 'sync_balance') return [$row, $plan];
        }
        $this->fail('Unable to construct a safe lot balance repair fixture.');
    }

    private function syntheticPhysicalCountPlan(): array
    {
        $warehouseIds = array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());
        $scan = StockHealthService::scan($warehouseIds, ['include_healthy' => true]);
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] !== 'lot' || $row['status'] !== 'healthy' || $row['fifo_qty'] < 1) continue;
            $source = StockDetail::find()->alias('sd')->joinWith(['stockOrder so'])->where([
                'sd.item_code' => $row['item_code'], 'sd.lot_number' => $row['lot_number'],
                'so.status' => StockOrder::STATUS_CONFIRMED, 'so.main_warehouse_id' => (int) $row['warehouse_id'],
            ])->andWhere(['or', ['so.order_type' => StockOrder::ORDER_TYPE_IN], ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]]])
                ->andWhere(['>=', 'sd.remain_qty', 1])->orderBy(['sd.id' => SORT_ASC])->one();
            if (!$source) continue;
            $source->remain_qty = (float) $source->remain_qty - 1;
            $this->assertTrue($source->save(false, ['remain_qty']));
            $itemScan = StockHealthService::scan([(int) $row['warehouse_id']], ['include_healthy' => true]);
            foreach ($itemScan['rows'] as $itemRow) {
                if ($itemRow['scope'] !== 'item' || $itemRow['item_code'] !== $row['item_code']) continue;
                $physicalQty = (float) $itemRow['balance_qty'];
                $plan = StockRepairService::plan((int) $row['warehouse_id'], $row['item_code'], 'item', '', $physicalQty);
                if ($plan['allowed'] && $plan['plan']['mode'] === 'physical_count_to_balance') return [$row, $physicalQty, $plan];
            }
        }
        $this->fail('Unable to construct a physical-count repair fixture.');
    }

    private function syntheticOrphanLotPlan(): array
    {
        $warehouseIds = array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());
        $scan = StockHealthService::scan($warehouseIds, ['include_healthy' => true]);
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] !== 'lot' || $row['status'] !== 'healthy' || $row['balance_qty'] < 1 || $row['fifo_qty'] < 1 || $row['balance_rows'] !== 1) continue;
            $balance = StockBalance::findOne(['warehouse_id' => $row['warehouse_id'], 'item_code' => $row['item_code'], 'lot_number' => $row['lot_number']]);
            $source = StockDetail::find()->alias('sd')->joinWith(['stockOrder so'])->where([
                'sd.item_code' => $row['item_code'], 'sd.lot_number' => $row['lot_number'],
                'so.status' => StockOrder::STATUS_CONFIRMED, 'so.main_warehouse_id' => (int) $row['warehouse_id'],
            ])->andWhere(['or', ['so.order_type' => StockOrder::ORDER_TYPE_IN], ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]]])
                ->andWhere(['>=', 'sd.remain_qty', 1])->orderBy(['sd.id' => SORT_ASC])->one();
            if (!$balance || !$source) continue;
            $balance->balance_qty = (float) $balance->balance_qty - 1;
            $source->remain_qty = (float) $source->remain_qty - 1;
            $this->assertTrue($balance->save(false, ['balance_qty']));
            $this->assertTrue($source->save(false, ['remain_qty']));
            $orphan = new StockBalance(['warehouse_id' => $row['warehouse_id'], 'item_code' => $row['item_code'], 'lot_number' => 'TEST-ORPHAN-' . $row['lot_number'], 'balance_qty' => 1]);
            $this->assertTrue($orphan->save(false));
            $physicalQty = (float) $row['balance_qty'];
            $plan = StockRepairService::plan((int) $row['warehouse_id'], $row['item_code'], 'item', '', $physicalQty);
            if ($plan['allowed'] && $plan['plan']['mode'] === 'physical_count_to_balance') return [$row, $physicalQty, $plan];
        }
        $this->fail('Unable to construct an orphan-lot physical-count repair fixture.');
    }
}
