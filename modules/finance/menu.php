<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$items = [
    ['show' => Yii::$app->user->can('financeView'), 'key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/finance/dashboard']],
    ['show' => Yii::$app->user->can('financeOperate'), 'key' => 'loan', 'label' => 'เงินยืม', 'icon' => 'bi-person-vcard', 'url' => ['/finance/loan']],
    ['show' => Yii::$app->user->can('financeOperate'), 'key' => 'payment', 'label' => 'รับ–จ่ายเงิน', 'icon' => 'bi-bank', 'url' => ['/finance/payment']],
    ['show' => Yii::$app->user->can('payrollView'), 'key' => 'payroll', 'label' => 'เงินเดือน', 'icon' => 'bi-cash-stack', 'url' => ['/finance/payroll']],
];
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนูระบบการเงิน">
    <?php foreach ($items as $item): ?>
        <?php if (!$item['show']) continue; ?>
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
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-bar-chart-line me-2"></i>รายงาน</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item" type="button" disabled><i class="bi bi-gear me-2"></i>ตั้งค่าระบบ</button></li>
        </ul>
    </div>
</nav>
