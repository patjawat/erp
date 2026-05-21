<?php

use yii\helpers\Url;

$active = $active ?? 'dashboard';
$canManageMigration = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');
?>
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 flex-shrink-0">
                    <i data-lucide="package-search"></i>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold text-nowrap">
                            Procurement Workspace
                        </span>
                    </div>
                    <div class="fw-bold">ระบบจัดซื้อจัดจ้าง</div>
                    <div class="text-muted small">ภาพรวม งานค้าง คำขอ และการย้ายข้อมูลเดิมอยู่ในแถบเดียว</div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= Url::to(['/purchase-v2/default/index']) ?>" class="btn btn-sm <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?> rounded-3 fw-semibold text-nowrap">
                    <i data-lucide="layout-dashboard" class="me-1"></i>
                    ภาพรวม
                </a>
                <a href="<?= Url::to(['/purchase-v2/request/index']) ?>" class="btn btn-sm <?= $active !== 'request' ? 'btn-outline-primary' : 'btn-primary' ?> rounded-3 fw-semibold text-nowrap">
                    <i data-lucide="clipboard-list" class="me-1"></i>
                    Worklist
                </a>
                <a href="<?= Url::to(['/purchase-v2/request/create']) ?>" class="btn btn-sm <?= $active !== 'create' ? 'btn-outline-primary' : 'btn-primary' ?> rounded-3 fw-semibold text-nowrap">
                    <i data-lucide="circle-plus" class="me-1"></i>
                    สร้างคำขอ
                </a>
                <?php if ($canManageMigration): ?>
                    <a href="<?= Url::to(['/purchase-v2/migration/index']) ?>" class="btn btn-sm <?= $active !== 'migration' ? 'btn-outline-secondary' : 'btn-secondary' ?> rounded-3 fw-semibold text-nowrap">
                        <i data-lucide="database-zap" class="me-1"></i>
                        ย้ายข้อมูลเดิม
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
