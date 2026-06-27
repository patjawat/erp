<?php
/**
 * Shared page header for inventoryV2 sub-stock pages
 * ใช้ภายใน $this->beginBlock('page-title') ของทุกเพจในกลุ่ม sub-stock
 *
 * @var string       $icon     bi-* icon class (default: bi-grid-1x2-fill)
 * @var string       $title    หัวเรื่อง (default: $this->title)
 * @var string[]     $metas    meta chips คั่นด้วย · (เช่น ชื่อคลัง, วันที่)
 * @var string|null  $caption  ข้อความบรรยายใต้หัวเรื่อง (รองรับ raw HTML)
 * @var string|null  $extra    HTML เพิ่ม (เช่น health badge)
 */
use yii\helpers\Html;

$icon    = $icon    ?? 'bi-grid-1x2-fill';
$title   = $title   ?? (string) $this->title;
$metas   = $metas   ?? [];
$caption = $caption ?? null;
$extra   = $extra   ?? null;
?>
<div class="sub-stock-page-head">
    <div class="sub-stock-page-head__title">
        <i class="bi <?= Html::encode($icon) ?>" aria-hidden="true"></i>
        <h4 class="mb-0"><?= Html::encode($title) ?></h4>
    </div>
    <?php foreach ($metas as $meta): if ($meta === '' || $meta === null) continue; ?>
        <span class="sub-stock-page-head__sep" aria-hidden="true">·</span>
        <span class="sub-stock-page-head__meta"><?= Html::encode($meta) ?></span>
    <?php endforeach; ?>
    <?php if ($extra !== null): ?>
        <?= $extra ?>
    <?php endif; ?>
</div>
<?php if ($caption !== null && $caption !== ''): ?>
    <p class="sub-stock-page-head__caption mb-0"><?= $caption ?></p>
<?php endif; ?>

<style>
.sub-stock-page-head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}
.sub-stock-page-head__title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.sub-stock-page-head__title i {
    color: #0d6efd;
    font-size: 1.15rem;
}
.sub-stock-page-head__title h4 {
    font-size: 1.15rem;
    font-weight: 600;
    color: #1a202c;
    line-height: 1.2;
}
.sub-stock-page-head__sep { color: #a0aec0; }
.sub-stock-page-head__meta {
    font-size: 0.82rem;
    color: #4a5568;
}
.sub-stock-page-head__caption {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.82rem;
    color: #4a5568;
    line-height: 1.5;
}
.sub-stock-page-head__caption .inline-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.05rem 0.45rem;
    background: rgba(13, 110, 253, 0.08);
    color: #0a58ca;
    border: 1px solid rgba(13, 110, 253, 0.22);
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.78rem;
}
</style>
