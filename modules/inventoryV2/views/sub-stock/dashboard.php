<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Dashboard คลังย่อย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);

$pendingReceiveCount = (int) ($pendingReceiveCount ?? 0);
$criticalCount = (int) ($criticalCount ?? 0);
$monthlyValue = (float) ($monthlyValue ?? 0);
$expiringSoonCount = (int) ($expiringSoonCount ?? 0);
$incomingList = $incomingList ?? [];
$chartData = $chartData ?? ['categories' => [], 'series' => []];
$warehouses = $warehouses ?? [];
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
    <p class="text-muted small mb-0"><?= Html::encode($currentWarehouseName) ?> — ภาพรวมคลังย่อย รอตรวจรับ ต่ำกว่าจุดวิกฤต และแนวโน้มการจ่ายมาคลังย่อย</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="container-fluid px-3 px-md-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-lg-auto">
            <form method="get" action="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>" id="form-sub-warehouse" class="d-inline">
                <label for="subWarehouseFilter" class="form-label visually-hidden">เลือกคลังย่อย</label>
                <select name="warehouse_id" class="form-select form-select-sm border shadow-sm rounded-pill px-3" id="subWarehouseFilter" style="min-width: 180px;">
                    <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>แสดงคลังย่อยทั้งหมด</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>><?= Html::encode($w->warehouse_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col-12 col-lg-auto">
            <?= $this->render('@app/modules/inventoryV2/views/default/_menu_sub', ['active' => 'dashboard', 'subWarehouseIds' => $subWarehouseIds ?? []]) ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 px-3 px-md-4 sub-stock-dashboard">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-info border-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">รอตรวจรับเข้า</p>
                            <span class="fs-4 fw-bold text-dark"><?= (int)$pendingReceiveCount ?></span>
                            <span class="text-muted small">ใบ</span>
                        </div>
                        <div class="erp-icon-box bg-info bg-opacity-10 text-info rounded-3">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                    </div>
                    <span class="badge text-bg-info mt-2">จากคลังหลัก</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="text-decoration-none d-block h-100 kpi-card-link">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-danger border-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดวิกฤต</p>
                                <span class="fs-4 fw-bold text-danger"><?= (int)$criticalCount ?></span>
                                <span class="text-muted small">รายการ</span>
                            </div>
                            <div class="erp-icon-box bg-danger bg-opacity-10 text-danger rounded-3">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <span class="text-danger small fw-bold mt-2 d-inline-block">รีบกดเบิกทันที <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-success border-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">มูลค่าเบิกใช้เดือนนี้</p>
                            <span class="fs-4 fw-bold text-dark"><?= number_format($monthlyValue, 0) ?></span>
                            <span class="text-muted small">฿</span>
                        </div>
                        <div class="erp-icon-box bg-success bg-opacity-10 text-success rounded-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                    </div>
                    <p class="text-success small mb-0 mt-2"><i class="bi bi-graph-up me-1"></i>เทียบเดือนก่อน</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 border-top border-warning border-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">หมดอายุภายใน 30 วัน</p>
                            <span class="fs-4 fw-bold text-warning"><?= (int)$expiringSoonCount ?></span>
                            <span class="text-muted small">Lot</span>
                        </div>
                        <div class="erp-icon-box bg-warning bg-opacity-10 text-warning rounded-3">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-2">ควรนำออกมาใช้งานก่อน</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- แนวโน้มการตัดจ่าย (7 วันล่าสุด) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3">
                    <h6 class="text-white mb-0 fw-normal"><i class="bi bi-graph-up me-1"></i>แนวโน้มมูลค่าที่จ่ายมาคลังย่อย (7 วันล่าสุด)</h6>
                </div>
                <div class="card-body p-3">
                    <div id="usageApexChart"></div>
                </div>
            </div>
        </div>

        <!-- รายการส่งของจากคลังหลัก -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="text-white mb-0 fw-normal"><i class="bi bi-box-seam me-1"></i>รายการส่งของจากคลังหลัก</h6>
                    <span class="badge text-bg-light text-dark"><?= count($incomingList) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($incomingList)): ?>
                            <?php foreach ($incomingList as $item): ?>
                                <div class="list-group-item border-0 border-start border-3 border-primary px-3 py-2 incoming-item">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-25 rounded p-2 flex-shrink-0"><i class="bi bi-file-earmark-text text-primary"></i></div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold"><?= Html::encode($item['doc_no'] ?? '-') ?></div>
                                            <span class="text-muted"><?= (int)($item['detail_count'] ?? 0) ?> รายการ</span>
                                            <?php if (!empty($item['main_warehouse_name'])): ?>
                                                <span class="text-muted"> | <?= Html::encode($item['main_warehouse_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?= Html::a('ดูใบส่ง', ['/inventory-v2/requisition/view', 'id' => $item['id'] ?? null], ['class' => 'btn btn-outline-primary rounded-pill flex-shrink-0']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item border-0 px-3 py-4 text-center text-muted">
                                ไม่มีรายการส่งของจากคลังหลักในขณะนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 py-2 text-center">
                    <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>" class="text-muted text-decoration-none">ดูทะเบียนใบขอเบิก <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ตรวจรับพัสดุ -->
<div class="modal fade border-0" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>ตรวจรับพัสดุเข้าคลังย่อย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                    <div><span class="text-muted">เลขที่ใบส่ง:</span> <strong id="m-doc-no">—</strong></div>
                    <div><span class="text-muted">คลังต้นทาง:</span> <strong id="m-from">คลังหลัก</strong></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th>รายการ</th>
                                <th class="text-center">Lot</th>
                                <th class="text-center">ส่งมา</th>
                                <th class="text-center" style="width: 140px;">รับจริง</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider" id="receive-list-body">
                            <tr>
                                <td><strong>SSD 500GB Samsung</strong></td>
                                <td class="text-center font-monospace small">67-01</td>
                                <td class="text-center">5</td>
                                <td><input type="number" class="form-control text-center" value="5" min="0"></td>
                                <td><input type="text" class="form-control" placeholder="หมายเหตุ"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-5 me-2" id="btnConfirmModal">ยืนยันรับเข้าสต็อก</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<style>
.sub-stock-dashboard .erp-icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sub-stock-dashboard .kpi-card-link { color: inherit; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.sub-stock-dashboard .kpi-card-link:hover { color: inherit; transform: translateY(-2px); }
.sub-stock-dashboard .kpi-card-link:hover .card { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
.sub-stock-dashboard .kpi-card-link:focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; border-radius: 0.5rem; }
.sub-stock-dashboard .incoming-item { transition: background-color 0.15s ease; }
.sub-stock-dashboard .incoming-item:hover { background-color: rgba(13, 110, 253, 0.04); }
</style>

<?php
$chartCategoriesJson = json_encode($chartData['categories']);
$chartSeriesJson = json_encode($chartData['series']);
$js = <<<JS
$(document).ready(function() {
    var categories = {$chartCategoriesJson};
    var seriesData = {$chartSeriesJson};
    if (categories.length === 0) categories = ['-'];
    if (seriesData.length === 0) seriesData = [0];
    var options = {
        series: [{ name: 'มูลค่าที่จ่ายมา (บาท)', data: seriesData }],
        chart: { height: 320, type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ['#0d6efd'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: { categories: categories, axisBorder: { show: false }, axisTicks: { show: false } },
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
        tooltip: { theme: 'light', y: { formatter: function(v) { return v != null ? Number(v).toLocaleString('th-TH') + ' บาท' : ''; } } }
    };
    var chart = new ApexCharts(document.querySelector("#usageApexChart"), options);
    chart.render();
});

$('#subWarehouseFilter').on('change', function() {
    $('#form-sub-warehouse').submit();
});

$('#receiveModal').on('show.bs.modal', function(e) {
    var docNo = $(e.relatedTarget).data('doc-no') || '—';
    $('#m-doc-no').text(docNo);
});

$('#btnConfirmModal').on('click', function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'ยืนยันการรับ?', text: 'บันทึกการตรวจรับพัสดุ', icon: 'question', showCancelButton: true, confirmButtonText: 'ยืนยัน', confirmButtonColor: '#0d6efd' })
            .then(function(result) {
                if (result.isConfirmed) $('#receiveModal').modal('hide');
            });
    } else {
        $('#receiveModal').modal('hide');
    }
});
JS;
$this->registerJs($js, View::POS_READY);
?>
