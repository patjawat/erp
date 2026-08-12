<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\UserHelper;
use app\components\ApproveHelper;
use app\components\SiteHelper;

$site = Categorise::findOne(['name' => 'site']);
$dataJson = ($site && is_array($site->data_json)) ? $site->data_json : [];
$colorName = $dataJson['theme_color_name'] ?? 'blue';
$headerBrand = SiteHelper::resolveHeaderBrand($dataJson, \Yii::$app->user->isGuest);
if (!empty($headerBrand['google_font_href'])) {
    $this->registerLinkTag([
        'rel' => 'stylesheet',
        'href' => $headerBrand['google_font_href'],
    ]);
}
$notify = ApproveHelper::Info();
$total = $notify['total'];
$jdNotify = $notify['jd_acknowledgement'] ?? ['total' => 0, 'datas' => []];
$pendingJd = $jdNotify['datas'][0] ?? null;
$jdSignNotify = $notify['jd_signature'] ?? ['total' => 0, 'datas' => []];
$jdReviewNotify = $notify['jd_change_review'] ?? ['total' => 0, 'datas' => [], 'url' => ['/jd/employee-jd/review-inbox']];
$idpNotify = $notify['idp'] ?? ['total' => 0, 'datas' => [], 'url' => ['/profile', 'name' => 'idp']];
$probationNotify = $notify['probation'] ?? ['total' => 0, 'datas' => []];
$housingHandoverNotify = $notify['housing_handover'] ?? ['total' => 0, 'datas' => []];
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

    .erp-header-brand-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: inherit;
    }

    .erp-header-brand-icon svg {
        width: 40px;
        height: 40px;
        stroke-width: 1.25;
    }

