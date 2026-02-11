<?php
use yii\web\View;

$this->title = 'รายการใบเบิกพัสดุ (Stock Issue List)';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-list-ul"></i> รายการใบเบิกจากคลังย่อย</h5>
            <div class="btn-group">
                <button class="btn btn-outline-primary active">ทั้งหมด</button>
                <button class="btn btn-outline-warning">รอจ่าย</button>
                <button class="btn btn-outline-success">จ่ายแล้ว</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="ค้นหาเลขที่ใบเบิก/หน่วยงาน...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select">
                        <option value="">ทุกคลังย่อย</option>
                        <option value="ER">แผนกฉุกเฉิน (ER)</option>
                        <option value="OPD">แผนกผู้ป่วยนอก (OPD)</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="issueTable">
                    <thead class="table-light">
                        <tr>
                            <th width="12%">วันที่เบิก</th>
                            <th width="15%">เลขที่ใบเบิก</th>
                            <th width="20%">หน่วยงานที่เบิก</th>
                            <th width="25%">รายการย่อ</th>
                            <th width="13%" class="text-center">สถานะ</th>
                            <th width="15%" class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>11/02/2026</td>
                            <td><span class="fw-bold">REQ67-0045</span></td>
                            <td>แผนกฉุกเฉิน (ER)</td>
                            <td>ถุงมือตรวจโรค, หน้ากาก N95...</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-warning text-dark">รอคลังหลักจ่าย</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-primary btn-sm btn-issue-process" data-id="REQ67-0045">
                                    <i class="bi bi-box-seam"></i> ดำเนินการจ่าย
                                </a>
                            </td>
                        </tr>
                        <tr class="text-muted">
                            <td>10/02/2026</td>
                            <td>REQ67-0040</td>
                            <td>รพ.สต. บ้านโพธิ์</td>
                            <td>สำลีพันปลายไม้, แอลกอฮอล์...</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-success">จ่ายพัสดุแล้ว</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-file-earmark-text"></i> ดูใบจ่าย
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {

    // เมื่อกดปุ่ม "ดำเนินการจ่าย"
    $('.btn-issue-process').click(function(e) {
        e.preventDefault();
        let docNo = $(this).data('id');
        
        Swal.fire({
            title: 'เตรียมจ่ายพัสดุ?',
            text: "ระบบจะพาคุณไปหน้าเลือก Lot สินค้าสำหรับใบเบิก " + docNo,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'ไปหน้าจ่ายพัสดุ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ใน Yii2 คุณจะใช้การ redirect ไปยัง action ที่เราทำไว้ก่อนหน้านี้
                // window.location.href = '/inventory/stock-issue?id=' + docNo;
                Swal.fire('กำลังย้ายหน้า...', 'Redirecting to issue process', 'success');
            }
        });
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>