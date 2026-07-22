<?php
use yii\web\View;
$this->title = 'Main Inventory Control Center';

// ลงทะเบียน ApexCharts และ Bootstrap Icons
$this->registerJsFile('@web/apexcharts/apexcharts.min.js', ['position' => View::POS_HEAD]); // self-hosted (เดิม jsdelivr)

use yii\helpers\Url;
?>


<div class="container-fluid py-4 bg-light" style="font-family: 'Sarabun', sans-serif;">
    
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" id="breadcrumb-current">Main Warehouse</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">ระบบควบคุมคลังรวม</h3>
        </div>
        
        <div class="col-md-7 d-flex justify-content-md-end align-items-center gap-2 mt-3 mt-md-0">
            <div class="position-relative me-2">
                <select class="form-select border-0 shadow-sm rounded-pill px-4" id="warehouseFilter" style="min-width: 220px; height: 45px; appearance: auto;">
                    <option value="all">📊 แสดงคลังทั้งหมด</option>
                    <option value="it">💻 แผนกผู้ป่วยนอก (OPD)</option>
                    <option value="maint">🔧 แผนกฉุกเฉิน (ER)</option>
                    <option value="central">📦 คลังพัสดุกลาง (รพ.)</option>
                </select>
            </div>
            
            <div class="btn-group shadow-sm bg-white rounded-pill p-1">
                <button class="btn btn-sm btn-white border-0 rounded-pill px-3 active-mode" data-mode="monthly">รายเดือน</button>
                <button class="btn btn-sm btn-white border-0 rounded-pill px-3" data-mode="daily">รายวัน</button>
            </div>
            <a href="<?= Url::to(['/inventory-v2/default/stock-inbound']) ?>" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-plus-lg"></i> รับสินค้า</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 border-top border-primary border-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">ใบเบิกที่รอการจ่าย</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-primary" id="kpi-pending">18</h2>
                        <span class="text-muted mb-1">ฉบับ</span>
                    </div>
                    <div class="mt-3 py-1 px-2 bg-primary-subtle rounded-3 d-inline-block">
                        <small class="text-primary fw-bold"><i class="bi bi-clock-history"></i> อัปเดตล่าสุดวันนี้</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">รายการที่ต้องสั่งเพิ่ม</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-danger" id="kpi-critical">42</h2>
                        <span class="text-muted mb-1">รายการ</span>
                    </div>
                    <div class="mt-3"><a href="#" class="text-danger text-decoration-none small fw-bold">ดูรายการวิกฤต <i class="bi bi-arrow-right"></i></a></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body p-4">
                    <p class="text-white-50 small mb-1 fw-bold">มูลค่าพัสดุในคลัง</p>
                    <div class="d-flex align-items-end gap-2">
                        <h2 class="fw-bold mb-0 text-white" id="kpi-value">2.48M</h2>
                        <span class="text-white-50 mb-1">฿</span>
                    </div>
                    <div class="mt-3 small text-warning"><i class="bi bi-lightning-fill"></i> รวมมูลค่าทุก Lot</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1 fw-bold">พื้นที่จัดเก็บที่ใช้ไป</p>
                    <h2 class="fw-bold mb-0 text-dark" id="kpi-usage">85%</h2>
                    <div class="progress mt-3" style="height: 8px; border-radius: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%" id="usage-progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">แนวโน้มการเบิกจ่าย (<span id="chart-title">ทุกคลัง</span>)</h5>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="dropdown">ส่งออก <i class="bi bi-download ms-1"></i></button>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="#">PDF</a></li>
                            <li><a class="dropdown-item" href="#">Excel</a></li>
                        </ul>
                    </div>
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
                        <div class="list-group-item border-0 p-3 mb-2 rounded-4 hover-action bg-white shadow-xs border-start border-4 border-warning">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge text-bg-light text-dark mb-1">REQ-6702-001</span>
                                    <h6 class="fw-bold mb-0">แผนกผู้ป่วยนอก (OPD)</h6>
                                </div>
                                <small class="text-muted">10 นาทีที่แล้ว</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="bi bi-box me-1"></i> 5 รายการ</span>
                                <button class="btn btn-primary btn-sm rounded-pill px-3">จัดของจ่าย</button>
                            </div>
                        </div>
                        <div class="list-group-item border-0 p-3 mb-2 rounded-4 hover-action bg-white shadow-xs border-start border-4 border-info">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge text-bg-light text-dark mb-1">REQ-6702-005</span>
                                    <h6 class="fw-bold mb-0">ซ่อมบำรุง (MT)</h6>
                                </div>
                                <small class="text-muted">2 ชม. ที่แล้ว</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="bi bi-box me-1"></i> 12 รายการ</span>
                                <button class="btn btn-primary btn-sm rounded-pill px-3">จัดของจ่าย</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <a href="#" class="text-decoration-none small text-muted">ดูรายการค้างจ่ายทั้งหมด <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .hover-action { transition: all 0.2s ease-in-out; border: 1px solid transparent; }
    .hover-action:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.08); background: #fff !important; cursor: pointer; }
    .active-mode { background: #0d6efd !important; color: white !important; font-weight: bold; }
    .form-select:focus { outline: none; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); }
    .breadcrumb-item + .breadcrumb-item::before { content: "›"; font-size: 1.2rem; line-height: 1; vertical-align: middle; }
