<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var int $total */
/** @var array<int,array{label:string,count:int}> $typeBreakdown */
/** @var array<int,array{label:string,count:int}> $groupBreakdown */
/** @var array<int,array{label:string,count:int}> $topPositions */
/** @var \app\modules\hr\models\Employees[] $recent */

$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'ภาพรวมบุคลากร';
$this->params['mobileSubtitle'] = 'สรุปจำนวน ประเภท และตำแหน่ง';

$total          = (int) $total;
$typeBreakdown  = is_array($typeBreakdown ?? null)  ? $typeBreakdown  : [];
$groupBreakdown = is_array($groupBreakdown ?? null) ? $groupBreakdown : [];
$topPositions   = is_array($topPositions ?? null)   ? $topPositions   : [];
$recent         = is_array($recent ?? null)         ? $recent         : [];

$typeCount  = count($typeBreakdown);
$groupCount = array_sum(array_column($groupBreakdown, 'count'));
$posCount   = count($topPositions);

// Bar chart: หา max เพื่อสเกล
$groupMax = 0;
foreach ($groupBreakdown as $g) { if ($g['count'] > $groupMax) $groupMax = (int) $g['count']; }
$posMax = 0;
foreach ($topPositions as $p) { if ($p['count'] > $posMax) $posMax = (int) $p['count']; }

// สัดส่วน type สำหรับ stacked bar
$typeTotal = array_sum(array_column($typeBreakdown, 'count'));
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => 'users',
    'title'    => 'ภาพรวมบุคลากร',
    'subtitle' => 'จำนวน · ประเภท · ตำแหน่ง',
    'stats'    => [
        ['value' => number_format($total),     'label' => 'ทั้งหมด',     'tone' => 'primary'],
        ['value' => number_format($typeCount), 'label' => 'ประเภท',     'tone' => 'success'],
        ['value' => number_format($posCount),  'label' => 'ตำแหน่ง',    'tone' => 'warning'],
    ],
    'statsLabel' => 'สรุปบุคลากร',
]) ?>

