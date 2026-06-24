<?php
/**
 * Health badge — derives ระดับสุขภาพคลังย่อย จากตัวแปรที่มีอยู่แล้ว
 * ใช้ semantic CSS variable ไม่ใช้ Bootstrap warning/info defaults (#ffc107/#0dcaf0)
 *
 * @var int $criticalCount
 * @var int $pendingReceiveCount
 * @var int $expiringSoonCount
 * @var string $size 'sm' | 'lg' (default 'sm')
 */
use yii\helpers\Html;

$criticalCount = (int) ($criticalCount ?? 0);
$pendingReceiveCount = (int) ($pendingReceiveCount ?? 0);
$expiringSoonCount = (int) ($expiringSoonCount ?? 0);
$size = $size ?? 'sm';

if ($criticalCount >= 5) {
    $level = 'critical';
    $thLabel = 'ต้องดูแลด่วน';
    $icon = 'bi-exclamation-octagon-fill';
} elseif ($criticalCount >= 1 || $expiringSoonCount >= 1) {
    $level = 'warning';
    $thLabel = 'ต้องระวัง';
    $icon = 'bi-exclamation-triangle-fill';
} elseif ($pendingReceiveCount > 0) {
    $level = 'good';
    $thLabel = 'ปกติ';
    $icon = 'bi-check-circle-fill';
} else {
    $level = 'excellent';
    $thLabel = 'สมบูรณ์';
    $icon = 'bi-shield-check';
}

$isLarge = $size === 'lg';
?>
<span class="health-badge health-badge--<?= $level ?> health-badge--<?= $isLarge ? 'lg' : 'sm' ?>"
      title="<?= Html::encode("สุขภาพคลัง: {$thLabel}") ?>">
    <span class="health-badge__dot" aria-hidden="true"></span>
    <i class="bi <?= $icon ?>" aria-hidden="true"></i>
    <span><?= Html::encode($thLabel) ?></span>
</span>
