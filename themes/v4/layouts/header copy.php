<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\UserHelper;
use app\components\ApproveHelper;

$site = Categorise::findOne(['name' => 'site']);
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : 'blue';
$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalBookingCar = $notify['booking_car']['total'];
$totalPurchase = $notify['purchase']['total'];
$totalStock = $notify['stock']['total'];
$totalDevelopment = $notify['development']['total'];
$totalAssetMove = $notify['assetMove']['total'];
?>
<style>
    /* container สำหรับ animation เปิด–ปิด */
    .navbar-fixed-container {
        max-height: 0;
        opacity: 0;

        /* แยก overflow ให้ชัด */
        overflow-y: hidden;
        overflow-x: auto;

        transition:
            max-height 0.35s ease,
            opacity 0.25s ease;
    }

    /* ตอนแสดง */
    .navbar-fixed-container.show {
        max-height: 600px;
        /* หรือ calc(100vh - header-height) */
        opacity: 1;
    }

    /* ให้เมนูไม่หด */
    .erp-nav-list {
        width: 90%;

    }

    /* ถ้าเมนูเป็น ul */
    .erp-nav-list ul {
        display: flex;
        flex-wrap: nowrap;
    }
</style>
<header class="header-fixed container-fluid d-flex align-items-center justify-content-between px-4">
    <div class="d-flex align-items-center gap-3">
        <i data-lucide="menu"></i>
        <a href="<?= Url::to(['/me']) ?>">

            <div style="line-height: 1;">
                <div class="fw-bold text-white" style="font-size: 1.35rem; letter-spacing: 0.5px;">HOSPITAL</div>
                <div class="text-white-50" style="font-size: 11px; font-weight: 500; letter-spacing: 1px;">ERP SYSTEM</div>
            </div>
        </a>
    </div>
    <div class="d-flex align-items-center">
        <div class="d-flex align-items-center gap-2">

            <div class="dropdown">
                <button class="header-btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="bell"></i>
                     <?php if ($total > 0): ?>
                    <span class="position-absolute bottom-0 start-0 translate-middle badge rounded-pill text-bg-danger"><?= $total ?> </span>
                <?php endif; ?>
                </button>

                <ul class="dropdown-menu pe-4" aria-labelledby="dropdownMenuButton1" style="">
                    <li>
                        <a href="<?= Url::to(['/approve-v2/leave']) ?>"class="dropdown-item">
                            <i data-lucide="calendar" class="me-1"></i> ขออนุมัติการลา
                            <?php if ($totalLeave > 0): ?>
                                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                                    <?= $totalLeave ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= Url::to(['/approve-v2/purchase']) ?>"  class="dropdown-item">
                            <i data-lucide="shopping-cart" class="me-1"></i>   ขออนุมัติจัดซื้อจัดจ้าง
                            <?php if ($totalPurchase > 0): ?>
                                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                                    <?= $totalPurchase ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= Url::to(['/approve-v2/main-stock']) ?>"  class="dropdown-item">
                            <i data-lucide="shopping-basket" class="me-1"></i>   ขออนุมัติเบิกวัสดุ
                            <?php if ($totalStock > 0): ?>
                                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                                    <?= $totalStock ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= Url::to(['/approve-v2/devlopment']) ?>"  class="dropdown-item">
                            <i data-lucide="user-star" class="me-1"></i> ขออนุมัติอบรม/ประชุม/ดูงาน
                            <?php if ($totalDevelopment > 0): ?>
                                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                                    <?= $totalDevelopment ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                     <li>
                        <a href="<?= Url::to(['/approve-v2/asset-move']) ?>"  class="dropdown-item">
                            <i data-lucide="arrow-left-right" class="me-1"></i> ขออนุมัติเคลื่อนย้ายครุภัณฑ์
                            <?php if ($totalAssetMove > 0): ?>
                                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                                    <?= $totalAssetMove ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>


            <button class="header-btn" id="toggleNavbar">
                <i data-lucide="menu"></i>
            </button>
            <button type="button" class="header-btn" id="toggleTheme"><i data-lucide="moon"></i> </button>
            <button type="button" class="header-btn d-none d-lg-flex" id="toggleFullscreen"><i data-lucide="maximize"></i> </button>
            <a href="<?= Url::to(['/settings']) ?>" class="header-btn">
                <i data-lucide="settings"></i>
            </a>

        </div>
        <div class="header-divider"></div>
        <div class="d-inline-flex ms-0 ms-sm-2 dropdown">
            <div class="d-flex align-items-center gap-3 header-profile" data-bs-toggle="dropdown" aria-haspopup="true" type="button"
                id="page-header-profile-dropdown" aria-expanded="false">
                <?php if (UserHelper::GetEmployee()): ?>
                    <div class="rounded-circle border border-2 border-white border-opacity-50 d-flex align-items-center justify-content-center overflow-hidden" style="width: 38px; height: 38px; background-color: rgba(255,255,255,0.1);">
                        <?= Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'avatar avatar-xs me-0']) ?>
                    </div>
                <?php endif; ?>

                <div class="d-none d-md-block text-white fw-medium"><?= UserHelper::GetEmployee()->fullname ?></div>
            </div>


            <div aria-labelledby="page-header-profile-dropdown" class="dropdown-menu-right dropdown-menu">
                <a href="<?= Url::to('/profile') ?>" class="dropdown-item">
                    <i class="fa-solid fa-clipboard-user fs-4 me-3"></i> โปรไฟล์
                </a>
                <a href="<?= Url::to('/profile/setting') ?>" class="dropdown-item">
                    <i class="fa-solid fa-user-gear fs-4 me-3"></i> ตั้งค่า
                </a>
                <a href="<?= Url::to('/profile/line-connect') ?>" class="dropdown-item">
                    <i class="fa-brands fa-line fs-4 me-3 text-success"></i> เชื่อม LineID
                </a>
                <div class="dropdown-divider"></div>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?php
                    echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                        . Html::submitButton(
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-power-icon lucide-power"><path d="M12 2v10"/><path d="M18.4 6.6a9 9 0 1 1-12.77.04"/></svg> &nbsp;ออกจากระบบ',
                            ['class' => 'dropdown-item btn btn-danger']
                        )
                        . Html::endForm();
                    ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>
