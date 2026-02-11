<style>

    .select-step { border-left: 5px solid #0d6efd; }
</style>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-cart-plus-fill"></i> สร้างใบเบิกพัสดุใหม่</h5>
        </div>
        <div class="card-body">
            
            <div class="row g-3 mb-4 p-3 bg-primary-soft rounded select-step">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-house-up"></i> 1. เลือกคลังหลัก (เบิกจาก...)</label>
                    <select class="form-select border-primary shadow-sm" id="mainWarehouseSelector">
                        <option value="">-- กรุณาเลือกคลังหลักที่มีพัสดุ --</option>
                        <option value="WH01">คลังพัสดุการแพทย์ (Medical Main Stock)</option>
                        <option value="WH02">คลังพัสดุทั่วไป (General Main Stock)</option>
                        <option value="WH03">คลังพัสดุคอมพิวเตอร์ (IT Main Stock)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-house-down"></i> 2. หน่วยงานผู้เบิก (คลังย่อย)</label>
                    <select class="form-select border-secondary shadow-sm" id="subWarehouse">
                        <option value="SUB01">แผนกฉุกเฉิน (ER)</option>
                        <option value="SUB02">แผนกผู้ป่วยนอก (OPD)</option>
                        <option value="SUB03">รพ.สต. บ้านโพธิ์</option>
                    </select>
                </div>
            </div>

            <div id="requestSection" style="display: none;">
                <div class="row g-2 mb-3 align-items-end p-3 bg-light rounded border">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">3. เลือกวัสดุที่ต้องการ</label>
                        <select class="form-select" id="itemSelector">
                            </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">จำนวน</label>
                        <input type="number" class="form-control text-center" id="itemQty" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btnAddItem"><i class="bi bi-plus-lg"></i> เพิ่มรายการ</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border" id="reqTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>รายการพัสดุ</th>
                                <th width="15%" class="text-center">จำนวนที่เบิก</th>
                                <th width="10%">หน่วย</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-4 text-muted">ยังไม่มีรายการพัสดุในใบเบิกนี้</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-outline-secondary me-2">ยกเลิก</button>
                    <button class="btn btn-success btn-lg px-5 shadow" id="btnSubmit">ส่งใบเบิกไปที่คลังหลัก</button>
                </div>
            </div>

        </div>
    </div>
</div>


<?php

use yii\web\View;

$js = <<< JS
$(document).ready(function() {
    
    // 1. เมื่อเลือกคลังหลัก
    $('#mainWarehouseSelector').change(function() {
        const whId = $(this).val();
        const whName = $(this).find('option:selected').text();

        if (whId !== "") {
            // แสดงส่วนเลือกสินค้า
            $('#requestSection').slideDown();
            
            // จำลองการดึงสินค้าจากคลังหลักที่เลือก (Mockup Data)
            let itemsHtml = '<option value="">-- เลือกวัสดุจาก ' + whName + ' --</option>';
            if(whId === "WH01") {
                itemsHtml += '<option value="1" data-name="ถุงมือตรวจโรค" data-unit="กล่อง">ถุงมือตรวจโรค (คงเหลือ 500)</option>';
                itemsHtml += '<option value="2" data-name="หน้ากาก N95" data-unit="ชิ้น">หน้ากาก N95 (คงเหลือ 1,200)</option>';
            } else if(whId === "WH02") {
                itemsHtml += '<option value="3" data-name="กระดาษ A4" data-unit="รีม">กระดาษ A4 (คงเหลือ 200)</option>';
                itemsHtml += '<option value="4" data-name="ปากกาลูกลื่น" data-unit="ด้าม">ปากกาลูกลื่น (คงเหลือ 50)</option>';
            }
            $('#itemSelector').html(itemsHtml);
            
            // ล้างรายการในตารางถ้ามีการเปลี่ยนคลัง
            $('#reqTable tbody .item-row').remove();
            $('#emptyRow').show();
        } else {
            $('#requestSection').slideUp();
        }
    });

    // 2. เมื่อกดปุ่มเพิ่มรายการ
    $('#btnAddItem').click(function() {
        const selected = $('#itemSelector option:selected');
        const id = selected.val();
        const name = selected.data('name');
        const unit = selected.data('unit');
        const qty = $('#itemQty').val();

        if (!id) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือกวัสดุก่อนครับ', 'warning');
            return;
        }

        $('#emptyRow').hide();
        
        const rowCount = $('#reqTable tbody tr.item-row').length + 1;
        const row = `
            <tr class="item-row">
                <td>\${rowCount}</td>
                <td class="fw-bold">\${name}</td>
                <td><input type="number" class="form-control form-control-sm text-center mx-auto" style="width:80px" value="\${qty}"></td>
                <td>\${unit}</td>
                <td><button class="btn btn-sm btn-outline-danger btn-del"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#reqTable tbody').append(row);
    });

    // 3. ลบแถว
    $(document).on('click', '.btn-del', function() {
        $(this).closest('tr').remove();
        if ($('#reqTable tbody tr.item-row').length === 0) {
            $('#emptyRow').show();
        }
    });

    // 4. บันทึกใบเบิก
    $('#btnSubmit').click(function() {
        if ($('#reqTable tbody tr.item-row').length === 0) {
            Swal.fire('ข้อผิดพลาด', 'ยังไม่มีรายการเบิกครับ', 'error');
            return;
        }

        const sourceWH = $('#mainWarehouseSelector option:selected').text();
        const targetDept = $('#subWarehouse option:selected').text();

        Swal.fire({
            title: 'ยืนยันการส่งใบเบิก?',
            html: `เบิกจาก: <b>\${sourceWH}</b><br>ไปยัง: <b>\{targetDept}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันส่งใบเบิก',
            cancelButtonText: 'แก้ไขข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('ส่งใบเบิกสำเร็จ!', 'ระบบส่งรายการให้คลังหลักเพื่อรอจ่ายของแล้ว', 'success')
                .then(() => location.reload());
            }
        });
    });

});
JS;
$this->registerJS($js,View::POS_READY);
?>
