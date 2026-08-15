<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$items = [
    ['key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/finance/dashboard']],
    ['key' => 'inbox', 'label' => 'กล่องรับงานบัญชี', 'icon' => 'bi-inbox', 'url' => ['/finance/inbox']],
    ['key' => 'payable', 'label' => 'ทะเบียนเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
    ['key' => 'budget', 'label' => 'งบประมาณ', 'icon' => 'bi-pie-chart', 'url' => ['/finance/dashboard', '#' => 'budget-overview']],
    ['key' => 'voucher', 'label' => 'เบิกจ่าย', 'icon' => 'bi-file-earmark-check', 'url' => ['/finance/voucher']],
    ['key' => 'loan', 'label' => 'เงินยืม', 'icon' => 'bi-person-vcard', 'url' => ['/finance/loan']],
    ['key' => 'payment', 'label' => 'รับ–จ่ายเงิน', 'icon' => 'bi-bank', 'url' => ['/finance/payment']],
];
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนูระบบการเงิน">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i>
            <?= Html::encode($item['label']) ?>
        </a>
    <?php endforeach; ?>

    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots me-1" aria-hidden="true"></i> เพิ่มเติม
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-cash-stack me-2"></i>เงินเดือน</button></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-bar-chart-line me-2"></i>รายงาน</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-gear me-2"></i>ตั้งค่าระบบ</button></li>
        </ul>
    </div>
</nav>
