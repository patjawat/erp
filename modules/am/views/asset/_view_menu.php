<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="bg-light p-2 rounded-3 d-flex flex-wrap gap-2">
    <a href="<?= Url::to(['view-asset', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'detail'  ? 'btn-primary' : 'bg-body' ?>">
        <i data-lucide="file-text"></i>
        รายละเอียด
    </a>
    <?php // Html::a('<i data-lucide="arrow-left-right"></i> โอนย้าย', ['/am/asset/transfer', 'asset_id' => $model->id], ['class' => 'btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn bg-body']) ?>
    <?php // Html::a('<i data-lucide="wrench"></i> ส่งซ่อม', ['/am/asset/repair', 'asset_id' => $model->id], ['class' => 'btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn bg-body']) ?>
    <?php //  Html::a('<i data-lucide="trash-2"></i> จำหน่าย', ['/am/asset/dispose', 'asset_id' => $model->id], ['class' => 'btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn bg-body']) ?>
   
    <?php if ($model->asset_group_id == 4): ?>
        <a href="<?= Url::to(['repair-history', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'repair_history'  ? 'btn-primary' : 'bg-body' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path>
            </svg>
            ประวัติซ่อมบำรุง
        </a>


    <a href="<?= Url::to(['maintenance', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'maintenance'  ? 'btn-primary' : 'bg-body' ?>">
        <i data-lucide="brush-cleaning"></i>
        ประวัติการ PM
    </a>

        <a href="<?= Url::to(['calibration', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'calibration'  ? 'btn-primary' : 'bg-body' ?>">
            <i data-lucide="git-compare-arrows"></i>
             ประวัติการ CAL
        </a>

                <a href="<?= Url::to(['borrow', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'borrow'  ? 'btn-primary' : 'bg-body' ?>">
            <i data-lucide="calendar-sync"></i> 
             ประวัติการยืมคืน
        </a>

            <a href="<?= Url::to(['move', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'move'  ? 'btn-primary' : 'bg-body' ?>">
            <i data-lucide="arrow-left-right"></i>  
             ประวัติการเคลื่อนย้าย
        </a>
        <a href="<?= Url::to(['document', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'documents'  ? 'btn-primary' : 'bg-body' ?>">
            <i data-lucide="paperclip"></i>
            เอกสารแนบ
        </a>
        <?php endif; ?>
        <?php if ($model->license_plate): ?>
            <a href="<?= Url::to(['vehicle-tax', 'id' => $model->id]) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $menu === 'vehicle-tax'  ? 'btn-primary' : 'bg-body' ?>">
                <i data-lucide="shield-check"></i>
                พรบ./ต่อภาษี
            </a>
        <?php endif; ?>
</div>