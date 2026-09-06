<?php

/**
 * กระทบยอดรายเดือน: เข้าคลังแล้ว vs ค้างเข้าคลัง (แกนเวลา = วันตรวจรับ)
 * สไตล์ Dashboard V2 · สีกราฟจาก Bootstrap CSS var (theme-aware)
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use app\modules\sm\services\PurchaseDashboardService;
use yii\helpers\Json;

$rc = $dashboard->reconcile();
$labels = PurchaseDashboardService::MONTH_LABELS;

$series = [
    ['name' => 'เข้าคลังแล้ว', 'data' => array_map(fn($v) => round($v, 2), $rc['monthly']['stocked'])],
    ['name' => 'ค้างเข้าคลัง (ตรวจรับแล้ว)', 'data' => array_map(fn($v) => round($v, 2), $rc['monthly']['pending'])],
];
$seriesJson = Json::encode($series);
$labelsJson = Json::encode($labels);
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-info bg-opacity-10">
                <i class="bi bi-arrow-left-right text-info"></i>
            </div>
            <h6 class="text-body-secondary m-0">กระทบยอด ตรวจรับ ↔ เข้าคลัง (รายเดือน)</h6>
        </div>
        <span class="small text-body-secondary">ตามวันตรวจรับ</span>
    </div>
    <div class="card-body">
        <div id="smReconcileChart" style="min-height:320px;"></div>
    </div>
</div>
<?php
$js = <<< JS
(function () {
    var css = function (v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); };
    var options = {
        series: $seriesJson,
        chart: { type: 'bar', height: 340, stacked: true, fontFamily: 'Kanit, sans-serif', toolbar: { show: false }, parentHeightOffset: 0 },
        colors: [css('--bs-success'), css('--bs-danger')],
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
    new ApexCharts(document.querySelector('#smReconcileChart'), options).render();
})();
JS;
$this->registerJS($js);
