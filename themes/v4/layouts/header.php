<?php
use yii\helpers\Url;
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
        min-width: max-content;
    }

    /* ถ้าเมนูเป็น ul */
    .erp-nav-list ul {
        display: flex;
        flex-wrap: nowrap;
    }
</style>
<header class="header-fixed container-fluid d-flex align-items-center justify-content-between px-4">
    <div class="d-flex align-items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
            <path d="M12 22V12"></path>
            <polyline points="3.29 7 12 12 20.71 7"></polyline>
            <path d="m7.5 4.27 9 5.15"></path>
        </svg>
        <a href="<?=Url::to(['/me'])?>">

            <div style="line-height: 1;">
                <div class="fw-bold text-white" style="font-size: 1.35rem; letter-spacing: 0.5px;">HOSPITAL</div>
                <div class="text-white-50" style="font-size: 11px; font-weight: 500; letter-spacing: 1px;">ERP SYSTEM</div>
            </div>
        </a>
    </div>
    <div class="d-flex align-items-center">
        <div class="d-flex align-items-center gap-1">
            <button class="header-btn" id="toggleNavbar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-text-align-justify-icon lucide-text-align-justify">
                    <path d="M3 5h18" />
                    <path d="M3 12h18" />
                    <path d="M3 19h18" />
                </svg>
            </button>
            <button type="button" class="header-btn" id="toggleTheme"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path>
                </svg></button>
            <button type="button" class="header-btn d-none d-lg-flex"  id="toggleFullscreen"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3"></path>
                    <path d="M21 8V5a2 2 0 0 0-2-2h-3"></path>
                    <path d="M3 16v3a2 2 0 0 0 2 2h3"></path>
                    <path d="M16 21h3a2 2 0 0 0 2-2v-3"></path>
                </svg></button>
<a href="<?=Url::to(['/settings'])?>"  class="header-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
</a>

        </div>
        <div class="header-divider"></div>
        <div class="d-flex align-items-center gap-3 header-profile">
            <div class="rounded-circle border border-2 border-white border-opacity-50 d-flex align-items-center justify-content-center overflow-hidden" style="width: 38px; height: 38px; background-color: rgba(255,255,255,0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="d-none d-md-block text-white fw-medium">Administrator Lastname</div>
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
    // const theme = localStorage.getItem('theme') || '';
    // $('html').attr('data-bs-theme', theme);

    // $('#toggleTheme').on('click', function () {
    //     const current = $('html').attr('data-bs-theme');
    //     const next = current === 'dark' ? '' : 'dark';

    //     $('html').attr('data-bs-theme', next);
    //     localStorage.setItem('theme', next);
    // });

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