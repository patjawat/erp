<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
/** @var int $fy */
$active = $active ?? '';
$fy = $fy ?? null;

$items = [
    ['key' => 'index', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/finance/budget/index']],
    ['key' => 'allotments', 'label' => 'เงินประจำงวด', 'icon' => 'bi-inboxes', 'url' => ['/finance/budget/allotments']],
    ['key' => 'transactions', 'label' => 'รับ-จ่ายงบประมาณ', 'icon' => 'bi-arrow-left-right', 'url' => ['/finance/budget/transactions']],
    ['key' => 'treasury', 'label' => 'นำส่งคลัง (นส.02)', 'icon' => 'bi-send', 'url' => ['/finance/budget/treasury']],
    ['key' => 'returns', 'label' => 'เบิกเกินส่งคืน', 'icon' => 'bi-arrow-return-left', 'url' => ['/finance/budget/returns']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="เมนูเงินงบประมาณ">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($fy ? $item['url'] + ['fiscal_year' => $fy] : $item['url']) ?>"
           class="btn btn-sm <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
