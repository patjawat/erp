<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Dashboard คลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$stats = $stats ?? [
    'pending_count' => 0,
    'critical_count' => 0,
    'total_value' => 0,
    'lots_count' => 0,
    'items_with_stock' => 0,
    'insufficient_to_disburse_count' => 0,
];
$warehouses = $warehouses ?? [];
$pendingRequisitions = $pendingRequisitions ?? [];
$chartData = $chartData ?? ['categories' => [], 'series' => [], 'fiscal_year' => null];
$fiscalYear = $chartData['fiscal_year'] ?? null;
$currentWarehouseId = $currentWarehouseId ?? null;
$warehouseIdParam = $currentWarehouseId !== null ? (int)$currentWarehouseId : 'all';

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

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0"><?= Html::encode($currentWarehouseName) ?> — ภาพรวมสต็อก ใบเบิกรอจ่าย และแนวโน้มรับเข้า-จ่ายออก</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="container-fluid px-3 px-md-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-lg-auto">
            <form method="get" action="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" id="form-warehouse" class="d-inline">
                <label for="warehouseFilter" class="form-label visually-hidden">เลือกคลัง</label>
                <select name="warehouse_id" class="form-select form-select-sm border shadow-sm rounded-pill px-3" id="warehouseFilter" style="min-width: 180px;">
                    <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>แสดงคลังทั้งหมด</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>><?= Html::encode($w->warehouse_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col-12 col-lg-auto">
            <?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'dashboard']) ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 px-3 px-md-4 main-stock-dashboard">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="text-decoration-none d-block h-100 kpi-card-link">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-primary border-3">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 fw-bold">ใบเบิกที่รอการจ่าย</p>
                        <div class="d-flex align-items-end gap-2">
                            <span class="fs-4 fw-bold text-primary" id="kpi-pending"><?= (int)$stats['pending_count'] ?></span>
                            <span class="text-muted small mb-1">ฉบับ</span>
                        </div>
                        <span class="text-primary small fw-bold mt-2 d-inline-block">ดูรายการค้างจ่าย <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= Url::to(['/inventory-v2/report/insufficient-to-disburse']) ?>" class="text-decoration-none d-block h-100 kpi-card-link">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-warning border-3">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 fw-bold">วัสดุไม่พอจ่าย</p>
                        <div class="d-flex align-items-end gap-2">
                            <span class="fs-4 fw-bold text-warning text-dark" id="kpi-insufficient"><?= (int)($stats['insufficient_to_disburse_count'] ?? 0) ?></span>
                            <span class="text-muted small mb-1">รายการ</span>
                        </div>
                        <span class="text-warning text-dark small fw-bold mt-2 d-inline-block">ดูรายการ + ต้องซื้อเพิ่ม <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= Url::to(['/inventory-v2/main-stock/critical-items-offcanvas', 'warehouse_id' => $warehouseIdParam]) ?>" class="text-decoration-none d-block h-100 kpi-card-link open-critical-items-offcanvas" data-offcanvas-title="<?= Html::encode($currentWarehouseName) ?>">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-danger border-3">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดสั่งซื้อ</p>
                        <div class="d-flex align-items-end gap-2">
                            <span class="fs-4 fw-bold text-danger" id="kpi-critical"><?= (int)$stats['critical_count'] ?></span>
                            <span class="text-muted small mb-1">รายการ</span>
                        </div>
                        <span class="text-danger small fw-bold mt-2 d-inline-block">ดูรายการพัสดุ <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-dark border-3 text-white">
                <div class="card-body p-3">
                    <p class="small mb-1 fw-bold">มูลค่าพัสดุในคลัง</p>
                    <div class="d-flex align-items-end gap-2">
                        <span class="fs-4 fw-bold" id="kpi-value"><?= number_format($stats['total_value'], 0) ?></span>
                        <span class="small mb-1">฿</span>
                    </div>
                    <p class="text-warning small mb-0 mt-2"><i class="bi bi-lightning-fill me-1"></i>รวมมูลค่าทุก Lot (ราคาทุน)</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= Url::to(['/inventory-v2/main-stock/items-with-stock-offcanvas', 'warehouse_id' => $warehouseIdParam]) ?>" class="text-decoration-none d-block h-100 kpi-card-link open-items-with-stock-offcanvas" data-offcanvas-title="<?= Html::encode($currentWarehouseName) ?>">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-secondary border-3">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 fw-bold">รายการพัสดุที่มีสต๊อก</p>
                        <div class="d-flex align-items-end gap-2">
                            <span class="fs-4 fw-bold text-dark" id="kpi-items"><?= (int)$stats['items_with_stock'] ?></span>
                            <span class="text-muted small mb-1">รายการ</span>
                        </div>
                        <p class="text-muted small mb-0 mt-2"><i class="bi bi-box-seam me-1"></i><?= (int)$stats['lots_count'] ?> Lot ในคลัง</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- แนวโน้มรับเข้า-จ่ายออก -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="text-white mb-0 small fw-normal">
                        <i class="bi bi-graph-up me-1"></i>แนวโน้มการรับเข้า-จ่ายออก
                        <span class="badge text-bg-light text-dark ms-1" id="chart-badge"><?= Html::encode($currentWarehouseName) ?></span>
                    </h6>
                    <?php if ($fiscalYear): ?>
                        <span class="small opacity-90">ปีงบประมาณ <?= (int)$fiscalYear ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-3">
                    <div id="mainWarehouseChart"></div>
                </div>
            </div>
        </div>

        <!-- รายการเบิกที่รอจัดของ -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-box-seam me-1"></i>รายการเบิกที่รอจัดของ</h6>
                    <span class="badge text-bg-light text-dark"><?= count($pendingRequisitions) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($pendingRequisitions)): ?>
                            <div class="list-group-item border-0 p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-2"></i>
                                <p class="mb-0 mt-2 small">ไม่มีใบขอเบิกรอดำเนินการ</p>
                            </div>
                        <?php else: ?>
                            <?php
                            foreach ($pendingRequisitions as $req): ?>
                                <?php
                                $subName = $req->subWarehouse ? $req->subWarehouse->warehouse_name : 'ไม่ระบุ';
                                $detailCount = is_array($req->stockDetails) ? count($req->stockDetails) : (method_exists($req, 'getStockDetails') ? $req->getStockDetails()->count() : 0);
                                $timeAgo = $req->order_date ? Yii::$app->formatter->asRelativeTime(strtotime($req->order_date)) : '';
                                $statusInfo = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($req->status);
                                $statusIcon = !empty($statusInfo['icon']) ? '<i data-lucide="' . Html::encode($statusInfo['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                                ?>
                                <div class="list-group-item border-0 border-start border-3 border-primary px-3 py-2 pending-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                                                <span class="badge text-bg-primary"><?= Html::encode($req->order_no) ?></span>
                                                <span class="<?= $statusInfo['class'] ?>"><?= $statusIcon . Html::encode($statusInfo['label']) ?></span>
                                            </div>
                                            <div class="fw-bold text-truncate"><?= Html::encode($subName) ?></div>
                                            <small class="text-muted"><?= $detailCount ?> รายการ · <?= $timeAgo ?></small>
                                        </div>
                                        <?= Html::a('จัดของจ่าย', ['/inventory-v2/issue/process', 'id' => $req->id], ['class' => 'btn btn-primary btn-sm rounded-pill flex-shrink-0']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 py-2 text-center">
                    <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="small text-muted text-decoration-none">ดูรายการค้างจ่ายทั้งหมด <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="itemsWithStockOffcanvas" aria-labelledby="itemsWithStockOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="itemsWithStockOffcanvasLabel">รายการพัสดุที่มีสต๊อก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="itemsWithStockOffcanvasBody">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm mb-2"></div>
            <div class="small">กำลังโหลด...</div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="criticalItemsOffcanvas" aria-labelledby="criticalItemsOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="criticalItemsOffcanvasLabel">ต่ำกว่าจุดสั่งซื้อ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="criticalItemsOffcanvasBody">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm mb-2"></div>
            <div class="small">กำลังโหลด...</div>
        </div>
    </div>
</div>

<style>
.main-stock-dashboard .kpi-card-link { color: inherit; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.main-stock-dashboard .kpi-card-link:hover { color: inherit; transform: translateY(-2px); }
.main-stock-dashboard .kpi-card-link:hover .card { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
.main-stock-dashboard .kpi-card-link:focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; border-radius: 0.5rem; }
.main-stock-dashboard .pending-item { transition: background-color 0.15s ease; }
.main-stock-dashboard .pending-item:hover { background-color: rgba(13, 110, 253, 0.04); }
.main-stock-dashboard #warehouseFilter:focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; }
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

    if (!categories || categories.length === 0) { categories = defaultMonths; }
    if (!seriesData || seriesData.length === 0) { seriesData = defaultSeries; }

    var options = {
        series: seriesData,
        chart: { type: 'bar', height: 360, stacked: false, toolbar: { show: false }, fontFamily: 'Sarabun' },
        plotOptions: { bar: { borderRadius: 8, columnWidth: '65%', horizontal: false } },
        dataLabels: { enabled: false },
        xaxis: {
            categories: categories,
            axisBorder: { show: false },
            title: { text: 'เดือน (ปีงบประมาณ ต.ค. - ก.ย.)' }
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

    $('#warehouseFilter').on('change', function() { $('#form-warehouse').submit(); });

    $('body').on('click', '.open-items-with-stock-offcanvas', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!url) return;

        $('#itemsWithStockOffcanvas').data('base-url', url);

        var title = $(this).data('offcanvas-title') || 'รายการพัสดุที่มีสต๊อก';
        $('#itemsWithStockOffcanvasLabel').text(title);
        $('#itemsWithStockOffcanvasBody').html(
            '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm mb-2"></div>' +
                '<div class="small">กำลังโหลด...</div>' +
            '</div>'
        );

        var offcanvasEl = document.getElementById('itemsWithStockOffcanvas');
        var offcanvas = new bootstrap.Offcanvas(offcanvasEl);
        offcanvas.show();

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success' && res.content) {
                    $('#itemsWithStockOffcanvasBody').html(res.content);
                } else {
                    $('#itemsWithStockOffcanvasBody').html(
                        '<div class="alert alert-warning mb-0">ไม่พบข้อมูล</div>'
                    );
                }
            },
            error: function() {
                $('#itemsWithStockOffcanvasBody').html(
                    '<div class="alert alert-danger mb-0">โหลดข้อมูลไม่สำเร็จ</div>'
                );
            }
        });
    });

    $('body').on('click', '.open-critical-items-offcanvas', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!url) return;

        $('#criticalItemsOffcanvas').data('base-url', url);

        var title = $(this).data('offcanvas-title') || 'ต่ำกว่าจุดสั่งซื้อ';
        $('#criticalItemsOffcanvasLabel').text('ต่ำกว่าจุดสั่งซื้อ');
        $('#criticalItemsOffcanvasBody').html(
            '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm mb-2"></div>' +
                '<div class="small">กำลังโหลด...</div>' +
            '</div>'
        );

        var offcanvasEl = document.getElementById('criticalItemsOffcanvas');
        var offcanvas = new bootstrap.Offcanvas(offcanvasEl);
        offcanvas.show();

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success' && res.content) {
                    $('#criticalItemsOffcanvasBody').html(res.content);
                    return;
                }
                var msg = (res && res.message) ? res.message : 'ไม่พบข้อมูล';
                $('#criticalItemsOffcanvasBody').html(
                    '<div class="alert alert-warning mb-0">' + msg + '</div>'
                );
            },
            error: function(xhr) {
                var detail = xhr && xhr.responseText ? xhr.responseText : '';
                detail = (detail || '').toString().slice(0, 300);
                $('#criticalItemsOffcanvasBody').html(
                    '<div class="alert alert-danger mb-0">โหลดข้อมูลไม่สำเร็จ' + (detail ? (' (รายละเอียด: ' + detail + ')') : '') + '</div>'
                );
            }
        });
    });

    $('body').on('click', '.items-with-stock-search-btn', function(e) {
        e.preventDefault();
        var baseUrl = $('#itemsWithStockOffcanvas').data('base-url');
        if (!baseUrl) return;

        var q = ($('#itemsWithStockSearchQ').val() || '').toString().trim();
        var sep = baseUrl.indexOf('?') !== -1 ? '&' : '?';
        var url = baseUrl + sep + 'q=' + encodeURIComponent(q);

        $('#itemsWithStockOffcanvasBody').html(
            '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm mb-2"></div>' +
                '<div class="small">กำลังค้นหา...</div>' +
            '</div>'
        );

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success' && res.content) {
                    $('#itemsWithStockOffcanvasBody').html(res.content);
                } else {
                    $('#itemsWithStockOffcanvasBody').html(
                        '<div class="alert alert-warning mb-0">ไม่พบข้อมูล</div>'
                    );
                }
            },
            error: function() {
                $('#itemsWithStockOffcanvasBody').html(
                    '<div class="alert alert-danger mb-0">โหลดข้อมูลไม่สำเร็จ</div>'
                );
            }
        });
    });

    $('body').on('keydown', '#itemsWithStockSearchQ', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('.items-with-stock-search-btn').first().trigger('click');
        }
    });

    $('body').on('click', '.critical-items-search-btn', function(e) {
        e.preventDefault();
        var baseUrl = $('#criticalItemsOffcanvas').data('base-url');
        if (!baseUrl) return;

        var q = ($('#criticalItemsSearchQ').val() || '').toString().trim();
        var sep = baseUrl.indexOf('?') !== -1 ? '&' : '?';
        var url = baseUrl + sep + 'q=' + encodeURIComponent(q);

        $('#criticalItemsOffcanvasBody').html(
            '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm mb-2"></div>' +
                '<div class="small">กำลังค้นหา...</div>' +
            '</div>'
        );

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success' && res.content) {
                    $('#criticalItemsOffcanvasBody').html(res.content);
                } else {
                    $('#criticalItemsOffcanvasBody').html(
                        '<div class="alert alert-warning mb-0">ไม่พบข้อมูล</div>'
                    );
                }
            },
            error: function() {
                $('#criticalItemsOffcanvasBody').html(
                    '<div class="alert alert-danger mb-0">โหลดข้อมูลไม่สำเร็จ</div>'
                );
            }
        });
    });

    $('body').on('keydown', '#criticalItemsSearchQ', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('.critical-items-search-btn').first().trigger('click');
        }
    });
});
JS;
$this->registerJs($js, View::POS_READY);
?>
