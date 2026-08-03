<?php

use yii\helpers\Html;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Checkout;

/** @var string $active */
$primaryItems = [
    'dashboard' => ['label' => 'ภาพรวม', 'url' => ['/housing/dashboard/index'], 'icon' => 'bi-speedometer2'],
    'building' => ['label' => 'บ้านพักและแฟลต', 'url' => ['/housing/building/index'], 'icon' => 'bi-building'],
    'unit' => ['label' => 'ห้องพัก', 'url' => ['/housing/unit/index'], 'icon' => 'bi-door-open'],
    'request' => ['label' => 'คำขอ', 'url' => ['/housing/request/index'], 'icon' => 'bi-clipboard-check'],
];
$operationItems = [
    'maintenance' => ['label' => 'งานแจ้งซ่อม', 'url' => ['/housing/maintenance/index'], 'icon' => 'bi-wrench'],
    'utility' => ['label' => 'ค่าใช้จ่ายประจำเดือน', 'url' => ['/housing/utility/index'], 'icon' => 'bi-calculator'],
    'checkout' => ['label' => 'ส่งคืน', 'url' => ['/housing/checkout/index'], 'icon' => 'bi-box-arrow-right'],
    'guest' => ['label' => 'บุคคลภายนอก', 'url' => ['/housing/guest/index'], 'icon' => 'bi-person-plus'],
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
            '<i class="bi ' . $item['icon'] . '"></i><span class="d-none d-sm-inline">' . Html::encode($item['label']) . '</span>'
                . ($key === 'request' && $newRequestCount > 0
                    ? ' <span class="badge rounded-pill text-bg-danger ms-1">' . $newRequestCount . '</span>'
                    : ''),
            $item['url'],
            [
                'class' => 'btn d-inline-flex align-items-center gap-2 ' . ($active === $key ? 'btn-primary' : 'btn-outline-primary'),
                'aria-current' => $active === $key ? 'page' : null,
            ]
        ) ?>
    <?php endforeach; ?>
    <div class="dropdown">
        <button
            class="btn dropdown-toggle d-inline-flex align-items-center gap-2 <?= $operationActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i class="bi bi-briefcase"></i><span class="d-none d-sm-inline">งานดำเนินการ</span>
            <?php if ($checkoutCount > 0): ?><span class="badge rounded-pill text-bg-danger ms-1"><?= $checkoutCount ?></span><?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <?php foreach ($operationItems as $key => $item): ?>
                <li>
                    <?= Html::a(
                        '<span class="d-inline-flex align-items-center gap-2"><i class="bi ' . $item['icon'] . '"></i>'
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
