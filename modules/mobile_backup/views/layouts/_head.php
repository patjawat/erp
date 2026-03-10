<?php
/**
 * Head section: viewport meta, custom CSS.
 * Bootstrap 5 และ Lucide โหลดจาก AppAsset (ไม่ใช้ CDN).
 */
use yii\bootstrap5\Html;
?>
<meta charset="<?= Yii::$app->charset ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
<?php $this->registerCsrfMetaTags() ?>
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<style>
    :root {
        --mobile-primary: #0d6efd;
        --mobile-primary-dark: #0a58ca;
        --mobile-fab-shadow: 0 4px 14px rgba(13, 110, 253, 0.4);
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
    .mobile-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }
    .mobile-header {
        background: #fff;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
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
        border: 3px solid rgba(93, 95, 239, 0.2);
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
