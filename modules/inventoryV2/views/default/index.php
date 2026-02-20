<?php
use yii\web\View;
use yii\helpers\Url;

$this->title = 'เมนูนำทางระบบ | ขั้นตอนการทำงานระบบคลังสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventoryV2/menu',['active' => 'navigation']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4" style="font-family: 'Sarabun', sans-serif;">
    
    <!-- Workflow Diagram -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>แผนผังขั้นตอนการทำงานระบบคลังสินค้า</h5>
        </div>
        <div class="card-body p-4">
            <div class="workflow-container">
                <!-- Step 1: จัดการพัสดุ -->
                <div class="workflow-step step-start">
                    <div class="step-card bg-info text-white">
                        <div class="step-icon">
                            <i class="bi bi-box-seam h3 mb-2"></i>
                        </div>
                        <h6 class="fw-bold mb-2">1. จัดการพัสดุ</h6>
                        <p class="small mb-0">เพิ่ม/แก้ไขข้อมูลพัสดุ</p>
                    </div>
                    <div class="step-arrow">
                        <i class="bi bi-arrow-down"></i>
                    </div>
                </div>

                <!-- Step 2: รับเข้าคลังหลัก -->
                <div class="workflow-step">
                    <div class="step-card bg-success text-white">
                        <div class="step-icon">
                            <i class="bi bi-box-arrow-in-down h3 mb-2"></i>
                        </div>
                        <h6 class="fw-bold mb-2">2. รับเข้าคลังหลัก</h6>
                        <p class="small mb-0">บันทึกการรับพัสดุเข้าคลัง</p>
                    </div>
                    <div class="step-arrow">
                        <i class="bi bi-arrow-down"></i>
                    </div>
                </div>

                <!-- Step 3: ขอเบิกจากคลังย่อย -->
                <div class="workflow-step">
                    <div class="step-card bg-warning text-white">
                        <div class="step-icon">
                            <i class="bi bi-file-earmark-plus h3 mb-2"></i>
                        </div>
                        <h6 class="fw-bold mb-2">3. ขอเบิกจากคลังย่อย</h6>
                        <p class="small mb-0">คลังย่อยสร้างใบขอเบิก</p>
                    </div>
                    <div class="step-arrow">
                        <i class="bi bi-arrow-down"></i>
                    </div>
                </div>

                <!-- Step 4: จ่ายออกจากคลังหลัก -->
                <div class="workflow-step">
                    <div class="step-card bg-danger text-white">
                        <div class="step-icon">
                            <i class="bi bi-box-arrow-right h3 mb-2"></i>
                        </div>
                        <h6 class="fw-bold mb-2">4. จ่ายออกจากคลังหลัก</h6>
                        <p class="small mb-0">คลังหลักอนุมัติและจ่ายพัสดุ</p>
                    </div>
                    <div class="step-arrow">
                        <i class="bi bi-arrow-down"></i>
                    </div>
                </div>

                <!-- Step 5: ตรวจสอบและรายงาน -->
                <div class="workflow-step step-end">
                    <div class="step-card bg-secondary text-white">
                        <div class="step-icon">
                            <i class="bi bi-journal-text h3 mb-2"></i>
                        </div>
                        <h6 class="fw-bold mb-2">5. ตรวจสอบและรายงาน</h6>
                        <p class="small mb-0">ดู Stock Card และรายงาน</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Links by Category -->
    <div class="row g-4">
        
        <!-- ส่วนจัดการข้อมูลพื้นฐาน -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i>ส่วนจัดการข้อมูลพื้นฐาน</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-info-subtle text-info me-3">
                                        <i class="bi bi-tags fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">จัดการรายการพัสดุ</h6>
                                        <small class="text-muted">เพิ่ม/แก้ไข/ลบ ข้อมูลพัสดุ</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/default/setting']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-secondary-subtle text-secondary me-3">
                                        <i class="bi bi-building fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">ตั้งค่าคลังสินค้า</h6>
                                        <small class="text-muted">จัดการคลังหลัก/คลังย่อย</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ส่วนคลังหลัก -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-buildings-fill me-2"></i>ส่วนงานคลังหลัก (Main Warehouse)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-primary-subtle text-primary me-3">
                                        <i class="bi bi-speedometer2 fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Dashboard คลังหลัก</h6>
                                        <small class="text-muted">ภาพรวมสต็อกและยอดเบิกจ่าย</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/receive/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-success-subtle text-success me-3">
                                        <i class="bi bi-box-arrow-in-down fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">รับเข้าคลัง</h6>
                                        <small class="text-muted">บันทึกการรับพัสดุเข้าคลังหลัก</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/receive/create']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-success-subtle text-success me-3">
                                        <i class="bi bi-plus-circle fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">สร้างใบรับเข้า</h6>
                                        <small class="text-muted">สร้างเอกสารรับเข้าคลังใหม่</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-danger-subtle text-danger me-3">
                                        <i class="bi bi-box-arrow-right fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">จ่ายออกจากคลัง</h6>
                                        <small class="text-muted">อนุมัติและจ่ายพัสดุตาม FIFO</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-warning-subtle text-warning me-3">
                                        <i class="bi bi-list-check fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">รายการใบขอเบิก</h6>
                                        <small class="text-muted">ดูรายการใบขอเบิกทั้งหมด</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ส่วนคลังย่อย -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-house-door-fill me-2"></i>ส่วนงานคลังย่อย (Sub-Warehouse)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-warning-subtle text-warning me-3">
                                        <i class="bi bi-shop fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Dashboard คลังย่อย</h6>
                                        <small class="text-muted">เช็คยอดของในตู้/แผนก</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/requisition/create']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-warning-subtle text-warning me-3">
                                        <i class="bi bi-file-earmark-plus fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">สร้างใบขอเบิก</h6>
                                        <small class="text-muted">ส่งรายการขอเบิกไปคลังหลัก</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ส่วนรายงานและตรวจสอบ -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>ส่วนรายงานและตรวจสอบ</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/stock-card/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-secondary-subtle text-secondary me-3">
                                        <i class="bi bi-journal-text fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Stock Card</h6>
                                        <small class="text-muted">ตรวจสอบความเคลื่อนไหวรายตัว</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/main-stock/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-primary-subtle text-primary me-3">
                                        <i class="bi bi-clipboard-data fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">รายงานคลังหลัก</h6>
                                        <small class="text-muted">ดูรายงานและสถิติคลังหลัก</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= Url::to(['/inventory-v2/sub-stock/index']) ?>" class="menu-link-card">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-wrapper bg-warning-subtle text-warning me-3">
                                        <i class="bi bi-clipboard-check fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">รายงานคลังย่อย</h6>
                                        <small class="text-muted">ดูรายงานและสถิติคลังย่อย</small>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ขั้นตอนการทำงานแบบละเอียด -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>ขั้นตอนการทำงานแบบละเอียด</h5>
                </div>
                <div class="card-body">
                    <div class="steps-detailed">
                        <div class="step-item mb-4">
                            <div class="step-number bg-info text-white">1</div>
                            <div class="step-content">
                                <h6 class="fw-bold mb-2">จัดการข้อมูลพัสดุ</h6>
                                <p class="text-muted mb-2">ก่อนเริ่มใช้งานระบบ ต้องเพิ่มข้อมูลพัสดุเข้าสู่ระบบก่อน</p>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ไปที่ <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>">จัดการรายการพัสดุ</a></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เพิ่มข้อมูลพัสดุ (รหัส, ชื่อ, หมวดหมู่, หน่วยนับ)</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ตั้งค่าจุดสั่งซื้อขั้นต่ำ/สูง (ถ้ามี)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="step-number bg-success text-white">2</div>
                            <div class="step-content">
                                <h6 class="fw-bold mb-2">รับเข้าคลังหลัก</h6>
                                <p class="text-muted mb-2">เมื่อได้รับพัสดุเข้าคลังหลัก ให้บันทึกการรับเข้า</p>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ไปที่ <a href="<?= Url::to(['/inventory-v2/receive/create']) ?>">สร้างใบรับเข้า</a></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เลือกคลังหลักที่รับเข้า</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เลือกพัสดุและระบุจำนวน, Lot Number, วันหมดอายุ, ราคา</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>บันทึกข้อมูล - ระบบจะอัปเดตยอดสต็อกอัตโนมัติ</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="step-number bg-warning text-dark">3</div>
                            <div class="step-content">
                                <h6 class="fw-bold mb-2">คลังย่อยขอเบิก</h6>
                                <p class="text-muted mb-2">เมื่อคลังย่อยต้องการพัสดุ ให้สร้างใบขอเบิก</p>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ไปที่ <a href="<?= Url::to(['/inventory-v2/requisition/create']) ?>">สร้างใบขอเบิก</a></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เลือกคลังหลักที่ต้องการเบิก</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เลือกพัสดุและระบุจำนวนที่ต้องการ</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>บันทึกใบขอเบิก - สถานะจะเป็น DRAFT</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="step-number bg-danger text-white">4</div>
                            <div class="step-content">
                                <h6 class="fw-bold mb-2">คลังหลักอนุมัติและจ่าย</h6>
                                <p class="text-muted mb-2">คลังหลักตรวจสอบและจ่ายพัสดุตามระบบ FIFO</p>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ไปที่ <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>">รายการใบขอเบิก</a></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>เลือกใบขอเบิกที่ต้องการจ่าย</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ไปที่ <a href="<?= Url::to(['/inventory-v2/issue/process']) ?>">หน้าจ่าย</a> - เลือก Lot ที่จะจ่าย (ระบบจะเรียงตาม FIFO)</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ยืนยันการจ่าย - ระบบจะหักยอดสต็อกอัตโนมัติ</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="step-number bg-secondary text-white">5</div>
                            <div class="step-content">
                                <h6 class="fw-bold mb-2">ตรวจสอบและรายงาน</h6>
                                <p class="text-muted mb-2">ตรวจสอบความเคลื่อนไหวและดูรายงาน</p>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ดู <a href="<?= Url::to(['/inventory-v2/stock-card/index']) ?>">Stock Card</a> เพื่อตรวจสอบความเคลื่อนไหวรายตัว</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ดู Dashboard เพื่อดูภาพรวมสต็อก</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>ตรวจสอบยอดคงเหลือในแต่ละคลัง</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.workflow-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem 0;
}

