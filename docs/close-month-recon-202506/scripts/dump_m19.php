<?php
// Standalone recon dump: M19 per-item June 2026 across all MAIN warehouses
// Run: docker exec dansai php /app/docs/close-month-recon-202506/scripts/dump_m19.php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
require '/app/vendor/autoload.php';
require '/app/vendor/yiisoft/yii2/Yii.php';
$config = require '/app/config/console.php';
new yii\console\Application($config);

use app\modules\inventoryV2\controllers\ReportController;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockItem;

$year = 2026; $month = (int)($argv[2] ?? 6);
$catFilter = $argv[1] ?? 'M19';

// MAIN warehouse ids
$whIds = Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column();
$whIds = array_map('intval', $whIds);
fwrite(STDERR, "MAIN warehouses: " . implode(',', $whIds) . "\n");

// item_codes in the target category (categorise name=asset_item group_id=MATER category_id=?)
$catCodes = StockItem::find()->select('code')
    ->where(['name' => 'asset_item', 'group_id' => 'MATER', 'category_id' => $catFilter])
    ->column();
$catSet = array_flip($catCodes);
fwrite(STDERR, "items in $catFilter: " . count($catCodes) . "\n");

// aggregate computeMonthlyRows per item across warehouses
$agg = [];
foreach ($whIds as $wid) {
    $opening = ReportController::buildOpeningForMonth($wid, $year, $month);
    foreach (ReportController::computeMonthlyRows($wid, $year, $month, $opening) as $r) {
        $code = $r['item_code'];
        if (!isset($catSet[$code])) continue;
        if (!isset($agg[$code])) {
            $agg[$code] = ['opening_value'=>0,'in_value'=>0,'adjust_in_value'=>0,'adjust_out_value'=>0,'total_out_value'=>0,'closing_value'=>0,
                          'opening_qty'=>0,'in_qty'=>0,'total_out_qty'=>0,'closing_qty'=>0];
        }
        foreach (['opening_value','in_value','adjust_in_value','adjust_out_value','total_out_value','closing_value','opening_qty','in_qty','total_out_qty','closing_qty'] as $k) {
            $agg[$code][$k] += (float)$r[$k];
        }
    }
}

$out = fopen('/app/docs/close-month-recon-202506/scripts/sys_'.$catFilter.'.csv', 'w');
fputcsv($out, ['code','open_val','in_val','adj_in_val','adj_out_val','out_val','close_val','close_val_no_adj']);
$tOpen=$tIn=$tAdjIn=$tAdjOut=$tOut=$tClose=$tCloseNoAdj=0;
ksort($agg);
foreach ($agg as $code => $a) {
    $closeNoAdj = $a['closing_value'] - $a['adjust_in_value'] + $a['adjust_out_value'];
    fputcsv($out, [$code, round($a['opening_value'],2), round($a['in_value'],2), round($a['adjust_in_value'],2),
        round($a['adjust_out_value'],2), round($a['total_out_value'],2), round($a['closing_value'],2), round($closeNoAdj,2)]);
    $tOpen+=$a['opening_value']; $tIn+=$a['in_value']; $tAdjIn+=$a['adjust_in_value']; $tAdjOut+=$a['adjust_out_value'];
    $tOut+=$a['total_out_value']; $tClose+=$a['closing_value']; $tCloseNoAdj+=$closeNoAdj;
}
fclose($out);
printf("=== %s TOTALS (system) ===\n", $catFilter);
printf("opening      = %.2f\n", $tOpen);
printf("in           = %.2f\n", $tIn);
printf("adjust_in    = %.2f\n", $tAdjIn);
printf("adjust_out   = %.2f\n", $tAdjOut);
printf("out          = %.2f\n", $tOut);
printf("closing (with adj)   = %.2f\n", $tClose);
printf("closing (no adj)     = %.2f\n", $tCloseNoAdj);
