<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * เมนูระดับโซน "งานคุณภาพ" สำหรับวางในบล็อก page-action (มุมขวาหัวข้อหน้า)
 * จัดวางแนวเดียวกับงาน HRD (modules/hr/menu.php) — ปุ่ม btn-outline-primary / active=btn-primary
 *
 * @var string $active  'qms' | 'km'
 */
$active = $active ?? '';
?>
<div class="d-flex align-items-center gap-2 flex-wrap justify-content-center justify-content-lg-end">
    <a href="<?= Url::to(['/qms/default/index']) ?>"
       aria-label="มาตรฐานโรงพยาบาล"
       class="btn <?= $active === 'qms' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">มาตรฐานโรงพยาบาล</span>
    </a>

    <a href="<?= Url::to(['/km/default/index']) ?>"
       aria-label="คลังกิจกรรม KM"
       class="btn <?= $active === 'km' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i class="bi bi-collection" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">คลังกิจกรรม KM</span>
    </a>

    <?php if ($active === 'km'): ?>
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2"
                type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="ตั้งค่า">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><?= Html::a('<i class="bi bi-tags me-2"></i>จัดการหมวดหมู่', ['/km/category/index'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
    <?php endif; ?>
</div>
