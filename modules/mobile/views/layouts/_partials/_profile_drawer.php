<?php

/**
 * Profile offcanvas drawer — shared across every mobile page.
 *
 * Renders the right-side profile drawer (#profileDrawer). Toggled from
 * (a) navbar "ส่วนตัว" button, (b) hero menu button in index.php, or
 * (c) any element with data-bs-toggle="offcanvas" data-bs-target="#profileDrawer".
 *
 * Per-page badge counts (pending approvals, recent requests) are passed via
 * $this->params['profileBadges'] = ['pending' => N, 'recent' => N] so partial
 * stays page-agnostic. Missing values render no badge.
 *
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;
use yii\helpers\Url;

if (Yii::$app->user->isGuest) {
    return; // ไม่มี drawer ก่อนล็อกอิน
}

// ── Identity (avatar / name / role / dept / email) ─────────────────────
$identity   = Yii::$app->user->identity;
$userName   = $identity->username ?? 'ผู้ใช้';
$userEmail  = $identity->email ?? '';
$fullName   = $userName;
$roleLabel  = 'MEMBER';
$departmentName = '';
$avatarUrl  = null;

if (!empty($identity->employee)) {
    $emp = $identity->employee;
    try { $avatarUrl = $emp->ShowAvatar(); } catch (\Throwable $e) { $avatarUrl = null; }
    try { if (!empty($emp->fullname)) $fullName = $emp->fullname; } catch (\Throwable $e) {}
    try {
        if (!empty($emp->positionType) && !empty($emp->positionType->title)) {
            $roleLabel = $emp->positionType->title;
        }
    } catch (\Throwable $e) {}
    try {
        if (method_exists($emp, 'departmentName') && $emp->departmentName()) {
            $departmentName = $emp->departmentName();
        }
    } catch (\Throwable $e) {}
}

// ── Per-page badges (graceful degrade when not provided) ───────────────
$profileBadges = is_array($this->params['profileBadges'] ?? null) ? $this->params['profileBadges'] : [];
$pendingCount  = (int) ($profileBadges['pending'] ?? 0);
$recentCount   = (int) ($profileBadges['recent']  ?? 0);

$canManageMeeting = Yii::$app->user->can('meeting');

// ── Menu items ─────────────────────────────────────────────────────────
$drawerMenu = [
    [
        'icon'  => 'clipboard-list',
        'tone'  => 'primary',
        'label' => 'คำขอของฉัน',
        'url'   => Url::to(['/mobile/default/my-requests']),
        'badge' => $recentCount > 0 ? $recentCount : null,
    ],
    [
        'icon'  => 'clock',
        'tone'  => 'success',
        'label' => 'ลงเวลาเข้า-ออกงาน',
        'url'   => Url::to(['/mobile/default/attendance']),
    ],
];
if ($canManageMeeting) {
    $drawerMenu[] = [
        'icon'  => 'calendar-check',
        'tone'  => 'meeting',
        'label' => 'จัดห้องประชุม',
        'url'   => Url::to(['/mobile/default/room-manage']),
    ];
}
$drawerMenu[] = [
    'icon'  => 'check-square',
    'tone'  => 'warning',
    'label' => 'งานที่ต้องอนุมัติ',
    'url'   => Url::to(['/mobile/default/approvals', 'bucket' => 'pending']),
    'badge' => $pendingCount > 0 ? $pendingCount : null,
];
$drawerMenu[] = [
    'icon'  => 'bell',
    'tone'  => 'amber',
    'label' => 'การแจ้งเตือน',
    'url'   => Url::to(['/mobile/default/notifications']),
];

$toneStyles = [
    'primary' => 'background: var(--mobile-primary-soft); color: var(--mobile-primary);',
    'success' => 'background: var(--success-soft); color: var(--success);',
    'warning' => 'background: var(--warning-soft); color: var(--warning);',
    'meeting' => 'background: rgba(168, 85, 247, 0.13); color: #7e22ce;',
    'amber'   => 'background: rgba(245, 158, 11, 0.13); color: #b45309;',
    'danger'  => 'background: var(--danger-soft); color: var(--danger-strong);',
];
?>

<style>
/* ── Profile Offcanvas drawer (slide-from-right) ─────────────────────── */
#profileDrawer.offcanvas-end {
    width: min(86vw, 360px);
    background: #f5f7fa;
    border-left: 0;
}
#profileDrawer .pd-hero {
    position: relative;
    padding: calc(env(safe-area-inset-top, 0px) + var(--space-md)) var(--space-md) var(--space-lg);
    background: linear-gradient(180deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
    color: #fff;
    overflow: hidden;
}
#profileDrawer .pd-hero::before {
    content: ''; position: absolute;
    top: -80px; right: -60px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    pointer-events: none;
}
#profileDrawer .pd-close {
    position: absolute; top: calc(env(safe-area-inset-top, 0px) + var(--space-sm)); right: var(--space-md);
    width: 2.25rem; height: 2.25rem; border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    color: #fff; border: 0;
    display: inline-flex; align-items: center; justify-content: center;
    z-index: 2;
}
#profileDrawer .pd-close:hover, #profileDrawer .pd-close:focus { background: rgba(255, 255, 255, 0.28); }

