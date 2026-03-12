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
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-icon lucide-package"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/></svg>
        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-mouse-pointer-icon lucide-square-mouse-pointer"><path d="M12.034 12.681a.498.498 0 0 1 .647-.647l9 3.5a.5.5 0 0 1-.033.943l-3.444 1.068a1 1 0 0 0-.66.66l-1.067 3.443a.5.5 0 0 1-.943.033z"/><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/></svg> -->
        <a href="<?= Url::to(['/me']) ?>">

            <div style="line-height: 1;">
                <div class="fw-bold text-white" style="font-size: 1.35rem; letter-spacing: 0.5px;">HOSPITAL</div>
                <div class="text-white-50" style="font-size: 11px; font-weight: 500; letter-spacing: 1px;">ERP SYSTEM</div>
            </div>
        </a>
    </div>
    <div class="d-flex align-items-center">
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="header-btn d-none" id="erp-install-pwa" title="ติดตั้งแอป (PWA)">
                <i data-lucide="download"></i>
            </button>
            <!-- <button type="button" class="header-btn" id="erp-test-notification" title="ทดสอบการแจ้งเตือน (Push)">
                <i data-lucide="bell-ring"></i>
            </button> -->
            <a href="<?= Url::to(['/approve-v2/leave']) ?>" class="header-btn position-relative">
                <i data-lucide="bell"></i>
                <?php if ($total > 0): ?>
                    <span class="position-absolute bottom-0 start-0 translate-middle badge rounded-pill text-bg-danger"><?= $total ?> </span>
                <?php endif; ?>
            </a>
            <button class="header-btn" id="toggleNavbar">
                <i data-lucide="menu"></i>
            </button>
            <button type="button" class="header-btn d-none d-lg-flex" id="toggleFullscreen"><i data-lucide="maximize"></i> </button>
            <?php if(yii::$app->user->can('admin')):?>
            <a href="<?= Url::to(['/settings']) ?>" class="header-btn">
                <i data-lucide="settings"></i>
            </a>
            <?php endif;?>

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
                            ['class' => 'dropdown-item']
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

    /* โหมดสีใช้ data-bs-theme จาก layout (ยกเลิก dark mode แล้ว) */
    $('html').attr('data-bs-theme', '$colorName');
    localStorage.removeItem('theme');

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