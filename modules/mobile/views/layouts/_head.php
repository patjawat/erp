<?php
/**
 * Head section: viewport meta, custom CSS.
 * Bootstrap 5 และ Lucide โหลดจาก AppAsset (ไม่ใช้ CDN).
 */
use yii\bootstrap5\Html;
?>

<meta charset="<?= Yii::$app->charset ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no, viewport-fit=cover">
<?php $this->registerCsrfMetaTags() ?>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<style>
    :root {
        --mobile-primary: #0d6efd;
        --mobile-primary-dark: #0a58ca;
        --mobile-primary-soft: rgba(13, 110, 253, 0.06);
        --mobile-primary-soft-border: rgba(13, 110, 253, 0.15);
        --mobile-primary-soft-strong: rgba(13, 110, 253, 0.25);
    }
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f0f2f5;
        min-height: 100vh;
        padding-bottom: env(safe-area-inset-bottom);
    }
    .mobile-app-content {
        padding-bottom: calc(env(safe-area-inset-bottom) + 5.5rem);
        min-height: 100vh;
    }
    /* Shared card shell — every mobile card variant. Add the class name here, skip the per-view block. */
    .mobile-card,
    .leave-card, .booking-card, .maint-card, .vehicle-card,
    .meeting-booking-card, .meeting-calendar, .meeting-view-card,
    .home-greeting-card, .home-quick-action, .service-menu-card,
    .room-card, .room-manage-card,
    .notif-card, .profile-card, .asset-card, .req-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }
    /* Shared form-control radius inside any mobile-* card */
    .mobile-card .form-control, .mobile-card .form-select,
    .leave-card .form-control, .leave-card .form-select,
    .booking-card .form-control, .booking-card .form-select,
    .maint-card .form-control, .maint-card .form-select,
    .meeting-booking-card .form-control, .meeting-booking-card .form-select {
        border-radius: 12px;
        padding: 0.75rem 1rem;
    }
    .leave-card .form-label,
    .booking-card .form-label,
    .maint-card .form-label,
    .meeting-booking-card .form-label {
        font-weight: 500;
    }
    .mobile-header {
        background: #fff;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
    }

    /* Icon sizing utilities — replaces inline `style="width:...; height:...;"` */
    .mi-xs { width: 1rem;    height: 1rem;    flex-shrink: 0; }
    .mi-sm { width: 1.125rem; height: 1.125rem; flex-shrink: 0; }
    .mi-md { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
    .mi-lg { width: 1.75rem; height: 1.75rem; flex-shrink: 0; }
    .mi-xl { width: 2rem;    height: 2rem;    flex-shrink: 0; }
    .mi-baseline { vertical-align: -0.2em; }

    /* Keyboard focus — visible ring on every interactive surface in mobile module */
    .mobile-nav-item:focus-visible,
    .home-quick-action:focus-visible,
    .service-menu-card:focus-visible,
    .check-type-btn:focus-visible,
    .btn-attach:focus-visible,
    .maint-btn-camera:focus-visible,
    .maint-btn-gallery:focus-visible,
    .vehicle-card:focus-visible,
    .room-card:focus-visible,
    .home-request-item:focus-visible {
        outline: 2px solid var(--mobile-primary);
        outline-offset: -2px;
        border-radius: 12px;
    }

    /* Reduced motion — disable transitions/animations for users who request it */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    /* ---- Loading page ---- */
    .mobile-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #f5f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
    }
    .mobile-loading-overlay.mobile-loading-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .mobile-loading-spinner {
        width: 2.75rem;
        height: 2.75rem;
        border: 3px solid rgba(13, 110, 253, 0.2);
        border-top-color: var(--mobile-primary);
        border-radius: 50%;
        animation: mobile-loading-spin 0.7s linear infinite;
    }
    .mobile-loading-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }
    @keyframes mobile-loading-spin {
        to { transform: rotate(360deg); }
    }
</style>
