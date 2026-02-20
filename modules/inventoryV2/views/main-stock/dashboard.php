<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Dashboard คลังหลัก';
$this->params['breadcrumbs'][] = $this->title;

$stats = $stats ?? [
    'pending_count' => 0,
    'critical_count' => 0,
    'total_value' => 0,
    'lots_count' => 0,
    'items_with_stock' => 0,
];
$warehouses = $warehouses ?? [];
$pendingRequisitions = $pendingRequisitions ?? [];
$chartData = $chartData ?? ['categories' => [], 'series' => [], 'fiscal_year' => null];
$fiscalYear = $chartData['fiscal_year'] ?? null;
$currentWarehouseId = $currentWarehouseId ?? null;

$currentWarehouseName = 'ทั้งหมด';
if ($currentWarehouseId && $warehouses) {
    foreach ($warehouses as $w) {
        if ((int)$w->id === (int)$currentWarehouseId) {
            $currentWarehouseName = $w->warehouse_name;
            break;
        }
    }
}

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);
?>

<div class="container-fluid py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/inventory-v2/default/index']) ?>" class="text-decoration-none">คลังสินค้า</a></li>
                    <li class="breadcrumb-item active" id="breadcrumb-current"><?= Html::encode($currentWarehouseName) ?></li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Dashboard คลังหลัก</h3>
        </div>

        <div class="col-md-7 d-flex justify-content-md-end align-items-center gap-2 mt-3 mt-md-0">
            <div class="position-relative me-2">
                <form method="get" action="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" id="form-warehouse" class="d-inline">
                    <select name="warehouse_id" class="form-select border-0 shadow-sm rounded-pill px-4" id="warehouseFilter" style="min-width: 220px; height: 45px;">
                        <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>📊 แสดงคลังทั้งหมด</option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>><?= Html::encode($w->warehouse_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <a href="<?= Url::to(['/inventory-v2/receive/create']) ?>" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-plus-lg"></i> รับสินค้า</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 border-top border-primary border-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">ใบเบิกที่รอการจ่าย</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-primary" id="kpi-pending"><?= (int)$stats['pending_count'] ?></h2>
                        <span class="text-muted mb-1">ฉบับ</span>
                    </div>
                    <div class="mt-3">
                        <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="text-primary text-decoration-none small fw-bold">ดูรายการค้างจ่าย <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">รายการที่ต่ำกว่าจุดสั่งซื้อ</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-danger" id="kpi-critical"><?= (int)$stats['critical_count'] ?></h2>
                        <span class="text-muted mb-1">รายการ</span>
                    </div>
                    <div class="mt-3">
                        <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="text-danger text-decoration-none small fw-bold">ดูรายการพัสดุ <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body p-4">
                    <p class="text-white-50 small mb-1 fw-bold">มูลค่าพัสดุในคลัง</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-white" id="kpi-value"><?= number_format($stats['total_value'], 0) ?></h2>
                        <span class="text-white-50 mb-1">฿</span>
                    </div>
                    <div class="mt-3 small text-warning"><i class="bi bi-lightning-fill"></i> รวมมูลค่าทุก Lot (ราคาทุน)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">รายการพัสดุที่มีสต็อก</p>
                    <h2 class="fw-bold mb-0 text-dark" id="kpi-items"><?= (int)$stats['items_with_stock'] ?></h2>
                    <div class="mt-3 text-muted small"><i class="bi bi-box-seam me-1"></i> <?= (int)$stats['lots_count'] ?> Lot ในคลัง</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">แนวโน้มการรับเข้า-จ่ายออก (<span id="chart-title"><?= Html::encode($currentWarehouseName) ?></span>) <?= $fiscalYear ? 'ปีงบประมาณ ' . (int)$fiscalYear : '' ?></h5>
                </div>
                <div class="card-body px-2">
                    <div id="mainWarehouseChart"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0 text-primary">รายการเบิกที่รอจัดของ</h5>
                </div>
                <div class="card-body p-0">
                    <div id="pending-list" class="list-group list-group-flush px-2">
                        <?php if (empty($pendingRequisitions)): ?>
                            <div class="list-group-item border-0 p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-0 mt-2">ไม่มีใบขอเบิกรอดำเนินการ</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pendingRequisitions as $req): ?>
                                <?php
                                $subName = $req->subWarehouse ? $req->subWarehouse->warehouse_name : 'ไม่ระบุ';
                                $detailCount = is_array($req->stockDetails) ? count($req->stockDetails) : (method_exists($req, 'getStockDetails') ? $req->getStockDetails()->count() : 0);
                                $timeAgo = $req->order_date ? Yii::$app->formatter->asRelativeTime(strtotime($req->order_date)) : '';
                                ?>
                                <div class="list-group-item border-0 p-3 mb-2 rounded-4 hover-action bg-white shadow-xs border-start border-4 border-warning">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-light text-dark mb-1"><?= Html::encode($req->order_no) ?></span>
                                            <h6 class="fw-bold mb-0"><?= Html::encode($subName) ?></h6>
                                        </div>
                                        <small class="text-muted"><?= $timeAgo ?></small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted"><i class="bi bi-box me-1"></i> <?= $detailCount ?> รายการ</span>
                                        <?= Html::a('จัดของจ่าย', ['/inventory-v2/issue/process', 'id' => $req->id], ['class' => 'btn btn-primary btn-sm rounded-pill px-3']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="text-decoration-none small text-muted">ดูรายการค้างจ่ายทั้งหมด <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .hover-action { transition: all 0.2s ease-in-out; border: 1px solid transparent; }
    .hover-action:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.08); background: #fff !important; cursor: pointer; }
    .form-select:focus { outline: none; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); }
    .breadcrumb-item + .breadcrumb-item::before { content: "›"; font-size: 1.2rem; line-height: 1; vertical-align: middle; }
</style>

<?php
$chartCategories = json_encode($chartData['categories']);
$chartSeries = json_encode($chartData['series']);
$defaultMonthsJson = json_encode(['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.']);
$defaultSeriesJson = json_encode([['name' => 'รับเข้า', 'data' => array_fill(0, 12, 0)], ['name' => 'จ่ายออก', 'data' => array_fill(0, 12, 0)]]);
$js = <<< JS
$(document).ready(function() {

    var categories = $chartCategories;
    var seriesData = $chartSeries;
    var defaultMonths = $defaultMonthsJson;
    var defaultSeries = $defaultSeriesJson;

    if (!categories || categories.length === 0) {
        categories = defaultMonths;
    }
    if (!seriesData || seriesData.length === 0) {
        seriesData = defaultSeries;
    }

    var options = {
        series: seriesData,
        chart: {
            type: 'bar',
            height: 380,
            stacked: false,
            toolbar: { show: false },
            fontFamily: 'Sarabun'
        },
        plotOptions: {
            bar: { borderRadius: 8, columnWidth: '65%', horizontal: false }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: categories,
            axisBorder: { show: false },
            title: { text: 'เดือน (ปีงบประมาณ ตุลาคม - กันยายน)' }
        },
        yaxis: {
            title: { text: 'จำนวนเอกสาร' },
            labels: { formatter: function(v) { return Math.round(v); } }
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        fill: { opacity: 1 },
        colors: ['#198754', '#dc3545'],
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
    };

    var chart = new ApexCharts(document.querySelector("#mainWarehouseChart"), options);
    chart.render();

    $('#warehouseFilter').on('change', function() {
        $('#form-warehouse').submit();
    });
});
JS;
$this->registerJs($js, View::POS_READY);
?>
