<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'คลังสินค้า';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = 'เมนูนำทาง';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-box-seam fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">ระบบคลังของโรงพยาบาล — <strong>คลังหลัก</strong> รับเข้า/จ่ายออก <strong>คลังย่อย</strong> (แผนก/ฝ่าย) ขอเบิกจากคลังหลัก เลือกเมนูตามบทบาทของคุณด้านล่าง</p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">

    <!-- บทบาท: คลังหลัก vs คลังย่อย -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-buildings me-1"></i>สำหรับผู้ดูแลคลังหลัก</h6>
                    <span class="text-muted">รับของเข้าคลัง จ่ายของออกตามใบขอเบิกจากแผนก/ฝ่าย ดู Dashboard</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= Url::to(['/inventory-v2/receive/create']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-success bg-opacity-10 text-success flex-shrink-0"><i class="bi bi-box-arrow-in-down fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">รับเข้าคลัง</span>
                                <span class="d-block text-muted">สร้างใบรับเข้า</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger flex-shrink-0"><i class="bi bi-box-arrow-right fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">รายการจ่ายพัสดุ</span>
                                <span class="d-block text-muted">ดำเนินการจ่ายตามใบขอเบิก</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/stock-adjust/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-info bg-opacity-10 text-info flex-shrink-0"><i class="bi bi-wrench-adjustable fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">ปรับยอด stock</span>
                                <span class="d-block text-muted">เพิ่ม/ลดยอดคงเหลือโดยตรง (ตรวจนับหรือแก้ยอดผิด)</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary flex-shrink-0"><i class="bi bi-speedometer2 fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">Dashboard คลังหลัก</span>
                                <span class="d-block text-muted">ภาพรวมสต็อกและรายการรอจ่าย</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/receive/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0"><i class="bi bi-list-ul fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">ทะเบียนรับเข้า</span>
                                <span class="d-block text-muted">ดูประวัติใบรับเข้า</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-house-door me-1"></i>สำหรับผู้ดูแลคลังย่อย</h6>
                    <span class="text-muted">สร้างใบขอเบิกจากแผนก/ฝ่าย ดูสถานะและ Dashboard คลังย่อย</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= Url::to(['/inventory-v2/requisition/create']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-warning bg-opacity-25 text-dark flex-shrink-0"><i class="bi bi-file-earmark-plus fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">สร้างใบขอเบิก</span>
                                <span class="d-block text-muted">ส่งคำขอเบิกจากคลังหลัก</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-info bg-opacity-10 text-info flex-shrink-0"><i class="bi bi-shop fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">Dashboard คลังย่อย</span>
                                <span class="d-block text-muted">ภาพรวมและรายการที่ส่งมา</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="rounded-3 p-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0"><i class="bi bi-list-check fs-5"></i></span>
                            <div class="flex-grow-1">
                                <span class="fw-semibold">ทะเบียนใบขอเบิก</span>
                                <span class="d-block text-muted">ดูสถานะใบขอเบิกทั้งหมด</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- วิธีเบิกใช้งานในคลัง (สำหรับแผนก/ฝ่ายของโรงพยาบาล) -->
    <div class="card border-0 shadow-sm mb-4 border-start border-warning border-3">
        <div class="card-header bg-warning bg-opacity-10 py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="mb-0 fw-normal"><i class="bi bi-question-circle me-1"></i>วิธีเบิกพัสดุ (แผนก/ฝ่าย)</h6>
            <?= Html::a('สร้างใบขอเบิก', ['/inventory-v2/requisition/create'], ['class' => 'btn btn-warning btn-sm']) ?>
        </div>
        <div class="card-body py-2 px-3">
            <p class="mb-2 text-muted">แผนก/ฝ่ายของโรงพยาบาลที่ต้องการเบิกของจากคลังหลัก — สร้างใบขอเบิกได้ตามต้องการ</p>
            <ol class="mb-0 ps-3 text-muted">
                <li>ไปที่ <strong>สร้างใบขอเบิก</strong> → เลือกคลังที่จ่าย + แผนก/ฝ่ายที่รับ → ใส่รายการพัสดุและจำนวน → ส่งคำขอ</li>
                <li>หัวหน้าอนุมัติที่ <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>">ทะเบียนใบขอเบิก</a></li>
                <li>คลังหลักไปที่ <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>">รายการจ่ายพัสดุ</a> → ดำเนินการจ่าย → ยืนยัน (ระบบตัดสต็อก)</li>
                <li>ดูสถานะและรายการที่ส่งมาได้ที่ <a href="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>">Dashboard คลังย่อย</a></li>
            </ol>
        </div>
    </div>

    <!-- แผนผังขั้นตอน (เข้าใจง่าย) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary-gradient text-white py-2 px-3">
            <h6 class="text-white mb-0 fw-normal"><i class="bi bi-diagram-3 me-1"></i>ขั้นตอนการทำงานโดยรวม</h6>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 gap-md-3">
                <span class="badge rounded-pill bg-info bg-opacity-10 text-dark px-3 py-2"><i class="bi bi-box-seam me-1"></i>จัดการพัสดุ</span>
                <i class="bi bi-chevron-right text-muted d-none d-md-inline"></i>
                <span class="badge rounded-pill bg-success bg-opacity-10 text-dark px-3 py-2"><i class="bi bi-box-arrow-in-down me-1"></i>คลังหลักรับเข้า</span>
                <i class="bi bi-chevron-right text-muted d-none d-md-inline"></i>
                <span class="badge rounded-pill bg-warning bg-opacity-25 text-dark px-3 py-2"><i class="bi bi-file-earmark-plus me-1"></i>คลังย่อยขอเบิก</span>
                <i class="bi bi-chevron-right text-muted d-none d-md-inline"></i>
                <span class="badge rounded-pill bg-danger bg-opacity-10 text-dark px-3 py-2"><i class="bi bi-box-arrow-right me-1"></i>คลังหลักจ่ายออก</span>
                <i class="bi bi-chevron-right text-muted d-none d-md-inline"></i>
                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-dark px-3 py-2"><i class="bi bi-journal-text me-1"></i>รายงาน</span>
            </div>
        </div>
    </div>

    <!-- เมนูเพิ่มเติม (ร่วมกัน) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-secondary bg-opacity-10 py-2 px-3">
            <h6 class="mb-0 fw-normal"><i class="bi bi-grid-3x3-gap me-1"></i>เมนูเพิ่มเติม</h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="text-decoration-none d-flex align-items-center p-2 rounded border border-opacity-50">
                        <i class="bi bi-tags text-info me-2"></i>
                        <span>จัดการรายการพัสดุ</span>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/inventory-v2/stock-card/index']) ?>" class="text-decoration-none d-flex align-items-center p-2 rounded border border-opacity-50">
                        <i class="bi bi-journal-text text-secondary me-2"></i>
                        <span>Stock Card</span>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/inventory-v2/report/material-summary']) ?>" class="text-decoration-none d-flex align-items-center p-2 rounded border border-opacity-50">
                        <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>
                        <span>สรุปรายงานวัสดุคงคลัง</span>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/inventory-v2/default/setting']) ?>" class="text-decoration-none d-flex align-items-center p-2 rounded border border-opacity-50">
                        <i class="bi bi-gear text-secondary me-2"></i>
                        <span>ตั้งค่าคลังสินค้า</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ขั้นตอนแบบละเอียด (accordion) -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-secondary bg-opacity-10 py-2 px-3">
            <button class="btn btn-link text-dark text-decoration-none p-0 fw-normal w-100 text-start shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#steps-detail" aria-expanded="false" aria-controls="steps-detail">
                <i class="bi bi-list-ol me-1"></i>ขั้นตอนการทำงานแบบละเอียด
            </button>
        </div>
        <div class="collapse" id="steps-detail">
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-3"><strong>จัดการข้อมูลพัสดุ</strong> — ไปที่ <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>">จัดการรายการพัสดุ</a> เพิ่มรหัส ชื่อ หมวดหมู่ หน่วยนับ</li>
                    <li class="mb-3"><strong>คลังหลักรับเข้า</strong> — ไปที่ <a href="<?= Url::to(['/inventory-v2/receive/create']) ?>">สร้างใบรับเข้า</a> เลือกคลัง พัสดุ จำนวน Lot ราคา บันทึก</li>
                    <li class="mb-3"><strong>คลังย่อยขอเบิก</strong> — ไปที่ <a href="<?= Url::to(['/inventory-v2/requisition/create']) ?>">สร้างใบขอเบิก</a> เลือกคลังที่จ่าย แผนก/ฝ่ายที่รับ พัสดุและจำนวน ส่งคำขอ</li>
                    <li class="mb-3"><strong>หัวหน้าอนุมัติ → คลังหลักจ่าย</strong> — อนุมัติที่ <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>">ทะเบียนใบขอเบิก</a> จากนั้นไปที่ <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>">รายการจ่ายพัสดุ</a> ดำเนินการจ่าย เลือก Lot และจำนวน ยืนยัน (ระบบตัดสต็อก)</li>
                    <li><strong>ตรวจสอบ</strong> — ดู <a href="<?= Url::to(['/inventory-v2/stock-card/index']) ?>">Stock Card</a> และ Dashboard</li>
                </ol>
            </div>
        </div>
    </div>

</div>
