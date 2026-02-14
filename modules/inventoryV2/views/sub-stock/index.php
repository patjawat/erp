<?php
use yii\helpers\Url;
?>
       <div class="col-12 border-bottom pb-2 mt-5 mb-2">
                <h5 class="fw-bold text-warning"><i class="bi bi-house-door-fill me-2"></i>ส่วนงานคลังย่อย (Sub-Warehouse)</h5>
            </div>

<div class="row g-4 justify-content-center">
<div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/sub-stock/dashboard']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card">
                   <div class="card-body p-4 d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="erp-icon-box-xl">
                    <i class="bi bi-shop h2 mb-0"></i>
                </div>
                        <h6 class="fw-bold text-dark">หน้าแรกคลังย่อย</h6>
                        <small class="text-muted">เช็คยอดของในตู้/แผนก</small>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= Url::to(['/inventory-v2/sub-stock/requisition']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none nav-portal-card border-warning-subtle border-start border-4">
                   <div class="card-body p-4 d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="erp-icon-box-xl">
                    <i class="bi bi-file-earmark-plus h2 mb-0"></i>
                </div>
                        <h6 class="fw-bold text-dark">สร้างใบเบิกวัสดุ</h6>
                        <small class="text-muted">ส่งรายการขอเบิกไปคลังหลัก</small>
                    </div>
                </a>
            </div>
</div>