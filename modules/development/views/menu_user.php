<?php

use yii\helpers\Url;

/**
 * เมนูสำหรับผู้ใช้งานทั่วไป — ภาพรวม, รายการของฉัน, สร้างใหม่
 * @var string $active ค่า active: 'dashboard' | 'mine' | 'create'
 */
$active = $active ?? 'dashboard';
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
<a href="<?= Url::to(['/development/default/create']) ?>" class="btn <?= $active === 'create' ? 'btn-primary' : 'btn-outline-success' ?> rounded-3">
        <i class="bi bi-plus-circle"></i>
        <span class="d-none d-sm-inline ms-1">สร้างใหม่</span>
    </a>
    <a href="<?= Url::to(['/development/default/mine']) ?>" class="btn <?= $active === 'mine' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-3">
        <i class="bi bi-list-ul"></i>
        <span class="d-none d-sm-inline ms-1">รายการของฉัน</span>
    </a>
  
</div>
