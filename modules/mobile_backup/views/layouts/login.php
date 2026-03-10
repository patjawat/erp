<?php
/**
 * Layout หน้า login — mobile-first, ไม่มี header bar (เนื้อหาหน้า login มี top section เป็นของตัวเอง)
 */
use app\assets\AppAsset;
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <?= $this->render('_head') ?>
    <style>
        body.mobile-login-page { background: #f0f2f5 !important; }
        body.mobile-login-page .mobile-login-top { min-height: 10rem; }
    </style>
</head>
<body class="d-flex flex-column mobile-login-page">
<?php $this->beginBody() ?>

<div id="mobile-loading-overlay" class="mobile-loading-overlay" role="status" aria-live="polite" aria-label="กำลังโหลด">
    <div class="mobile-loading-spinner" aria-hidden="true"></div>
    <p class="mobile-loading-text">กำลังโหลด...</p>
</div>

<main class="flex-grow-1 d-flex flex-column min-vh-100">
    <?= $content ?>
</main>

<?= $this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
