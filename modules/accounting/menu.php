<?php

use yii\helpers\Html;
use yii\helpers\Url;

$active = $active ?? '';
$items = [
    ['key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/accounting/dashboard']],
    ['key' => 'inbox', 'label' => 'กล่องรับงาน', 'icon' => 'bi-inbox', 'url' => ['/accounting/inbox']],
    ['key' => 'payable', 'label' => 'ทะเบียนเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/accounting/payable']],
];
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนูระบบบัญชี">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>" class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
        </a>
    <?php endforeach; ?>
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots me-1" aria-hidden="true"></i>เพิ่มเติม
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-person-lines-fill me-2"></i>ทะเบียนลูกหนี้</button></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-box-seam me-2"></i>สินค้าคงคลังทางบัญชี</button></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-building me-2"></i>สินทรัพย์และค่าเสื่อมราคา</button></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-book me-2"></i>บัญชีแยกประเภท</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-calendar-check me-2"></i>ปิดงวดและรายงาน</button></li>
        </ul>
    </div>
</nav>
