<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="bi bi-wrench-adjustable"></i> ปรับปรุงยอดสินค้าคงคลัง (Stock Adjustment)</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">1. เลือกวัสดุและ Lot ที่ต้องการปรับปรุง</label>
                    <select class="form-select select2" id="adjItemSelector">
                        <option value="">-- ค้นหาวัสดุ (ชื่อ, Lot Number) --</option>
                        <option data-id="1" data-name="ถุงมือตรวจโรค" data-lot="LOT67-001" data-stock="100" data-unit="กล่อง">
                            ถุงมือตรวจโรค (Size M) | Lot: LOT67-001 | คงเหลือ: 100
                        </option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle d-none" id="adjTable">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>รายการ/Lot</th>
                            <th width="15%">จำนวนเดิม</th>
                            <th width="15%">ประเภทการปรับ</th>
                            <th width="15%">จำนวนที่ปรับ</th>
                            <th width="15%">ยอดใหม่</th>
                            <th width="20%">เหตุผลการปรับปรุง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong id="adjName"></strong><br>
                                <small class="text-primary">Lot: <span id="adjLot"></span></small>
                            </td>
                            <td class="text-center"><span id="currQty" class="h5">0</span></td>
                            <td>
                                <select class="form-select" id="adjType">
                                    <option value="plus">เพิ่มพัสดุ (+)</option>
                                    <option value="minus">ลดพัสดุ (-)</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control text-center fw-bold border-primary" id="adjAmount" value="0">
                            </td>
                            <td class="text-center">
                                <span id="newQty" class="h5 text-primary fw-bold">0</span>
                            </td>
                            <td>
                                <select class="form-select" id="adjReason">
                                    <option value="">-- ระบุเหตุผล --</option>
                                    <option>คีย์ยอดรับเข้าผิด</option>
                                    <option>สินค้าชำรุด/แตกหัก</option>
                                    <option>พบสินค้าขาด/เกินจากการนับสต็อก</option>
                                    <option>สินค้าหมดอายุ (เสื่อมสภาพ)</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-secondary me-2">ยกเลิก</button>
                <button class="btn btn-dark px-4" id="btnSaveAdj" disabled>บันทึกการปรับปรุงยอด</button>
            </div>
        </div>
    </div>
</div>
<?php

use yii\web\View;

$js = <<< JS

    $(document).ready(function() {
    // เมื่อเลือกพัสดุ
    $('#adjItemSelector').change(function() {
        let opt = $(this).find(':selected');
        if(opt.val() != "") {
            $('#adjTable').removeClass('d-none');
            $('#adjName').text(opt.data('name'));
            $('#adjLot').text(opt.data('lot'));
            $('#currQty').text(opt.data('stock'));
            updateNewQty();
            $('#btnSaveAdj').prop('disabled', false);
        }
    });

    // เมื่อเปลี่ยนประเภทหรือจำนวน
    $('#adjType, #adjAmount').on('input change', function() {
        updateNewQty();
    });

    function updateNewQty() {
        let current = parseInt($('#currQty').text());
        let amount = parseInt($('#adjAmount').val()) || 0;
        let type = $('#adjType').val();
        let result = (type === 'plus') ? current + amount : current - amount;

        $('#newQty').text(result);
        
        // UX: แจ้งเตือนถ้าติดลบ
        if(result < 0) {
            $('#newQty').addClass('text-danger').removeClass('text-primary');
            $('#btnSaveAdj').prop('disabled', true);
        } else {
            $('#newQty').addClass('text-primary').removeClass('text-danger');
            $('#btnSaveAdj').prop('disabled', false);
        }
    }

    $('#btnSaveAdj').click(function() {
        if($('#adjReason').val() === "") {
            Swal.fire('กรุณาระบุเหตุผล', 'การปรับปรุงสต็อกต้องมีเหตุผลประกอบเสมอ', 'error');
            return;
        }

        Swal.fire({
            title: 'ยืนยันการปรับปรุงสต็อก?',
            text: "ยอดคงเหลือใน Lot นี้จะถูกเปลี่ยนเป็น " + $('#newQty').text(),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#212529',
            confirmButtonText: 'ยืนยัน'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('สำเร็จ!', 'อัปเดตยอดคงเหลือเรียบร้อย', 'success').then(() => location.reload());
            }
        });
    });
});
JS;
$this->registerJS($js,View::POS_READY);
?>