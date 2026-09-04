<?php

use app\components\SiteHelper;
use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanItemKind;
use app\modules\finance\models\FinanceLoanSearch;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var FinanceLoan[] $loans */
/** @var int $fiscalYear */
/** @var string|null $month  รูปแบบ Y-m */
/** @var string[] $months */

$this->title = 'ทะเบียนคุมลูกหนี้เงินยืม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-table fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รูปแบบเดียวกับทะเบียนที่ส่งจังหวัด สั่งพิมพ์ได้จากปุ่มพิมพ์ของเบราว์เซอร์<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();

$site = SiteHelper::getInfo();
$thaiMonths = ['01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'];
$monthLabel = static function (string $ym) use ($thaiMonths): string {
    [$year, $mm] = explode('-', $ym);
    return ($thaiMonths[$mm] ?? $mm) . ' ' . ((int) $year + 543);
};
$date = static fn($value) => $value ? Yii::$app->formatter->asDate($value, 'php:d/m/y') : '';
$columns = FinanceLoanItemKind::registerColumnOptions();

$totals = ['approved' => 0.0, 'voucher' => 0.0, 'cash' => 0.0, 'outstanding' => 0.0];
foreach ($columns as $key => $label) {
    $totals[$key] = 0.0;
}
$rows = [];
foreach ($loans as $loan) {
    $registerTotals = $loan->registerTotals();
    $rows[] = ['loan' => $loan, 'totals' => $registerTotals];
    foreach ($columns as $key => $label) {
        $totals[$key] += $registerTotals[$key] ?? 0;
    }
    $totals['approved'] += (float) $loan->approved_amount;
    $totals['voucher'] += (float) $loan->voucher_amount;
    $totals['cash'] += (float) $loan->cash_return_amount;
    $totals['outstanding'] += (float) $loan->outstanding_amount;
}
?>

<section class="card border mb-3 d-print-none">
    <div class="card-body">
        <form method="get" action="<?= \yii\helpers\Url::to(['register']) ?>" class="row g-2 align-items-end">
            <div class="col-6 col-lg-3">
                <label class="form-label" for="register-year">ปีงบประมาณ</label>
                <?= Html::dropDownList('fiscal_year', $fiscalYear, FinanceLoanSearch::fiscalYearOptions(), ['class' => 'form-select', 'id' => 'register-year']) ?>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label" for="register-month">ประจำเดือน</label>
                <?= Html::dropDownList('month', $month, array_combine($months, array_map($monthLabel, $months)), [
                    'class' => 'form-select', 'id' => 'register-month', 'prompt' => 'ทั้งปีงบประมาณ',
                ]) ?>
            </div>
            <div class="col-6 col-lg-2 d-grid"><?= Html::submitButton('<i class="bi bi-search me-1"></i> แสดง', ['class' => 'btn btn-outline-primary']) ?></div>
            <div class="col-6 col-lg-2 d-grid">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1" aria-hidden="true"></i> พิมพ์
                </button>
            </div>
        </form>
    </div>
</section>

