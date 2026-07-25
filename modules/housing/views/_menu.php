<?php

use yii\helpers\Html;

/** @var string $active */
$items = [
    'dashboard' => ['label' => 'กระดานห้องพัก', 'url' => ['/housing/dashboard/index'], 'icon' => 'layout-dashboard'],
    'building' => ['label' => 'บ้านพักและแฟลต', 'url' => ['/housing/building/index'], 'icon' => 'building-2'],
    'unit' => ['label' => 'ยูนิตและห้อง', 'url' => ['/housing/unit/index'], 'icon' => 'door-open'],
    'request' => ['label' => 'คำขอ', 'url' => ['/housing/request/index'], 'icon' => 'clipboard-list'],
    'guest' => ['label' => 'บุคคลภายนอก', 'url' => ['/housing/guest/index'], 'icon' => 'user-round-plus'],
];
?>
<div class="d-flex flex-wrap gap-2">
    <?php foreach ($items as $key => $item): ?>
        <?= Html::a(
            '<i data-lucide="' . $item['icon'] . '"></i> ' . Html::encode($item['label']),
            $item['url'],
            ['class' => 'btn btn-sm ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
    <?php endforeach; ?>
</div>
