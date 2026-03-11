<?php
use yii\helpers\Url;

/**
 * เมนูสำหรับหน้าการตั้งค่าระบบคลัง (ลดรายการ)
 * @var string $active 'setting' | 'navigation' | 'stock-item'
 */
$active = $active ?? 'setting';
?>
<nav class="inventory-nav inventory-nav-setting" aria-label="เมนูตั้งค่าระบบคลัง">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= Url::to(['/inventory-v2/default/index']) ?>" class="btn btn-sm <?= $active === 'navigation' ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3" title="เลือกเข้าหน้าคลังหลัก หรือคลังย่อย">
            <i class="bi bi-arrow-left-circle me-1"></i>เลือกบทบาท
        </a>
        <a href="<?= Url::to(['/inventory-v2/default/setting']) ?>" class="btn btn-sm <?= $active === 'setting' ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill px-3">
            <i class="bi bi-gear me-1"></i>ตั้งค่าคลังสินค้า
        </a>
        <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="btn btn-sm <?= $active === 'stock-item' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
            <i class="bi bi-tags me-1"></i>จัดการรายการพัสดุ
        </a>
    </div>
</nav>
