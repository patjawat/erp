<?php
/**
 * Sticky bottom navigation bar with floating Scan (FAB).
 * Active state from $current_page or $mobileNavActive: home | news | services | profile | scan
 */
use yii\bootstrap5\Html;
use yii\helpers\Url;

$current_page = $this->params['current_page'] ?? $this->params['mobileNavActive'] ?? 'home';
$fabUrl   = $this->params['fabUrl'] ?? ['/mobile/default/scan'];
$fabLabel = $this->params['fabLabel'] ?? 'สแกน';

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
    .mobile-fab-slot { position: relative; min-width: 4.5rem; }
    .mobile-fab {
        position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
        width: 4rem; height: 4rem; border-radius: 999px;
        background: linear-gradient(135deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
        box-shadow: var(--mobile-fab-shadow);
    }
    .mobile-fab i, .mobile-fab svg { width: 1.75rem; height: 1.75rem; }
    .mobile-fab svg { stroke: currentColor; }
    .mobile-fab.mobile-fab-active { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.35); }
</style>

<nav class="mobile-bottom-nav fixed-bottom bg-white border-top shadow-lg d-flex align-items-end justify-content-center" role="navigation" aria-label="เมนูหลัก">
    <div class="d-flex align-items-end w-100 justify-content-around px-1 pb-2 pt-2">
        <?php foreach (['home', 'news'] as $key): ?>
            <?php $item = $navItems[$key]; $isActive = $current_page === $key; ?>
            <a href="<?= Html::encode(Url::to($item['url'])) ?>" class="mobile-nav-item flex-grow-1 d-flex flex-column align-items-center py-1 text-decoration-none <?= $isActive ? 'mobile-nav-item-active' : 'text-secondary' ?>" aria-label="<?= Html::encode($item['label']) ?>">
                <i data-lucide="<?= Html::encode($item['icon']) ?>" class="mb-1"></i>
                <span class="small"><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <div class="mobile-fab-slot d-flex justify-content-center align-items-center flex-grow-1">
            <a href="<?= Html::encode(Url::to($fabUrl)) ?>" class="mobile-fab d-flex align-items-center justify-content-center text-white text-decoration-none <?= $current_page === 'scan' ? 'mobile-fab-active' : '' ?>" aria-label="<?= Html::encode($fabLabel) ?>">
                <i data-lucide="qr-code"></i>
            </a>
        </div>

        <?php foreach (['services', 'profile'] as $key): ?>
            <?php $item = $navItems[$key]; $isActive = $current_page === $key; ?>
            <a href="<?= Html::encode(Url::to($item['url'])) ?>" class="mobile-nav-item flex-grow-1 d-flex flex-column align-items-center py-1 text-decoration-none <?= $isActive ? 'mobile-nav-item-active' : 'text-secondary' ?>" aria-label="<?= Html::encode($item['label']) ?>">
                <i data-lucide="<?= Html::encode($item['icon']) ?>" class="mb-1"></i>
                <span class="small"><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
