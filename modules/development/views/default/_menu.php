<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * เมนูโมดูล Development — ไม่เชื่อมระบบเก่า ให้เริ่มสร้างใหม่ได้จากโมดูลนี้
 * @var string $active ค่า active: 'dashboard' | 'mine' | 'approver' | 'create' | 'setting'
 */
$active = $active ?? 'dashboard';
$canManageSetting = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <a href="<?= Url::to(['/development/default/dashboard']) ?>" class="btn <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        <span class="d-none d-sm-inline ms-1">ภาพรวม</span>
    </a>
    <a href="<?= Url::to(['/development/default/mine']) ?>" class="btn <?= $active === 'mine' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-3">
        <i class="bi bi-list-ul"></i>
        <span class="d-none d-sm-inline ms-1">รายการของฉัน</span>
    </a>
    <?php if ($canManageSetting): ?>
    <a href="<?= Url::to(['/development/approver/index']) ?>" class="btn <?= $active === 'approver' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-3">
        <i class="bi bi-person-check"></i>
        <span class="d-none d-sm-inline ms-1">ผู้ตรวจสอบ</span>
    </a>
    <?php endif; ?>
    <?php if ($canManageSetting): ?>
    <a href="<?= Url::to(['/development/setting/index']) ?>" class="btn <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-3">
        <i class="bi bi-gear"></i>
        <span class="d-none d-sm-inline ms-1">ตั้งค่าแบบฟอร์ม</span>
    </a>
    <?php endif; ?>
</div>