#profileDrawer .pd-avatar {
    width: 4.5rem; height: 4.5rem; border-radius: 50%;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; margin-bottom: var(--space-sm);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18), inset 0 0 0 3px rgba(255, 255, 255, 0.95);
    position: relative; z-index: 1;
}
#profileDrawer .pd-avatar img { width: 100%; height: 100%; object-fit: cover; }
#profileDrawer .pd-avatar i { color: var(--mobile-primary); }
#profileDrawer .pd-name {
    font-size: var(--fs-lg); font-weight: 700;
    margin: 0; line-height: 1.2; letter-spacing: -0.01em;
    color: #fff; position: relative; z-index: 1;
}
#profileDrawer .pd-role {
    font-size: var(--fs-xs); color: rgba(255, 255, 255, 0.85);
    margin: 4px 0 0; font-weight: 500; position: relative; z-index: 1;
}
#profileDrawer .pd-dept {
    font-size: var(--fs-xs); color: rgba(255, 255, 255, 0.75);
    margin: 2px 0 0; position: relative; z-index: 1;
}

#profileDrawer .pd-body {
    padding: var(--space-md);
    display: flex; flex-direction: column; gap: var(--space-sm);
    overflow-y: auto;
}

#profileDrawer .pd-cta {
    display: flex; align-items: center; justify-content: space-between;
    gap: var(--space-sm);
    background: #fff;
    border-radius: 14px;
    padding: var(--space-sm) var(--space-md);
    text-decoration: none;
    color: var(--mobile-primary);
    font-weight: 600; font-size: var(--fs-sm);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
#profileDrawer .pd-cta:hover { color: var(--mobile-primary-dark); }
#profileDrawer .pd-cta svg { transition: transform 180ms cubic-bezier(0.22, 1, 0.36, 1); }
#profileDrawer .pd-cta:hover svg { transform: translateX(3px); }

#profileDrawer .pd-section-label {
    font-size: 0.6875rem; color: var(--ink-4); font-weight: 600;
    text-transform: none;
    margin: var(--space-sm) var(--space-xs) 0;
}

#profileDrawer .pd-menu {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
#profileDrawer .pd-item {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    text-decoration: none; color: inherit;
    border-bottom: 1px solid var(--ink-line);
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
#profileDrawer .pd-item:last-child { border-bottom: 0; }
#profileDrawer .pd-item:hover, #profileDrawer .pd-item:focus { background: rgba(13, 110, 253, 0.04); color: inherit; }
#profileDrawer .pd-item-icon {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
#profileDrawer .pd-item-icon svg { width: 1.125rem; height: 1.125rem; }
#profileDrawer .pd-item-label { flex-grow: 1; font-size: var(--fs-sm); color: var(--ink); font-weight: 500; }
#profileDrawer .pd-item-badge {
    flex-shrink: 0;
    background: var(--danger); color: #fff;
    border-radius: 999px;
    font-size: 0.6875rem; font-weight: 700;
    padding: 2px 8px; min-width: 1.25rem; text-align: center;
}
#profileDrawer .pd-chevron { color: var(--ink-5); flex-shrink: 0; width: 16px; height: 16px; }

