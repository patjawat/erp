<?php

use yii\helpers\Url;
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


<div class="d-flex flex-wrap gap-2">
    <a href="<?= Url::to(['/me']) ?>" class="btn <?= ($active ?? '') !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i data-lucide="layout-grid"></i>
        ภาพรวม
    </a>
    <?php if (!Yii::$app->user->can('branch')): ?>
        <a href="<?= Url::to(['/approve-v2']) ?>" class="btn <?= $active !== 'approve' ? 'btn-outline-primary' : 'btn-primary' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-check-icon lucide-user-round-check">
                <path d="M2 21a8 8 0 0 1 13.292-6" />
                <circle cx="10" cy="8" r="5" />
                <path d="m16 19 2 2 4-4" />
            </svg>
            รายการที่รออนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary ms-1"> <?= $total ?> </span>
        </a>
    <?php endif; ?>

    <a href="<?= Url::to(['/me/store-v2/dashboard']) ?>" class="btn btn-outline-primary">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-store-icon lucide-store">
                <path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5" />
                <path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244" />
                <path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05" />
            </svg>
        คลังหน่วยงาน
    </a>

    <a href="<?= Url::to(['/notify/default/index']) ?>" class="btn <?= ($active ?? '') !== 'notify' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i data-lucide="bell"></i>
        แจ้งเตือน
    </a>
    <a href="<?= Url::to(['/me/guide']) ?>" class="btn <?= $active !== 'guide' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
        </svg>
        คู่มือให้งาน
    </a>

    <!-- <div class="dropdown">
        <button class="btn <?= $active !== 'setting' ? 'btn-outline-primary' : 'btn-primary' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-store-icon lucide-store">
                <path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5" />
                <path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244" />
                <path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05" />
            </svg>
            <span class="d-none d-sm-inline">คลังหน่วยงาน</span>
        </button>

        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
            <?= Html::a('<i class="fa-solid fa-gauge me-2"></i> Dashboard ', ['/me/store-v2/dashboard'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="fa-solid fa-cube me-2"></i> เบิกวัสดุคลังหลัก ', ['/me/main-stock/store'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="bi bi-shop me-2"></i> สต๊อก/ตัดจ่าย ', ['/me/store-v2/index'], ['class' => 'dropdown-item']) ?>
        </div>
    </div> -->
</div>