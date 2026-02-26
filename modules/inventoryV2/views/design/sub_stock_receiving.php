<?php
use yii\web\View;
$this->title = 'ตรวจรับพัสดุเข้าคลังย่อย';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-primary">📦 ตรวจรับพัสดุจากคลังหลัก</h5>
                <small class="text-muted">อ้างอิงใบโอนจ่าย: <strong>ISS-670045</strong> | ผู้ส่ง: นายสมชาย (คลังกลาง)</small>
            </div>
            <span class="badge text-bg-info rounded-pill px-3 py-2">สถานะ: อยู่ระหว่างขนส่ง</span>
        </div>
        
        <div class="card-body">
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center rounded-4 mb-4">
                <i class="bi bi-shield-check h4 mb-0 me-3"></i>
                <div>
                    <strong>คำแนะนำ:</strong> กรุณาตรวจสอบจำนวนพัสดุและ Lot Number ให้ตรงกับที่คลังหลักส่งมา หากยอดไม่ตรงให้แก้ไขที่ช่อง <strong>"รับจริง"</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="receiveTable">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th width="5%">#</th>
                            <th width="35%">รายการพัสดุ</th>
                            <th width="15%" class="text-center">Lot / Exp</th>
                            <th width="12%" class="text-center">จำนวนที่ส่งมา</th>
                            <th width="15%" class="text-center">จำนวนที่รับจริง</th>
                            <th width="18%">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <div class="fw-bold">SSD 500GB Samsung</div>
                                <small class="text-muted text-uppercase">IT-0023</small>
                            </td>
                            <td class="text-center font-monospace small">LOT: 67-01<br>EXP: 12/2027</td>
                            <td class="text-center h6">5</td>
                            <td>
                                <input type="number" class="form-control text-center border-primary fw-bold qty-received" 
                                       value="5" min="0" max="5">
                            </td>
                            <td><input type="text" class="form-control form-control-sm border-light" placeholder="ระบุเหตุผลหากรับไม่ครบ"></td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>
                                <div class="fw-bold">สาย LAN CAT6 (300m)</div>
                                <small class="text-muted">IT-CAB-01</small>
                            </td>
                            <td class="text-center font-monospace small">LOT: CAB-99<br>EXP: -</td>
                            <td class="text-center h6">1</td>
                            <td>
                                <input type="number" class="form-control text-center border-primary fw-bold qty-received" 
                                       value="1" min="0" max="1">
                            </td>
                            <td><input type="text" class="form-control form-control-sm border-light" placeholder="..."></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-light border-0 px-4 me-2 rounded-pill shadow-sm">ยกเลิก</button>
                <button class="btn btn-primary btn-lg px-5 rounded-pill shadow" id="btnConfirmReceive">
                    <i class="bi bi-check2-circle"></i> ยืนยันการรับเข้าคลังย่อย
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).ready(function() {

    // 1. ตรวจสอบการรับพัสดุ (กรณีรับไม่เท่ากับที่ส่ง)
    $('.qty-received').on('change', function() {
        let sentQty = parseInt($(this).closest('tr').find('td:eq(3)').text());
        let receivedQty = parseInt($(this).val());

        if (receivedQty < sentQty) {
            $(this).addClass('is-invalid border-danger');
            Swal.fire({
                title: 'รับของไม่ครบ?',
                text: 'จำนวนที่รับจริงน้อยกว่าที่คลังหลักส่งมา กรุณาระบุหมายเหตุด้วย',
                icon: 'warning',
                confirmButtonColor: '#0d6efd'
            });
        } else if (receivedQty > sentQty) {
            $(this).val(sentQty);
            Swal.fire('เกินจำนวน', 'ท่านไม่สามารถรับของเกินกว่าที่คลังหลักจ่ายมาได้', 'error');
        } else {
            $(this).removeClass('is-invalid border-danger').addClass('border-primary');
        }
    });

    // 2. กดยืนยันรับพัสดุ
    $('#btnConfirmReceive').click(function() {
        Swal.fire({
            title: 'ยืนยันการรับพัสดุ?',
            text: "ระบบจะเพิ่มยอดสต็อกในคลังย่อย และบันทึกปิดใบงานนี้",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันรับเข้าสต็อก',
            cancelButtonText: 'ตรวจสอบอีกครั้ง',
            confirmButtonColor: '#0d6efd',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Logic: ส่ง Ajax ไปยัง Controller เพื่อ Update Table 'stocks'
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'เพิ่มพัสดุเข้าคลังย่อยเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?r=inventory/sub-stock-dashboard';
                });
            }
        });
    });

});
JS;
$this->registerJS($js, View::POS_READY);
?>