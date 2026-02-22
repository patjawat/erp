<?php
use yii\web\View;
use yii\helpers\Url;

$this->title = 'WMS Portal | ระบบบริหารจัดการคลังสินค้า';
// $this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <circle cx="9" cy="7" r="4"></circle>
        </svg>
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventoryV2/menu',['active' => 'index']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-5" style="font-family: 'Sarabun', sans-serif; min-height: 100vh;">
    
    <div class="text-center mb-5">
        <h1 class="fw-bold text-dark">ระบบคลังพัสดุและพัสดุย่อย</h1>
        <p class="text-muted">ยินดีต้อนรับเข้าสู่ระบบจัดการสต็อกกลางและจุดเบิกจ่ายพัสดุ</p>
    </div>

    <div class="container">
        <div class="row g-4">
            
            <div class="col-12 border-bottom pb-2 mb-2">
                <h5 class="fw-bold text-primary"><i class="bi bi-buildings-fill me-2"></i>ส่วนงานคลังหลัก (Main Warehouse)</h5>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/main-dashboard']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary-subtle text-primary mb-3 mx-auto">
                            <i class="bi bi-speedometer2 h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Dashboard คลังหลัก</h6>
                        <small class="text-muted">ภาพรวมสต็อกและยอดเบิกจ่าย</small>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/stock-issue-list']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-success-subtle text-success mb-3 mx-auto">
                            <i class="bi bi-box-arrow-right h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">ดำเนินการจ่ายของ</h6>
                        <small class="text-muted">ตัดสต็อกและจัด Lot สินค้า</small>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/product-list']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-info-subtle text-info mb-3 mx-auto">
                            <i class="bi bi-tags h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">จัดการรายการพัสดุ</h6>
                        <small class="text-muted">เพิ่ม/แก้ไข ข้อมูลพัสดุ Master</small>
                    </div>
                </a>
            </div>

            <div class="col-12 border-bottom pb-2 mt-5 mb-2">
                <h5 class="fw-bold text-warning"><i class="bi bi-house-door-fill me-2"></i>ส่วนงานคลังย่อย (Sub-Warehouse)</h5>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/sub-stock-dashboard']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-warning-subtle text-warning mb-3 mx-auto">
                            <i class="bi bi-shop h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">หน้าแรกคลังย่อย</h6>
                        <small class="text-muted">เช็คยอดของในตู้/แผนก</small>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/requisition']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card border-warning-subtle border-start border-4">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-light text-dark mb-3 mx-auto shadow-sm">
                            <i class="bi bi-file-earmark-plus h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">สร้างใบเบิกวัสดุ</h6>
                        <small class="text-muted">ส่งรายการขอเบิกไปคลังหลัก</small>
                    </div>
                </a>
            </div>

            <div class="col-12 border-bottom pb-2 mt-5 mb-2">
                <h5 class="fw-bold text-secondary"><i class="bi bi-gear-fill me-2"></i>รายงานและตั้งค่าระบบ</h5>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/stock-card']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-secondary-subtle text-secondary mb-3 mx-auto">
                            <i class="bi bi-journal-text h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Stock Card</h6>
                        <small class="text-muted">ตรวจสอบความเคลื่อนไหวรายตัว</small>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/default/setting']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-dark-subtle text-dark mb-3 mx-auto">
                            <i class="bi bi-diagram-3 h2 mb-0"></i>
                        </div>
                        <h6 class="fw-bold text-dark">ตั้งค่าคลังสินค้า</h6>
                        <small class="text-muted">เพิ่ม/ลบ คลังและแผนกต่างๆ</small>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    .nav-portal-card {
        transition: all 0.3s ease;
        border-radius: 20px !important;
    }
    .nav-portal-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        background-color: #fff;
    }
    .icon-circle {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .bg-primary-subtle { background-color: #eef2ff !important; }
    .bg-success-subtle { background-color: #ecfdf5 !important; }
    .bg-info-subtle { background-color: #e0f2fe !important; }
    .bg-warning-subtle { background-color: #fffbeb !important; }
    .bg-secondary-subtle { background-color: #f3f4f6 !important; }
    .bg-dark-subtle { background-color: #e5e7eb !important; }
</style>


