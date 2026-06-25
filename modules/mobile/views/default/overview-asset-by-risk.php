<?php

use app\modules\am\models\Asset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $level — H|M|L|UNASSESSED */
/** @var array{label:string,tone:string,icon:string,desc:string} $meta */
/** @var Asset[] $assets */

$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'ครุภัณฑ์ความเสี่ยง' . $meta['label'];
$this->params['mobileSubtitle'] = $meta['desc'];

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
    'icon'     => $meta['icon'],
    'title'    => 'ความเสี่ยง' . $meta['label'],
    'subtitle' => $meta['desc'] . ' · ' . number_format($count) . ' รายการ',
    'stats'    => [
        ['value' => number_format($count),         'label' => 'รายการ',     'tone' => 'primary'],
        ['value' => $formatBaht($totalAmount),     'label' => 'มูลค่ารวม ฿', 'tone' => $meta['tone']],
    ],
    'statsLabel' => 'สรุปกลุ่ม',
]) ?>

<style>
.ov-rl {
    display: flex; flex-direction: column;
    gap: var(--space-md, 1rem);
    padding-top: var(--space-md);
}

/* Tone-banded summary chip */
.ov-rl-band {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.65rem 0.8rem;
    border-radius: 12px;
    background: var(--band-bg, var(--surface-3));
    border: 1px solid var(--band-border, var(--line));
    color: var(--ink, #1a202c);
}
.ov-rl-band.tone-danger    { --band-bg: var(--danger-soft,  rgba(185,28,28,0.10));  --band-border: rgba(185,28,28,0.20);  --band-ink: var(--cat-issue-fg, #b91c1c); }
.ov-rl-band.tone-warning   { --band-bg: var(--warning-soft, rgba(180,83,9,0.10));   --band-border: rgba(180,83,9,0.20);   --band-ink: var(--cat-attendance-fg, #b45309); }
.ov-rl-band.tone-success   { --band-bg: var(--success-soft, rgba(21,128,61,0.10));  --band-border: rgba(21,128,61,0.20);  --band-ink: var(--cat-asset-fg, #15803d); }
.ov-rl-band.tone-secondary { --band-bg: rgba(100,116,139,0.08); --band-border: rgba(100,116,139,0.18); --band-ink: var(--ink-3, #718096); }
.ov-rl-band__icon {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    background: rgba(255,255,255,0.7);
    color: var(--band-ink, var(--ink-3));
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ov-rl-band__icon svg { width: 1.1rem; height: 1.1rem; }
.ov-rl-band__body { flex: 1; min-width: 0; }
.ov-rl-band__title {
    font-size: 0.86rem; font-weight: 700;
    color: var(--ink, #1a202c);
    line-height: 1.25;
}
.ov-rl-band__sub {
    font-size: 0.72rem; color: var(--ink-3, #718096);
    font-weight: 500;
}

/* List card */
.ov-rl-card {
    background: var(--surface, #fff);
    border: 1px solid var(--line, rgba(15,23,42,0.08));
    border-radius: var(--radius, 10px);
    box-shadow: var(--shadow-1, 0 1px 2px rgba(15,23,42,0.04));
    overflow: hidden;
}
.ov-rl-row {
    display: grid;
    grid-template-columns: 2.25rem 1fr auto;
    gap: 0.75rem;
    padding: 0.7rem 0.95rem;
    border-bottom: 1px solid var(--line, rgba(15,23,42,0.08));
    color: inherit; text-decoration: none;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-rl-row:last-child { border-bottom: 0; }
.ov-rl-row:hover,
.ov-rl-row:focus-visible {
    background: color-mix(in oklch, var(--band-bg, var(--surface-2)) 60%, transparent);
    color: inherit;
}
.ov-rl-row__medal {
    width: 2.25rem; height: 2.25rem;
    border-radius: 10px;
    background: var(--band-bg, var(--surface-3));
    color: var(--band-ink, var(--ink-3));
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ov-rl-row__medal svg { width: 1.05rem; height: 1.05rem; }
.ov-rl-row__body { min-width: 0; }
.ov-rl-row__name {
    font-size: 0.88rem; font-weight: 600;
    color: var(--ink, #1a202c);
    line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-rl-row__meta {
    margin-top: 2px;
    display: flex; gap: 0.5rem; align-items: center;
    font-size: 0.72rem;
    color: var(--ink-3, #718096);
}
.ov-rl-row__code {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, monospace;
}
.ov-rl-row__right {
    display: flex; flex-direction: column;
    align-items: flex-end; justify-content: center;
    gap: 2px;
    flex-shrink: 0;
    text-align: right;
}
.ov-rl-row__amt {
    font-size: 0.86rem; font-weight: 700;
    color: var(--ink, #1a202c);
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.01em;
}
.ov-rl-row__amt::after {
    content: ' ฿';
    color: var(--ink-4, #a0aec0);
    font-weight: 500;
    margin-left: 1px;
}
.ov-rl-row__dept {
    font-size: 0.7rem; color: var(--ink-3, #718096);
    max-width: 9rem;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.ov-rl-empty {
    padding: 2.5rem 1.25rem;
    text-align: center;
    color: var(--ink-3, #718096);
}
.ov-rl-empty__icon {
    width: 3.25rem; height: 3.25rem;
    border-radius: 18px;
    background: var(--band-bg, var(--surface-3));
    color: var(--band-ink, var(--ink-3));
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 0.75rem;
}
.ov-rl-empty__icon svg { width: 1.5rem; height: 1.5rem; }
.ov-rl-empty__title {
    font-size: 0.95rem; font-weight: 700;
    color: var(--ink, #1a202c);
    margin: 0 0 4px;
}
.ov-rl-empty__sub {
    font-size: 0.8rem;
    color: var(--ink-3, #718096);
    margin: 0;
}

/* Back link to overview */
.ov-rl-back {
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
.ov-rl-back:hover { background: var(--surface-hover, #f1f5f9); color: var(--mobile-primary, #0d6efd); }
.ov-rl-back svg { width: 14px; height: 14px; }

@keyframes ov-rl-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ov-rl > * {
    animation: ov-rl-enter 340ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 50ms);
}

@media (prefers-reduced-motion: reduce) {
    .ov-rl > *, .ov-rl-row, .ov-rl-back { animation: none !important; transition: none !important; }
}
</style>

<div class="app-scroll has-stats">
    <div class="ov-rl" style="--band-bg: var(--<?= Html::encode($meta['tone']) ?>-soft, var(--surface-3));
                              --band-border: rgba(15,23,42,0.08);
                              --band-ink: var(--ink-2);">
        <a href="<?= Html::encode($overviewUrl) ?>" class="ov-rl-back" style="--i: 0">
            <i data-lucide="arrow-left"></i> กลับสู่ภาพรวมทรัพย์สิน
        </a>

        <?php if ($count > 0): ?>
            <div class="ov-rl-card tone-<?= Html::encode($meta['tone']) ?>" style="--i: 1">
                <?php foreach ($assets as $a):
                    $name = trim((string) ($a->asset_name ?? '')) ?: 'ไม่ระบุชื่อ';
                    $code = trim((string) ($a->code ?? ''));
                    $price = (float) ($a->price ?? 0);
                    $dept = trim((string) ($a->department_name ?? ''));
                    $url  = Url::to(['/mobile/default/asset', 'id' => $a->id]);
                ?>
                    <a href="<?= Html::encode($url) ?>"
                       class="ov-rl-row tone-<?= Html::encode($meta['tone']) ?>"
                       style="--band-bg: var(--<?= Html::encode($meta['tone']) ?>-soft, var(--surface-3));
                              --band-ink: var(--cat-asset-fg);">
                        <span class="ov-rl-row__medal" aria-hidden="true">
                            <i data-lucide="<?= Html::encode($meta['icon']) ?>"></i>
                        </span>
                        <div class="ov-rl-row__body">
                            <div class="ov-rl-row__name"><?= Html::encode($name) ?></div>
                            <div class="ov-rl-row__meta">
                                <?php if ($code !== ''): ?>
                                    <span class="ov-rl-row__code"><?= Html::encode($code) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ov-rl-row__right">
                            <?php if ($price > 0): ?>
                                <span class="ov-rl-row__amt"
                                      title="<?= Html::encode($formatBahtFull($price)) ?> บาท">
                                    <?= Html::encode($formatBaht($price)) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($dept !== ''): ?>
                                <span class="ov-rl-row__dept"><?= Html::encode($dept) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="ov-rl-empty" style="--i: 1">
                <div class="ov-rl-empty__icon" aria-hidden="true">
                    <i data-lucide="<?= Html::encode($meta['icon']) ?>"></i>
                </div>
                <p class="ov-rl-empty__title">ไม่มีครุภัณฑ์ในกลุ่มความเสี่ยง<?= Html::encode($meta['label']) ?></p>
                <p class="ov-rl-empty__sub">
                    <?php if ($level === 'UNASSESSED'): ?>
                        ครุภัณฑ์ทั้งหมดมีระดับความเสี่ยงครบแล้ว
                    <?php else: ?>
                        ยังไม่มีรายการในระดับนี้
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
