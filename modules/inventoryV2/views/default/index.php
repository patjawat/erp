<?php
use yii\web\View;
$this->title = 'Main Inventory Control Center';

// ลงทะเบียน ApexCharts
$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);
?>

<div class="container-fluid py-4 bg-light" style="font-family: 'Sarabun', sans-serif;">
    
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-end">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Main Warehouse</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 text-dark">ศูนย์ควบคุมคลังหลัก <span class="text-muted fs-6 fw-normal">(Main Warehouse)</span></h3>
            </div>
            <div class="btn-group shadow-sm">
                <button class="btn btn-white border-0 bg-white">รายวัน</button>
                <button class="btn btn-white border-0 bg-white active text-primary">รายเดือน</button>
                <button class="btn btn-primary px-4"><i class="bi bi-plus-circle me-2"></i>รับสินค้าเข้า</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">ใบเบิกที่รอการจ่าย</p>
                            <h2 class="fw-bold mb-0 text-primary">18 <small class="fs-6 fw-normal">ฉบับ</small></h2>
                        </div>
                        <div class="rounded-3 bg-primary-subtle p-3">
                            <i class="bi bi-clipboard-check text-primary h4 mb-0"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="text-danger fw-bold"><i class="bi bi-arrow-up"></i> 3 ใบ</span> จากเมื่อวาน
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">รายการที่ต้องจัดซื้อเพิ่ม</p>
                            <h2 class="fw-bold mb-0 text-danger">42 <small class="fs-6 fw-normal">รายการ</small></h2>
                        </div>
                        <div class="rounded-3 bg-danger-subtle p-3">
                            <i class="bi bi-cart-dash text-danger h4 mb-0"></i>
                        </div>
                    </div>
                    <div class="mt-3"><a href="#" class="text-danger text-decoration-none small fw-bold">เปิดใบสั่งซื้อ (PO) <i class="bi bi-chevron-right"></i></a></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-white bg-dark">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-white-50 small mb-1 fw-bold">มูลค่าพัสดุรวม</p>
                            <h2 class="fw-bold mb-0 text-white">2.48M <small class="fs-6 fw-normal">฿</small></h2>
                        </div>
                        <div class="rounded-3 bg-white-subtle p-3" style="background: rgba(255,255,255,0.1);">
                            <i class="bi bi-currency-bitcoin text-warning h4 mb-0"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-white-50">คำนวณตามราคาต้นทุนเฉลี่ย</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">อัตราพัสดุหมุนเวียน</p>
                            <h2 class="fw-bold mb-0">85%</h2>
                        </div>
                        <div class="rounded-3 bg-success-subtle p-3">
                            <i class="bi bi-arrow-repeat text-success h4 mb-0"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0">แนวโน้มการเบิกจ่ายแยกตามคลังย่อย</h5>
                </div>
                <div class="card-body px-2">
                    <div id="mainWarehouseChart"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary">ใบเบิกล่าสุด</h5>
                    <span class="badge bg-primary rounded-pill">รอจ่าย 18</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush px-2">
                        <div class="list-group-item border-0 p-3 mb-2 rounded-3 hover-bg-light shadow-xs border-start border-4 border-warning">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small">REQ-6702-001</span>
                                <span class="small text-muted">10 นาทีที่แล้ว</span>
                            </div>
                            <div class="small text-dark fw-bold mb-1">แผนกไอที (IT Dept)</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">5 รายการ (SSD, RAM...)</span>
                                <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">จ่ายของ</a>
                            </div>
                        </div>
                        <div class="list-group-item border-0 p-3 mb-2 rounded-3 hover-bg-light shadow-xs border-start border-4 border-warning">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small">REQ-6702-005</span>
                                <span class="small text-muted">2 ชม. ที่แล้ว</span>
                            </div>
                            <div class="small text-dark fw-bold mb-1">แผนกซ่อมบำรุง</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">12 รายการ (หลอดไฟ, น็อต)</span>
                                <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">จ่ายของ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .hover-bg-light:hover { background-color: #f8f9fa; cursor: pointer; transition: 0.3s; }
</style>

<?php
$js = <<< JS
$(document).ready(function() {
    
    // ApexCharts: Stacked Bar Chart สำหรับคลังหลัก
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
            height: 350,
            stacked: true,
            toolbar: { show: false },
            zoom: { enabled: true }
        },
        responsive: [{
            breakpoint: 480,
            options: { legend: { position: 'bottom', offsetX: -10, offsetY: 0 } }
        }],
        plotOptions: {
            bar: { borderRadius: 8, columnWidth: '35%' }
        },
        xaxis: {
            categories: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        fill: { opacity: 1 },
        colors: ['#0d6efd', '#6610f2', '#6c757d'],
    };

    var chart = new ApexCharts(document.querySelector("#mainWarehouseChart"), options);
    chart.render();

});
JS;
$this->registerJS($js, View::POS_READY);
?>