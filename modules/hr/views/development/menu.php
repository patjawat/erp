<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * เมนูหลัก — อบรม/ประชุม/ดูงาน (ขอไปราชการ)
 * @var string $active 'dashboard' | 'index' | 'setting-template'
 */
$active = $active ?? '';
$isSettingActive = ($active === 'setting-template');
?>

<div class="d-flex flex-wrap gap-2 align-items-center">
    <a href="<?= Url::to(['/hr/development/dashboard']) ?>" class="btn <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-2">
        <i class="bi bi-grid-1x2"></i>
        <span class="d-none d-sm-inline">ภาพรวม</span>
    </a>
    <a href="<?= Url::to(['/hr/development/index', 'status' => 'Checking']) ?>" class="btn <?= $active === 'index' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-2">
        <i class="bi bi-journal-check"></i>
        <span class="d-none d-sm-inline">ทะเบียนประวัติ</span>
    </a>
    <div class="dropdown">
        <button class="btn <?= $isSettingActive ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle rounded-2" type="button" id="hrDevelopmentSettingMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear"></i>
            <span class="d-none d-sm-inline">การตั้งค่า</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-2 border-0 py-2" aria-labelledby="hrDevelopmentSettingMenu">
            <li>
                <!-- <?= Html::a('<i class="bi bi-file-earmark-pdf me-2"></i> Template รายงานขอไปราชการ', ['/hr/development/pdf-editor'], ['class' => 'dropdown-item py-2 px-3']) ?> -->
                <?= Html::a('<i class="bi bi-file-earmark-pdf me-2"></i> Template รายงานขอไปราชการ', ['//pdf-template'], ['class' => 'dropdown-item py-2 px-3']) ?>
            </li>
        </ul>
    </div>
</div>