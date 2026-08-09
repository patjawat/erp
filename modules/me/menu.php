<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ApproveHelper;
use app\components\SiteHelper;

$active = $active ?? '';
$notify = $notify ?? ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];
$siteInfo = SiteHelper::getInfo();
$manualUrl = !empty($siteInfo['manual']) ? $siteInfo['manual'] : Url::to(['/me/guide']);

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
            รออนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary ms-1"> <?= $total ?> </span>
        </a>
    <?php endif; ?>

    <a href="<?= Url::to(!empty(env('INVENTORY_SUB_URL')) ? env('INVENTORY_SUB_URL') : ['/me/store-v2/dashboard']) ?>" class="btn btn-outline-primary">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-store-icon lucide-store">
                <path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5" />
                <path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244" />
                <path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05" />
            </svg>
        คลังหน่วยงาน
    </a>
    <?php $meEmp = \app\components\UserHelper::GetEmployee(); ?>
    <?php
    // งานประเมินสมรรถนะ — ขึ้นเฉพาะเมื่อ HR เปิดรอบและมอบหมายให้คนนี้เป็นผู้ประเมิน
    $competencyPending = 0;
    if ($meEmp) {
        try {
            $competencyPending = (int) \app\modules\hr\models\CompetencyAssignment::find()
                ->alias('a')
                ->innerJoin(['r' => \app\modules\hr\models\AppraisalRound::tableName()], 'r.id = a.round_id')
                ->leftJoin(['ev' => \app\modules\hr\models\CompetencyEvaluation::tableName()], 'ev.assignment_id = a.id')
                ->where(['a.evaluator_id' => $meEmp->id, 'r.status' => \app\modules\hr\models\AppraisalRound::STATUS_OPEN])
                ->andWhere(['or', ['ev.id' => null], ['<>', 'ev.status', \app\modules\hr\models\CompetencyEvaluation::STATUS_SUBMITTED]])
                ->count();
        } catch (\Throwable $e) {
            $competencyPending = 0; // ยังไม่ได้ migrate ตารางสมรรถนะ
        }
    }
    ?>
    <?php if ($competencyPending > 0): ?>
        <a href="<?= Url::to(['/me/competency']) ?>" class="btn <?= $active !== 'competency' ? 'btn-outline-primary' : 'btn-primary' ?> position-relative">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check">
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <path d="m9 14 2 2 4-4" />
            </svg>
            ประเมินสมรรถนะ
            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"><?= $competencyPending ?></span>
        </a>
    <?php endif; ?>
    <?php if ($meEmp && $meEmp->isDepartmentHead()): ?>
        <a href="<?= Url::to(['/me/plan']) ?>" class="btn <?= $active !== 'plan' ? 'btn-outline-primary' : 'btn-primary' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list">
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <path d="M12 11h4" /><path d="M12 16h4" /><path d="M8 11h.01" /><path d="M8 16h.01" />
            </svg>
            จัดทำแผนหน่วยงาน
        </a>
    <?php endif; ?>


    <a href="<?= $manualUrl ?>" class="btn <?= $active !== 'guide' ? 'btn-outline-primary' : 'btn-primary' ?>" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
        </svg>
        คู่มือ
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
