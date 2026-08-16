<?php
/**
 * Integration harness ของสายงานค่าเสื่อม (run → post → reverse → adjustment + กฎลำดับงวด)
 *
 * Codeception ในคอนเทนเนอร์ dev ต่อ DB ทดสอบไม่ได้ (config/test_db.php ชี้ไป yii2basic_test
 * ที่ localhost ซึ่งไม่มีในสภาพแวดล้อมนี้) จึงใช้ CLI harness ตัวนี้รันเคสที่ต้องใช้ DB จริง
 * ส่วนเคสคำนวณล้วนอยู่ใน tests/unit/services/*Test.php (Codeception)
 *
 * ความปลอดภัย: ทุกอย่างทำในทรานแซกชันเดียวแล้ว ROLLBACK เสมอ — ไม่แตะข้อมูลจริงแม้แต่แถวเดียว
 * (เวอร์ชันก่อนหน้าใช้ DELETE ทั้งตาราง accounting_periods / asset_depreciations ตอน cleanup
 *  ซึ่งจะล้างงวดบัญชีจริงทิ้งทั้งหมดเมื่อระบบเริ่มใช้งานจริง)
 *
 * Run:  docker exec dansai php /app/tests/depreciation_integration.php
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

echo "DB = " . Yii::$app->db->dsn . "\n";
echo "(ทุกอย่างรันในทรานแซกชันแล้ว rollback — ข้อมูลจริงไม่ถูกแตะ)\n\n";

$assetId = (int) (new yii\db\Query())->select('id')->from('asset')
    ->where(['deleted_at' => null])->andWhere(['>', 'price', 5000])
    ->andWhere(['between', 'receive_date', '2022-01-01', '2024-06-01'])
    ->limit(1)->scalar();

if (!$assetId) {
    echo "SKIP: ไม่พบทรัพย์สินที่เหมาะกับการทดสอบบน tenant นี้\n";
    exit(0);
}
$asset = Asset::findOne($assetId);
echo "asset $assetId code={$asset->code} price={$asset->price} recv={$asset->receive_date}\n\n";

$tx = Yii::$app->db->beginTransaction();
try {
    $periodSvc = new AccountingPeriodService();
    $runSvc = new DepreciationRunService();
    $postSvc = new DepreciationPostingService();
    $reportSvc = new DepreciationReportService();

    // ---------- setup: งวดปีงบ 2568 + เกณฑ์ 2 ตัว ----------
    $periodSvc->generateFiscalYear(2568);
    check('generate FY2568 = 17 งวด', AccountingPeriod::find()->where(['fiscal_year' => 2568])->count() == 17);

    $months = [];
    for ($n = 1; $n <= 5; $n++) {
        $months[$n] = AccountingPeriod::findOne(['fiscal_year' => 2568, 'period_type' => 'month', 'period_no' => $n]);
    }

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

    // ---------- กฎลำดับงวด: ห้ามข้ามงวด ----------
    $skip = $runSvc->runForPeriod($months[4], true, 1);
    check('guard: คำนวณข้ามงวดถูกปฏิเสธ', !$skip['success'] && strpos($skip['message'], 'งวดก่อนหน้า') !== false);
    check('guard: ทดลองคำนวณ (ไม่บันทึก) ยังทำได้', $runSvc->runForPeriod($months[4], false, 1)['success']);

    // ---------- run ตามลำดับ ----------
    for ($n = 1; $n <= 4; $n++) {
        $runSvc->runForPeriod($months[$n], true, 1);
    }
    $m1 = $months[4];
    $row1 = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m1->id, 'transaction_type' => 'normal']);
    check('run: สร้างรายการค่าเสื่อมของงวดแล้ว', $row1 !== null);
    $m1dep = $row1 ? (float) $row1->depreciation_amount : 0.0;

    // ---------- post ----------
    $badPost = $postSvc->postPeriod($m1, 1);
    check('guard: บันทึกบัญชีข้ามงวดถูกปฏิเสธ', !$badPost['success'] && strpos($badPost['message'], 'งวดก่อนหน้า') !== false);
    for ($n = 1; $n <= 3; $n++) {
        $postSvc->postPeriod($months[$n], 1);
    }
    $okPost = $postSvc->postPeriod($m1, 1);
    check('post: บันทึกบัญชีสำเร็จเมื่องวดก่อนหน้าปิดครบ', $okPost['success']);
    $m1->refresh();
    check('post: สถานะงวด = posted', $m1->status === AccountingPeriod::STATUS_POSTED);
    check('post: งวดที่ปิดแล้วคำนวณทับไม่ได้', !$runSvc->runForPeriod($m1, true, 1)['success']);

    // ---------- reverse ----------
    $row1->refresh();
    $rev = $postSvc->reverse($row1, 1, 'ทดสอบกลับรายการ');
    check('reverse: กลับรายการสำเร็จ', $rev['success']);
    $row1->refresh();
    check('reverse: รายการเดิม = reversed', $row1->status === AssetDepreciation::STATUS_REVERSED);
    $revRow = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m1->id, 'transaction_type' => 'reversal']);
    check('reverse: ยอดกลับรายการเป็นลบเท่ายอดเดิม',
        $revRow && abs((float) $revRow->depreciation_amount + $m1dep) < 0.001);
    check('reverse: ยอดสุทธิของงวดเป็นศูนย์',
        $revRow && abs($m1dep + (float) $revRow->depreciation_amount) < 0.001);
    check('reverse: กลับรายการซ้ำไม่ได้', !$postSvc->reverse($row1, 1)['success']);

    // ---------- เปลี่ยนเกณฑ์กลางปี ----------
    $chg = (new AssetDepreciationChangeService())->changeProfile($asset, $pB, '2025-02-01', 'unposted_periods', 'test', 'DOC1', null, 1);
    check('change: บันทึกการเปลี่ยนเกณฑ์', $chg['success']);
    $asset->refresh();
    check('change: snapshot เปลี่ยนเป็น 120 เดือน', (int) $asset->useful_life_months === 120);
    $runSvc->runForPeriod($months[5], true, 1);
    $row2 = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $months[5]->id, 'transaction_type' => 'normal']);
    check('change: งวดถัดไปใช้อัตราใหม่ที่ต่ำลง', $row2 && (float) $row2->depreciation_amount < $m1dep);
    $row1->refresh();
    check('change: งวดที่ปิดแล้วไม่ถูกคำนวณใหม่', abs((float) $row1->depreciation_amount - $m1dep) < 0.001);

    // ---------- adjustment บนงวดที่ปิดแล้ว ----------
    $adj = $postSvc->createAdjustment($assetId, $m1->id, -50.0, 'ปรับย้อนหลัง', 1);
    check('adjustment: สร้างรายการปรับปรุงได้', $adj['success']);
    $adjRow = AssetDepreciation::findOne(['asset_id' => $assetId, 'accounting_period_id' => $m1->id, 'transaction_type' => 'adjustment']);
    check('adjustment: ยอด = -50', $adjRow && abs((float) $adjRow->adjustment_amount + 50.0) < 0.001);
    check('adjustment: สร้างซ้ำไม่ได้', !$postSvc->createAdjustment($assetId, $m1->id, -10.0, 'ซ้ำ', 1)['success']);
    $row1->refresh();
    check('adjustment: รายการเดิมไม่ถูกแตะ', abs((float) $row1->depreciation_amount - $m1dep) < 0.001);

    // ---------- lock ----------
    $lock = $postSvc->lockPeriod($m1, 1);
    check('lock: ล็อกงวดที่ post แล้วได้', $lock['success']);
    $m1->refresh();
    check('lock: สถานะงวด = locked', $m1->status === AccountingPeriod::STATUS_LOCKED);

    // ---------- รายงานต้องกระทบยอดกับรายเดือน ----------
    $sumMonths = 0.0;
    foreach ([1, 2, 3, 4, 5] as $n) {
        $sumMonths += $reportSvc->totals($reportSvc->monthly($months[$n]->id))['depreciation'];
    }
    $q1 = $reportSvc->totals($reportSvc->quarter(2568, 1))['depreciation'];
    $q2 = $reportSvc->totals($reportSvc->quarter(2568, 2))['depreciation'];
    check('report: ไตรมาส 1+2 = ผลรวมรายเดือน 1-5', abs(($q1 + $q2) - $sumMonths) < 0.02);
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $fail++;
} finally {
    $tx->rollBack();
    echo "\n[ROLLBACK] เกณฑ์=" . DepreciationProfile::find()->count()
        . " งวด=" . AccountingPeriod::find()->count()
        . " รายการค่าเสื่อม=" . AssetDepreciation::find()->count() . "\n";
    echo "==== $pass passed, $fail failed ====\n";
    exit($fail === 0 ? 0 : 1);
}