</style>

<?php
$js = <<< JS
$(document).ready(function() {
    
    // --- 1. กราฟหลัก (ApexCharts) ---
    var options = {
        series: [{
            name: 'ไอที',
            data: [44, 55, 41, 67, 22, 43, 21]
        }, {
            name: 'ซ่อมบำรุง',
            data: [13, 23, 20, 8, 13, 27, 33]
        }, {
            name: 'พัสดุกลาง',
            data: [11, 17, 15, 15, 21, 14, 15]
        }],
        chart: {
            type: 'bar',
            height: 380,
            stacked: true,
            toolbar: { show: false },
            fontFamily: 'Sarabun'
        },
        plotOptions: {
            bar: { borderRadius: 10, columnWidth: '30%' }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
            axisBorder: { show: false }
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        fill: { opacity: 1 },
        colors: ['#0d6efd', '#6610f2', '#6c757d'],
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
    };

    var chart = new ApexCharts(document.querySelector("#mainWarehouseChart"), options);
    chart.render();

    // --- 2. Logic การเลือกคลัง (Warehouse Filter) ---
    $('#warehouseFilter').on('change', function() {
        let val = $(this).val();
        let name = $("#warehouseFilter option:selected").text().replace(/💻 |🔧 |📦 |📊 /g, "");
        
        // จำลองการเปลี่ยนข้อมูล (ในงานจริงให้ใช้ AJAX)
        $('#breadcrumb-current').text(name);
        $('#chart-title').text(name);

        if(val === 'it') {
            $('#kpi-pending').text('5');
            $('#kpi-value').text('0.45M');
            chart.updateSeries([{ name: 'ไอที', data: [44, 55, 41, 67, 22, 43, 21] }]);
        } else if(val === 'all') {
            location.reload(); // รีโหลดเพื่อกลับมาที่หน้าหลักรวม
        } else {
            $('#kpi-pending').text('12');
            $('#kpi-value').text('1.20M');
        }
        
        // เอฟเฟกต์การเปลี่ยนข้อมูล
        $('.card').addClass('animate__animated animate__fadeIn');
        setTimeout(() => $('.card').removeClass('animate__animated animate__fadeIn'), 1000);
    });

    // ปุ่มสลับรายวัน/เดือน
    $('.btn-group .btn').click(function() {
        $('.btn-group .btn').removeClass('active-mode');
        $(this).addClass('active-mode');
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>