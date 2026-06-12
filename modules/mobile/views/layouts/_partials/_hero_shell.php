<?php
/**
 * Reusable fixed Hero (drenched blue) + optional Stats overlay.
 *
 * Renders the .app-shell wrapper, .app-hero block, and an optional .app-stats
 * grid. Pair with <div class="app-scroll"> for the scrollable body. CSS lives
 * in _head.php (.app-shell, .app-scroll, .app-hero, .app-stats); the height
 * observer lives in mobile-shared.js.
 *
 * Usage example:
 *
 *   <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
 *       'icon'     => 'mail',
 *       'title'    => 'หนังสือราชการ',
 *       'subtitle' => 'รายการหนังสือทั้งหมด',
 *       'stats'    => [
 *           ['url' => Url::to(['/mobile/default/news', 'filter' => 'all']),
 *            'value' => $total, 'label' => 'ทั้งหมด', 'tone' => 'primary',
 *            'isActive' => $filter === 'all'],
 *           ['url' => Url::to(['/mobile/default/news', 'filter' => 'unread']),
 *            'value' => $unread, 'label' => 'ยังไม่อ่าน', 'tone' => 'warning',
 *            'isActive' => $filter === 'unread'],
 *       ],
 *   ]) ?>
 *
 *   <div class="app-scroll has-stats">  <!-- has-stats adds extra top padding for the overlay -->
 *       ...page body...
 *   </div>
 *
 * @var string      $icon      Lucide icon name shown in the hero medallion.
 * @var string      $title     Hero heading (h1).
 * @var string|null $subtitle  Optional hero subtitle line.
 * @var array       $stats     Optional list of stats. Each item: ['value','label','tone'?,'url'?,'isActive'?].
 *                             tone ∈ {primary, warning, success, danger}.
 *                             url makes the row clickable; omit for non-interactive.
 * @var string|null $extraClass Optional extra class added to .app-shell (page-level tweaks).
 */

use yii\bootstrap5\Html;

$subtitle   = $subtitle   ?? null;
$stats      = $stats      ?? [];
$extraClass = $extraClass ?? '';

$statCount = count($stats);
?>
<div class="app-shell <?= Html::encode($extraClass) ?>">

    <header class="app-hero">
        <div class="app-hero-row">
            <span class="app-hero-icon" aria-hidden="true">
                <i data-lucide="<?= Html::encode($icon) ?>"></i>
            </span>
            <div class="min-w-0 flex-grow-1">
                <h1 class="app-hero-title"><?= Html::encode($title) ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p class="app-hero-sub"><?= Html::encode($subtitle) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($statCount > 0): ?>
        <nav class="app-stats" data-cols="<?= $statCount ?>" aria-label="สรุปสถานะ">
            <?php foreach ($stats as $s):
                $tone     = $s['tone']     ?? '';
                $isActive = !empty($s['isActive']);
                $hasUrl   = !empty($s['url']);
                $tag      = $hasUrl ? 'a' : 'span';
            ?>
                <<?= $tag ?>
                    <?php if ($hasUrl): ?>href="<?= Html::encode($s['url']) ?>"<?php endif; ?>
                    class="app-stat<?= $isActive ? ' is-active' : '' ?>"
                    <?php if ($tone): ?>data-tone="<?= Html::encode($tone) ?>"<?php endif; ?>>
                    <span class="app-stat-num"><?= Html::encode((string) $s['value']) ?></span>
                    <span class="app-stat-lbl"><?= Html::encode($s['label']) ?></span>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

</div>
