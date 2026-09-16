<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var int $year */
/** @var string $period */
/** @var float $inToday */
/** @var float $outToday */
/** @var array $donutIn groupName => sum */
/** @var array $donutOut groupName => sum */
/** @var array $trend */
/** @var array $monthly */

$this->registerJsFile('@web/apexcharts/apexcharts.min.js', ['position' => View::POS_HEAD]);

$this->title = 'ภาพรวมรายรับ-รายจ่าย';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = 'ภาพรวม';

$diff = $inToday - $outToday;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>สรุปรับ-จ่ายเงินบำรุง แบบเรียลไทม์<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$donutInJson = Json::htmlEncode(['labels' => array_keys($donutIn), 'values' => array_map(fn ($v) => round($v, 2), array_values($donutIn))]);
$donutOutJson = Json::htmlEncode(['labels' => array_keys($donutOut), 'values' => array_map(fn ($v) => round($v, 2), array_values($donutOut))]);
$trendJson = Json::htmlEncode($trend);
$monthlyJson = Json::htmlEncode($monthly);
?>

<style>
.fc-kpi{border:0;border-radius:1rem;color:#fff;overflow:hidden;position:relative;box-shadow:0 8px 24px rgba(0,0,0,.08)}
.fc-kpi .fc-ic{position:absolute;right:-6px;bottom:-10px;font-size:5.5rem;opacity:.18}
.fc-kpi .fc-lbl{font-size:.9rem;opacity:.92}
.fc-kpi .fc-val{font-size:1.9rem;font-weight:800;line-height:1.1}
.fc-in{background:linear-gradient(135deg,#10b981,#059669)}
.fc-out{background:linear-gradient(135deg,#f59e0b,#d97706)}
.fc-diff-pos{background:linear-gradient(135deg,#0ea5e9,#2563eb)}
.fc-diff-neg{background:linear-gradient(135deg,#ef4444,#b91c1c)}
.fc-card{border:0;border-radius:1rem;box-shadow:0 6px 18px rgba(0,0,0,.05)}
</style>

<?= $this->render('_menu', ['active' => 'overview']) ?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <form method="get" class="d-flex gap-2 align-items-end">
        <input type="hidden" name="period" value="<?= Html::encode($period) ?>">
        <div><label class="form-label mb-0 small text-body-secondary">ปีงบประมาณ</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:110px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
    <div class="btn-group btn-group-sm" role="group">
        <?php foreach (['today' => 'วันนี้', 'month' => 'เดือนนี้', 'year' => 'ทั้งปีงบ'] as $p => $lbl): ?>
            <a href="<?= Url::to(['overview', 'year' => $year, 'period' => $p]) ?>" class="btn <?= $period === $p ? 'btn-dark' : 'btn-outline-dark' ?>"><?= $lbl ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- KPI -->
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="fc-kpi fc-in p-3 h-100">
        <div class="fc-lbl"><i class="bi bi-cash-coin me-1"></i>รายรับ (วันนี้)</div>
        <div class="fc-val"><?= number_format($inToday, 2) ?></div>
        <i class="bi bi-cash-stack fc-ic"></i>
    </div></div>
    <div class="col-md-4"><div class="fc-kpi fc-out p-3 h-100">
        <div class="fc-lbl"><i class="bi bi-receipt me-1"></i>รายจ่าย (วันนี้)</div>
        <div class="fc-val"><?= number_format($outToday, 2) ?></div>
        <i class="bi bi-wallet2 fc-ic"></i>
    </div></div>
    <div class="col-md-4"><div class="fc-kpi <?= $diff < 0 ? 'fc-diff-neg' : 'fc-diff-pos' ?> p-3 h-100">
        <div class="fc-lbl"><i class="bi bi-<?= $diff < 0 ? 'emoji-frown' : 'emoji-smile' ?> me-1"></i>ผลต่าง (วันนี้)</div>
        <div class="fc-val"><?= number_format($diff, 2) ?></div>
        <i class="bi bi-graph-up-arrow fc-ic"></i>
    </div></div>
</div>

<!-- Donuts -->
<div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="card fc-card h-100"><div class="card-body">
        <h6 class="text-success mb-1"><i class="bi bi-pie-chart-fill me-1"></i>สรุปตามหมวดรายรับ</h6>
        <div class="text-body-secondary small mb-2"><?= $period === 'today' ? 'วันนี้' : ($period === 'month' ? 'เดือนนี้' : 'ปีงบ ' . $year) ?></div>
        <div id="fc-donut-in" style="min-height:300px"></div>
    </div></div></div>
    <div class="col-lg-6"><div class="card fc-card h-100"><div class="card-body">
        <h6 class="text-warning mb-1"><i class="bi bi-pie-chart-fill me-1"></i>สรุปตามหมวดรายจ่าย</h6>
        <div class="text-body-secondary small mb-2"><?= $period === 'today' ? 'วันนี้' : ($period === 'month' ? 'เดือนนี้' : 'ปีงบ ' . $year) ?></div>
        <div id="fc-donut-out" style="min-height:300px"></div>
    </div></div></div>
</div>

<!-- Trend 10 วัน -->
<div class="card fc-card mb-3"><div class="card-body">
    <h6 class="mb-3"><i class="bi bi-activity me-1"></i>รายรับ-รายจ่าย ย้อนหลัง 10 วัน</h6>
    <div id="fc-trend" style="min-height:320px"></div>
</div></div>

<!-- รายเดือนตลอดปีงบ -->
<div class="card fc-card"><div class="card-body">
    <h6 class="mb-3"><i class="bi bi-bar-chart-fill me-1"></i>รายรับ-รายจ่าย รายเดือน ปีงบ <?= $year ?></h6>
    <div id="fc-monthly" style="min-height:340px"></div>
</div></div>

<?php
$this->registerJs(<<<JS
(function () {
    if (typeof ApexCharts === 'undefined') { return; }
    const IN = '#10b981', OUT = '#f59e0b';
    const inPalette = ['#10b981', '#34d399', '#6ee7b7', '#059669'];
    const outPalette = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#14b8a6'];
    const baht = v => Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const dIn = {$donutInJson}, dOut = {$donutOutJson}, tr = {$trendJson}, mo = {$monthlyJson};

    function donut(el, data, palette) {
        const total = data.values.reduce((a, b) => a + b, 0);
        if (!total) { document.querySelector(el).innerHTML = '<div class="text-center text-body-secondary py-5">ไม่มียอดในช่วงนี้</div>'; return; }
        new ApexCharts(document.querySelector(el), {
            chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
            series: data.values, labels: data.labels, colors: palette,
            legend: { position: 'bottom' },
            dataLabels: { formatter: (v) => v.toFixed(1) + '%' },
            plotOptions: { pie: { donut: { size: '66%', labels: { show: true, total: { show: true, label: 'รวม', formatter: () => baht(total) } } } } },
            tooltip: { y: { formatter: (v) => baht(v) + ' บาท' } },
            stroke: { width: 2 }
        }).render();
    }
    donut('#fc-donut-in', dIn, inPalette);
    donut('#fc-donut-out', dOut, outPalette);

    new ApexCharts(document.querySelector('#fc-trend'), {
        chart: { type: 'area', height: 320, fontFamily: 'inherit', toolbar: { show: true, tools: { download: true, pan: false, reset: true, zoom: false, zoomin: false, zoomout: false, selection: false } } },
        series: [{ name: 'รายรับ', data: tr.in }, { name: 'รายจ่าย', data: tr.out }],
        xaxis: { categories: tr.labels },
        colors: [IN, OUT], stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .4, opacityTo: .05 } },
        dataLabels: { enabled: false }, legend: { position: 'top' },
        yaxis: { labels: { formatter: (v) => Number(v).toLocaleString() } },
        tooltip: { y: { formatter: (v) => baht(v) + ' บาท' } }
    }).render();

    new ApexCharts(document.querySelector('#fc-monthly'), {
        chart: { type: 'bar', height: 340, fontFamily: 'inherit', toolbar: { show: true, tools: { download: true, pan: false, zoom: false, zoomin: false, zoomout: false, selection: false, reset: false } } },
        series: [{ name: 'รายรับ', data: mo.in }, { name: 'รายจ่าย', data: mo.out }],
        xaxis: { categories: mo.labels },
        colors: [IN, OUT], plotOptions: { bar: { borderRadius: 4, columnWidth: '62%' } },
        dataLabels: { enabled: false }, legend: { position: 'top' },
        yaxis: { labels: { formatter: (v) => Number(v).toLocaleString() } },
        tooltip: { y: { formatter: (v) => baht(v) + ' บาท' } }
    }).render();
})();
JS, View::POS_END);
?>
