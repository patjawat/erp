<?php
use yii\web\View;
$this->title = 'ระบบตัดจ่ายพัสดุอเนกประสงค์';
?>



<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 border-top border-4 border-primary">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-box-arrow-up-right"></i> บันทึกการจ่ายพัสดุ/การใช้งาน</h5>
            <span class="badge bg-primary bg-opacity-10 border border-1 border-primary text-primary">คลังที่ดำเนินการ: <span id="currentWarehouse">ห้องยา/คลังเวชภัณฑ์</span></span>
        </div>
        <div class="card-body">
            
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label fw-bold">ประเภทงาน/การเบิก</label>
                    <select class="form-select border-primary" id="jobType">
                        <option value="patient">งานคลินิก (ตัดจ่ายรายคนไข้)</option>
                        <option value="maintenance">งานซ่อมบำรุง/ไอที (ตัดจ่ายตาม Job)</option>
                        <option value="office">งานบริหาร/บัญชี (เบิกใช้ในสำนักงาน)</option>
                        <option value="emergency">งานอุบัติเหตุ/ฉุกเฉิน (เบิกเติม Unit Stock)</option>
                    </select>
                </div>

                <div class="col-md-5" id="dynamicFieldContainer">
                    <label class="form-label fw-bold" id="dynamicLabel">HN / ชื่อคนไข้</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="referenceInput" placeholder="ค้นหาข้อมูลอ้างอิง...">
                        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">ศูนย์ต้นทุน (Cost Center)</label>
                    <input type="text" class="form-control bg-white" id="costCenter" readonly value="70101-ห้องยา">
                </div>
            </div>

            <div class="row g-2 mb-3 align-items-end border-bottom pb-4">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">ค้นหาวัสดุ/เลือก Lot (FEFO)</label>
                    <select class="form-select select2" id="stockItemSelector">
                        <option value="">-- พิมพ์ชื่อพัสดุเพื่อค้นหา --</option>
                        <optgroup label="วัสดุการแพทย์ (คลังยา/ER)">
                            <option data-name="Paracetamol 500mg" data-lot="LOT-001" data-stock="1000" data-unit="เม็ด">Paracetamol | Lot: LOT-001 | เหลือ: 1000</option>
                        </optgroup>
                        <optgroup label="วัสดุไอที/ซ่อมบำรุง">
                            <option data-name="SSD 500GB" data-lot="IT-67-A" data-stock="5" data-unit="ชิ้น">SSD 500GB | Lot: IT-67-A | เหลือ: 5</option>
                            <option data-name="หลอดไฟ LED 18W" data-lot="MT-22" data-stock="20" data-unit="หลอด">หลอดไฟ LED | Lot: MT-22 | เหลือ: 20</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">จำนวนที่ใช้</label>
                    <input type="number" class="form-control text-center" id="inputQty" value="1">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" id="btnAddToList"><i class="bi bi-plus-circle"></i> เพิ่มรายการ</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle border" id="disburseTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">รายการพัสดุ</th>
                            <th width="15%">Lot</th>
                            <th width="15%" class="text-center">จำนวน</th>
                            <th width="10%">หน่วย</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="noDataRow"><td colspan="6" class="text-center py-4 text-muted">ยังไม่มีรายการตัดจ่าย</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-light me-2">พิมพ์ใบเบิก/จ่าย</button>
                <button class="btn btn-primary btn-lg px-5" id="btnSaveFinal">ยืนยันการบันทึกรายการ</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {

    // 1. Logic การเปลี่ยน UI ตามประเภทงาน
    $('#jobType').change(function() {
        let type = $(this).val();
        let label = "";
        let placeholder = "";

        switch(type) {
            case 'patient':
                label = "HN / ชื่อคนไข้";
                placeholder = "สแกนบาร์โค้ดคนไข้...";
                break;
            case 'maintenance':
                label = "เลขที่ใบแจ้งซ่อม (Job Order)";
                placeholder = "เช่น IT67-001 หรือ MT-05...";
                break;
            case 'office':
                label = "รหัสผู้เบิก/โครงการ";
                placeholder = "ชื่อเจ้าหน้าที่ หรือ ชื่อโครงการ...";
                break;
            default:
                label = "อ้างอิงการเบิก";
                placeholder = "ระบุรายละเอียด...";
        }
        
        $('#dynamicLabel').text(label);
        $('#referenceInput').attr('placeholder', placeholder).val('');
    });

    // 2. เพิ่มรายการลงตาราง
    $('#btnAddToList').click(function() {
        let selected = $('#stockItemSelector option:selected');
        if(!selected.val()) return;

        $('#noDataRow').hide();
        let name = selected.data('name');
        let lot = selected.data('lot');
        let unit = selected.data('unit');
        let qty = $('#inputQty').val();

        let row = `
            <tr class="item-row">
                <td class="text-center">#</td>
                <td><b>\${name}</b></td>
                <td class="text-center fw-bold text-primary">\${lot}</td>
                <td class="text-center">\${qty}</td>
                <td>\${unit}</td>
                <td><button class="btn btn-sm btn-link text-danger btn-remove"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#disburseTable tbody').append(row);
    });

    $(document).on('click', '.btn-remove', function() {
        $(this).closest('tr').remove();
        if($('#disburseTable tbody tr.item-row').length === 0) $('#noDataRow').show();
    });

    // 3. บันทึกข้อมูล
    $('#btnSaveFinal').click(function() {
        let job = $('#jobType option:selected').text();
        let ref = $('#referenceInput').val() || "ไม่ได้ระบุ";

        Swal.fire({
            title: 'ยืนยันการตัดสต็อก?',
            html: `งาน: <b>\${job}</b><br>อ้างอิง: <b>\${ref}</b>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'บันทึกและตัดสต็อก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('สำเร็จ!', 'สต็อกถูกหักยอดเรียบร้อยแล้ว', 'success').then(() => location.reload());
            }
        });
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>
