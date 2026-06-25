<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var int $hrTotal */
/** @var int $assetTotal */

$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'ภาพรวม';
$this->params['mobileSubtitle'] = 'แดชบอร์ดสรุปข้อมูล';

$cards = [
    [
        'cat'   => 'leave',
        'icon'  => 'users',
        'kicker'=> 'บุคลากร',
        'title' => 'ภาพรวมบุคลากร',
        'desc'  => 'จำนวน · สัดส่วนประเภท · ตำแหน่งและกลุ่มงาน',
        'value' => number_format((int) $hrTotal),
        'unit'  => 'คน',
        'url'   => Url::to(['/mobile/default/overview-hr']),
    ],
    [
        'cat'   => 'asset',
        'icon'  => 'package',
        'kicker'=> 'ทรัพย์สิน',
        'title' => 'ภาพรวมทรัพย์สิน',
        'desc'  => 'วงจรชีวิต · หมวดครุภัณฑ์ · รับเข้าใหม่',
        'value' => number_format((int) $assetTotal),
        'unit'  => 'รายการ',
        'url'   => Url::to(['/mobile/default/overview-asset']),
    ],
];
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => 'layout-dashboard',
    'title'    => 'ภาพรวม',
    'subtitle' => 'แดชบอร์ดสรุปข้อมูลในระบบ',
]) ?>

<style>
.ov-hub {
    display: flex; flex-direction: column;
    gap: var(--space-md);
    padding-top: var(--space-md);
}
.ov-hub-card {
    position: relative;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--surface, #fff);
    border: 1px solid var(--line, rgba(15,23,42,0.08));
    border-radius: var(--radius, 10px);
    box-shadow: var(--shadow-1, 0 1px 2px rgba(15,23,42,0.04));
    color: inherit; text-decoration: none;
    overflow: hidden;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-hub-card::before {
    content: '';
    position: absolute; inset: 0 0 0 auto;
    width: 40%;
    background:
        radial-gradient(circle at top right,
            color-mix(in oklch, var(--accent, #0d6efd) 16%, transparent) 0%,
            transparent 65%);
    opacity: 0.55;
    pointer-events: none;
}
.ov-hub-card:hover,
.ov-hub-card:focus-visible {
    color: inherit;
    border-color: color-mix(in oklch, var(--accent, #0d6efd) 32%, var(--line));
    box-shadow:
        0 10px 24px color-mix(in oklch, var(--accent, #0d6efd) 12%, transparent),
        0 2px 4px rgba(15,23,42,0.04);
    transform: translateY(-2px);
}
.ov-hub-card:active { transform: translateY(0); }
.ov-hub-card[data-cat="leave"]  { --accent: var(--cat-leave-fg); }
.ov-hub-card[data-cat="asset"]  { --accent: var(--cat-asset-fg); }

.ov-hub-medal {
    position: relative; z-index: 1;
    width: 3.25rem; height: 3.25rem;
    border-radius: 18px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--cat-bg);
    color: var(--cat-fg);
    flex-shrink: 0;
}
.ov-hub-card[data-cat="leave"] .ov-hub-medal {
    background: var(--cat-leave-bg); color: var(--cat-leave-fg);
}
.ov-hub-card[data-cat="asset"] .ov-hub-medal {
    background: var(--cat-asset-bg); color: var(--cat-asset-fg);
}
.ov-hub-medal svg { width: 1.5rem; height: 1.5rem; }

.ov-hub-body {
    position: relative; z-index: 1;
    min-width: 0;
    display: flex; flex-direction: column;
    gap: 2px;
}
.ov-hub-kicker {
    font-size: 0.72rem; font-weight: 700;
    color: var(--accent, var(--mobile-primary));
    line-height: 1.2;
}
.ov-hub-title {
    font-size: 1rem; font-weight: 700;
    color: var(--ink, #1a202c);
    line-height: 1.25;
}
.ov-hub-desc {
    font-size: 0.78rem; color: var(--ink-3, #718096);
    line-height: 1.35; margin-top: 2px;
    overflow: hidden; text-overflow: ellipsis;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}

.ov-hub-meta {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    text-align: right;
}
.ov-hub-value {
    font-size: 1.5rem; font-weight: 800;
    color: var(--ink, #1a202c);
    font-variant-numeric: tabular-nums;
    line-height: 1; letter-spacing: -0.02em;
}
.ov-hub-unit {
    font-size: 0.7rem; color: var(--ink-4, #a0aec0);
    font-weight: 500;
}
.ov-hub-arrow {
    margin-top: 6px;
    width: 28px; height: 28px;
    border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-2, #f7f9fc);
    color: var(--accent, var(--mobile-primary));
    transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.ov-hub-card:hover .ov-hub-arrow,
.ov-hub-card:focus-visible .ov-hub-arrow { transform: translateX(3px); }
.ov-hub-arrow svg { width: 14px; height: 14px; }

@keyframes ov-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ov-hub > * {
    animation: ov-enter 360ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 80ms);
}

@media (prefers-reduced-motion: reduce) {
    .ov-hub > *, .ov-hub-card, .ov-hub-arrow { animation: none !important; transition: none !important; }
    .ov-hub-card:hover { transform: none; }
}
</style>

<div class="app-scroll">
    <div class="ov-hub">
        <?php foreach ($cards as $i => $c): ?>
            <a href="<?= Html::encode($c['url']) ?>"
               class="ov-hub-card"
               data-cat="<?= Html::encode($c['cat']) ?>"
               style="--i: <?= (int) $i ?>"
               aria-label="<?= Html::encode($c['title'] . ' จำนวน ' . $c['value'] . ' ' . $c['unit']) ?>">
                <span class="ov-hub-medal" aria-hidden="true">
                    <i data-lucide="<?= Html::encode($c['icon']) ?>"></i>
                </span>
                <span class="ov-hub-body">
                    <span class="ov-hub-kicker"><?= Html::encode($c['kicker']) ?></span>
                    <span class="ov-hub-title"><?= Html::encode($c['title']) ?></span>
                    <span class="ov-hub-desc"><?= Html::encode($c['desc']) ?></span>
                </span>
                <span class="ov-hub-meta">
                    <span class="ov-hub-value"><?= Html::encode($c['value']) ?></span>
                    <span class="ov-hub-unit"><?= Html::encode($c['unit']) ?></span>
                    <span class="ov-hub-arrow" aria-hidden="true">
                        <i data-lucide="arrow-right"></i>
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
