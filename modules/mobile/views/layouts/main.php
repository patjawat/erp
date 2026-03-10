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
<html lang="<?= Yii::$app->language ?>" class="h-100">
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

<main class="mobile-app-content flex-grow-1 px-3 py-3 mb-5">
    <?= $content ?>
</main>

<?= $this->render('_navbar') ?>
<?= $this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
