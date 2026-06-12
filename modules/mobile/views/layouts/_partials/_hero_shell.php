<?php
/**
 * Reusable fixed Hero (drenched blue) + optional overlay card.
 *
 * Renders the .app-shell wrapper, .app-hero block, and either an optional
 * custom overlay card or the legacy .app-stats grid. Pair with
 * <div class="app-scroll"> for the scrollable body. CSS lives in _head.php
 * (.app-shell, .app-scroll, .app-hero, .app-stats, .app-scroll.has-overlay);
 * the height observer lives in mobile-shared.js.
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
 *   <div class="app-scroll has-stats">  <!-- has-stats / has-overlay adds extra top padding for the overlay -->
 *       ...page body...
 *   </div>
 *
 * @var string      $icon      Lucide icon name shown in the hero medallion.
 * @var string      $title     Hero heading (h1).
 * @var string|null $subtitle  Optional hero subtitle line.
 * @var string|null $overlayHtml Optional raw HTML rendered between the hero and scroll body.
 * @var array       $stats     Optional list of stats. Each item: ['value','label','tone'?,'url'?,'isActive'?].
 *                             tone ∈ {primary, warning, success, danger}.
 *                             url makes the row clickable; omit for non-interactive.
 * @var string|null $statsLabel Optional aria-label for the stats nav.
 * @var string|null $extraClass Optional extra class added to .app-shell (page-level tweaks).
 */

use yii\bootstrap5\Html;

$subtitle   = $subtitle   ?? null;
$overlayHtml = $overlayHtml ?? null;
$stats      = $stats      ?? [];
$statsLabel = $statsLabel ?? 'สรุปสถานะ';
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

    <?php if (!empty($overlayHtml)): ?>
        <?= $overlayHtml ?>
    <?php elseif ($statCount > 0): ?>
        <nav class="app-stats" data-cols="<?= $statCount ?>" aria-label="<?= Html::encode($statsLabel) ?>">
            <?php foreach ($stats as $s):
                $tone      = $s['tone']      ?? '';
                $isActive  = !empty($s['isActive']);
                $hasUrl    = !empty($s['url']);
                $clickable = !empty($s['clickable']);
                $tag       = $hasUrl ? 'a' : ($clickable ? 'button' : 'span');
                $data      = isset($s['data']) && is_array($s['data']) ? $s['data'] : [];
            ?>
                <<?= $tag ?>
                    <?php if ($hasUrl): ?>href="<?= Html::encode($s['url']) ?>"<?php endif; ?>
                    <?php if ($clickable && !$hasUrl): ?>type="button"<?php endif; ?>
                    class="app-stat<?= $isActive ? ' is-active' : '' ?>"
                    <?php if ($tone): ?>data-tone="<?= Html::encode($tone) ?>"<?php endif; ?>
                    <?php foreach ($data as $k => $v): ?>data-<?= Html::encode((string) $k) ?>="<?= Html::encode((string) $v) ?>" <?php endforeach; ?>>
                    <span class="app-stat-num"><?= Html::encode((string) $s['value']) ?></span>
                    <span class="app-stat-lbl"><?= Html::encode($s['label']) ?></span>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

</div>
