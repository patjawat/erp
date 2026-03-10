<?php
/**
 * Shared header: top bar (title + subtitle).
 * Set $this->params['mobileTitle'] and ['mobileSubtitle'] in each page.
 */
use yii\bootstrap5\Html;

$mobileTitle    = $this->params['mobileTitle'] ?? 'บริการออนไลน์';
$mobileSubtitle = $this->params['mobileSubtitle'] ?? 'หน้าหลัก';
?>
<header class="mobile-header sticky-top">
    <div class="d-flex justify-content-between align-items-center">
        <div class="min-w-0">
            <h1 class="h6 mb-0 fw-semibold text-dark text-truncate"><?= Html::encode($mobileTitle) ?></h1>
            <p class="mb-0 small text-body-secondary text-truncate"><?= Html::encode($mobileSubtitle) ?></p>
        </div>
    </div>
</header>
