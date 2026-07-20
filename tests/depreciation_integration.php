<?php
/**
 * Integration test harness for the depreciation posting pipeline (cases 10-12 + end-to-end).
 *
 * Codeception's vendor install is broken in the dev container (missing behat/gherkin i18n.php),
 * so this self-contained, self-cleaning CLI harness runs the DB-backed cases instead.
 * Pure calculation cases (1-9, 13-15) live in tests/unit/services/*Test.php (Codeception).
 *
 * Run:  docker exec dansai php /app/tests/depreciation_integration.php
 * It creates temp data on the ACTIVE tenant, verifies, then restores/cleans everything.
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../config/console.php';
$app = new yii\console\Application($config);
Yii::setAlias('@webroot', dirname(__DIR__) . '/web');
Yii::setAlias('@web', '/');

use app\modules\am\models\Asset;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\models\AssetDepreciation;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\services\AccountingPeriodService;
use app\modules\am\services\DepreciationRunService;
use app\modules\am\services\DepreciationPostingService;
use app\modules\am\services\DepreciationReportService;
use app\modules\am\services\AssetDepreciationChangeService;

$pass = 0;
$fail = 0;
function check(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  PASS  $label\n";
    } else {
        $fail++;
        echo "  FAIL  $label\n";
    }
}

echo "DB = " . Yii::$app->db->dsn . "\n\n";

$assetId = (int) (new yii\db\Query())->select('id')->from('asset')
    ->where(['deleted_at' => null])->andWhere(['>', 'price', 5000])
    ->andWhere(['>', 'useful_life', 0])
    ->andWhere(['between', 'receive_date', '2022-01-01', '2024-06-01'])
    ->limit(1)->scalar();

if (!$assetId) {
    echo "SKIP: ไม่พบทรัพย์สินที่เหมาะกับการทดสอบบน tenant นี้\n";
    exit(0);
}
$asset = Asset::findOne($assetId);
echo "asset $assetId code={$asset->code} price={$asset->price} recv={$asset->receive_date}\n\n";

$origFields = ['depreciation_profile_id', 'useful_life_months', 'depreciation_start_date', 'depreciation_end_date',
    'depreciation_source_type', 'depreciation_source_id', 'depreciation_status', 'residual_value', 'depreciation_rate', 'depreciation_method'];
$orig = [];
foreach ($origFields as $f) {
    $orig[$f] = $asset->{$f};
}

$pA = $pB = null;
try {
    $periodSvc = new AccountingPeriodService();
    $runSvc = new DepreciationRunService();
    $postSvc = new DepreciationPostingService();
    $reportSvc = new DepreciationReportService();

    // ---- setup: periods + 2 profiles ----
    $periodSvc->generateFiscalYear(2568);
    check('generate FY2568 = 17 periods', AccountingPeriod::find()->where(['fiscal_year' => 2568])->count() == 17);
    $m1 = AccountingPeriod::findOne(['fiscal_year' => 2568, 'period_type' => 'month', 'period_no' => 4]);
    $m2 = AccountingPeriod::findOne(['fiscal_year' => 2568, 'period_type' => 'month', 'period_no' => 5]);

    $pA = new DepreciationProfile(['code' => '_IT_A', 'name' => '5y', 'method' => 'straight_line', 'useful_life_months' => 60, 'salvage_value_type' => 'one_baht', 'calculation_basis' => 'monthly', 'start_rule' => 'ready_month', 'status' => 'active']);
    $pA->save();
    $pB = new DepreciationProfile(['code' => '_IT_B', 'name' => '10y', 'method' => 'straight_line', 'useful_life_months' => 120, 'salvage_value_type' => 'one_baht', 'calculation_basis' => 'monthly', 'start_rule' => 'ready_month', 'status' => 'active']);
    $pB->save();

    $asset->depreciation_profile_id = $pA->id;
    $asset->useful_life_months = 60;
    $asset->residual_value = 1;
    $asset->depreciation_method = 'straight_line';
    $asset->depreciation_start_date = date('Y-m-01', strtotime($asset->receive_date));
    $asset->depreciation_source_type = 'asset';
    $asset->save(false);

    // ---- pipeline: run + post month1 ----
    $runSvc->runForPeriod($m1, true, 1);
    $postSvc->postPeriod($m1, 1);
    $m1->refresh();
    check('period posted after post', $m1->status === AccountingPeriod::STATUS_POSTED);
    $row1 = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m1->id, 'transaction_type' => 'normal']);
    $m1dep = (float) $row1->depreciation_amount;

    // ---- case 11: posted period must refuse recalc ----
    check('case11: posted period refuses recalc', !$runSvc->runForPeriod($m1, true, 1)['success']);

    // ---- case 10: change profile mid-year ----
    $chg = (new AssetDepreciationChangeService())->changeProfile($asset, $pB, '2025-02-01', 'unposted_periods', 'test', 'DOC1', null, 1);
    check('case10: change recorded', $chg['success']);
    $asset->refresh();
    check('case10: snapshot -> 120 months', (int) $asset->useful_life_months === 120);
    $runSvc->runForPeriod($m2, true, 1);
    $row2 = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m2->id, 'transaction_type' => 'normal']);
    check('case10: month2 uses new smaller rate', (float) $row2->depreciation_amount < $m1dep);
    $row1->refresh();
    check('case10: closed month1 unchanged', abs((float) $row1->depreciation_amount - $m1dep) < 0.001 && $row1->status === 'posted');

    // ---- case 12: adjustment on closed period ----
    $adj = $postSvc->createAdjustment($assetId, $m1->id, -50.0, 'ปรับย้อนหลัง', 1);
    check('case12: adjustment created', $adj['success']);
    $adjRow = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m1->id, 'transaction_type' => 'adjustment']);
    check('case12: adjustment amount = -50', $adjRow && abs((float) $adjRow->adjustment_amount + 50.0) < 0.001);
    $row1->refresh();
    check('case12: original row untouched', abs((float) $row1->depreciation_amount - $m1dep) < 0.001);

    // ---- report reconcile: quarter == sum of its months (same monthly rows) ----
    // Q2 = ม.ค.-มี.ค.; งวดที่มีข้อมูล = ม.ค.(post: normal+adjustment) + ก.พ.(calculated: normal)
    $janTot = $reportSvc->totals($reportSvc->monthly($m1->id));
    $febTot = $reportSvc->totals($reportSvc->monthly($m2->id));
    $qTot = $reportSvc->totals($reportSvc->quarter(2568, 2));
    check('report: quarter == sum of monthly (Jan+Feb)',
        abs($qTot['depreciation'] - ($janTot['depreciation'] + $febTot['depreciation'])) < 0.02);

} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    $fail++;
} finally {
    if ($asset) {
        foreach ($orig as $f => $v) {
            $asset->{$f} = $v;
        }
        $asset->save(false);
    }
    Yii::$app->db->createCommand()->delete('{{%asset_depreciations}}')->execute();
    Yii::$app->db->createCommand()->delete('{{%asset_depreciation_changes}}')->execute();
    Yii::$app->db->createCommand()->delete('{{%accounting_periods}}')->execute();
    Yii::$app->db->createCommand()->delete('{{%depreciation_profiles}}', ['code' => ['_IT_A', '_IT_B']])->execute();
    echo "\ncleanup: profiles=" . DepreciationProfile::find()->count()
        . " periods=" . AccountingPeriod::find()->count()
        . " deps=" . AssetDepreciation::find()->count() . "\n";
    echo "==== $pass passed, $fail failed ====\n";
    exit($fail === 0 ? 0 : 1);
}
