<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$items = [
    ['key' => 'bank', 'label' => 'บัญชีธนาคาร', 'icon' => 'bi-bank2', 'url' => ['/finance/account/bank']],
    ['key' => 'treasury', 'label' => 'บัญชีเงินฝากคลัง', 'icon' => 'bi-safe2', 'url' => ['/finance/account/treasury']],
    ['key' => 'summary', 'label' => 'เงินคงเหลือสะสม', 'icon' => 'bi-cash-stack', 'url' => ['/finance/account/summary']],
    ['key' => 'transfer', 'label' => 'โอนเงินข้ามบัญชี', 'icon' => 'bi-arrow-left-right', 'url' => ['/finance/account/transfer']],
];
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($items as $it): ?>
        <a href="<?= Url::to($it['url']) ?>" class="btn btn-sm rounded-pill <?= $active === $it['key'] ? 'btn-info' : 'btn-outline-info' ?>">
            <i class="bi <?= $it['icon'] ?> me-1"></i><?= Html::encode($it['label']) ?>
        </a>
    <?php endforeach; ?>
</div>
