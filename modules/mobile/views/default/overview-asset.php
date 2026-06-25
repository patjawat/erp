<?php

use app\modules\am\models\Asset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var int $total */
/** @var array<int,array{id:string,label:string,count:int,tone:string,icon:string}> $statusBreakdown */
/** @var array<int,array{id:string,label:string,count:int,tone:string,icon:string}> $conditionBreakdown */
/** @var array<int,array{id:string,label:string,count:int,tone:string,icon:string,desc:string}> $riskBreakdown */
/** @var array<int,array{label:string,count:int}> $categoryBreakdown */
/** @var array<int,array{code:string,label:string,count:int,amount:float}> $budgetTypeBreakdown */
/** @var array<int,array{code:string,label:string,count:int,amount:float}> $methodGetBreakdown */
/** @var array<int,array{code:string,label:string,count:int,amount:float}> $purchaseBreakdown */
/** @var float $totalAmount */
/** @var Asset[] $recent */

$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'ภาพรวมทรัพย์สิน';
$this->params['mobileSubtitle'] = 'สถานะ สภาพ การเงิน';

$total              = (int) $total;
$statusBreakdown    = is_array($statusBreakdown ?? null)    ? $statusBreakdown    : [];
$conditionBreakdown = is_array($conditionBreakdown ?? null) ? $conditionBreakdown : [];
$riskBreakdown      = is_array($riskBreakdown ?? null)      ? $riskBreakdown      : [];
$categoryBreakdown    = is_array($categoryBreakdown ?? null)    ? $categoryBreakdown    : [];
$budgetTypeBreakdown  = is_array($budgetTypeBreakdown ?? null)  ? $budgetTypeBreakdown  : [];
$methodGetBreakdown   = is_array($methodGetBreakdown ?? null)   ? $methodGetBreakdown   : [];
$purchaseBreakdown    = is_array($purchaseBreakdown ?? null)    ? $purchaseBreakdown    : [];
$totalAmount          = (float) ($totalAmount ?? 0);
$recent               = is_array($recent ?? null)               ? $recent               : [];

// ─────────────────────────────────────────────────────
// money helpers (format + compact thai numeric)
$formatBaht = static function (float $value): string {
    if ($value <= 0) return '0';
    // ใช้ Thai compact: ล้าน / พัน / หน่วย
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

// สี (CSS color value) สำหรับแต่ละ tone — ใช้ token เดิมจาก DESIGN
$toneColor = [
    'primary'   => 'var(--mobile-primary, #0d6efd)',
    'success'   => 'var(--cat-asset-fg, #15803d)',
    'warning'   => 'var(--cat-attendance-fg, #b45309)',
    'danger'    => 'var(--cat-issue-fg, #b91c1c)',
    'secondary' => 'var(--ink-3, #718096)',
    'info'      => 'var(--cat-vehicle-fg, #0e7490)',
];

// สร้าง statusById เพื่อใช้ค้นเร็วๆ (recent pill ฯลฯ)
$statusById = [];
foreach ($statusBreakdown as $s) {
    $statusById[(string) $s['id']] = $s + ['color' => $toneColor[$s['tone']] ?? $toneColor['secondary']];
}

$statusTotal = 0;
foreach ($statusBreakdown as $s) { $statusTotal += (int) $s['count']; }

$catMax = 0;
foreach ($categoryBreakdown as $c) { if ($c['count'] > $catMax) $catMax = (int) $c['count']; }

// คำนวณ donut segments (เริ่มจาก 12 นาฬิกา) — ใช้ statusBreakdown ตาม sort_order/count จาก controller
$donutRadius = 56;
$donutStroke = 18;
$donutCircumference = 2 * M_PI * $donutRadius;
$donutSegments = [];
$cumulativeOffset = 0;
$animDelay = 0;
foreach ($statusBreakdown as $s) {
    $count = (int) $s['count'];
    if ($count <= 0 || $statusTotal <= 0) continue;
    $portion = $count / $statusTotal;
    $dash    = $portion * $donutCircumference;
    $donutSegments[] = [
        'id'     => (string) $s['id'],
        'label'  => (string) $s['label'],
        'count'  => $count,
        'pct'    => $portion,
        'color'  => $toneColor[$s['tone']] ?? $toneColor['secondary'],
        'dash'   => $dash,
        'offset' => $cumulativeOffset,
        'delay'  => $animDelay,
    ];
    $cumulativeOffset += $dash;
    $animDelay += 90;
}

// totals สำหรับ section สภาพ + ความเสี่ยง
$conditionTotal = 0;
foreach ($conditionBreakdown as $c) { $conditionTotal += (int) $c['count']; }
$riskTotal = 0;
$riskAssessed = 0;
foreach ($riskBreakdown as $r) {
    $riskTotal += (int) $r['count'];
    if ((string) $r['id'] !== '-') $riskAssessed += (int) $r['count'];
}

// hero stats: ใบรวม + ยอดเงินรวม + top 2 สถานะที่ count สูงสุด
$statsRanked = $statusBreakdown;
usort($statsRanked, static fn($a, $b) => (int) $b['count'] <=> (int) $a['count']);
$heroStats = [
    ['value' => number_format($total), 'label' => 'รายการ', 'tone' => 'primary'],
];
if ($totalAmount > 0) {
    $heroStats[] = [
        'value' => $formatBaht($totalAmount),
        'label' => 'มูลค่ารวม ฿',
        'tone'  => 'success',
    ];
}
$picked = 0;
$maxStatTiles = 4 - count($heroStats);
foreach ($statsRanked as $s) {
    if ((int) $s['count'] <= 0) break;
    if ($maxStatTiles <= 0) break;
    $heroStats[] = [
        'value' => number_format((int) $s['count']),
        'label' => (string) $s['label'],
        'tone'  => (string) $s['tone'],
    ];
    if (++$picked >= $maxStatTiles) break;
}
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'       => 'package',
    'title'      => 'ภาพรวมทรัพย์สิน',
    'subtitle'   => 'สถานะ · หมวดครุภัณฑ์',
    'stats'      => $heroStats,
    'statsLabel' => 'สรุปทรัพย์สิน',
]) ?>