#profileDrawer .pd-logout {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    background: #fff;
    border: 0;
    width: 100%; border-radius: 14px;
    color: var(--danger-strong); font-weight: 600;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
#profileDrawer .pd-logout:hover, #profileDrawer .pd-logout:focus { background: rgba(239, 68, 68, 0.08); }
#profileDrawer .pd-logout-icon {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    background: var(--danger-soft); color: var(--danger-strong);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
#profileDrawer .pd-version {
    text-align: center;
    font-size: 0.6875rem; color: var(--ink-5);
    padding: var(--space-md) 0 calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
}

/* Stagger menu rows when drawer opens */
@keyframes pd-enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
#profileDrawer.show .pd-cta,
#profileDrawer.show .pd-menu,
#profileDrawer.show .pd-logout-wrap {
    animation: pd-enter 320ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
}
#profileDrawer.show .pd-cta { animation-delay: 60ms; }
#profileDrawer.show .pd-menu { animation-delay: 120ms; }
#profileDrawer.show .pd-logout-wrap { animation-delay: 180ms; }

@media (prefers-reduced-motion: reduce) {
    #profileDrawer.show .pd-cta,
    #profileDrawer.show .pd-menu,
    #profileDrawer.show .pd-logout-wrap { animation: none !important; }
    #profileDrawer .pd-item, #profileDrawer .pd-logout, #profileDrawer .pd-cta svg { transition: none !important; }
}
</style>

<div class="offcanvas offcanvas-end" tabindex="-1" id="profileDrawer" aria-labelledby="profileDrawerLabel">

    <header class="pd-hero">
        <button type="button" class="pd-close" data-bs-dismiss="offcanvas" aria-label="ปิดเมนูโปรไฟล์">
            <i data-lucide="x" class="mi-sm"></i>
        </button>
        <span class="pd-avatar" aria-hidden="true">
            <?php if ($avatarUrl): ?>
                <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="72" height="72">
            <?php else: ?>
                <i data-lucide="user" class="mi-lg"></i>
            <?php endif; ?>
        </span>
        <h2 class="pd-name" id="profileDrawerLabel"><?= Html::encode($fullName) ?></h2>
        <p class="pd-role"><?= Html::encode($roleLabel) ?></p>
        <?php if ($departmentName !== ''): ?>
            <p class="pd-dept"><?= Html::encode($departmentName) ?></p>
        <?php endif; ?>
    </header>

    <div class="pd-body">

        <a href="<?= Html::encode(Url::to(['/mobile/default/profile'])) ?>" class="pd-cta">
            <span>ดูโปรไฟล์เต็ม<?= $userEmail !== '' ? ' · ' . Html::encode($userEmail) : '' ?></span>
            <i data-lucide="arrow-right" aria-hidden="true"></i>
        </a>

        <p class="pd-section-label">เมนู</p>
        <nav class="pd-menu" aria-label="เมนูผู้ใช้">
            <?php foreach ($drawerMenu as $item): ?>
                <a href="<?= Html::encode($item['url']) ?>" class="pd-item">
                    <span class="pd-item-icon" aria-hidden="true" style="<?= $toneStyles[$item['tone']] ?? $toneStyles['primary'] ?>">
                        <i data-lucide="<?= Html::encode($item['icon']) ?>"></i>
                    </span>
                    <span class="pd-item-label"><?= Html::encode($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="pd-item-badge"><?= Html::encode((string) $item['badge']) ?></span>
                    <?php endif; ?>
                    <i data-lucide="chevron-right" class="pd-chevron" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="pd-logout-wrap">
            <?= Html::beginForm(['/mobile/auth/logout'], 'post', ['class' => 'mb-0']) ?>
                <button type="submit" class="pd-logout">
                    <span class="pd-logout-icon"><i data-lucide="log-out"></i></span>
                    <span class="flex-grow-1 text-start">ออกจากระบบ</span>
                </button>
            <?= Html::endForm() ?>
        </div>

        <p class="pd-version">บริการออนไลน์ v1.0</p>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
// Refresh lucide icons after Bootstrap shows the drawer (icons inside a hidden
// offcanvas can be skipped during the initial createIcons pass).
document.getElementById('profileDrawer')?.addEventListener('shown.bs.offcanvas', function () {
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
});
JS, \yii\web\View::POS_READY);
