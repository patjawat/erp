<?php

use yii\helpers\Html;
use app\modules\housing\models\HousingRequest;

/** @var string $active */
$items = [
    'dashboard' => ['label' => 'กระดานห้องพัก', 'url' => ['/housing/dashboard/index'], 'icon' => 'layout-dashboard'],
    'building' => ['label' => 'บ้านพักและแฟลต', 'url' => ['/housing/building/index'], 'icon' => 'building-2'],
    'unit' => ['label' => 'ยูนิตและห้อง', 'url' => ['/housing/unit/index'], 'icon' => 'door-open'],
    'maintenance' => ['label' => 'แจ้งซ่อม', 'url' => ['/housing/maintenance/index'], 'icon' => 'wrench'],
    'utility' => ['label' => 'ค่าใช้จ่าย', 'url' => ['/housing/utility/index'], 'icon' => 'calculator'],
    'request' => ['label' => 'คำขอ', 'url' => ['/housing/request/index'], 'icon' => 'clipboard-list'],
    'guest' => ['label' => 'บุคคลภายนอก', 'url' => ['/housing/guest/index'], 'icon' => 'user-round-plus'],
];
$newRequestCount = (int)HousingRequest::find()
    ->where(['status' => HousingRequest::STATUS_SUBMITTED])
    ->count();
?>
<div class="d-flex flex-wrap gap-2">
    <?php foreach ($items as $key => $item): ?>
        <?= Html::a(
            '<i data-lucide="' . $item['icon'] . '"></i> ' . Html::encode($item['label'])
                . ($key === 'request' && $newRequestCount > 0
                    ? ' <span class="badge rounded-pill text-bg-danger ms-1">' . $newRequestCount . '</span>'
                    : ''),
            $item['url'],
            ['class' => 'btn btn-sm ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
    <?php endforeach; ?>
</div>
