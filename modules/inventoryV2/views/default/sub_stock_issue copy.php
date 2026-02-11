<?php
use yii\web\View;

$this->title = 'บันทึกการใช้พัสดุ/ตัดจ่าย (คลังย่อย)';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 border-top border-4 border-info">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-info fw-bold"><i class="bi bi-person-up"></i> ตัดจ่ายพัสดุสำหรับผู้รับบริการ</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">ประเภทการจ่าย</label>
                    <select class="form-select border-info" id="issueTarget">
                        <option value="patient">จ่ายให้คนไข้ (Patient Case)</option>
                        <option value="unit">จ่ายเพื่อใช้ในหน่วยงาน (Ward Use)</option>
                    </select>
                </div>
                <div class="col-md-5" id="patientField">
                    <label class="form-label fw-bold small text-muted">HN / ชื่อผู้รับบริการ</label>
                    <div class="input-group">
                        <input type="text" class="form-control border-info" placeholder="สแกน HN หรือค้นหาชื่อ...">
                        <button class="btn btn-info text-white"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">วันที่จ่าย</label>
                    <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">เลือกวัสดุจากสต็อก (เลือก Lot)</label>
                    <select class="form-select" id="stockItemSelector">
                        <option value="">-- ค้นหาสิ่งที่จะจ่าย --</option>
                        <option value="S1" data-name="Alcohol 70%" data-lot="LOT-001" data-stock="10" data-unit="ขวด">
                            Alcohol 70% | Lot: LOT-001 | คงเหลือ: 10
                        </option>
                        <option value="S2" data-name="Gauze 2x2" data-lot="GZ-67A" data-stock="45" data-unit="ซอง">
                            Gauze 2x2 | Lot: GZ-67A | คงเหลือ: 45
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">จำนวน</label>
                    <input type="number" class="form-control text-center" id="issueQty" value="1">
                </div>
                <div class="col-md-2 align-self-end">
                    <button class="btn btn-outline-info w-100" id="btnAddIssue"><i class="bi bi-plus-circle"></i> เพิ่ม</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="issueTable">
                    <thead class="table-info">
                        <tr class="text-center">
                            <th>#</th>
                            <th>รายการ</th>
                            <th>Lot Number</th>
                            <th>จำนวน</th>
                            <th>หน่วย</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="emptyRow"><td colspan="6" class="text-center py-4 text-muted">กรุณาเพิ่มรายการเพื่อตัดจ่าย</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-info btn-lg px-5 shadow text-white" id="btnSaveIssue">
                    <i class="bi bi-check2-square"></i> บันทึกตัดสต็อกคลังย่อย
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {

    // เปลี่ยนฟิลด์ตามประเภทการจ่าย
    $('#issueTarget').change(function() {
        if($(this).val() === 'patient') {
            $('#patientField label').text('HN / ชื่อผู้รับบริการ');
        } else {
            $('#patientField label').text('ผู้เบิกภายในหน่วยงาน');
        }
    });

    // เพิ่มรายการตัดจ่าย
    $('#btnAddIssue').click(function() {
        let selected = $('#stockItemSelector option:selected');
        if(!selected.val()) return;

        let name = selected.data('name');
        let lot = selected.data('lot');
        let unit = selected.data('unit');
        let qty = $('#issueQty').val();
        let maxStock = selected.data('stock');

        if(parseInt(qty) > parseInt(maxStock)) {
            Swal.fire('ยอดสต็อกไม่พอ', 'คุณมีในคลังย่อยแค่ ' + maxStock + ' ' + unit, 'error');
            return;
        }

        $('#emptyRow').hide();
        let rowCount = $('#issueTable tbody tr:visible').length + 1;
        let row = `
            <tr class="issue-row">
                <td class="text-center">\${rowCount}</td>
                <td><strong>\${name}</strong></td>
                <td class="text-center font-monospace text-primary">\${lot}</td>
                <td class="text-center fw-bold">\${qty}</td>
                <td class="text-center">\${unit}</td>
                <td class="text-center"><button class="btn btn-sm btn-outline-danger btn-del"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#issueTable tbody').append(row);
    });

    // ลบแถว
    $(document).on('click', '.btn-del', function() {
        $(this).closest('tr').remove();
        if($('#issueTable tbody tr.issue-row').length === 0) $('#emptyRow').show();
    });

    // บันทึก
    $('#btnSaveIssue').click(function() {
        if($('#issueTable tbody tr.issue-row').length === 0) return;

        Swal.fire({
            title: 'ยืนยันการตัดจ่าย?',
            text: "ระบบจะตัดยอดสต็อกในคลังย่อยทันที",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonColor: '#0dcaf0'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('สำเร็จ!', 'ตัดสต็อกคลังย่อยเรียบร้อย', 'success').then(() => location.reload());
            }
        });
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>