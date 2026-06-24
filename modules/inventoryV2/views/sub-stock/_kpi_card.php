<?php
/**
 * KPI card — แสดงตัวเลขสำคัญ + คลิกได้ทั้งใบ
 * Aligned to PRODUCT.md token system + surface-card vocab
 *
 * @var string $label     ป้าย (ไทย, sentence case)
 * @var string $value     ค่าที่แสดง (formatted string)
 * @var string $unit      หน่วย เช่น 'รายการ', '฿'
 * @var string $icon      bi-*
 * @var string $color     'primary' | 'danger' | 'success' | 'warning' | 'info'
 * @var string $trend     ข้อความ trend (optional)
 * @var string|array|null $url  link (null = ไม่ใช่ link)
 * @var string $hint      hint ใต้ค่า
 */
use yii\helpers\Html;
use yii\helpers\Url;

$label = $label ?? '';
$value = $value ?? '0';
$unit = $unit ?? '';
$icon = $icon ?? 'bi-circle';
$color = $color ?? 'primary';
$trend = $trend ?? '';
$hint = $hint ?? '';
$url = $url ?? null;

$tag = $url ? 'a' : 'div';
$attrs = [
    'class' => 'kpi-card kpi-card--' . $color . ($url ? ' kpi-card--link' : ''),
];
if ($url) {
    $attrs['href'] = is_array($url) ? Url::to($url) : (string) $url;
}
?>
<<?= $tag ?> <?= Html::renderTagAttributes($attrs) ?>>
    <div class="kpi-card__head">
        <div class="kpi-card__text">
            <div class="kpi-card__label"><?= Html::encode($label) ?></div>
            <div class="kpi-card__value-row">
                <span class="kpi-card__value"><?= Html::encode($value) ?></span>
                <?php if ($unit !== ''): ?>
                    <span class="kpi-card__unit"><?= Html::encode($unit) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <span class="kpi-card__icon kpi-card__icon--<?= $color ?>" aria-hidden="true">
            <i class="bi <?= Html::encode($icon) ?>"></i>
        </span>
    </div>
    <?php if ($trend !== '' || $hint !== '' || $url): ?>
        <div class="kpi-card__foot">
            <?php if ($trend !== ''): ?>
                <span class="kpi-card__trend kpi-card__trend--<?= $color ?>"><?= Html::encode($trend) ?></span>
            <?php endif; ?>
            <?php if ($hint !== ''): ?>
                <span class="kpi-card__hint"><?= Html::encode($hint) ?></span>
            <?php endif; ?>
            <?php if ($url): ?>
                <span class="kpi-card__more" aria-hidden="true">
                    <i class="bi bi-arrow-right"></i>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</<?= $tag ?>>
