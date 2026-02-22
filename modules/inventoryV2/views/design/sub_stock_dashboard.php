<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\View;

// ลงทะเบียน ApexCharts และ Bootstrap Icons
$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);
?>

<div class="card">
    <div class="card-body">
<div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1 text-dark">ระบบจัดการคลังย่อย: <span class="text-primary">แผนกไอที / ซ่อมบำรุง</span></h4>
                <p class="text-muted mb-0"><i class="bi bi-clock"></i> ข้อมูลอัปเดตล่าสุด: <?= date('d/m/Y H:i') ?></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-white border shadow-sm"><i class="bi bi-printer"></i> พิมพ์รายงาน</button>
                <a href="<?= Url::to(['/inventory-v2/default/sub-stock-issue']) ?>" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> บันทึกการใช้งาน</a>
            </div>
        </div>
    </div>
    </div>
</div>


<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1 fw-bold">รอตรวจรับเข้า</p>
                        <h3 class="fw-bold mb-0">5 <small class="h6 text-muted">ใบ</small></h3>
                    </div>
                    <div class="icon-box bg-info-subtle rounded-circle p-3">
                        <i class="bi bi-truck text-info h4 mb-0"></i>
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-info-subtle text-info">จากคลังหลัก</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1 fw-bold">พัสดุต่ำกว่าจุดวิกฤต</p>
                        <h3 class="fw-bold mb-0 text-danger">12 <small class="h6 text-muted">รายการ</small></h3>
                    </div>
                    <div class="icon-box bg-danger-subtle rounded-circle p-3">
                        <i class="bi bi-exclamation-triangle text-danger h4 mb-0"></i>
                    </div>
                </div>
                <div class="mt-2"><a href="#" class="small text-danger fw-bold">รีบกดเบิกทันที <i class="bi bi-arrow-right"></i></a></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1 fw-bold">มูลค่าเบิกใช้เดือนนี้</p>
                        <h3 class="fw-bold mb-0">45,200 <small class="h6 text-muted">฿</small></h3>
                    </div>
                    <div class="icon-box bg-success-subtle rounded-circle p-3">
                        <i class="bi bi-wallet2 text-success h4 mb-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-success small fw-bold"><i class="bi bi-graph-up"></i> +12% จากเดือนก่อน</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1 fw-bold">วัสดุหมดอายุ (30 วัน)</p>
                        <h3 class="fw-bold mb-0 text-warning">2 <small class="h6 text-muted">Lot</small></h3>
                    </div>
                    <div class="icon-box bg-warning-subtle rounded-circle p-3">
                        <i class="bi bi-hourglass-split text-warning h4 mb-0"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">ควรนำออกมาใช้งานก่อน</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0">แนวโน้มการตัดจ่ายพัสดุในแผนก (7 วันล่าสุด)</h6>
            </div>
            <div class="card-body">
                <div id="usageApexChart"></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0 text-primary">รายการส่งของจากคลังหลัก</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-3 py-3 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded p-2 me-3"><i class="bi bi-file-earmark-text"></i></div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-bold">ISS-670045</h6>
                                <small class="text-muted">8 รายการ | โดย: นายสมชาย</small>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill">ตรวจรับ</button>
                        </div>
                    </li>
                    <li class="list-group-item px-3 py-3 border-0 border-top">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded p-2 me-3"><i class="bi bi-file-earmark-text"></i></div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-bold">ISS-670048</h6>
                                <small class="text-muted">3 รายการ | โดย: นางสาวสวย</small>
                            </div>
                            <?php echo Html::a('ตรวจรับ', ['sub-stock-receiving'], ['class' => 'btn btn-outline-primary btn-sm rounded-pill']) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-3">
                <a href="#" class="small text-decoration-none">ดูรายการค้างรับทั้งหมด</a>
            </div>
        </div>
    </div>
</div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {
    
    // 1. คำนวณวันที่ย้อนหลัง (แก้บั๊กชื่อวันไม่ตรง)
    const last7Days = [];
    for (let i = 6; i >= 0; i--) {
        let d = new Date();
        d.setDate(d.getDate() - i);
        last7Days.push(d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }));
    }

    // 2. ข้อมูลจำลอง (Mockup)
    const options = {
        series: [{
            name: 'จำนวนชิ้นที่จ่ายออก',
            data: [44, 55, 31, 47, 31, 43, 26]
        }],
        chart: {
            height: 320,
            type: 'area',
            toolbar: { show: false },
            fontFamily: 'inherit',
            dropShadow: { enabled: true, top: 10, left: 0, blur: 3, color: '#000', opacity: 0.1 }
        },
        colors: ['#0d6efd'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            categories: last7Days,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
        tooltip: { theme: 'light' }
    };

    const chart = new ApexCharts(document.querySelector("#usageApexChart"), options);
    chart.render();

});
JS;
$this->registerJS($js, View::POS_READY);
?>


<button class="btn btn-outline-primary btn-sm rounded-pill"
    data-bs-toggle="modal"
    data-bs-target="#receiveModal"
    onclick="loadReceiveData('ISS-670045')">
    ตรวจรับ
</button>

<div class="modal fade border-0" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i> ตรวจรับพัสดุเข้าคลังย่อย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <span class="text-muted">เลขที่ใบส่ง:</span> <strong id="m-doc-no">ISS-670045</strong>
                    </div>
                    <div>
                        <span class="text-muted">คลังต้นทาง:</span> <strong id="m-from">คลังพัสดุกลาง</strong>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle border-top">
                        <thead class="sticky-top bg-white">
                            <tr class="small text-muted text-uppercase">
                                <th>รายการ</th>
                                <th class="text-center">Lot</th>
                                <th class="text-center">ส่งมา</th>
                                <th class="text-center" width="150">รับจริง</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody id="receive-list-body">
                            <tr>
                                <td><strong>SSD 500GB Samsung</strong></td>
                                <td class="text-center font-monospace small">67-01</td>
                                <td class="text-center">5</td>
                                <td><input type="number" class="form-control text-center border-primary" value="5"></td>
                                <td><input type="text" class="form-control form-control-sm border-light" placeholder="..."></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 shadow" id="btnConfirmModal">ยืนยันรับเข้าสต็อก</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
function loadReceiveData(id) {
    // ในความเป็นจริงคุณจะใช้ $.ajax({ url: 'get-data?id=' + id })
    console.log('กำลังโหลดข้อมูลใบส่งของ: ' + id);
    $('#m-doc-no').text(id);
}

$('#btnConfirmModal').click(function() {
    Swal.fire({
        title: 'ยืนยันการรับ?',
        text: "สต็อกในคลังย่อยจะถูกเพิ่มทันที",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        confirmButtonColor: '#0d6efd',
    }).then((result) => {
        if (result.isConfirmed) {
            $('#receiveModal').modal('hide');
            Swal.fire('สำเร็จ!', 'รับพัสดุเข้าคลังย่อยเรียบร้อย', 'success');
        }
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>