<style>
.ov-hr {
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
.ov-card__body {
    padding: 1rem 1.05rem;
}

/* Stacked type bar */
.ov-typebar {
    display: flex; height: 14px;
    border-radius: 999px; overflow: hidden;
    background: var(--surface-3, #eef2f7);
    margin-bottom: 0.85rem;
}
.ov-typebar__seg {
    height: 100%;
    transform-origin: left;
    animation: ov-bar-grow 520ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 60ms + 100ms);
}
.ov-typebar__seg:nth-child(1) { background: var(--cat-leave-fg, #c2410c); }
.ov-typebar__seg:nth-child(2) { background: var(--cat-meeting-fg, #6d28d9); }
.ov-typebar__seg:nth-child(3) { background: var(--cat-vehicle-fg, #0e7490); }
.ov-typebar__seg:nth-child(4) { background: var(--cat-asset-fg, #15803d); }
.ov-typebar__seg:nth-child(5) { background: var(--cat-attendance-fg, #b45309); }
.ov-typebar__seg:nth-child(n+6) { background: var(--ink-3, #718096); }

.ov-typelegend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem 0.85rem;
}
.ov-typelegend__item {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.78rem;
    color: var(--ink-2, #4a5568);
    min-width: 0;
}
.ov-typelegend__dot {
    width: 9px; height: 9px; border-radius: 50%;
    flex-shrink: 0;
}
.ov-typelegend__item:nth-child(1) .ov-typelegend__dot { background: var(--cat-leave-fg); }
.ov-typelegend__item:nth-child(2) .ov-typelegend__dot { background: var(--cat-meeting-fg); }
.ov-typelegend__item:nth-child(3) .ov-typelegend__dot { background: var(--cat-vehicle-fg); }
.ov-typelegend__item:nth-child(4) .ov-typelegend__dot { background: var(--cat-asset-fg); }
.ov-typelegend__item:nth-child(5) .ov-typelegend__dot { background: var(--cat-attendance-fg); }
.ov-typelegend__item:nth-child(n+6) .ov-typelegend__dot { background: var(--ink-3); }
.ov-typelegend__label {
    flex: 1; min-width: 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-typelegend__num {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    color: var(--ink, #1a202c);
}

/* Horizontal bar list */
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
        color-mix(in oklch, var(--cat-leave-fg) 70%, var(--mobile-primary)),
        var(--cat-leave-fg));
    border-radius: 999px;
    transform-origin: left;
    transform: scaleX(0);
    animation: ov-bar-fill 540ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: calc(var(--i, 0) * 60ms + 120ms);
}
.ov-barlist--asset .ov-barlist__fill {
    background: linear-gradient(90deg,
        color-mix(in oklch, var(--cat-asset-fg) 70%, var(--mobile-primary)),
        var(--cat-asset-fg));
}

/* Recent list */
.ov-recent {
    display: flex; flex-direction: column;
}
.ov-recent__row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid var(--line, rgba(15,23,42,0.08));
    text-decoration: none; color: inherit;
}
.ov-recent__row:last-child { border-bottom: 0; }
.ov-recent__avatar {
    width: 2.25rem; height: 2.25rem;
    border-radius: 50%;
    background: var(--cat-leave-bg);
    color: var(--cat-leave-fg);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 0.78rem; font-weight: 700;
}
.ov-recent__body { flex: 1; min-width: 0; }
.ov-recent__name {
    font-size: 0.86rem; font-weight: 600;
    color: var(--ink, #1a202c);
    line-height: 1.25;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ov-recent__meta {
    font-size: 0.72rem; color: var(--ink-3, #718096);
    line-height: 1.25;
    margin-top: 1px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.ov-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: var(--ink-3, #718096);
    font-size: 0.82rem;
}

/* Animations */
@keyframes ov-bar-grow {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@keyframes ov-bar-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(var(--pct, 1)); }
}
@keyframes ov-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ov-hr > * {
    animation: ov-enter 360ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 70ms);
}

@media (prefers-reduced-motion: reduce) {
    .ov-hr > *,
    .ov-typebar__seg,
    .ov-barlist__fill { animation: none !important; }
    .ov-barlist__fill { transform: scaleX(var(--pct, 1)); }
}
</style>

<div class="app-scroll has-stats">
    <div class="ov-hr">

        <!-- ── สัดส่วนตามประเภทบุคลากร ────────────────────────── -->
        <section class="ov-card" style="--i: 0">
            <header class="ov-card__head">
                <h3 class="ov-card__title">สัดส่วนตามประเภท</h3>
                <span class="ov-card__sub"><?= Html::encode(number_format($typeTotal)) ?> คน</span>
            </header>
            <div class="ov-card__body">
                <?php if ($typeTotal > 0): ?>
                    <div class="ov-typebar" aria-hidden="true">
                        <?php foreach ($typeBreakdown as $i => $t):
                            $pct = $typeTotal > 0 ? ($t['count'] / $typeTotal) * 100 : 0;
                        ?>
                            <div class="ov-typebar__seg"
                                 style="flex: 0 0 <?= round($pct, 2) ?>%; --i: <?= $i ?>;"
                                 title="<?= Html::encode($t['label'] . ' ' . $t['count']) ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ov-typelegend">
                        <?php foreach ($typeBreakdown as $t):
                            $pct = $typeTotal > 0 ? ($t['count'] / $typeTotal) * 100 : 0;
                        ?>
                            <div class="ov-typelegend__item">
                                <span class="ov-typelegend__dot" aria-hidden="true"></span>
                                <span class="ov-typelegend__label"><?= Html::encode($t['label']) ?></span>
                                <span class="ov-typelegend__num">
                                    <?= Html::encode(number_format((int) $t['count'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีข้อมูลประเภทบุคลากร</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── กลุ่มงาน / ฝ่าย ─────────────────────────────────── -->
        <section class="ov-card" style="--i: 1">
            <header class="ov-card__head">
                <h3 class="ov-card__title">กลุ่มงาน / ฝ่าย</h3>
                <span class="ov-card__sub">รวม <?= Html::encode(number_format((int) $groupCount)) ?> คน</span>
            </header>
            <div class="ov-card__body">
                <?php if (!empty($groupBreakdown)): ?>
                    <div class="ov-barlist">
                        <?php foreach ($groupBreakdown as $i => $g):
                            $pct = $groupMax > 0 ? min(1, $g['count'] / $groupMax) : 0;
                        ?>
                            <div class="ov-barlist__row">
                                <div class="ov-barlist__label" title="<?= Html::encode($g['label']) ?>">
                                    <?= Html::encode($g['label']) ?>
                                </div>
                                <div class="ov-barlist__num">
                                    <?= Html::encode(number_format((int) $g['count'])) ?>
                                </div>
                                <div class="ov-barlist__track">
                                    <div class="ov-barlist__fill"
                                         style="--pct: <?= number_format($pct, 4, '.', '') ?>; --i: <?= $i ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่ได้กำหนดกลุ่มงานของบุคลากร</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── ตำแหน่งยอดนิยม ─────────────────────────────────── -->
        <section class="ov-card" style="--i: 2">
            <header class="ov-card__head">
                <h3 class="ov-card__title">ตำแหน่งยอดนิยม</h3>
                <span class="ov-card__sub">Top <?= Html::encode((string) count($topPositions)) ?></span>
            </header>
            <div class="ov-card__body">
                <?php if (!empty($topPositions)): ?>
                    <div class="ov-barlist">
                        <?php foreach ($topPositions as $i => $p):
                            $pct = $posMax > 0 ? min(1, $p['count'] / $posMax) : 0;
                        ?>
                            <div class="ov-barlist__row">
                                <div class="ov-barlist__label" title="<?= Html::encode($p['label']) ?>">
                                    <?= Html::encode($p['label']) ?>
                                </div>
                                <div class="ov-barlist__num">
                                    <?= Html::encode(number_format((int) $p['count'])) ?>
                                </div>
                                <div class="ov-barlist__track">
                                    <div class="ov-barlist__fill"
                                         style="--pct: <?= number_format($pct, 4, '.', '') ?>; --i: <?= $i ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีตำแหน่งบุคลากร</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── เพิ่มล่าสุด ─────────────────────────────────────── -->
        <section class="ov-card" style="--i: 3">
            <header class="ov-card__head">
                <h3 class="ov-card__title">เพิ่มล่าสุด</h3>
                <span class="ov-card__sub">5 รายการ</span>
            </header>
            <div class="ov-card__body" style="padding-top: 0.35rem; padding-bottom: 0.35rem;">
                <?php if (!empty($recent)): ?>
                    <div class="ov-recent">
                        <?php foreach ($recent as $emp):
                            $name = trim((string) ($emp->fullname ?? ''));
                            if ($name === '') {
                                $name = trim((string) (($emp->prefix ?? '') . ' ' . ($emp->fname ?? '') . ' ' . ($emp->lname ?? '')));
                            }
                            if ($name === '') $name = 'ไม่ระบุชื่อ';
                            $initials = '';
                            if (function_exists('mb_substr')) {
                                $initials = mb_substr($name, 0, 1, 'UTF-8');
                            } else {
                                $initials = substr($name, 0, 1);
                            }
                            $meta = '';
                            try {
                                $meta = $emp->positionTypeName ?? '';
                            } catch (\Throwable $e) { $meta = ''; }
                        ?>
                            <div class="ov-recent__row">
                                <span class="ov-recent__avatar" aria-hidden="true">
                                    <?= Html::encode($initials) ?>
                                </span>
                                <div class="ov-recent__body">
                                    <div class="ov-recent__name"><?= Html::encode($name) ?></div>
                                    <?php if ($meta !== ''): ?>
                                        <div class="ov-recent__meta"><?= Html::encode($meta) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ov-empty">ยังไม่มีข้อมูลบุคลากร</div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>
