<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$items = [
    ['key' => 'daily', 'label' => 'ปิดบัญชีประจำวัน', 'icon' => 'bi-calendar-day', 'url' => ['/finance/cash/close-daily']],
    ['key' => 'summary', 'label' => 'สรุปการปิดบัญชี', 'icon' => 'bi-list-check', 'url' => ['/finance/cash/close-summary']],
    ['key' => 'yearly', 'label' => 'รายงานประจำปี', 'icon' => 'bi-bar-chart-line', 'url' => ['/finance/cash/close-yearly']],
];
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($items as $it): ?>
        <a href="<?= Url::to($it['url']) ?>" class="btn btn-sm rounded-pill <?= $active === $it['key'] ? 'btn-dark' : 'btn-outline-dark' ?>">
            <i class="bi <?= $it['icon'] ?> me-1"></i><?= Html::encode($it['label']) ?>
        </a>
    <?php endforeach; ?>
</div>
