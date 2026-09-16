<?php
// Isolated regression: no application database or configuration is loaded.
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Connection;

new yii\console\Application([
    'id' => 'fifo-regression', 'basePath' => dirname(__DIR__),
    'components' => ['db' => ['class' => Connection::class, 'dsn' => 'sqlite::memory:']],
]);
$db = Yii::$app->db;
$db->createCommand('CREATE TABLE warehouses (id INTEGER PRIMARY KEY, warehouse_name TEXT, warehouse_type TEXT, "delete" TEXT)')->execute();
$db->createCommand('CREATE TABLE stock_balance (id INTEGER PRIMARY KEY, warehouse_id INTEGER, item_code TEXT, lot_number TEXT, balance_qty REAL)')->execute();
$db->createCommand('CREATE TABLE stock_order (id INTEGER PRIMARY KEY, main_warehouse_id INTEGER, sub_warehouse_id INTEGER, order_type TEXT, status TEXT)')->execute();
$db->createCommand('CREATE TABLE stock_detail (id INTEGER PRIMARY KEY, stock_order_id INTEGER, item_code TEXT, lot_number TEXT, qty REAL, remain_qty REAL)')->execute();
$db->createCommand()->batchInsert('warehouses', ['id', 'warehouse_name', 'warehouse_type'], [[1, 'คลังหลัก', 'MAIN'], [2, 'CAL-งานซ่อมบำรุง', 'SUB']])->execute();
$db->createCommand()->batchInsert('stock_order', ['id', 'main_warehouse_id', 'sub_warehouse_id', 'order_type', 'status'], [[1, 1, null, 'IN', 'CONFIRMED'], [2, 1, 2, 'OUT', 'CONFIRMED']])->execute();
$db->createCommand()->batchInsert('stock_detail', ['stock_order_id', 'item_code', 'lot_number', 'qty', 'remain_qty'], [[1, '02-00109', 'NEW-A', 30, 30], [1, '02-00109', 'NEW-B', 20, 20], [1, '02-00109', 'LOT69-00893', 10, 0], [2, '02-00109', 'LOT69-00893', 10, 7]])->execute();
$db->createCommand()->batchInsert('stock_balance', ['warehouse_id', 'item_code', 'lot_number', 'balance_qty'], [[1, '02-00109', 'NEW-A', 30], [1, '02-00109', 'NEW-B', 20], [1, '02-00109', 'LOT69-00893', 0], [2, '02-00109', 'LOT69-00893', 0]])->execute();
InventoryService::assertBalanceMatchesFifo('02-00109', 1);
try {
    InventoryService::assertBalanceMatchesFifo('02-00109', 2);
    throw new Exception('Expected destination mismatch');
} catch (RuntimeException $e) {
    foreach (['CAL-งานซ่อมบำรุง', 'ID 2', 'LOT69-00893', 'Balance 0, FIFO 7'] as $expected) {
        if (!str_contains($e->getMessage(), $expected)) throw new Exception('Missing diagnostic: ' . $expected);
    }
}
if ((float) $db->createCommand('SELECT SUM(balance_qty) FROM stock_balance WHERE warehouse_id=1')->queryScalar() !== 50.0) throw new Exception('Preflight changed stock');
foreach ([1, 50] as $quantity) {
    $tx = $db->beginTransaction();
    try {
        $remaining = $quantity;
        $moved = [];
        foreach (['NEW-A' => 30, 'NEW-B' => 20] as $lot => $available) {
            if ($remaining <= 0) break;
            $take = min($remaining, $available);
            foreach ([1, 2] as $warehouseId) InventoryService::assertBalanceMatchesFifo('02-00109', $warehouseId, [$lot]);
            $db->createCommand()->update('stock_detail', ['remain_qty' => $available - $take], ['stock_order_id' => 1, 'lot_number' => $lot])->execute();
            $db->createCommand()->update('stock_balance', ['balance_qty' => $available - $take], ['warehouse_id' => 1, 'lot_number' => $lot])->execute();
            $db->createCommand()->insert('stock_balance', ['warehouse_id' => 2, 'item_code' => '02-00109', 'lot_number' => $lot, 'balance_qty' => $take])->execute();
            $db->createCommand()->insert('stock_detail', ['stock_order_id' => 2, 'item_code' => '02-00109', 'lot_number' => $lot, 'qty' => $take, 'remain_qty' => $take])->execute();
            $moved[] = $lot;
            $remaining -= $take;
        }
        foreach ([1, 2] as $warehouseId) InventoryService::assertBalanceMatchesFifo('02-00109', $warehouseId, $moved);
        $old = $db->createCommand("SELECT remain_qty FROM stock_detail WHERE stock_order_id=2 AND lot_number='LOT69-00893'")->queryScalar();
        if ((float) $old !== 7.0) throw new Exception('Legacy FIFO was modified');
        // An error in a moved lot must still abort, even with the legacy lot excluded.
        $db->createCommand()->update('stock_balance', ['balance_qty' => 0], ['warehouse_id' => 2, 'lot_number' => 'NEW-A'])->execute();
        try {
            InventoryService::assertBalanceMatchesFifo('02-00109', 2, $moved);
            throw new Exception('Expected moved-lot mismatch');
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'NEW-A')) throw $e;
        }
    } finally {
        $tx->rollBack();
    }
    if ((float) $db->createCommand('SELECT SUM(balance_qty) FROM stock_balance WHERE warehouse_id=1')->queryScalar() !== 50.0) throw new Exception('Rollback failed');
}
// Admin uses the existing requisition scope, which must include the destination.
Yii::$app->set('user', new class extends yii\base\Component {
    public function getIsGuest() { return false; }
    public function can($permission) { return $permission === 'admin'; }
});
$destinations = Warehouse::findSubWarehousesForUser(true);
if (count($destinations) !== 1 || (int) $destinations[0]->id !== 2) throw new Exception('Destination not accessible');
echo "PASS: issues of 1 and 50; unrelated legacy lot unchanged; moved-lot errors rejected; rollback; destination scope\n";
