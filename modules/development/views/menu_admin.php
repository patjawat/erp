<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * เมนูสำหรับผู้ดูแลระบบ — ภาพรวม, ผู้ตรวจสอบ, การตั้งค่าระบบ, แบบฟอร์มไปราชการ (แยกคนละเรื่อง)
 * @var string $active 'dashboard' | 'approver' | 'setting-system' | 'setting-form'
 */
$active = $active ?? 'dashboard';
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <a href="<?= Url::to(['/development/default/dashboard']) ?>" class="btn <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-grid-1x2"></i> ภาพรวม
    </a>
    <a href="<?= Url::to(['/development/approver/index']) ?>" class="btn <?= $active === 'approver' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-person-check"></i> ผู้ตรวจสอบ
    </a>
    <div class="dropdown">
        <button class="btn <?= in_array($active, ['setting-system', 'setting-form'], true) ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" id="developmentMenuSystem" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear"></i> การตั้งค่า
        </button>
        <ul class="dropdown-menu" aria-labelledby="developmentMenuSystem">
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> แบบฟอร์มไปราชการ', ['/development/setting/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-file-earmark-pdf me-1"></i> Template แบบฟอร์มไปราชการ', ['/development/setting/template'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> ประเภทการอบรม/ประชุม/ดูงาน', ['/settings/categorise/index', 'name' => 'development_type', 'title' => 'ประเภทการอบรม/ประชุม/ดูงาน'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> ประเภทยานพาหนะ', ['/settings/categorise/index', 'name' => 'vehicle_type', 'title' => 'ประเภทยานพาหนะ'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> ประเภทค่าใช้จ่าย', ['/settings/categorise/index', 'name' => 'expense_type', 'title' => 'ประเภทค่าใช้จ่าย'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
</div>
