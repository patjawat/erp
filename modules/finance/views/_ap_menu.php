<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** เมนูงานเจ้าหนี้ (การเงิน) — กล่องรอรับ / ทะเบียนคุมเจ้าหนี้ / เจ้าหนี้ค้างชำระ */
$active = $active ?? '';
$items = [
    ['key' => 'inbox', 'label' => 'กล่องรอรับ', 'icon' => 'bi-inbox', 'url' => ['/finance/inbox']],
    ['key' => 'payable', 'label' => 'ทะเบียนคุมเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
    ['key' => 'aging', 'label' => 'เจ้าหนี้ค้างชำระ', 'icon' => 'bi-hourglass-split', 'url' => ['/finance/payable/aging']],
    ['key' => 'cheque', 'label' => 'พิมพ์เช็ค', 'icon' => 'bi-cash-stack', 'url' => ['/finance/cheque']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="เมนูงานเจ้าหนี้">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>" class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
