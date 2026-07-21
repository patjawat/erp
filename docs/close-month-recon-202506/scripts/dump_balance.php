<?php
// Balance-page ledger value for a category across MAIN warehouses (mirrors loadBalanceData filter)
// Run: docker exec dansai php /app/docs/close-month-recon-202506/scripts/dump_balance.php M19
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
require '/app/vendor/autoload.php';
require '/app/vendor/yiisoft/yii2/Yii.php';
$config = require '/app/config/console.php';
new yii\console\Application($config);

use app\modules\inventoryV2\controllers\ReportController;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Query;
use yii\db\Expression;

$cat = $argv[1] ?? 'M19';
$whIds = array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());

// ledger value map (same as balance page), no date filter = current cumulative
$ledgerMap = ReportController::loadLedgerValues($whIds);

// same row filter as loadBalanceData: setting + active + name/group + allowedTypes
$raw = (new Query())
    ->select(['warehouse_id' => 's.warehouse_id', 'item_code' => 's.item_code', 'category_id' => new Expression('i.category_id')])
    ->from(['s' => 'stock_item_warehouse_setting'])
    ->innerJoin(['i' => 'categorise'], 'i.code = s.item_code')
    ->where(['s.warehouse_id' => $whIds])
    ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
    ->andWhere(['i.active' => 1])
    ->groupBy(['s.warehouse_id', 's.item_code', 'i.category_id'])
    ->all();

$tot = 0; $rows = [];
foreach ($raw as $r) {
    if (($r['category_id'] ?? null) !== $cat) continue;
    $v = $ledgerMap[(int)$r['warehouse_id'].':'.(string)$r['item_code']] ?? 0.0;
    $tot += $v;
    $rows[$r['item_code']] = ($rows[$r['item_code']] ?? 0) + $v;
}
printf("=== BALANCE page ledger total for %s = %.2f  (rows=%d)\n", $cat, $tot, count($rows));
printf("19-00069 balance value = %.2f\n", $rows['19-00069'] ?? -999999);

// July 2026 transactions for this category that make balance differ from June closing
$catCodes = (new Query())->select('code')->from('categorise')
    ->where(['name' => 'asset_item', 'group_id' => 'MATER', 'category_id' => $cat])->column();
$jul = (new Query())
    ->select(['so.order_type','so.order_no','sd.item_code','sd.qty','sd.unit_price','so.order_date','so.main_warehouse_id','sd.data_json'])
    ->from(['sd' => 'stock_detail'])->innerJoin(['so' => 'stock_order'], 'so.id=sd.stock_order_id')
    ->where(['sd.item_code' => $catCodes])
    ->andWhere(['so.status' => 'CONFIRMED'])
    ->andWhere(['so.main_warehouse_id' => $whIds])
    ->andWhere(['between', 'so.order_date', '2026-07-01', '2026-07-31 23:59:59'])
    ->all();
echo "\n=== July 2026 CONFIRMED $cat transactions on MAIN warehouses (affect balance but not June close) ===\n";
$jsum = 0;
foreach ($jul as $t) {
    $v = (float)$t['qty'] * (float)($t['unit_price'] ?? 0);
    $sign = in_array($t['order_type'], ['OUT','TRANSFER']) ? -1 : 1;
    printf("%-8s %-24s %-12s qty=%8.2f price=%9.2f  date=%s\n",
        $t['order_type'], $t['order_no'], $t['item_code'], $t['qty'], $t['unit_price'], $t['order_date']);
}
