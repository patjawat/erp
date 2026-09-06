<?php

/**
 * กราฟแท่งรายเดือน แยก 4 หมวด — สลับ "ขอซื้อ" / "ตรวจรับ"
 * สไตล์ Dashboard V2 · สีกราฟ resolve จาก Bootstrap CSS var (theme-aware)
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use app\modules\sm\services\PurchaseDashboardService;
use yii\helpers\Json;

$prSeries = $dashboard->chartSeries('pr');
$grSeries = $dashboard->chartSeries('gr');
$labels = PurchaseDashboardService::MONTH_LABELS;

$prJson = Json::encode($prSeries);
$grJson = Json::encode($grSeries);
$labelsJson = Json::encode($labels);
$cssVarsJson = Json::encode(PurchaseDashboardService::categoryCssVars());

$prTotal = array_sum(array_map(fn($s) => array_sum($s['data']), $prSeries));
$grTotal = array_sum(array_map(fn($s) => array_sum($s['data']), $grSeries));
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
                <span class="badge rounded-pill bg-white text-primary ms-1"><?= number_format($prTotal / 1e6, 2) ?> ล.</span>
            </button>
            <button type="button" class="btn btn-outline-primary" id="smStageGr">
                <i class="bi bi-bag-check me-1"></i>ตรวจรับ
                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis ms-1"><?= number_format($grTotal / 1e6, 2) ?> ล.</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="smMonthlyChart" style="min-height:340px;"></div>
    </div>
</div>
<?php
$js = <<< JS
(function () {
    var css = function (v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); };
    var colors = $cssVarsJson.map(css);
    var prSeries = $prJson;
    var grSeries = $grJson;
    var stageMeta = { pr: prSeries, gr: grSeries };

    var options = {
        series: prSeries,
        chart: { type: 'bar', height: 360, stacked: true, fontFamily: 'Kanit, sans-serif', toolbar: { show: false }, parentHeightOffset: 0 },
        colors: colors,
        plotOptions: { bar: { borderRadius: 3, columnWidth: '58%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'left' },
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
        tooltip: { y: { formatter: function (v) { return Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท'; } } }
    };

    var chart = new ApexCharts(document.querySelector('#smMonthlyChart'), options);
    chart.render();

    var btnPr = document.getElementById('smStagePr');
    var btnGr = document.getElementById('smStageGr');
    function setStage(stage) {
        chart.updateSeries(stageMeta[stage]);
        var prOn = stage === 'pr';
        btnPr.className = 'btn ' + (prOn ? 'btn-primary' : 'btn-outline-primary');
        btnGr.className = 'btn ' + (prOn ? 'btn-outline-primary' : 'btn-primary');
    }
    btnPr.addEventListener('click', function () { setStage('pr'); });
    btnGr.addEventListener('click', function () { setStage('gr'); });
})();
JS;
$this->registerJS($js);
