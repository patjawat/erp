<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
?>
<div class="row g-4 justify-content-center">

    <div class="col-12 border-bottom pb-2 mb-2">
        <h5 class="fw-bold text-primary"><i class="bi bi-buildings-fill me-2"></i>ส่วนงานคลังหลัก (Main Warehouse)</h5>
    </div>

    <div class="col-md-4 col-lg-3">
        <a href="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none rounded-4">
            <div class="card-body p-4 d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="erp-icon-box-xl">
                    <i class="bi bi-speedometer2 h2 mb-0"></i>
                </div>
                <h6 class="fw-bold text-dark">Dashboard คลังหลัก</h6>
                <small class="text-muted">ภาพรวมสต็อกและยอดเบิกจ่าย</small>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-lg-3">
        <a href="<?= Url::to(['/inventory-v2/default/stock-issue-list']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none rounded-4">
            <div class="card-body p-4 d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="erp-icon-box-xl">
                    <i class="bi bi-box-arrow-right h2 mb-0"></i>
                </div>
                <h6 class="fw-bold text-dark">ดำเนินการจ่ายของ</h6>
                <small class="text-muted">ตัดสต็อกและจัด Lot สินค้า</small>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-lg-3">
        <a href="<?= Url::to(['/inventory-v2/stock-item']) ?>" class="card h-100 border-0 shadow-sm text-decoration-none rounded-4">
            <div class="card-body p-4 d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="erp-icon-box-xl">
                    <i class="bi bi-tags h2 mb-0"></i>
                </div>
                <h6 class="fw-bold text-dark">จัดการรายการพัสดุ</h6>
                <small class="text-muted">เพิ่ม/แก้ไข ข้อมูลพัสดุ Master</small>
            </div>
        </a>
    </div>
</div>