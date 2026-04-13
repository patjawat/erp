<?php

/**
 * Main layout: uses shared _head, _header, _navbar, _footer.
 * Bootstrap + Lucide จาก AppAsset (ไม่ใช้ CDN).
 */

use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100 telegram-app">

<head>
    <?= $this->render('_head') ?>
</head>

<body class="d-flex flex-column">
    <?php $this->beginBody() ?>

    <div id="mobile-loading-overlay" class="mobile-loading-overlay" role="status" aria-live="polite" aria-label="กำลังโหลด">
        <div class="mobile-loading-spinner" aria-hidden="true"></div>
        <p class="mobile-loading-text">กำลังโหลด...</p>
    </div>

    <?= $this->render('_header') ?>

    <style>
        body {
            margin: 0;
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            background: var(--tg-bg, #f5f6f8);
            color: var(--tg-text, #000);
        }

        .mobile-app-content {
            min-height: 100vh;
            padding-bottom: 90px;
        }

        .mobile-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }

        .mobile-loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #ddd;
            border-top: 4px solid #0088cc;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .mobile-loading-text {
            margin-top: 10px;
            font-size: 14px;
        }

        .telegram-navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #fff;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }

        .telegram-navbar a {
            text-decoration: none;
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .telegram-navbar a.active {
            color: #0088cc;
        }
    </style>


    <main class="mobile-app-content flex-grow-1 px-3 py-3 mb-5">
        <?= $content ?>
    </main>

    <?= $this->render('_navbar') ?>
    <?= $this->render('_footer') ?>

    <?php $this->endBody() ?>

</body>

</html>
<?php $this->endPage() ?>