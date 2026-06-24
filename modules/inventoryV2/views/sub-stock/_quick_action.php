<?php
/**
 * Quick action button — large touch target (≥56px) สำหรับมือถือ
 *
 * @var string $label      ข้อความปุ่ม (ไทย)
 * @var string $sublabel   คำอธิบายสั้น
 * @var string $icon       bi-* icon class
 * @var string $color      'primary' | 'success' | 'info' | 'warning' | 'danger' | 'secondary'
 * @var string|array $url  Url::to() input
 * @var int $badge         ตัวเลข badge (optional)
 */
use yii\helpers\Html;
use yii\helpers\Url;

$label = $label ?? '';
$sublabel = $sublabel ?? '';
$icon = $icon ?? 'bi-arrow-right';
$color = $color ?? 'primary';
$url = $url ?? '#';
$badge = isset($badge) ? (int) $badge : 0;
?>
<a href="<?= is_array($url) || is_string($url) ? Url::to($url) : '#' ?>"
   class="quick-action d-flex flex-column align-items-start justify-content-between text-decoration-none p-3 rounded-4 bg-white shadow-sm h-100 position-relative quick-action--<?= $color ?>">
    <span class="quick-action__icon d-inline-flex align-items-center justify-content-center rounded-3 bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>">
        <i class="bi <?= Html::encode($icon) ?>"></i>
    </span>
    <?php if ($badge > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-<?= $color ?> quick-action__badge">
            <?= $badge ?>
            <span class="visually-hidden">รายการรอ</span>
        </span>
    <?php endif; ?>
    <div class="quick-action__body w-100">
        <div class="fw-semibold text-body lh-sm"><?= Html::encode($label) ?></div>
        <?php if ($sublabel !== ''): ?>
            <div class="text-muted small mt-1 lh-sm"><?= Html::encode($sublabel) ?></div>
        <?php endif; ?>
    </div>
    <span class="quick-action__chevron text-muted small mt-2 align-self-end">
        <i class="bi bi-arrow-right"></i>
    </span>
</a>
