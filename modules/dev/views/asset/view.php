<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

// 1. หัวข้อใหญ่ (Title) จะไปแสดงที่ Layout หลัก
$this->title = 'ทรัพย์สิน';

// 2. Breadcrumb (เส้นทางด้านบน)
$this->params['breadcrumbs'][] = ['label' => 'รายการ', 'url' => ['index']];
?>

<div class="container-fluid p-0 fade-in">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        
        <div class="d-flex align-items-center gap-2 text-secondary small">
            <a href="<?= Url::to(['index']) ?>" class="text-decoration-none text-secondary hover-text-primary" style="cursor: pointer;">ทรัพย์สิน</a>
            <span>/</span>
            <span class="text-dark fw-medium">รายละเอียด</span>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                QR Code
            </button>
            
            <a href="<?= Url::to(['update', 'id' => 1]) ?>" class="btn btn-warning bg-warning bg-opacity-10 text-warning border-warning border-opacity-50 d-flex align-items-center gap-2 btn-sm px-3 py-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg>
                แก้ไขข้อมูล
            </a>

            <a href="<?= Url::to(['index']) ?>" class="btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                ย้อนกลับ
            </a>
        </div>

    </div>

    <div class="card border border-light-subtle shadow-sm mb-4" style="border-radius: 12px; border-color: #e5e7eb !important;">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-auto">
                    <div class="bg-light rounded-3 overflow-hidden border" style="width: 200px; height: 200px;">
                        <img src="https://picsum.photos/id/1/400/400" alt="Asset Image" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>

                <div class="col-12 col-md">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mb-2">ครุภัณฑ์</span>
                            <h3 class="fw-bold text-dark mb-1">เครื่องคอมพิวเตอร์ All-in-One</h3>
                            <div class="text-secondary small d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                                EQ-COM-66001
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-secondary small mb-1">มูลค่าทรัพย์สิน</div>
                            <h3 class="fw-bold text-primary mb-0">฿32,500.00</h3>
                        </div>
                    </div>

                    <div class="row g-3 py-3 border-top border-bottom border-light-subtle my-3">
                        <div class="col-6 col-md-3">
                            <div class="text-secondary small mb-1">วันที่ได้มา</div>
                            <div class="fw-medium text-dark">2566-03-10</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary small mb-1">อายุการใช้งาน</div>
                            <div class="fw-medium text-dark">5 ปี</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary small mb-1">สถานะ</div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-medium">Normal</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary small mb-1">ผู้รับผิดชอบ</div>
                            <div class="fw-medium text-dark">ศูนย์คอมพิวเตอร์</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button class="btn btn-white border w-50 text-secondary fw-medium">พิมพ์ทะเบียนคุม</button>
                        <button class="btn btn-primary w-50 fw-medium shadow-sm">ส่งซ่อม / แจ้งปัญหา</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border border-light-subtle shadow-sm" style="border-radius: 12px; border-color: #e5e7eb !important; overflow: hidden;">
        
        <div class="card-header bg-white p-0 border-bottom border-light-subtle">
            <ul class="nav nav-tabs border-0" id="assetTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-0 border-0 border-bottom border-2 text-dark fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" 
                            id="details-tab" data-bs-toggle="tab" data-bs-target="#tab-details" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                        รายละเอียด
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" 
                            id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#tab-maintenance" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"/></svg>
                        ประวัติซ่อมบำรุง
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" 
                            id="depreciation-tab" data-bs-toggle="tab" data-bs-target="#tab-depreciation" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 17h6v-6"/><path d="m22 17-8.5-8.5-5 5L2 7"/></svg>
                        ค่าเสื่อมราคา
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" 
                            id="files-tab" data-bs-toggle="tab" data-bs-target="#tab-files" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg>
                        เอกสารแนบ
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="assetTabsContent">
                
                <div class="tab-pane fade show active p-4" id="tab-details" role="tabpanel">
                    <div class="row g-5">
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-dark mb-4 border-start border-4 border-primary ps-3">ข้อมูลทั่วไป</h6>
                            <dl class="row mb-0 text-sm" style="font-size: 0.9rem;">
                                <dt class="col-sm-4 text-secondary fw-normal mb-3">รหัสทรัพย์สิน</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">EQ-COM-66001</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">ชื่อรายการ</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">เครื่องคอมพิวเตอร์ All-in-One</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">ยี่ห้อ / รุ่น</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">Dell Optiplex 7400</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">Serial Number</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">CN-0X5G9-74400</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">สถานที่ตั้ง</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">ศูนย์คอมพิวเตอร์</dd>
                            </dl>
                        </div>

                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-dark mb-4 border-start border-4 border-success ps-3">ข้อมูลการได้มา</h6>
                            <dl class="row mb-0 text-sm" style="font-size: 0.9rem;">
                                <dt class="col-sm-4 text-secondary fw-normal mb-3">วันที่รับ</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">2566-03-10</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">วิธีได้มา</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">-</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">ผู้จำหน่าย</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">-</dd>

                                <dt class="col-sm-4 text-secondary fw-normal mb-3">แหล่งงบประมาณ</dt>
                                <dd class="col-sm-8 text-dark fw-medium mb-3">-</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-2">
                        <h6 class="fw-bold text-dark mb-3">คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม</h6>
                        <div class="p-3 bg-light rounded text-secondary small">
                            ไม่มีข้อมูลรายละเอียดเพิ่มเติม
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="tab-maintenance" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">ประวัติการซ่อมบำรุง</h6>
                        
                        <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-1 text-primary" 
                                style="font-size: 0.9rem;"
                                data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            บันทึกการซ่อม
                        </button>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="px-4 py-3 fw-medium">วันที่แจ้ง</th>
                                    <th class="px-4 py-3 fw-medium">รายการ / อาการ</th>
                                    <th class="px-4 py-3 fw-medium">ผู้ดำเนินการ</th>
                                    <th class="px-4 py-3 fw-medium text-end">ค่าใช้จ่าย</th>
                                    <th class="px-4 py-3 fw-medium text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <tr>
                                    <td class="px-4 py-3 text-dark fw-medium">2566-12-10</td>
                                    <td class="px-4 py-3">
                                        <div class="fw-medium text-dark">เปลี่ยนแบตเตอรี่</div>
                                        <div class="text-muted small">เปลี่ยนแบตเตอรี่เนื่องจากเสื่อมสภาพ</div>
                                    </td>
                                    <td class="px-4 py-3 text-secondary">ร้านอมร อิเล็คโทรนิคส์</td>
                                    <td class="px-4 py-3 text-end fw-medium">฿450.00</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="tab-depreciation" role="tabpanel">
                    <div class="text-muted text-center py-5">ส่วนนี้กำลังพัฒนา...</div>
                </div>

                <div class="tab-pane fade p-4" id="tab-files" role="tabpanel">
                    <div class="text-muted text-center py-5">ยังไม่มีเอกสารแนบ</div>
                </div>

            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="maintenanceModalLabel">บันทึกประวัติการซ่อม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <form>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">วันที่แจ้งซ่อม</label>
                        <input type="date" class="form-control shadow-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">หัวข้อ / อาการเสีย</label>
                        <input type="text" class="form-control shadow-sm" placeholder="เช่น เครื่องเปิดไม่ติด, เปลี่ยนอะไหล่">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">รายละเอียดเพิ่มเติม</label>
                        <textarea class="form-control shadow-sm" rows="3" placeholder="ระบุรายละเอียด..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary small fw-medium">ผู้ดำเนินการ</label>
                            <input type="text" class="form-control shadow-sm" placeholder="ระบุร้านซ่อม">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary small fw-medium">ค่าใช้จ่าย (บาท)</label>
                            <input type="number" class="form-control shadow-sm" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">สถานะ</label>
                        <select class="form-select shadow-sm text-primary fw-medium">
                            <option value="1">กำลังดำเนินการ</option>
                            <option value="2">เสร็จสิ้น (Completed)</option>
                            <option value="0">ยกเลิก</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light border text-secondary w-50" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary w-50 shadow-sm">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa; color: #212529 !important; }
    .nav-tabs .nav-link { color: #6c757d; }
    .nav-tabs .nav-link:hover { background-color: #f8f9fa; color: #212529; }
    .nav-tabs .nav-link.active {
        color: var(--erp-primary, #0d6efd);
        border-color: transparent transparent var(--erp-primary, #0d6efd);
        background-color: #eff6ff; 
    }
</style>