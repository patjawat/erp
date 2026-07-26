<?php

use yii\helpers\Html;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Checkout;

/** @var string $active */
$primaryItems = [
    'dashboard' => ['label' => 'ภาพรวม', 'url' => ['/housing/dashboard/index'], 'icon' => 'layout-dashboard'],
    'building' => ['label' => 'บ้านพักและแฟลต', 'url' => ['/housing/building/index'], 'icon' => 'building-2'],
    'unit' => ['label' => 'ยูนิตและห้อง', 'url' => ['/housing/unit/index'], 'icon' => 'door-open'],
    'request' => ['label' => 'คำขอ', 'url' => ['/housing/request/index'], 'icon' => 'clipboard-list'],
];
$operationItems = [
    'maintenance' => ['label' => 'งานแจ้งซ่อม', 'url' => ['/housing/maintenance/index'], 'icon' => 'wrench'],
    'utility' => ['label' => 'ค่าใช้จ่ายประจำเดือน', 'url' => ['/housing/utility/index'], 'icon' => 'calculator'],
    'checkout' => ['label' => 'ส่งคืน', 'url' => ['/housing/checkout/index'], 'icon' => 'log-out'],
    'guest' => ['label' => 'บุคคลภายนอก', 'url' => ['/housing/guest/index'], 'icon' => 'user-round-plus'],
];
$newRequestCount = (int)HousingRequest::find()
    ->where(['status' => HousingRequest::STATUS_SUBMITTED])
    ->count();
$checkoutCount = (int)Checkout::find()->where(['status' => Checkout::STATUS_REQUESTED])->count();
$operationActive = array_key_exists($active, $operationItems);
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?php foreach ($primaryItems as $key => $item): ?>
        <?= Html::a(
            '<i data-lucide="' . $item['icon'] . '"></i> ' . Html::encode($item['label'])
                . ($key === 'request' && $newRequestCount > 0
                    ? ' <span class="badge rounded-pill text-bg-danger ms-1">' . $newRequestCount . '</span>'
                    : ''),
            $item['url'],
            ['class' => 'btn btn-sm ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
    <?php endforeach; ?>
    <div class="dropdown">
        <button
            class="btn btn-sm dropdown-toggle <?= $operationActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i data-lucide="briefcase-business"></i>
            งานดำเนินการ
            <?php if ($checkoutCount > 0): ?><span class="badge rounded-pill text-bg-danger ms-1"><?= $checkoutCount ?></span><?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <?php foreach ($operationItems as $key => $item): ?>
                <li>
                    <?= Html::a(
                        '<span class="d-inline-flex align-items-center gap-2"><i data-lucide="' . $item['icon'] . '"></i>'
                            . Html::encode($item['label']) . '</span>'
                            . ($key === 'checkout' && $checkoutCount > 0
                                ? '<span class="badge rounded-pill text-bg-danger ms-auto">' . $checkoutCount . '</span>'
                                : ''),
                        $item['url'],
                        [
                            'class' => 'dropdown-item d-flex align-items-center justify-content-between gap-3'
                                . ($active === $key ? ' active' : ''),
                            'aria-current' => $active === $key ? 'page' : null,
                        ]
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
