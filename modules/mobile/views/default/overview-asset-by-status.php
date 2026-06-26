<?php

use app\modules\am\models\Asset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $statusId */
/** @var array{id:string,label:string,count:int,tone:string,icon:string} $meta */
/** @var Asset[] $assets */

$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'ครุภัณฑ์สถานะ ' . $meta['label'];
$this->params['mobileSubtitle'] = 'รายการที่สถานะนี้';

$assets = is_array($assets ?? null) ? $assets : [];
$count  = count($assets);
$totalAmount = 0.0;
foreach ($assets as $a) {
    $totalAmount += (float) ($a->price ?? 0);
}

$formatBaht = static function (float $value): string {
    if ($value <= 0) return '0';
    if ($value >= 1_000_000) {
        return number_format($value / 1_000_000, $value >= 10_000_000 ? 1 : 2) . ' ล.';
    }
    if ($value >= 100_000) {
        return number_format($value / 1_000, 0) . ' K';
    }
    return number_format($value, 0);
};
$formatBahtFull = static function (float $value): string {
    return number_format($value, 2);
};

$overviewUrl = Url::to(['/mobile/default/overview-asset']);
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => $meta['icon'] ?: 'circle-dot',
    'title'    => $meta['label'],
    'subtitle' => 'รายการครุภัณฑ์ในสถานะนี้',
    'stats'    => [
        ['value' => number_format($count),     'label' => 'รายการ',     'tone' => 'primary'],
        ['value' => $formatBaht($totalAmount), 'label' => 'มูลค่ารวม ฿', 'tone' => (string) $meta['tone']],
    ],
    'statsLabel' => 'สรุปกลุ่ม',
]) ?>

<style>
.ov-sl {
    display: flex; flex-direction: column;
    gap: var(--space-md, 1rem);
    padding-top: var(--space-md);
}