<style>
.ov-am {
    display: flex; flex-direction: column;
    gap: var(--space-lg, 1.25rem);
    padding-top: var(--space-md);
}
.ov-card {
    background: var(--surface, #fff);
    border: 1px solid var(--line, rgba(15,23,42,0.08));
    border-radius: var(--radius, 10px);
    box-shadow: var(--shadow-1, 0 1px 2px rgba(15,23,42,0.04));
    overflow: hidden;
}
.ov-card__head {
    display: flex; align-items: center; justify-content: space-between;
    gap: var(--space-sm);
    padding: 0.85rem 1.05rem;
    border-bottom: 1px solid var(--line, rgba(15,23,42,0.08));
}
.ov-card__title {
    font-size: 0.95rem; font-weight: 700;
    color: var(--ink, #1a202c);
    margin: 0; line-height: 1.2;
}
.ov-card__sub {
    font-size: 0.72rem; color: var(--ink-3, #718096);
    font-weight: 500;
}
.ov-card__body { padding: 1rem 1.05rem; }

/* Donut + legend */
.ov-donut-wrap {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1.1rem;
    align-items: center;
}
.ov-donut {
    width: 144px; height: 144px;
    position: relative;
    flex-shrink: 0;
}
.ov-donut svg {
    width: 100%; height: 100%;
    transform: rotate(-90deg);
}
.ov-donut__track {
    fill: none;
    stroke: var(--surface-3, #eef2f7);
}
.ov-donut__seg {
    fill: none;
    stroke-linecap: butt;
    stroke-dashoffset: var(--len, 0);
    animation: ov-donut-draw 720ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: var(--delay, 0ms);
}
.ov-donut__center {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
}
.ov-donut__center-num {
    font-size: 1.5rem; font-weight: 800;
    color: var(--ink, #1a202c);
    font-variant-numeric: tabular-nums;
    line-height: 1; letter-spacing: -0.02em;
}
.ov-donut__center-lbl {
    font-size: 0.72rem; color: var(--ink-3, #718096);
    font-weight: 600;
    margin-top: 4px;
}

.ov-donut-legend {
    display: flex; flex-direction: column;
    gap: 0.55rem;
    min-width: 0;
}
.ov-donut-legend__item {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: var(--ink-2, #4a5568);
    min-width: 0;
}
.ov-donut-legend__dot {
    width: 10px; height: 10px;
    border-radius: 3px;
    background: var(--seg-color, var(--ink-3));
    flex-shrink: 0;
}
.ov-donut-legend__label {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-donut-legend__num {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    color: var(--ink, #1a202c);
}
.ov-donut-legend__pct {
    font-size: 0.7rem;
    color: var(--ink-4, #a0aec0);
    margin-left: 4px;
    font-weight: 500;
}
.ov-donut-legend__item.is-empty {
    opacity: 0.55;
}
.ov-donut-legend__item.is-empty .ov-donut-legend__dot {
    background: transparent !important;
    border: 1.5px dashed var(--seg-color, var(--ink-3));
}
.ov-donut-legend__item.is-empty .ov-donut-legend__num {
    color: var(--ink-4, #a0aec0);
    font-weight: 600;
}

/* Condition stacked bar */
.ov-condbar {
    display: flex;
    height: 18px;
    border-radius: 999px;
    overflow: hidden;
    background: var(--surface-3, #eef2f7);
    margin-bottom: 0.85rem;
}
.ov-condbar__seg {
    height: 100%;
    background: var(--seg-color, var(--ink-3));
    transform-origin: left;
    animation: ov-bar-grow 520ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 60ms + 100ms);
}
.ov-condlegend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem 1rem;
}
.ov-condlegend__item {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: var(--ink-2, #4a5568);
    min-width: 0;
}
.ov-condlegend__dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    background: var(--seg-color, var(--ink-3));
    flex-shrink: 0;
}
.ov-condlegend__label {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-condlegend__num {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    color: var(--ink, #1a202c);
}
.ov-condlegend__pct {
    font-size: 0.7rem;
    color: var(--ink-4, #a0aec0);
    margin-left: 4px;
    font-weight: 500;
}
.ov-condlegend__item.is-empty {
    opacity: 0.5;
}
.ov-condlegend__item.is-empty .ov-condlegend__dot {
    background: transparent;
    border: 1.5px dashed var(--seg-color, var(--ink-3));
}
.ov-condlegend__item.is-empty .ov-condlegend__num {
    color: var(--ink-4, #a0aec0);
}

/* Risk KPI tiles — 2×2 grid on phones */
.ov-risk {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.6rem;
}
.ov-risk__tile {
    position: relative;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.55rem;
    padding: 0.7rem 0.75rem;
    border-radius: 12px;
    background: var(--tile-bg, var(--surface-3));
    border: 1px solid var(--tile-border, var(--line));
    color: var(--ink, #1a202c);
    min-width: 0;
    overflow: hidden;
}
.ov-risk__tile.tone-danger    { --tile-bg: var(--danger-soft,  rgba(185,28,28,0.10)); --tile-border: rgba(185,28,28,0.20); --tile-ink: var(--cat-issue-fg, #b91c1c); }
.ov-risk__tile.tone-warning   { --tile-bg: var(--warning-soft, rgba(180,83,9,0.10));  --tile-border: rgba(180,83,9,0.20);  --tile-ink: var(--cat-attendance-fg, #b45309); }
.ov-risk__tile.tone-success   { --tile-bg: var(--success-soft, rgba(21,128,61,0.10)); --tile-border: rgba(21,128,61,0.20); --tile-ink: var(--cat-asset-fg, #15803d); }
.ov-risk__tile.tone-secondary { --tile-bg: rgba(100,116,139,0.08); --tile-border: rgba(100,116,139,0.18); --tile-ink: var(--ink-3, #718096); }
.ov-risk__medal {
    width: 2.1rem; height: 2.1rem;
    border-radius: 10px;
    background: rgba(255,255,255,0.7);
    color: var(--tile-ink, var(--ink-3));
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ov-risk__medal svg { width: 1.05rem; height: 1.05rem; }
.ov-risk__body {
    display: flex; flex-direction: column;
    min-width: 0;
    gap: 1px;
}
.ov-risk__num {
    font-size: 1.25rem; font-weight: 800;
    color: var(--tile-ink, var(--ink, #1a202c));
    font-variant-numeric: tabular-nums;
    line-height: 1;
    letter-spacing: -0.02em;
}
.ov-risk__label {
    font-size: 0.78rem;
    color: var(--ink, #1a202c);
    font-weight: 700;
    margin-top: 2px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-risk__desc {
    font-size: 0.66rem;
    color: var(--ink-3, #718096);
    font-weight: 500;
    margin-top: 1px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-risk__tile.is-zero { opacity: 0.55; }
.ov-risk__tile.is-zero .ov-risk__num { color: var(--ink-4, #a0aec0); }

/* Clickable tile */
a.ov-risk__tile {
    text-decoration: none; color: inherit;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
a.ov-risk__tile:hover,
a.ov-risk__tile:focus-visible {
    color: inherit;
    border-color: var(--tile-ink, var(--ink-3));
    box-shadow: 0 4px 14px color-mix(in oklch, var(--tile-ink, var(--ink-3)) 18%, transparent);
    transform: translateY(-1px);
}
a.ov-risk__tile:active { transform: translateY(0); }
a.ov-risk__tile:focus-visible {
    outline: 2px solid var(--tile-ink, var(--mobile-primary));
    outline-offset: 2px;
}
.ov-risk__chev {
    position: absolute; top: 8px; right: 8px;
    width: 14px; height: 14px;
    color: var(--tile-ink, var(--ink-4));
    opacity: 0.55;
    transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1), opacity 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
a.ov-risk__tile:hover .ov-risk__chev,
a.ov-risk__tile:focus-visible .ov-risk__chev {
    opacity: 0.85;
    transform: translateX(2px);
}

/* Money breakdown rows */
.ov-money {
    display: flex; flex-direction: column;
    gap: 0.85rem;
}
.ov-money__row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.25rem 0.65rem;
    align-items: baseline;
}
.ov-money__label {
    font-size: 0.82rem; color: var(--ink-2, #4a5568);
    font-weight: 500;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    min-width: 0;
}
.ov-money__meta {
    display: inline-flex; gap: 0.5rem; align-items: baseline;
    flex-shrink: 0;
    font-variant-numeric: tabular-nums;
}
.ov-money__cnt {
    font-size: 0.7rem; color: var(--ink-3, #718096);
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 999px;
    background: var(--surface-3, #eef2f7);
}
.ov-money__amt {
    font-size: 0.86rem; font-weight: 700;
    color: var(--ink, #1a202c);
    letter-spacing: -0.01em;
}
.ov-money__amt::after {
    content: ' ฿';
    color: var(--ink-4, #a0aec0);
    font-weight: 500;
    margin-left: 1px;
}
.ov-money__track {
    grid-column: 1 / -1;
    height: 6px;
    background: var(--surface-3, #eef2f7);
    border-radius: 999px;
    overflow: hidden;
}
.ov-money__fill {
    height: 100%;
    background: linear-gradient(90deg,
        color-mix(in oklch, var(--cat-vehicle-fg) 75%, var(--mobile-primary)),
        var(--mobile-primary, #0d6efd));
    border-radius: 999px;
    transform-origin: left;
    transform: scaleX(0);
    animation: ov-bar-fill 540ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: calc(var(--i, 0) * 50ms + 100ms);
}
.ov-money__row.is-empty .ov-money__amt {
    color: var(--ink-4, #a0aec0); font-weight: 600;
}

/* Money card sub line (total amount baht) */
.ov-card__sub.is-amount {
    font-variant-numeric: tabular-nums;
    color: var(--ink-2, #4a5568);
    font-weight: 600;
}
.ov-card__sub.is-amount strong {
    color: var(--ink, #1a202c);
    font-weight: 800;
}

/* Category bar list */
.ov-barlist {
    display: flex; flex-direction: column;
    gap: 0.85rem;
}
.ov-barlist__row {
    display: grid; grid-template-columns: 1fr auto;
    gap: 0.35rem 0.65rem;
    align-items: baseline;
}
.ov-barlist__label {
    font-size: 0.8rem; color: var(--ink-2, #4a5568);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    min-width: 0;
}
.ov-barlist__num {
    font-size: 0.8rem; font-weight: 700;
    color: var(--ink, #1a202c);
    font-variant-numeric: tabular-nums;
}
.ov-barlist__track {
    grid-column: 1 / -1;
    height: 8px;
    background: var(--surface-3, #eef2f7);
    border-radius: 999px;
    overflow: hidden;
}
.ov-barlist__fill {
    height: 100%;
    background: linear-gradient(90deg,
        color-mix(in oklch, var(--cat-asset-fg) 70%, var(--mobile-primary)),
        var(--cat-asset-fg));
    border-radius: 999px;
    transform-origin: left;
    transform: scaleX(0);
    animation: ov-bar-fill 540ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: calc(var(--i, 0) * 60ms + 120ms);
}

/* Recent list */
.ov-recent { display: flex; flex-direction: column; }
.ov-recent__row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid var(--line, rgba(15,23,42,0.08));
    color: inherit; text-decoration: none;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-recent__row:last-child { border-bottom: 0; }
.ov-recent__row:hover {
    background: color-mix(in oklch, var(--cat-asset-bg) 75%, transparent);
    color: inherit;
}
.ov-recent__medal {
    width: 2.25rem; height: 2.25rem;
    border-radius: 12px;
    background: var(--cat-asset-bg, #e3f5ec);
    color: var(--cat-asset-fg, #15803d);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ov-recent__medal svg { width: 1.1rem; height: 1.1rem; }
.ov-recent__body { flex: 1; min-width: 0; }
.ov-recent__name {
    font-size: 0.86rem; font-weight: 600;
    color: var(--ink, #1a202c);
    line-height: 1.25;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-recent__code {
    font-size: 0.72rem; color: var(--ink-3, #718096);
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, monospace;
    margin-top: 2px;
}
.ov-recent__pill {
    flex-shrink: 0;
    font-size: 0.7rem; font-weight: 600;
    padding: 3px 8px;
    border-radius: 999px;
}
.ov-recent__pill.is-primary   { background: var(--mobile-primary-soft, rgba(13,110,253,0.08)); color: var(--mobile-primary, #0d6efd); }
.ov-recent__pill.is-success   { background: var(--success-soft, rgba(21,128,61,0.10));    color: var(--success, #15803d); }
.ov-recent__pill.is-warning   { background: var(--warning-soft, rgba(180,83,9,0.10));     color: var(--warning, #b45309); }
.ov-recent__pill.is-danger    { background: var(--danger-soft,  rgba(185,28,28,0.10));    color: var(--danger,  #b91c1c); }
.ov-recent__pill.is-info      { background: color-mix(in oklch, var(--cat-vehicle-fg) 14%, transparent); color: var(--cat-vehicle-fg, #0e7490); }
.ov-recent__pill.is-secondary { background: rgba(100,116,139,0.12);                         color: var(--ink-3, #718096); }

.ov-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: var(--ink-3, #718096);
    font-size: 0.82rem;
}

/* Animations */
@keyframes ov-bar-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(var(--pct, 1)); }
}
@keyframes ov-donut-draw {
    from { stroke-dasharray: 0 9999; }
    to   { stroke-dasharray: var(--dash, 0) 9999; }
}
@keyframes ov-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ov-am > * {
    animation: ov-enter 360ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 70ms);
}

@media (prefers-reduced-motion: reduce) {
    .ov-am > *,
    .ov-donut__seg,
    .ov-barlist__fill { animation: none !important; }
    .ov-donut__seg    { stroke-dasharray: var(--dash, 0) 9999; }
    .ov-barlist__fill { transform: scaleX(var(--pct, 1)); }
}

@media (max-width: 360px) {
    .ov-donut-wrap { grid-template-columns: 1fr; justify-items: center; }
    .ov-donut { margin-bottom: 0.5rem; }
    .ov-donut-legend { width: 100%; }
}
</style>

<div class="app-scroll has-stats">
    <div class="ov-am">

        <!-- ── สถานะทรัพย์สิน ─────────────────────────────── -->
        <section class="ov-card" style="--i: 0">
            <header class="ov-card__head">
                <h3 class="ov-card__title">สถานะทรัพย์สิน</h3>
                <span class="ov-card__sub"><?= Html::encode(number_format($statusTotal)) ?> รายการ</span>
            </header>
            <div class="ov-card__body">
                <?php if ($statusTotal > 0): ?>
                    <div class="ov-donut-wrap">
                        <div class="ov-donut">
                            <svg viewBox="0 0 144 144" aria-hidden="true">
                                <circle class="ov-donut__track"
                                        cx="72" cy="72" r="<?= (int) $donutRadius ?>"
                                        stroke-width="<?= (int) $donutStroke ?>"></circle>
                                <?php foreach ($donutSegments as $seg): ?>
                                    <circle class="ov-donut__seg"
                                            cx="72" cy="72"
                                            r="<?= (int) $donutRadius ?>"
                                            stroke-width="<?= (int) $donutStroke ?>"
                                            stroke="<?= Html::encode($seg['color']) ?>"
                                            stroke-dashoffset="<?= number_format(-$seg['offset'], 3, '.', '') ?>"
                                            style="--dash: <?= number_format($seg['dash'], 3, '.', '') ?>; --delay: <?= (int) $seg['delay'] ?>ms;">
                                    </circle>
                                <?php endforeach; ?>
                            </svg>
                            <div class="ov-donut__center" aria-hidden="true">
                                <span class="ov-donut__center-num"><?= Html::encode(number_format($statusTotal)) ?></span>
                                <span class="ov-donut__center-lbl">รายการ</span>
                            </div>
                        </div>
                        <div class="ov-donut-legend">
                            <?php foreach ($statusBreakdown as $s):
                                $count = (int) $s['count'];
                                $pct   = $statusTotal > 0 ? ($count / $statusTotal) * 100 : 0;
                                $color = $toneColor[$s['tone']] ?? $toneColor['secondary'];
                                $isZero = $count === 0;
                            ?>
                                <div class="ov-donut-legend__item<?= $isZero ? ' is-empty' : '' ?>"
                                     style="--seg-color: <?= Html::encode($color) ?>;">
                                    <span class="ov-donut-legend__dot" aria-hidden="true"></span>
                                    <span class="ov-donut-legend__label"><?= Html::encode($s['label']) ?></span>
                                    <span>
                                        <span class="ov-donut-legend__num"><?= Html::encode(number_format($count)) ?></span>
                                        <?php if (!$isZero): ?>
                                            <span class="ov-donut-legend__pct"><?= Html::encode(number_format($pct, $pct >= 10 ? 0 : 1)) ?>%</span>
                                        <?php else: ?>
                                            <span class="ov-donut-legend__pct">—</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีข้อมูลสถานะครุภัณฑ์ในระบบ</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── สภาพครุภัณฑ์ ───────────────────────────────── -->
        <section class="ov-card" style="--i: 1">
            <header class="ov-card__head">
                <h3 class="ov-card__title">สภาพครุภัณฑ์</h3>
                <span class="ov-card__sub">
                    <?php if ($conditionTotal > 0): ?>
                        ประเมินแล้ว <?= Html::encode(number_format($conditionTotal)) ?>
                    <?php else: ?>
                        ยังไม่มีข้อมูล
                    <?php endif; ?>
                </span>
            </header>
            <div class="ov-card__body">
                <?php if ($conditionTotal > 0): ?>
                    <div class="ov-condbar" aria-hidden="true">
                        <?php foreach ($conditionBreakdown as $i => $c):
                            $count = (int) $c['count'];
                            if ($count <= 0) continue;
                            $pct   = ($count / $conditionTotal) * 100;
                            $color = $toneColor[$c['tone']] ?? $toneColor['secondary'];
                        ?>
                            <div class="ov-condbar__seg"
                                 style="flex: 0 0 <?= number_format($pct, 2, '.', '') ?>%; --seg-color: <?= Html::encode($color) ?>; --i: <?= (int) $i ?>;"
                                 title="<?= Html::encode($c['label'] . ' ' . $count) ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ov-condlegend">
                        <?php foreach ($conditionBreakdown as $c):
                            $count = (int) $c['count'];
                            $pct   = $conditionTotal > 0 ? ($count / $conditionTotal) * 100 : 0;
                            $color = $toneColor[$c['tone']] ?? $toneColor['secondary'];
                            $isZero = $count === 0;
                        ?>
                            <div class="ov-condlegend__item<?= $isZero ? ' is-empty' : '' ?>"
                                 style="--seg-color: <?= Html::encode($color) ?>;">
                                <span class="ov-condlegend__dot" aria-hidden="true"></span>
                                <span class="ov-condlegend__label"><?= Html::encode($c['label']) ?></span>
                                <span class="ov-condlegend__num">
                                    <?= Html::encode(number_format($count)) ?>
                                    <?php if (!$isZero): ?>
                                        <span class="ov-condlegend__pct"><?= Html::encode(number_format($pct, $pct >= 10 ? 0 : 1)) ?>%</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีข้อมูลสภาพครุภัณฑ์</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── ระดับความเสี่ยง ───────────────────────────────── -->
        <section class="ov-card" style="--i: 2">
            <header class="ov-card__head">
                <h3 class="ov-card__title">ระดับความเสี่ยง</h3>
                <span class="ov-card__sub">
                    <?php if ($riskAssessed > 0): ?>
                        ประเมินแล้ว <?= Html::encode(number_format($riskAssessed)) ?>
                        <?php if ($riskTotal > 0): ?>
                            / <?= Html::encode(number_format($riskTotal)) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        ยังไม่ประเมิน
                    <?php endif; ?>
                </span>
            </header>
            <div class="ov-card__body">
                <div class="ov-risk">
                    <?php foreach ($riskBreakdown as $r):
                        $count  = (int) $r['count'];
                        $isZero = $count === 0;
                        $riskUrl = Url::to(['/mobile/default/overview-asset-by-risk', 'level' => (string) $r['id']]);
                        $aria = 'ครุภัณฑ์ความเสี่ยง' . $r['label'] . ' ' . $count . ' รายการ';
                    ?>
                        <a href="<?= Html::encode($riskUrl) ?>"
                           class="ov-risk__tile tone-<?= Html::encode($r['tone']) ?><?= $isZero ? ' is-zero' : '' ?>"
                           aria-label="<?= Html::encode($aria) ?>">
                            <span class="ov-risk__medal" aria-hidden="true">
                                <i data-lucide="<?= Html::encode($r['icon']) ?>"></i>
                            </span>
                            <div class="ov-risk__body">
                                <span class="ov-risk__num"><?= Html::encode(number_format($count)) ?></span>
                                <span class="ov-risk__label"><?= Html::encode($r['label']) ?></span>
                                <span class="ov-risk__desc"><?= Html::encode($r['desc']) ?></span>
                            </div>
                            <i data-lucide="chevron-right" class="ov-risk__chev" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ── หมวดครุภัณฑ์ ─────────────────────────────────── -->
        <section class="ov-card" style="--i: 3">
            <header class="ov-card__head">
                <h3 class="ov-card__title">หมวดครุภัณฑ์</h3>
                <span class="ov-card__sub">Top <?= Html::encode((string) count($categoryBreakdown)) ?></span>
            </header>
            <div class="ov-card__body">
                <?php if (!empty($categoryBreakdown)): ?>
                    <div class="ov-barlist">
                        <?php foreach ($categoryBreakdown as $i => $c):
                            $pct = $catMax > 0 ? min(1, $c['count'] / $catMax) : 0;
                        ?>
                            <div class="ov-barlist__row">
                                <div class="ov-barlist__label" title="<?= Html::encode($c['label']) ?>">
                                    <?= Html::encode($c['label']) ?>
                                </div>
                                <div class="ov-barlist__num">
                                    <?= Html::encode(number_format((int) $c['count'])) ?>
                                </div>
                                <div class="ov-barlist__track">
                                    <div class="ov-barlist__fill"
                                         style="--pct: <?= number_format($pct, 4, '.', '') ?>; --i: <?= $i ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่ได้กำหนดหมวดของครุภัณฑ์</div>
                <?php endif; ?>
            </div>
        </section>

        <?php
        // Closure: render money breakdown section (label + bar by amount + count + ฿)
        $renderMoneySection = function (array $rows, string $title, int $sectionIndex) use ($formatBaht, $formatBahtFull) {
            $rowsTotal = 0.0;
            $rowsCount = 0;
            foreach ($rows as $r) {
                $rowsTotal += (float) $r['amount'];
                $rowsCount += (int) $r['count'];
            }
            $maxAmount = 0.0;
            foreach ($rows as $r) { if ($r['amount'] > $maxAmount) $maxAmount = (float) $r['amount']; }
            ?>
            <section class="ov-card" style="--i: <?= (int) $sectionIndex ?>">
                <header class="ov-card__head">
                    <h3 class="ov-card__title"><?= \yii\helpers\Html::encode($title) ?></h3>
                    <?php if ($rowsTotal > 0): ?>
                        <span class="ov-card__sub is-amount"
                              title="<?= \yii\helpers\Html::encode($formatBahtFull($rowsTotal)) ?> บาท">
                            <strong><?= \yii\helpers\Html::encode($formatBaht($rowsTotal)) ?></strong> ฿ ·
                            <?= \yii\helpers\Html::encode(number_format($rowsCount)) ?> รายการ
                        </span>
                    <?php elseif ($rowsCount > 0): ?>
                        <span class="ov-card__sub"><?= \yii\helpers\Html::encode(number_format($rowsCount)) ?> รายการ</span>
                    <?php else: ?>
                        <span class="ov-card__sub">ยังไม่มีข้อมูล</span>
                    <?php endif; ?>
                </header>
                <div class="ov-card__body">
                    <?php if (!empty($rows)): ?>
                        <div class="ov-money">
                            <?php foreach ($rows as $i => $row):
                                $amount = (float) $row['amount'];
                                $pct    = $maxAmount > 0 ? min(1, $amount / $maxAmount) : 0;
                                $isZero = $amount <= 0;
                            ?>
                                <div class="ov-money__row<?= $isZero ? ' is-empty' : '' ?>">
                                    <div class="ov-money__label" title="<?= \yii\helpers\Html::encode($row['label']) ?>">
                                        <?= \yii\helpers\Html::encode($row['label']) ?>
                                    </div>
                                    <div class="ov-money__meta">
                                        <span class="ov-money__cnt"><?= \yii\helpers\Html::encode(number_format((int) $row['count'])) ?></span>
                                        <span class="ov-money__amt"
                                              title="<?= \yii\helpers\Html::encode($formatBahtFull($amount)) ?> บาท">
                                            <?= \yii\helpers\Html::encode($formatBaht($amount)) ?>
                                        </span>
                                    </div>
                                    <?php if (!$isZero): ?>
                                        <div class="ov-money__track">
                                            <div class="ov-money__fill"
                                                 style="--pct: <?= number_format($pct, 4, '.', '') ?>; --i: <?= (int) $i ?>;"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="ov-empty">ยังไม่มีข้อมูล<?= \yii\helpers\Html::encode($title) ?></div>
                    <?php endif; ?>
                </div>
            </section>
            <?php
        };
        ?>

        <?php $renderMoneySection($budgetTypeBreakdown, 'ประเภทเงิน',      4); ?>
        <?php $renderMoneySection($methodGetBreakdown,  'วิธีได้มา',        5); ?>
        <?php $renderMoneySection($purchaseBreakdown,   'วิธีการได้มา',     6); ?>

        <!-- ── ครุภัณฑ์เพิ่มล่าสุด ────────────────────────────── -->
        <section class="ov-card" style="--i: 7">
            <header class="ov-card__head">
                <h3 class="ov-card__title">เพิ่มล่าสุด</h3>
                <span class="ov-card__sub">5 รายการ</span>
            </header>
            <div class="ov-card__body" style="padding-top: 0.35rem; padding-bottom: 0.35rem;">
                <?php if (!empty($recent)): ?>
                    <div class="ov-recent">
                        <?php foreach ($recent as $a):
                            $name = trim((string) ($a->asset_name ?? '')) ?: 'ไม่ระบุชื่อ';
                            $code = trim((string) ($a->code ?? ''));
                            $statusId = (string) ($a->asset_status ?? '');
                            $meta = $statusById[$statusId] ?? null;
                            $url  = Url::to(['/mobile/default/asset', 'id' => $a->id]);
                            $pillTone  = $meta['tone']  ?? 'secondary';
                            $pillLabel = $meta['label'] ?? 'ไม่ระบุ';
                            $pillIcon  = $meta['icon']  ?? 'circle-dot';
                        ?>
                            <a class="ov-recent__row" href="<?= Html::encode($url) ?>">
                                <span class="ov-recent__medal" aria-hidden="true">
                                    <i data-lucide="<?= Html::encode($pillIcon) ?>"></i>
                                </span>
                                <div class="ov-recent__body">
                                    <div class="ov-recent__name"><?= Html::encode($name) ?></div>
                                    <?php if ($code !== ''): ?>
                                        <div class="ov-recent__code"><?= Html::encode($code) ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="ov-recent__pill is-<?= Html::encode($pillTone) ?>">
                                    <?= Html::encode($pillLabel) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีรายการครุภัณฑ์เพิ่มล่าสุด</div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>
