<?php

use yii\web\View;

$this->title = 'จัดการรายการพัสดุ (Item Master)';
?>

<div class="card">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-content-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">จัดการรายการพัสดุ</h3>
                <p class="text-muted">บริหารจัดการข้อมูลพัสดุส่วนกลาง (Master Data)</p>
            </div>
            <div>
                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#itemModal">
                    <i class="bi bi-plus-circle me-2"></i>เพิ่มพัสดุใหม่
                </button>
            </div>

        </div>
    </div>
</div>


<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="ค้นหาชื่อพัสดุ หรือรหัสพัสดุ...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>เลือกหมวดหมู่ทั้งหมด</option>
                    <option>ไอทีและอุปกรณ์</option>
                    <option>เครื่องเขียน</option>
                    <option>งานซ่อมบำรุง</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">กรองข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4" width="10%">รหัสพัสดุ</th>
                    <th width="35%">ชื่อพัสดุ</th>
                    <th width="15%">หน่วยนับ</th>
                    <th width="15%" class="text-center">จุดสั่งซื้อขั้นต่ำ</th>
                    <th width="15%" class="text-end pe-4">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-4 fw-bold text-primary">IT-0023</td>
                    <td>
                        <div class="fw-bold">SSD 500GB Samsung</div>
                        <small class="text-muted">หมวดหมู่: ไอที</small>
                    </td>
                    <td>ชิ้น</td>
                    <td class="text-center">
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">5</span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-light btn-sm rounded-circle me-1" title="แก้ไข" data-bs-toggle="modal" data-bs-target="#itemModal"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-light btn-sm rounded-circle text-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 fw-bold text-primary">MT-0045</td>
                    <td>
                        <div class="fw-bold">หลอดไฟ LED 18W</div>
                        <small class="text-muted">หมวดหมู่: งานซ่อมบำรุง</small>
                    </td>
                    <td>หลอด</td>
                    <td class="text-center">
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">20</span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-light btn-sm rounded-circle me-1"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-light btn-sm rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" id="modalTitle">ข้อมูลพัสดุ Master</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="itemForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">รหัสพัสดุ (Item Code)</label>
                            <input type="text" class="form-control rounded-3" placeholder="เช่น IT-0001">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ชื่อพัสดุ</label>
                            <input type="text" class="form-control rounded-3" placeholder="ระบุชื่อพัสดุเต็ม">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">หน่วยนับ</label>
                            <input type="text" class="form-control rounded-3" placeholder="เช่น ชิ้น, กล่อง">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">จุดสั่งซื้อขั้นต่ำ (Min Stock)</label>
                            <input type="number" class="form-control rounded-3" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">หมวดหมู่</label>
                            <select class="form-select rounded-3">
                                <option>เลือกหมวดหมู่</option>
                                <option>ไอทีและอุปกรณ์</option>
                                <option>เครื่องเขียน</option>
                                <option>งานซ่อมบำรุง</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSaveItem">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
    }

    .bg-danger-subtle {
        background-color: #fee2e2 !important;
    }

    .bg-secondary-subtle {
        background-color: #f3f4f6 !important;
    }

    .btn-light:hover {
        background-color: #e5e7eb;
    }
</style>

<?php
$js = <<< JS
$(document).ready(function() {
    // Logic การเปลี่ยน Title ของ Modal เมื่อกดแก้ไข
    $('.bi-pencil-square').closest('button').click(function() {
        $('#modalTitle').text('แก้ไขข้อมูลพัสดุ');
    });

    $('.btn-primary[data-bs-target="#itemModal"]').click(function() {
        $('#modalTitle').text('เพิ่มพัสดุใหม่');
        $('#itemForm')[0].reset();
    });

    $('#btnSaveItem').click(function() {
        // จำลองการบันทึก
        Swal.fire({
            icon: 'success',
            title: 'บันทึกสำเร็จ',
            text: 'ข้อมูลพัสดุถูกจัดเก็บเข้าสู่ระบบแล้ว',
            confirmButtonColor: '#0d6efd'
        }).then(() => {
            $('#itemModal').modal('hide');
        });
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>