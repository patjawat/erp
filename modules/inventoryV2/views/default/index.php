<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'คลังสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-box-seam fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">เลือกบทบาทการทำงาน — เข้าหน้าคลังหลัก หรือหน้าคลังย่อย แล้วใช้เมนูในหน้านั้น</p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 px-3 px-md-4">
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <a href="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-primary border-4 hover-shadow">
                    <div class="card-body text-center py-5 px-4">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex p-3 mb-3">
                            <i class="bi bi-buildings fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-primary mb-2">คลังหลัก</h5>
                        <p class="text-muted small mb-4">รับของเข้าคลัง จ่ายของตามใบขอเบิก ดูภาพรวมสต็อก</p>
                        <span class="btn btn-primary rounded-pill px-4">เข้าหน้าคลังหลัก</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-warning border-4 hover-shadow">
                    <div class="card-body text-center py-5 px-4">
                        <div class="rounded-3 bg-warning bg-opacity-25 text-dark d-inline-flex p-3 mb-3">
                            <i class="bi bi-house-door fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">คลังย่อย</h5>
                        <p class="text-muted small mb-4">สร้างใบขอเบิก ดูสถานะ รับของจากคลังหลัก</p>
                        <span class="btn btn-warning rounded-pill px-4 text-dark">เข้าหน้าคลังย่อย</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="text-center py-2">
                <span class="text-muted small me-2">สำหรับผู้ดูแลระบบ:</span>
                <?= Html::a('ตั้งค่าคลังสินค้า', ['/inventory-v2/default/setting'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill me-1']) ?>
                <?= Html::a('จัดการรายการพัสดุ', ['/inventory-v2/stock-item/index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill']) ?>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow { transition: box-shadow 0.2s ease, transform 0.2s ease; }
a:hover .hover-shadow { box-shadow: 0 0.5rem 1.25rem rgba(0,0,0,0.12) !important; transform: translateY(-2px); }
</style>