/* List card */
.ov-sl-card {
    background: var(--surface, #fff);
    border: 1px solid var(--line, rgba(15,23,42,0.08));
    border-radius: var(--radius, 10px);
    box-shadow: var(--shadow-1, 0 1px 2px rgba(15,23,42,0.04));
    overflow: hidden;
}
.ov-sl-row {
    display: grid;
    grid-template-columns: 2.25rem 1fr auto;
    gap: 0.75rem;
    padding: 0.7rem 0.95rem;
    border-bottom: 1px solid var(--line, rgba(15,23,42,0.08));
    color: inherit; text-decoration: none;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-sl-row:last-child { border-bottom: 0; }
.ov-sl-row:hover,
.ov-sl-row:focus-visible {
    background: color-mix(in oklch, var(--band-bg, var(--surface-2)) 55%, transparent);
    color: inherit;
}

/* Tone tints — match overview-asset $toneColor map */
.ov-sl-row.tone-primary   { --band-bg: var(--mobile-primary-soft, rgba(13,110,253,0.08)); --band-ink: var(--mobile-primary, #0d6efd); }
.ov-sl-row.tone-success   { --band-bg: var(--success-soft, rgba(21,128,61,0.10));         --band-ink: var(--cat-asset-fg, #15803d); }
.ov-sl-row.tone-warning   { --band-bg: var(--warning-soft, rgba(180,83,9,0.10));          --band-ink: var(--cat-attendance-fg, #b45309); }
.ov-sl-row.tone-danger    { --band-bg: var(--danger-soft, rgba(185,28,28,0.10));          --band-ink: var(--cat-issue-fg, #b91c1c); }
.ov-sl-row.tone-info      { --band-bg: color-mix(in oklch, var(--cat-vehicle-fg) 14%, transparent); --band-ink: var(--cat-vehicle-fg, #0e7490); }
.ov-sl-row.tone-secondary { --band-bg: rgba(100,116,139,0.10);                            --band-ink: var(--ink-3, #718096); }

.ov-sl-row__medal {
    width: 2.25rem; height: 2.25rem;
    border-radius: 10px;
    background: var(--band-bg, var(--surface-3));
    color: var(--band-ink, var(--ink-3));
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ov-sl-row__medal svg { width: 1.05rem; height: 1.05rem; }
.ov-sl-row__body { min-width: 0; }
.ov-sl-row__name {
    font-size: 0.88rem; font-weight: 600;
    color: var(--ink, #1a202c);
    line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-sl-row__meta {
    margin-top: 2px;
    display: flex; gap: 0.5rem; align-items: center;
    font-size: 0.72rem;
    color: var(--ink-3, #718096);
}
.ov-sl-row__code {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, monospace;
}
.ov-sl-row__right {
    display: flex; flex-direction: column;
    align-items: flex-end; justify-content: center;
    gap: 2px;
    flex-shrink: 0;
    text-align: right;
}
.ov-sl-row__amt {
    font-size: 0.86rem; font-weight: 700;
    color: var(--ink, #1a202c);
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.01em;
}
.ov-sl-row__amt::after {
    content: ' ฿';
    color: var(--ink-4, #a0aec0);
    font-weight: 500;
    margin-left: 1px;
}
.ov-sl-row__dept {
    font-size: 0.7rem; color: var(--ink-3, #718096);
    max-width: 9rem;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.ov-sl-empty {
    padding: 2.5rem 1.25rem;
    text-align: center;
    color: var(--ink-3, #718096);
}
.ov-sl-empty__icon {
    width: 3.25rem; height: 3.25rem;
    border-radius: 18px;
    background: var(--surface-3, #eef2f7);
    color: var(--ink-3, #718096);
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 0.75rem;
}
.ov-sl-empty__icon svg { width: 1.5rem; height: 1.5rem; }
.ov-sl-empty__title {
    font-size: 0.95rem; font-weight: 700;
    color: var(--ink, #1a202c);
    margin: 0 0 4px;
}
.ov-sl-empty__sub {
    font-size: 0.8rem;
    color: var(--ink-3, #718096);
    margin: 0;
}

/* Back link to overview */
.ov-sl-back {
    align-self: flex-start;
    display: inline-flex; align-items: center; gap: 4px;
    padding: 6px 10px;
    border-radius: 999px;
    background: var(--surface-2, #f7f9fc);
    border: 1px solid var(--line, rgba(15,23,42,0.08));
    font-size: 0.78rem; font-weight: 600;
    color: var(--mobile-primary, #0d6efd);
    text-decoration: none;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-sl-back:hover { background: var(--surface-hover, #f1f5f9); color: var(--mobile-primary, #0d6efd); }
.ov-sl-back svg { width: 14px; height: 14px; }

@keyframes ov-sl-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ov-sl > * {
    animation: ov-sl-enter 340ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 50ms);
}

@media (prefers-reduced-motion: reduce) {
    .ov-sl > *, .ov-sl-row, .ov-sl-back { animation: none !important; transition: none !important; }
}
</style>

<div class="app-scroll has-stats">
    <div class="ov-sl">
        <a href="<?= Html::encode($overviewUrl) ?>" class="ov-sl-back" style="--i: 0">
            <i data-lucide="arrow-left"></i> กลับสู่ภาพรวมทรัพย์สิน
        </a>

        <?php if ($count > 0): ?>
            <div class="ov-sl-card" style="--i: 1">
                <?php foreach ($assets as $a):
                    $name = trim((string) ($a->asset_name ?? '')) ?: 'ไม่ระบุชื่อ';
                    $code = trim((string) ($a->code ?? ''));
                    $price = (float) ($a->price ?? 0);
                    $dept = trim((string) ($a->department_name ?? ''));
                    $url  = Url::to(['/mobile/default/asset', 'id' => $a->id]);
                ?>
                    <a href="<?= Html::encode($url) ?>"
                       class="ov-sl-row tone-<?= Html::encode((string) $meta['tone']) ?>">
                        <span class="ov-sl-row__medal" aria-hidden="true">
                            <i data-lucide="<?= Html::encode((string) ($meta['icon'] ?: 'circle-dot')) ?>"></i>
                        </span>
                        <div class="ov-sl-row__body">
                            <div class="ov-sl-row__name"><?= Html::encode($name) ?></div>
                            <div class="ov-sl-row__meta">
                                <?php if ($code !== ''): ?>
                                    <span class="ov-sl-row__code"><?= Html::encode($code) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ov-sl-row__right">
                            <?php if ($price > 0): ?>
                                <span class="ov-sl-row__amt"
                                      title="<?= Html::encode($formatBahtFull($price)) ?> บาท">
                                    <?= Html::encode($formatBaht($price)) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($dept !== ''): ?>
                                <span class="ov-sl-row__dept"><?= Html::encode($dept) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="ov-sl-empty" style="--i: 1">
                <div class="ov-sl-empty__icon" aria-hidden="true">
                    <i data-lucide="<?= Html::encode((string) ($meta['icon'] ?: 'circle-dot')) ?>"></i>
                </div>
                <p class="ov-sl-empty__title">ไม่มีครุภัณฑ์ในสถานะ <?= Html::encode((string) $meta['label']) ?></p>
                <p class="ov-sl-empty__sub">รายการอาจถูกลบหรือเปลี่ยนสถานะแล้ว</p>
            </div>
        <?php endif; ?>
    </div>
</div>
