<?php

use yii\helpers\Html;
use app\components\ApproveHelper;

$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];

$menus = [
    ['icon' => 'fa-regular fa-calendar', 'label' => 'ขอลา', 'url' => ['/me/leave']],
    ['icon' => 'fa-solid fa-screwdriver-wrench', 'label' => 'แจ้งซ่อม', 'url' => ['/me/repair-v2']],
    ['icon' => 'fa-solid fa-bag-shopping', 'label' => 'ขอซื้อขอจ้าง', 'url' => ['/me/purchase']],
    ['icon' => 'fa-solid fa-car', 'label' => 'จองรถ', 'url' => ['/me/booking-vehicle/calendar']],
    ['icon' => 'fa-solid fa-handshake', 'label' => 'ห้องประชุม', 'url' => ['/me/booking-meeting/calendar']],
    ['icon' => 'fa-solid fa-briefcase', 'label' => 'อบรม/ประชุม/ดูงาน', 'url' => ['/me/development']],
];
?>

<?php if (!Yii::$app->user->can('branch')): ?>
    <li class="nav-item mt-1">
        <?= Html::a('<i class="fa-solid fa-gauge me-1"></i> MyDashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>', ['/me'], ['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')]) ?>
    </li>
    <li class="nav-item mt-1">
        <?= Html::a('<i class="fa-regular fa-circle-check me-1"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fw-semibold ms-1"> ' . $total . ' </span>', ['/approve'], ['class' => 'nav-link ' . (isset($active) && $active == 'approve' ? 'active' : '')]) ?>
    </li>
<?php endif; ?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?= (isset($active) && $active == 'store' ? 'active' : '') ?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-shop me-1"></i> คลังหน่วยงาน
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?= Html::a('<i class="fa-solid fa-gauge me-2"></i> Dashboard ', ['/me/store-v2/dashboard'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-solid fa-cube me-2"></i> เบิกวัสดุคลังหลัก ', ['/me/main-stock/store'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="bi bi-shop me-2"></i> สต๊อก/ตัดจ่าย ', ['/me/store-v2/index'], ['class' => 'dropdown-item']) ?>
    </div>
</li>
<?php if (!Yii::$app->user->can('branch')): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= (isset($active) && $active == 'service' ? 'active' : '') ?>" href="#"
            id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bi bi-app-indicator me-2"></i> บริการ
            <i class="bx bx-chevron-down"></i>
        </a>


        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
            <?php foreach ($menus as $menu): ?>
                <?= Html::a(
                    '<i class="' . $menu['icon'] . ' me-2"></i> ' . $menu['label'],
                    $menu['url'],
                    ['class' => 'dropdown-item']
                ) ?>
            <?php endforeach; ?>
        </div>

    </li>
<?php endif; ?>