<?php
$js = <<< JS
$(function () {

    if (localStorage.getItem('fullscreen') === '1') {
        $('#toggleFullscreen').addClass('active');
    }
    /* ======================
     * Toggle Navbar
     * ====================== */
    $('#toggleNavbar').on('click', function () {
        const nav = $('.navbar-fixed-container');

        if (!nav.hasClass('show')) {
            nav.removeClass('d-none').addClass('show');
        } else {
            nav.removeClass('show');
        }
    });

    /* ======================
     * Theme (Bootstrap 5.3)
     * ====================== */
    const theme = localStorage.getItem('theme') || '$colorName';
    $('html').attr('data-bs-theme', theme);

    $('#toggleTheme').on('click', function () {
        const current = $('html').attr('data-bs-theme');
        const next = current === 'dark' ? '$colorName' : 'dark';

        $('html').attr('data-bs-theme', next);
        localStorage.setItem('theme', next);
    });

    /* ======================
     * Fullscreen
     * ====================== */
    $('#toggleFullscreen').on('click', function () {
        const doc = document;
        const docEl = document.documentElement;

        if (!doc.fullscreenElement && !doc.webkitFullscreenElement) {
            if (docEl.requestFullscreen) {
                docEl.requestFullscreen();
            } else if (docEl.webkitRequestFullscreen) {
                docEl.webkitRequestFullscreen();
            }
        } else {
            if (doc.exitFullscreen) {
                doc.exitFullscreen();
            } else if (doc.webkitExitFullscreen) {
                doc.webkitExitFullscreen();
            }
        }
    });

});

JS;
$this->registerJs($js);
?>