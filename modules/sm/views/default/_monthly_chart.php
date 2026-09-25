<?php

/**
 * กราฟแท่งรายเดือน — สลับ "ขอซื้อ" / "ตรวจรับ" และเลือกหมวด
 *  - ทุกหมวด: แยก 4 หมวดหลัก (วัสดุ/ครุภัณฑ์/งานจ้าง/ยา-เวชภัณฑ์)
 *  - เลือกหมวด: แตกเป็นประเภทพัสดุย่อยในหมวดนั้น (เช่น วัสดุวิทยาศาสตร์ฯ, วัสดุสำนักงาน)
 * สไตล์ Dashboard V2 · สีกราฟ resolve จาก Bootstrap CSS var (theme-aware)
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use app\modules\sm\services\PurchaseDashboardService;
use yii\helpers\Html;
use yii\helpers\Json;

// ข้อมูลทุกมุมมองส่งมาพร้อมหน้าเดียว (4 หมวด × 2 มุมมอง ขนาดเล็ก) สลับบนเครื่องได้ทันทีไม่ต้องยิง AJAX
$views = [
    'all' => ['pr' => $dashboard->chartSeries('pr'), 'gr' => $dashboard->chartSeries('gr')],
];
$subPr = $dashboard->monthlyBySubType('pr');
$subGr = $dashboard->monthlyBySubType('gr');
foreach (array_keys(PurchaseDashboardService::CATEGORIES) as $k) {
    $views[$k] = ['pr' => $subPr[$k], 'gr' => $subGr[$k]];
}

// เส้นแผนรายเดือน: ทุกหมวด = ผลรวม 4 หมวด
$planByCat = $dashboard->planMonthlyByCategory();
$plan = ['all' => array_fill(0, 12, 0.0)];
foreach ($planByCat as $k => $data) {
    $plan[$k] = $data;
    foreach ($data as $i => $v) {
        $plan['all'][$i] += $v;
    }
}

$total = fn($series) => array_sum(array_map(fn($s) => array_sum($s['data']), $series));
$prTotal = $total($views['all']['pr']);
$grTotal = $total($views['all']['gr']);

$viewsJson = Json::encode($views);
$planJson = Json::encode($plan);
$labelsJson = Json::encode(PurchaseDashboardService::MONTH_LABELS);
$cssVarsJson = Json::encode(PurchaseDashboardService::categoryCssVars());
// สีประเภทย่อย: วนตาม Bootstrap var, "อื่นๆ" ใช้สีเทาเสมอ
$subCssVarsJson = Json::encode(['--bs-indigo', '--bs-teal', '--bs-orange', '--bs-pink', '--bs-cyan', '--bs-purple', '--bs-green', '--bs-yellow', '--bs-red']);
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10">
                <i class="bi bi-bar-chart-line text-primary"></i>
            </div>
            <h6 class="text-body-secondary m-0">มูลค่ารายเดือน แยกประเภทพัสดุ</h6>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="สลับมุมมอง">
            <button type="button" class="btn btn-primary" id="smStagePr">
                <i class="bi bi-cart-plus me-1"></i>ขอซื้อ
                <span class="badge rounded-pill bg-white text-primary ms-1" id="smStagePrTotal"><?= number_format($prTotal / 1e6, 2) ?> ล.</span>
            </button>
            <button type="button" class="btn btn-outline-primary" id="smStageGr">
                <i class="bi bi-bag-check me-1"></i>ตรวจรับ
                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis ms-1" id="smStageGrTotal"><?= number_format($grTotal / 1e6, 2) ?> ล.</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-wrap gap-1" role="group" aria-label="เลือกหมวด" id="smCatPicker">
                <button type="button" class="btn btn-sm rounded-pill btn-primary" data-cat="all">ทุกหมวด</button>
                <?php foreach (PurchaseDashboardService::CATEGORIES as $k => $meta): ?>
                    <button type="button" class="btn btn-sm rounded-pill btn-outline-secondary" data-cat="<?= $k ?>">
                        <i class="bi bi-circle-fill me-1" style="color: var(<?= $meta['cssvar'] ?>); font-size: .6rem;"></i><?= Html::encode($meta['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="รายเดือนหรือสะสม">
                <button type="button" class="btn btn-outline-secondary active" data-mode="month">รายเดือน</button>
                <button type="button" class="btn btn-outline-secondary" data-mode="cum">สะสม</button>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 small mb-1" id="smPlanSummary"></div>
        <div class="small text-muted mb-1 d-none" id="smCatHint">แยกตามประเภทพัสดุย่อย — ประเภทที่มูลค่าน้อยรวมไว้ใน "อื่นๆ"</div>
        <div id="smMonthlyChart" style="min-height:340px;"></div>
    </div>
</div>
<?php
// เดือนล่าสุดที่นับ "ถึงวันนี้" (ตำแหน่งในปีงบ 0-11): ปีงบปัจจุบัน = เดือนนี้, ปีที่ผ่านมา = ทั้งปี, ปีล่วงหน้า = ยังไม่เริ่ม
$currentYear = (int) \app\components\AppHelper::YearBudget();
if ($dashboard->year < $currentYear) {
    $upto = 11;
} elseif ($dashboard->year > $currentYear) {
    $upto = -1;
} else {
    $upto = array_search((int) date('n'), PurchaseDashboardService::FISCAL_MONTHS, true);
}

$js = <<< JS
(function () {
    var css = function (v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); };
    var catColors = $cssVarsJson.map(css);
    var subColors = $subCssVarsJson.map(css);
    var otherColor = css('--bs-gray-500');
    var planColor = css('--bs-danger');
    var views = $viewsJson;
    var plan = $planJson;
    var upto = $upto;
    var state = { stage: 'pr', cat: 'all', mode: 'month' };

    function add(a, b) { return a + b; }
    function cumulate(arr) { var t = 0; return arr.map(function (v) { t += v; return Math.round(t * 100) / 100; }); }
    function mb(v) { return (v / 1e6).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ล.'; }
    function monthTotals(series) {
        var out = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        series.forEach(function (s) { s.data.forEach(function (v, i) { out[i] += v; }); });
        return out;
    }

    function build() {
        var bars = views[state.cat][state.stage];
        var planData = plan[state.cat];
        var hasPlan = planData.reduce(add, 0) > 0;
        var series = bars.map(function (s) {
            return { name: s.name, type: 'column', data: state.mode === 'cum' ? cumulate(s.data) : s.data };
        });
        var colors = state.cat === 'all'
            ? catColors.slice(0, series.length)
            : bars.map(function (s, i) { return s.name === 'อื่นๆ' ? otherColor : subColors[i % subColors.length]; });
        if (hasPlan) {
            series.push({ name: 'แผน', type: 'line', data: state.mode === 'cum' ? cumulate(planData) : planData });
            colors.push(planColor);
        }
        var widths = series.map(function (s) { return s.type === 'line' ? 3 : 0; });
        var dashes = series.map(function (s) { return s.type === 'line' ? 6 : 0; });
        return { series: series, colors: colors, stroke: { width: widths, dashArray: dashes, curve: 'straight' }, hasPlan: hasPlan };
    }

    var first = build();
    var chart = new ApexCharts(document.querySelector('#smMonthlyChart'), {
        series: first.series,
        chart: { type: 'bar', height: 360, stacked: true, fontFamily: 'Kanit, sans-serif', toolbar: { show: false }, parentHeightOffset: 0 },
        colors: first.colors,
        stroke: first.stroke,
        markers: { size: 0 },
        plotOptions: { bar: { borderRadius: 3, columnWidth: '58%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'left' },
        noData: { text: 'ไม่มีข้อมูลในหมวดนี้' },
        grid: { strokeDashArray: 5, borderColor: css('--bs-border-color') },
        xaxis: { categories: $labelsJson, axisTicks: { show: false }, axisBorder: { show: false } },
        yaxis: {
            labels: {
                formatter: function (v) {
                    if (v >= 1e6) return (v / 1e6).toLocaleString(undefined, { maximumFractionDigits: 1 }) + ' ล.';
                    if (v >= 1e3) return (v / 1e3).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' พ.';
                    return v;
                }
            }
        },
        tooltip: { shared: true, intersect: false, y: { formatter: function (v) { return v == null ? '-' : Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท'; } } }
    });
    chart.render();

    var btnPr = document.getElementById('smStagePr');
    var btnGr = document.getElementById('smStageGr');
    var catBtns = document.querySelectorAll('#smCatPicker [data-cat]');
    var modeBtns = document.querySelectorAll('[data-mode]');
    var summary = document.getElementById('smPlanSummary');

    function renderSummary(hasPlan) {
        if (!hasPlan) {
            summary.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i>ยังไม่มีแผนจัดซื้อของหมวดนี้ในปีงบนี้</span>';
            return;
        }
        var actualM = monthTotals(views[state.cat][state.stage]);
        var planM = plan[state.cat];
        var planYear = planM.reduce(add, 0);
        var actualYear = actualM.reduce(add, 0);
        var html = '<span class="badge bg-body-secondary text-body fw-normal">แผนทั้งปี ' + mb(planYear) + '</span>'
            + '<span class="badge bg-body-secondary text-body fw-normal">' + (state.stage === 'pr' ? 'ขอซื้อ' : 'ตรวจรับ') + 'แล้ว ' + mb(actualYear)
            + ' (' + (actualYear / planYear * 100).toFixed(1) + '%)</span>';
        if (upto >= 0 && upto < 11) {
            var planTo = planM.slice(0, upto + 1).reduce(add, 0);
            var actTo = actualM.slice(0, upto + 1).reduce(add, 0);
            var over = actTo > planTo;
            html += '<span class="badge fw-normal ' + (over ? 'bg-danger-subtle text-danger-emphasis' : 'bg-success-subtle text-success-emphasis') + '">'
                + '<i class="bi ' + (over ? 'bi-exclamation-triangle' : 'bi-check-circle') + ' me-1"></i>'
                + 'ถึงเดือนนี้ แผน ' + mb(planTo) + ' · ใช้ ' + mb(actTo)
                + (over ? ' · เกินแผน ' + mb(actTo - planTo) : ' · เหลือ ' + mb(planTo - actTo)) + '</span>';
        } else if (actualYear > planYear) {
            html += '<span class="badge fw-normal bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle me-1"></i>เกินแผน ' + mb(actualYear - planYear) + '</span>';
        }
        summary.innerHTML = html;
    }

    function redraw() {
        var b = build();
        chart.updateOptions({ series: b.series, colors: b.colors, stroke: b.stroke });

        var prOn = state.stage === 'pr';
        btnPr.className = 'btn ' + (prOn ? 'btn-primary' : 'btn-outline-primary');
        btnGr.className = 'btn ' + (prOn ? 'btn-outline-primary' : 'btn-primary');
        document.getElementById('smStagePrTotal').textContent = mb(monthTotals(views[state.cat].pr).reduce(add, 0));
        document.getElementById('smStageGrTotal').textContent = mb(monthTotals(views[state.cat].gr).reduce(add, 0));

        catBtns.forEach(function (el) {
            var on = el.dataset.cat === state.cat;
            el.classList.toggle('btn-primary', on);
            el.classList.toggle('btn-outline-secondary', !on);
        });
        modeBtns.forEach(function (el) { el.classList.toggle('active', el.dataset.mode === state.mode); });
        document.getElementById('smCatHint').classList.toggle('d-none', state.cat === 'all');
        renderSummary(b.hasPlan);
    }

    btnPr.addEventListener('click', function () { state.stage = 'pr'; redraw(); });
    btnGr.addEventListener('click', function () { state.stage = 'gr'; redraw(); });
    catBtns.forEach(function (el) { el.addEventListener('click', function () { state.cat = el.dataset.cat; redraw(); }); });
    modeBtns.forEach(function (el) { el.addEventListener('click', function () { state.mode = el.dataset.mode; redraw(); }); });
    renderSummary(first.hasPlan);
})();
JS;
$this->registerJS($js);
