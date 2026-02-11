<?php
use yii\web\View;
$this->title = 'ดำเนินการจ่ายพัสดุ (Issue Process)';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam"></i> บันทึกการจ่ายพัสดุ: REQ67-0045</h5>
            <span class="badge bg-primary">คลังต้นทาง: คลังพัสดุกลาง</span>
        </div>
        <div class="card-body">
            <div class="row mb-4 bg-light p-3 rounded mx-0">
                <div class="col-md-4">
                    <small class="text-muted d-block">หน่วยงานที่เบิก</small>
                    <strong class="h6">แผนกไอที (IT Department)</strong>
                </div>
                <div class="col-md-4 text-center border-start border-end">
                    <small class="text-muted d-block">อ้างอิงใบแจ้งซ่อม/โครงการ</small>
                    <strong>JOB-IT-2026-001</strong>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block">วันที่เบิก</small>
                    <strong>11 ก.พ. 2569</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="issueTable">
                    <thead class="table-secondary">
                        <tr class="text-center">
                            <th width="5%">#</th>
                            <th width="30%" class="text-start">รายการพัสดุ</th>
                            <th width="12%">จำนวนที่ขอ</th>
                            <th width="15%">จำนวนที่จ่ายจริง</th>
                            <th width="25%">ตัดจาก Lot (คลังหลัก)</th>
                            <th width="13%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row" data-id="101">
                            <td class="text-center">1</td>
                            <td>
                                <strong>SSD 500GB Samsung</strong><br>
                                <small class="text-muted">รหัสพัสดุ: IT-0023</small>
                            </td>
                            <td class="text-center h6">5</td>
                            <td>
                                <input type="number" class="form-control text-center fw-bold border-primary qty-issued" 
                                       value="5" min="0" max="5">
                            </td>
                            <td>
                                <select class="form-select border-warning lot-selector">
                                    <option value="L67-01" data-stock="10">LOT: 67-01 (คงเหลือ 10) [FEFO]</option>
                                    <option value="L67-05" data-stock="50">LOT: 67-05 (คงเหลือ 50)</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger btn-sm btn-cancel-item" title="ยกเลิกรายการนี้">
                                    <i class="bi bi-x-lg"></i> ยกเลิก
                                </button>
                                <button class="btn btn-link btn-sm btn-restore-item d-none">เรียกคืน</button>
                            </td>
                        </tr>

                        <tr class="item-row" data-id="102">
                            <td class="text-center">2</td>
                            <td><strong>สาย LAN CAT6 (300m)</strong></td>
                            <td class="text-center h6">2</td>
                            <td>
                                <input type="number" class="form-control text-center fw-bold border-primary qty-issued" 
                                       value="1" min="0" max="2">
                                <small class="text-danger">จ่ายไม่ครบ (ค้าง 1)</small>
                            </td>
                            <td>
                                <select class="form-select border-warning lot-selector">
                                    <option value="L-CAB-99">LOT: CAB-99 (เหลือ 1)</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger btn-sm btn-cancel-item"><i class="bi bi-x-lg"></i> ยกเลิก</button>
                                <button class="btn btn-link btn-sm btn-restore-item d-none">เรียกคืน</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 h4 mb-0"></i>
                <div>
                    <strong>หมายเหตุการจ่าย:</strong> รายการที่ถูกยกเลิก (Cancel) จะไม่ถูกหักสต็อก และจะถูกบันทึกสถานะเป็น "ไม่ได้จ่ายสินค้า" ในระบบ
                </div>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-light border me-2 px-4">กลับหน้าหลัก</button>
                <button class="btn btn-success btn-lg px-5 shadow-sm" id="btnSubmitIssue">
                    <i class="bi bi-check-all"></i> บันทึกการจ่ายพัสดุ
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {

    // 1. จัดการการยกเลิกรายการ (Item Cancellation)
    $(document).on('click', '.btn-cancel-item', function() {
        let row = $(this).closest('tr');
        
        row.find('td').css('text-decoration', 'line-through');
        row.find('td').addClass('text-muted bg-light');
        row.find('.qty-issued').val(0).prop('disabled', true);
        row.find('.lot-selector').prop('disabled', true);
        
        $(this).addClass('d-none');
        row.find('.btn-restore-item').removeClass('d-none');
        
        checkButtonStatus();
    });

    // 2. เรียกคืนรายการที่ยกเลิก
    $(document).on('click', '.btn-restore-item', function() {
        let row = $(this).closest('tr');
        let requestedQty = row.find('td:eq(2)').text();

        row.find('td').css('text-decoration', 'none').removeClass('text-muted bg-light');
        row.find('.qty-issued').val(requestedQty).prop('disabled', false);
        row.find('.lot-selector').prop('disabled', false);
        
        $(this).addClass('d-none');
        row.find('.btn-cancel-item').removeClass('d-none');
        
        checkButtonStatus();
    });

    // 3. ตรวจสอบสถานะปุ่มบันทึก
    function checkButtonStatus() {
        let activeItems = $('.qty-issued:not(:disabled)').filter(function() {
            return $(this).val() > 0;
        }).length;
        
        // ถ้าไม่มีรายการที่จะจ่ายเลย (ยกเลิกหมด) อาจจะเตือนหรือปิดปุ่ม
        if(activeItems === 0) {
            $('#btnSubmitIssue').addClass('btn-secondary').removeClass('btn-success');
        } else {
            $('#btnSubmitIssue').addClass('btn-success').removeClass('btn-secondary');
        }
    }

    // 4. กดยืนยันบันทึก
    $('#btnSubmitIssue').click(function() {
        Swal.fire({
            title: 'ยืนยันการบันทึก?',
            text: "ระบบจะตัดสต็อกตาม 'จำนวนจ่ายจริง' และเปลี่ยนสถานะรายการที่ยกเลิก",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'ยืนยันตัดสต็อก'
        }).then((result) => {
            if (result.isConfirmed) {
                // Logic ส่ง Ajax ไปที่ Controller ของคุณ
                Swal.fire('สำเร็จ!', 'บันทึกข้อมูลและอัปเดตสต็อกเรียบร้อย', 'success')
                .then(() => {
                    // location.reload();
                });
            }
        });
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>