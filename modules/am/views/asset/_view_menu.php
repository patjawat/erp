<?php

use yii\helpers\Url;
?>



<div class="bg-light p-2 rounded-3 d-flex gap-2">
    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'detail'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
            <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
            <path d="M10 9H8"></path>
            <path d="M16 13H8"></path>
            <path d="M16 17H8"></path>
        </svg>
        รายละเอียด
    </a>
    <a href="<?= Url::to(['repair-history', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'repair_history'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path>
        </svg>
        ประวัติซ่อมบำรุง
    </a>

    <a href="<?= Url::to(['maintenance', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'maintenance'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning">
            <path d="m16 22-1-4" />
            <path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1" />
            <path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z" />
            <path d="m8 22 1-4" />
        </svg>
        การบำรุงรักษา
    </a>

    <a href="<?= Url::to(['calibration', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'calibration'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-git-compare-arrows-icon lucide-git-compare-arrows">
            <circle cx="5" cy="6" r="3" />
            <path d="M12 6h5a2 2 0 0 1 2 2v7" />
            <path d="m15 9-3-3 3-3" />
            <circle cx="19" cy="18" r="3" />
            <path d="M12 18H7a2 2 0 0 1-2-2V9" />
            <path d="m9 15 3 3-3 3" />
        </svg>
        การสอบเทียบเครื่องมือวัด
    </a>

    <a href="<?= Url::to(['document', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'documents'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"></path>
        </svg>
        เอกสารแนบ
    </a>
    <?php if ($model->license_plate): ?>
        <a href="<?= Url::to(['vehicle-tax', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'vehicle-tax'  ? 'btn-primary' : 'bg-body' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text">
                <path d="M13 16H8" />
                <path d="M14 8H8" />
                <path d="M16 12H8" />
                <path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z" />
            </svg>
            พรบ./ต่อภาษี
        </a>
    <?php endif; ?>
</div>