</style>
<header class="header-fixed container-fluid d-flex align-items-center justify-content-between px-4">
    <div class="d-flex align-items-center gap-3">
    <span class="erp-header-brand-icon" aria-hidden="true"><i data-lucide="<?= Html::encode($headerBrand['lucide_icon']) ?>"></i></span>
        <a href="<?= Url::to(['/me']) ?>" class="text-decoration-none">
            <div style="line-height: 1;">
                <div style="<?= $headerBrand['line1_style'] ?>"><?= Html::encode($headerBrand['line1']) ?></div>
                <div style="<?= $headerBrand['line2_style'] ?>"><?= Html::encode($headerBrand['line2']) ?></div>
            </div>
        </a>
    </div>
    <div class="d-flex align-items-center">
        <div class="d-flex align-items-center gap-1">

            <div class="dropdown">
                <button type="button"
                    class="header-btn position-relative border-0"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="การแจ้งเตือน">
                    <i data-lucide="bell"></i>
                    <?php if ($total > 0): ?>
                        <span class="position-absolute bottom-0 start-0 translate-middle badge rounded-pill text-bg-danger"><?= $total ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 320px;">
                    <div class="px-3 py-2 border-bottom">
                        <div class="fw-semibold">การแจ้งเตือน</div>
                        <small class="text-body-secondary"><?= $total > 0 ? 'มี ' . $total . ' รายการที่ต้องดำเนินการ' : 'ไม่มีรายการใหม่' ?></small>
                    </div>
                    <?php if ((int) $jdSignNotify['total'] > 0): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3"
                            href="<?= Url::to(['/jd/employee-jd/inbox']) ?>">
                            <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="pen-line" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold">JD รอลงนาม</span>
                                <small class="text-body-secondary"><?= (int) $jdSignNotify['total'] ?> ฉบับที่รอให้คุณลงนาม</small>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if ($pendingJd): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3"
                            href="<?= Url::to(['/hr/employees/view', 'id' => $pendingJd->emp_id, 'name' => 'job_description_current']) ?>">
                            <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="file-signature" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold">JD รอลงนามรับทราบ</span>
                                <small class="text-body-secondary">Job Description Revision <?= (int) $pendingJd->revision_no ?></small>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if ((int) $jdReviewNotify['total'] > 0): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3" href="<?= Url::to($jdReviewNotify['url']) ?>">
                            <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="file-search" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold">คำขอทบทวน JD</span>
                                <small class="text-body-secondary"><?= (int) $jdReviewNotify['total'] ?> คำขอรอ HR รับ/ไม่รับ</small>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if ((int) $idpNotify['total'] > 0): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3" href="<?= Url::to($idpNotify['url']) ?>">
                            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="target" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold">IDP รอดำเนินการ</span>
                                <small class="text-body-secondary"><?= (int) $idpNotify['total'] ?> รายการที่ต้องตรวจสอบหรือปรับปรุง</small>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php foreach ($housingHandoverNotify['datas'] as $handover): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3"
                            href="<?= Url::to(['/housing/my/handover', 'id' => $handover->id]) ?>">
                            <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="house-check" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold">ลงนามรับรองบ้านพัก</span>
                                <small class="text-body-secondary">เอกสาร <?= Html::encode($handover->handover_no) ?> รอคุณตรวจและลงนามรับมอบ</small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($probationNotify['datas'] as $probationItem): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-start py-3" href="<?= Url::to($probationItem['url']) ?>">
                            <span class="rounded-circle bg-info-subtle text-info-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="clipboard-check" style="width: 19px;"></i>
                            </span>
                            <span class="text-wrap">
                                <span class="d-block fw-semibold"><?= Html::encode($probationItem['title']) ?></span>
                                <small class="d-block text-body-secondary"><?= Html::encode($probationItem['employee']->fullname ?? '-') ?></small>
                                <small class="text-body-secondary"><?= Html::encode($probationItem['detail']) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php $otherTotal = $total - (int) $jdNotify['total'] - (int) $jdSignNotify['total'] - (int) $jdReviewNotify['total'] - (int) $idpNotify['total'] - (int) $probationNotify['total'] - (int) $housingHandoverNotify['total']; ?>
                    <?php if ($otherTotal > 0): ?>
                        <a class="dropdown-item d-flex gap-3 align-items-center py-3" href="<?= Url::to(['/approve-v2/leave']) ?>">
                            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i data-lucide="list-checks" style="width: 19px;"></i>
                            </span>
                            <span>
                                <span class="d-block fw-semibold">รายการรออนุมัติ</span>
                                <small class="text-body-secondary"><?= $otherTotal ?> รายการ</small>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if ($total === 0): ?>
                        <div class="px-3 py-4 text-center text-body-secondary">
                            <i data-lucide="check-circle-2" class="mb-2"></i>
                            <div class="small">ดำเนินการครบแล้ว</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button"
                class="header-btn"
                id="toggleNavbar"
                aria-controls="erpPrimaryNavbar"
                aria-expanded="false">
                <i data-lucide="menu"></i>
            </button>
            <button type="button" class="header-btn d-none d-lg-flex" id="toggleFullscreen"><i data-lucide="maximize"></i> </button>
            <?php $isDark = (isset($_COOKIE['theme_mode']) && $_COOKIE['theme_mode'] === 'dark'); ?>
            <button type="button" class="header-btn" id="toggleTheme" title="สลับโหมดสว่าง/มืด" aria-label="สลับโหมดสว่าง/มืด">
                <i data-lucide="<?= $isDark ? 'sun' : 'moon-star' ?>"></i>
            </button>
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

                <div class="d-none d-md-block text-white fw-medium"><?= UserHelper::GetEmployee()->fullname ?? '-' ?></div>
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
    $(document)
        .off('click.erpNavbar', '#toggleNavbar')
        .on('click.erpNavbar', '#toggleNavbar', function (event) {
        event.preventDefault();
        const nav = $('.navbar-fixed-container');
        const willOpen = !nav.hasClass('show');

        if (willOpen) {
            nav.removeClass('d-none').addClass('show');
        } else {
            nav.removeClass('show');
        }

        $(this).attr('aria-expanded', String(willOpen));
    });

    /* ======================
     * สลับโหมดสว่าง/มืด (เก็บรายคนใน cookie theme_mode; light = ใช้สีแบรนด์ '$colorName')
     * ค่าเริ่มต้นถูก render จากฝั่ง PHP แล้ว ที่นี่จัดการเฉพาะตอนกดสลับ
     * ====================== */
    function erpSetThemeIcon(isDark) {
        var button = document.getElementById('toggleTheme');
        if (!button) return;

        var currentIcon = button.querySelector('svg.lucide, i[data-lucide]');
        var nextIcon = document.createElement('i');
        nextIcon.setAttribute('data-lucide', isDark ? 'sun' : 'moon-star');
        nextIcon.setAttribute('aria-hidden', 'true');

        if (currentIcon) {
            currentIcon.replaceWith(nextIcon);
        } else {
            button.prepend(nextIcon);
        }

        var actionLabel = isDark ? 'เปลี่ยนเป็นโหมดสว่าง' : 'เปลี่ยนเป็นโหมดมืด';
        button.setAttribute('title', actionLabel);
        button.setAttribute('aria-label', actionLabel);

        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons();
        }
    }
    $('#toggleTheme').on('click', function () {
        var toDark = document.documentElement.getAttribute('data-bs-theme') !== 'dark';
        document.documentElement.setAttribute('data-bs-theme', toDark ? 'dark' : '$colorName');
        document.cookie = 'theme_mode=' + (toDark ? 'dark' : 'light') + ';path=/;max-age=31536000;samesite=lax';
        erpSetThemeIcon(toDark);
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
