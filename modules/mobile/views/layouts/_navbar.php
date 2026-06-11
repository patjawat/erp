<?php
/**
 * Sticky bottom navigation bar.
 * Active state from $current_page or $mobileNavActive: home | news | services | profile
 */
use yii\bootstrap5\Html;
use yii\helpers\Url;

$current_page = $this->params['current_page'] ?? $this->params['mobileNavActive'] ?? 'home';

$navItems = [
    'home'     => ['label' => 'หน้าแรก', 'url' => ['/mobile/default/index'],    'icon' => 'house'],
    'news'     => ['label' => 'หนังสือราชการ', 'url' => ['/mobile/default/news'], 'icon' => 'file-text'],
    'services' => ['label' => 'บริการ',  'url' => ['/mobile/default/services'],   'icon' => 'grid-3x3'],
    'profile'  => ['label' => 'ส่วนตัว', 'url' => ['/mobile/default/profile'],   'icon' => 'user'],
];
?>
<style>
    .mobile-bottom-nav { padding-bottom: env(safe-area-inset-bottom); min-height: 4.75rem; }
    .mobile-nav-item i, .mobile-nav-item svg { width: 1.25rem; height: 1.25rem; margin-bottom: 0.2rem; flex-shrink: 0; }
    .mobile-nav-item svg { stroke: currentColor; }
    .mobile-nav-item:hover { color: var(--mobile-primary); }
    .mobile-nav-item.mobile-nav-item-active { color: var(--mobile-primary) !important; }
    .mobile-nav-item:active { opacity: 0.85; }
</style>

<nav class="mobile-bottom-nav fixed-bottom bg-white border-top shadow-lg d-flex align-items-end justify-content-center" role="navigation" aria-label="เมนูหลัก">
    <div class="d-flex align-items-end w-100 justify-content-around px-1 pb-2 pt-2">
        <?php foreach ($navItems as $key => $item): ?>
            <?php $isActive = $current_page === $key; ?>
            <a href="<?= Html::encode(Url::to($item['url'])) ?>" class="mobile-nav-item flex-grow-1 d-flex flex-column align-items-center py-1 text-decoration-none <?= $isActive ? 'mobile-nav-item-active' : 'text-secondary' ?>" aria-label="<?= Html::encode($item['label']) ?>">
                <i data-lucide="<?= Html::encode($item['icon']) ?>" class="mb-1"></i>
                <span class="small"><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