<div class="text-center mb-3">
    <div class="fw-semibold">ส่วนราชการ <?= Html::encode($site['company_name'] ?? '') ?></div>
    <div class="fs-5 fw-semibold">ทะเบียนคุมลูกหนี้เงินยืม</div>
    <div>ประจำ<?= $month ? Html::encode($monthLabel($month)) : 'ปีงบประมาณ ' . $fiscalYear ?></div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle small mb-0">
        <thead class="table-light">
            <tr class="text-center">
                <th rowspan="2">ลำดับ<br>(1)</th>
                <th rowspan="2">สถานะ</th>
                <th rowspan="2">เลขที่สัญญา<br>(2)</th>
                <th rowspan="2">วดป.ที่ยืม<br>(3)</th>
                <th rowspan="2">ผู้ยืม<br>(4)</th>
                <th rowspan="2">รายการ<br>(5)</th>
                <th colspan="<?= count($columns) ?>">รายละเอียด</th>
                <th rowspan="2">จำนวนเงิน<br>(6)</th>
                <th rowspan="2">วันครบกำหนด<br>(7)</th>
                <th rowspan="2">วดป.ที่ส่งใช้<br>(8)</th>
                <th rowspan="2">ใบสำคัญ<br>(10)</th>
                <th rowspan="2">เงินสด<br>(11)</th>
                <th rowspan="2">คงเหลือ<br>(12)</th>
                <th rowspan="2">เลขที่ บร./บค.<br>(9)</th>
            </tr>
            <tr class="text-center">
                <?php foreach ($columns as $label): ?>
                    <th><?= Html::encode($label) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="<?= 12 + count($columns) ?>" class="text-center text-body-secondary py-4">ไม่มีข้อมูลในช่วงที่เลือก</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $index => $entry): ?>
            <?php $loan = $entry['loan']; ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td class="text-nowrap"><?= Html::encode($loan->statusLabel()) ?></td>
                <td class="text-nowrap"><?= Html::a(Html::encode($loan->contract_no), ['view', 'id' => $loan->id]) ?></td>
                <td class="text-center text-nowrap"><?= $date($loan->borrowed_at) ?></td>
                <td class="text-nowrap"><?= Html::encode($loan->borrower_name) ?></td>
                <td><?= Html::encode($loan->purpose) ?></td>
                <?php foreach ($columns as $key => $label): ?>
                    <td class="text-end font-monospace text-nowrap"><?= ($entry['totals'][$key] ?? 0) > 0 ? number_format($entry['totals'][$key], 2) : '' ?></td>
                <?php endforeach; ?>
                <td class="text-end font-monospace text-nowrap"><?= number_format($loan->approved_amount, 2) ?></td>
                <td class="text-center text-nowrap"><?= $date($loan->due_at) ?></td>
                <td class="text-center text-nowrap"><?= $date($loan->last_settled_at) ?></td>
                <td class="text-end font-monospace text-nowrap"><?= (float) $loan->voucher_amount > 0 ? number_format($loan->voucher_amount, 2) : '' ?></td>
                <td class="text-end font-monospace text-nowrap"><?= (float) $loan->cash_return_amount > 0 ? number_format($loan->cash_return_amount, 2) : '' ?></td>
                <td class="text-end font-monospace text-nowrap fw-semibold"><?= number_format($loan->outstanding_amount, 2) ?></td>
                <td class="text-nowrap"><?= Html::encode($loan->disbursement_document_no ?: '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if ($rows): ?>
        <tfoot class="table-light fw-semibold">
            <tr>
                <td colspan="6" class="text-end">รวม <?= number_format(count($rows)) ?> ใบ</td>
                <?php foreach ($columns as $key => $label): ?>
                    <td class="text-end font-monospace"><?= number_format($totals[$key], 2) ?></td>
                <?php endforeach; ?>
                <td class="text-end font-monospace"><?= number_format($totals['approved'], 2) ?></td>
                <td colspan="2"></td>
                <td class="text-end font-monospace"><?= number_format($totals['voucher'], 2) ?></td>
                <td class="text-end font-monospace"><?= number_format($totals['cash'], 2) ?></td>
                <td class="text-end font-monospace"><?= number_format($totals['outstanding'], 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php
// พิมพ์แนวนอนและซ่อนส่วนที่ไม่ใช่ตาราง เพื่อให้กระดาษออกมาเหมือนทะเบียนที่ส่งจังหวัด
$this->registerCss(<<<'CSS'
@media print {
    @page { size: A4 landscape; margin: 10mm; }
    .d-print-none, .page-title-wrap, nav, .breadcrumb, footer { display: none !important; }
    .table { font-size: 9pt; }
    a { text-decoration: none; color: #000; }
}
CSS);
