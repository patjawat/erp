<div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-0">ตั้งค่าโครงสร้างคลังสินค้า</h3>
            <p class="text-muted">จัดการข้อมูลคลังหลัก คลังย่อย และหน่วยงานที่เกี่ยวข้อง</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#warehouseModal">
                <i class="bi bi-plus-lg me-2"></i>เพิ่มจุดเก็บ/แผนกใหม่
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">รายการจุดเก็บพัสดุทั้งหมด</h5>
                        <div class="badge bg-primary-subtle text-primary rounded-pill px-3">ทั้งหมด 5 แห่ง</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4" width="10%">ประเภท</th>
                                    <th width="30%">ชื่อคลัง / แผนก</th>
                                    <th width="20%">ผู้รับผิดชอบ</th>
                                    <th width="20%">สถานะ</th>
                                    <th class="text-end pe-4" width="20%">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-primary-subtle bg-opacity-10">
                                    <td class="ps-4">
                                        <span class="badge bg-primary text-white">Main</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">คลังพัสดุกลาง (Central)</div>
                                        <small class="text-muted">ID: WH-001</small>
                                    </td>
                                    <td>นายสมชาย มั่นคง</td>
                                    <td><span class="text-success"><i class="bi bi-circle-fill me-2 small"></i>เปิดใช้งาน</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 me-1"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 text-muted" disabled><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-warning text-dark">Sub</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">แผนกไอที (IT Dept)</div>
                                        <small class="text-muted">ID: WH-002</small>
                                    </td>
                                    <td>นางสาววิภา ใจดี</td>
                                    <td><span class="text-success"><i class="bi bi-circle-fill me-2 small"></i>เปิดใช้งาน</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 me-1"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-warning text-dark">Sub</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">แผนกซ่อมบำรุง (Maintenance)</div>
                                        <small class="text-muted">ID: WH-003</small>
                                    </td>
                                    <td>นายธีรพล ช่างเก่ง</td>
                                    <td><span class="text-danger"><i class="bi bi-circle-fill me-2 small"></i>ปิดปรับปรุง</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 me-1"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-white btn-sm border shadow-xs rounded-3 text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-light text-primary mx-auto mb-3" style="width:60px; height:60px; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                        <i class="bi bi-info-circle h3 mb-0"></i>
                    </div>
                    <h6 class="fw-bold">เกี่ยวกับประเภทคลัง</h6>
                    <p class="small text-muted text-start mb-0">
                        <strong>คลังหลัก (Main):</strong> มีอำนาจในการรับสินค้าเข้าจาก Supplier และกระจายของให้คลังย่อย<br><br>
                        <strong>คลังย่อย (Sub):</strong> รับพัสดุจากคลังหลักเพื่อนำไปใช้ภายในหน่วยงาน ไม่สามารถรับของจาก Supplier โดยตรงได้
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="warehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="fw-bold mb-0">รายละเอียดจุดเก็บพัสดุ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form id="warehouseForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="small fw-bold mb-1">ประเภทจุดเก็บ</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="wh_type" id="type_main" checked>
                                <label class="btn btn-outline-primary rounded-pill px-4 w-50" for="type_main">คลังหลัก</label>

                                <input type="radio" class="btn-check" name="wh_type" id="type_sub">
                                <label class="btn btn-outline-warning rounded-pill px-4 w-50" for="type_sub">คลังย่อย</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold mb-1">ชื่อเรียก (ภาษาไทย)</label>
                            <input type="text" class="form-control rounded-3" placeholder="เช่น แผนกบัญชี, คลังสินค้าไอที">
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold mb-1">ชื่อเรียก (English Name)</label>
                            <input type="text" class="form-control rounded-3" placeholder="Accounting Dept, IT Warehouse">
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold mb-1">ผู้รับผิดชอบหลัก</label>
                            <select class="form-select rounded-3">
                                <option selected>เลือกพนักงาน...</option>
                                <option>นายสมชาย มั่นคง</option>
                                <option>นางสาววิภา ใจดี</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked>
                                <label class="form-check-label small fw-bold" for="flexSwitchCheckChecked">เปิดใช้งานทันที (Active)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-dark rounded-pill px-4" id="saveWarehouse">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .table thead th { border-top: none; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd !important; color: #fff !important; }
    .btn-check:checked + .btn-outline-warning { background-color: #ffc107 !important; color: #000 !important; border-color: #ffc107 !important; }
</style>

<?php

use yii\web\View;
$js = <<< JS
$(document).ready(function() {
    $('#saveWarehouse').click(function() {
        Swal.fire({
            title: 'บันทึกสำเร็จ!',
            text: 'โครงสร้างคลังสินค้าถูกอัปเดตเรียบร้อยแล้ว',
            icon: 'success',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#000'
        }).then(() => {
            $('#warehouseModal').modal('hide');
        });
    });

    $('.btn-danger').click(function() {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลคลังนี้จะหายไปจากระบบ หากมีการเคลื่อนไหวพัสดุอยู่จะไม่สามารถลบได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        });
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>