.workflow-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 300px;
}

.step-card {
    padding: 1.5rem;
    border-radius: 15px;
    text-align: center;
    width: 100%;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.step-card:hover {
    transform: translateY(-5px);
}

.step-icon {
    margin-bottom: 0.5rem;
}

.step-arrow {
    font-size: 2rem;
    color: #6c757d;
    margin: 0.5rem 0;
}

.step-start .step-card {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

.step-end .step-card {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.menu-link-card {
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.3s ease;
}

.menu-link-card:hover {
    text-decoration: none;
    color: inherit;
    transform: translateX(5px);
}

.menu-link-card:hover .border {
    border-color: #0d6efd !important;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    flex-shrink: 0;
}

.steps-detailed {
    padding: 1rem 0;
}

.step-item {
    display: flex;
    gap: 1.5rem;
    position: relative;
}

.step-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 60px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    flex-shrink: 0;
    z-index: 1;
}

.step-content {
    flex-grow: 1;
    padding-top: 0.25rem;
}

.step-content ul li {
    margin-bottom: 0.5rem;
}

.step-content a {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
}

.step-content a:hover {
    text-decoration: underline;
}

.bg-info-subtle { background-color: #cff4fc !important; }
.bg-success-subtle { background-color: #d1e7dd !important; }
.bg-warning-subtle { background-color: #fff3cd !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
.bg-primary-subtle { background-color: #cfe2ff !important; }
.bg-secondary-subtle { background-color: #e2e3e5 !important; }

@media (max-width: 768px) {
    .workflow-step {
        max-width: 100%;
    }
    
    .step-item {
        flex-direction: column;
        gap: 1rem;
    }
    
    .step-item::after {
        display: none;
    }
}
</style>
