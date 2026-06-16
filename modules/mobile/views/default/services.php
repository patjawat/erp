<?php
/** @var yii\web\View $this */
/** @var string $current_page */
/** @var bool $isDriver */
/** @var int $driverMissionCount */
/** @var bool $canManageMeeting */
/** @var int $managedMeetingPendingCount */

use yii\helpers\Html;
use yii\helpers\Url;

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'บริการ';
$this->params['mobileSubtitle'] = 'เลือกบริการที่ต้องการใช้งาน';

$isDriver = (bool) ($isDriver ?? Yii::$app->user->can('driver'));
$driverMissionCount = (int) ($driverMissionCount ?? 0);
$canManageMeeting = (bool) ($canManageMeeting ?? Yii::$app->user->can('meeting'));
$managedMeetingPendingCount = (int) ($managedMeetingPendingCount ?? 0);

/**
 * Featured = primary services with their own large medallion card.
 * Secondary = lower-frequency or admin-flavoured services in a compact row list.
 * Categories drive icon colour via the cat-medallion system in _head.php.
 */
$featured = [
    ['icon' => 'car',         'cat' => 'vehicle',     'label' => 'จองรถ',         'desc' => 'จองรถสำหรับเดินทางราชการ',  'url' => Url::to(['/mobile/default/booking-vehicle'])],
    ['icon' => 'calendar',    'cat' => 'meeting',     'label' => 'จองห้องประชุม', 'desc' => 'ตรวจสอบและจองห้องประชุม',   'url' => Url::to(['/mobile/default/booking-meeting'])],
    ['icon' => 'wrench',      'cat' => 'maintenance', 'label' => 'แจ้งซ่อม',       'desc' => 'ดูประวัติและแจ้งซ่อมใหม่',  'url' => Url::to(['/mobile/default/maintenance-request'])],
    ['icon' => 'user-check',  'cat' => 'leave',       'label' => 'ขอลาออนไลน์',   'desc' => 'ส่งคำขอลาประเภทต่างๆ',     'url' => Url::to(['/mobile/default/leave-request'])],
];

$secondary = [
    ['icon' => 'clock',           'cat' => 'attendance', 'label' => 'ลงเวลาเข้า-ออกงาน',  'url' => Url::to(['/mobile/default/attendance'])],
    ['icon' => 'clipboard-check', 'cat' => 'approval',   'label' => 'งานอนุมัติ',          'url' => Url::to(['/mobile/default/approvals'])],
    ['icon' => 'qr-code',         'cat' => 'asset',      'label' => 'ตรวจสอบครุภัณฑ์',    'url' => Url::to(['/mobile/default/scan'])],
    ['icon' => 'alert-circle',    'cat' => 'issue',      'label' => 'แจ้งปัญหาระบบ',       'url' => '#', 'comingSoon' => true],
];
if ($canManageMeeting) {
    array_unshift($secondary, [
        'icon' => 'calendar-check',
        'cat' => 'meeting',
        'label' => 'จัดห้องประชุม',
        'desc' => 'ดูคำขอจอง อนุมัติ หรือยกเลิก',
        'url' => Url::to(['/mobile/default/room-manage']),
        'badge' => $managedMeetingPendingCount > 0 ? $managedMeetingPendingCount : null,
    ]);
}
if ($isDriver) {
    array_unshift($secondary, [
        'icon' => 'route',
        'cat' => 'vehicle',
        'label' => 'ภารกิจขับรถ',
        'desc' => 'บันทึกเวลา เลขไมล์ และผลการเดินทาง',
        'url' => Url::to(['/mobile/default/driver-missions']),
        'badge' => $driverMissionCount > 0 ? $driverMissionCount : null,
    ]);
}
?>
<style>
/* Services page: hero shell + touch-first action cards. */
.services-app-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + var(--space-xl));
}
.svc-page {
    display: flex;
    flex-direction: column;
    gap: var(--space-xl);
}
.svc-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.svc-section-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: var(--space-md);
    padding: 0 var(--space-2xs);
}
.svc-section-label {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    min-width: 0;
}
.svc-section-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--mobile-primary);
    background: var(--mobile-primary-soft);
    border: 1px solid var(--mobile-primary-soft-border);
}
.svc-section-icon svg {
    width: 1rem;
    height: 1rem;
}
.svc-heading-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
    line-height: 1.2;
    text-wrap: balance;
}
.svc-section-subtitle {
    margin: 2px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.4;
}
.svc-section-count {
    flex-shrink: 0;
    padding: var(--space-2xs) var(--space-xs);
    border-radius: 999px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
}
.svc-feature-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-sm);
}
.svc-feature {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    text-decoration: none;
    color: inherit;
    background: var(--surface);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    padding: var(--space-md);
    min-height: 8.75rem;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    -webkit-tap-highlight-color: transparent;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1),
        background 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.svc-feature-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.svc-medallion {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
}
.svc-feature-arrow,
.svc-row-chevron {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--ink-4);
    background: var(--surface-2);
    border-radius: 999px;
}
.svc-feature-arrow {
    width: 1.875rem;
    height: 1.875rem;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        color 180ms cubic-bezier(0.16, 1, 0.3, 1),
        background 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.svc-feature-copy {
    display: flex;
    flex-direction: column;
    gap: var(--space-2xs);
    min-width: 0;
}
.svc-feature-name {
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.28;
    text-wrap: pretty;
}
.svc-feature-desc {
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.38;
    margin: 0;
    text-wrap: pretty;
}
.svc-list {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    box-shadow: var(--shadow-md);
}
.svc-row-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    min-height: 3.75rem;
    padding: var(--space-sm) var(--space-md);
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid var(--ink-line);
    -webkit-tap-highlight-color: transparent;
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.svc-row-item:last-child {
    border-bottom: 0;
}
.svc-medallion-sm {
    width: 2.375rem;
    height: 2.375rem;
    border-radius: 12px;
}
.svc-row-copy {
    flex: 1 1 auto;
    min-width: 0;
}
.svc-row-title {
    display: block;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 600;
    line-height: 1.25;
    overflow-wrap: anywhere;
}
.svc-row-desc {
    display: block;
    margin-top: 2px;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 500;
    line-height: 1.35;
}
.svc-row-badge {
    min-width: 1.5rem;
    height: 1.5rem;
    padding: 0 var(--space-2xs);
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--danger-soft);
    color: var(--danger-strong);
    font-size: var(--fs-2xs);
    font-weight: 700;
    line-height: 1;
}
.svc-row-chevron {
    width: 1.625rem;
    height: 1.625rem;
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        color 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.svc-feature:focus-visible,
.svc-row-item:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.svc-feature:active {
    transform: translateY(1px) scale(0.985);
    box-shadow: var(--shadow-sm);
}
.svc-row-item:active {
    transform: scale(0.992);
    background: var(--surface-2);
}
@media (hover: hover) {
    .svc-feature:hover {
        color: inherit;
        transform: translateY(-2px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-lg);
    }
    .svc-feature:hover .svc-feature-arrow {
        color: var(--mobile-primary);
        background: var(--mobile-primary-soft);
        transform: translateX(2px);
    }
    .svc-row-item:hover {
        color: inherit;
        background: var(--surface-2);
    }
    .svc-row-item:hover .svc-row-chevron {
        color: var(--mobile-primary);
        transform: translateX(2px);
    }
}
@media (prefers-reduced-motion: no-preference) {
    .svc-feature {
        animation: svc-card-settle 260ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
        animation-delay: calc(var(--svc-i, 0) * 32ms);
    }
    .svc-row-item {
        animation: svc-row-settle 220ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
        animation-delay: calc(var(--svc-i, 0) * 22ms);
    }
}
@keyframes svc-card-settle {
    from {
        transform: translateY(4px) scale(0.995);
        box-shadow: var(--shadow-sm);
    }
    to {
        transform: translateY(0) scale(1);
        box-shadow: var(--shadow-md);
    }
}
@keyframes svc-row-settle {
    from { transform: translateY(3px); }
    to { transform: translateY(0); }
}
@media (max-width: 359.98px) {
    .svc-feature-grid {
        grid-template-columns: 1fr;
    }
    .svc-feature {
        min-height: 0;
    }
}
@media (prefers-reduced-motion: reduce) {
    .svc-feature,
    .svc-row-item {
        animation: none !important;
        transform: none !important;
    }
}
</style>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => 'layout-grid',
    'title'    => $this->params['mobileTitle'],
    'subtitle' => $this->params['mobileSubtitle'],
]) ?>

<div class="app-scroll services-app-scroll">
<div class="svc-page">
    <!-- Primary 2×2 grid: high-frequency services -->
    <section class="svc-section" aria-labelledby="svc-primary-title">
        <div class="svc-section-head">
            <div class="svc-section-label">
                <span class="svc-section-icon" aria-hidden="true"><i data-lucide="star"></i></span>
                <div class="min-w-0">
                    <h2 id="svc-primary-title" class="svc-heading-title">บริการหลัก</h2>
                    <p class="svc-section-subtitle">งานที่ใช้บ่อย เริ่มรายการได้ทันที</p>
                </div>
            </div>
            <span class="svc-section-count"><?= count($featured) ?> รายการ</span>
        </div>
        <div class="svc-feature-grid" aria-label="บริการหลัก">
            <?php foreach ($featured as $i => $s): ?>
                <a href="<?= Html::encode($s['url']) ?>" class="svc-feature" style="--svc-i: <?= (int) $i ?>">
                    <span class="svc-feature-top">
                        <span class="cat-medallion svc-medallion cat-<?= Html::encode($s['cat']) ?>" aria-hidden="true">
                            <i data-lucide="<?= Html::encode($s['icon']) ?>" class="mi-md"></i>
                        </span>
                        <span class="svc-feature-arrow" aria-hidden="true">
                            <i data-lucide="chevron-right" class="mi-xs"></i>
                        </span>
                    </span>
                    <span class="svc-feature-copy">
                        <span class="svc-feature-name"><?= Html::encode($s['label']) ?></span>
                        <p class="svc-feature-desc"><?= Html::encode($s['desc']) ?></p>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Secondary compact row list -->
    <section class="svc-section" aria-labelledby="svc-secondary-title">
        <div class="svc-section-head">
            <div class="svc-section-label">
                <span class="svc-section-icon" aria-hidden="true"><i data-lucide="list-checks"></i></span>
                <div class="min-w-0">
                    <h2 id="svc-secondary-title" class="svc-heading-title">บริการอื่น</h2>
                    <p class="svc-section-subtitle">รายการเสริม งานตรวจสอบ และเมนูตามสิทธิ์</p>
                </div>
            </div>
            <span class="svc-section-count"><?= count($secondary) ?> รายการ</span>
        </div>
        <div class="svc-list" aria-label="บริการอื่น">
            <?php foreach ($secondary as $i => $s): ?>
                <a href="<?= Html::encode($s['url']) ?>" class="svc-row-item<?= !empty($s['comingSoon']) ? ' is-coming-soon' : '' ?>" style="--svc-i: <?= (int) $i ?>"<?= !empty($s['comingSoon']) ? ' data-coming-soon="1"' : '' ?>>
                    <span class="cat-medallion svc-medallion-sm cat-<?= Html::encode($s['cat']) ?>" aria-hidden="true">
                        <i data-lucide="<?= Html::encode($s['icon']) ?>" class="mi-sm"></i>
                    </span>
                    <span class="svc-row-copy">
                        <span class="svc-row-title"><?= Html::encode($s['label']) ?></span>
                        <?php if (!empty($s['desc'])): ?>
                            <span class="svc-row-desc"><?= Html::encode($s['desc']) ?></span>
                        <?php endif; ?>
                    </span>
                    <?php if (!empty($s['badge'])): ?>
                        <span class="svc-row-badge" aria-label="<?= (int) $s['badge'] ?> รายการรอดำเนินการ"><?= (int) $s['badge'] ?></span>
                    <?php endif; ?>
                    <span class="svc-row-chevron" aria-hidden="true">
                        <i data-lucide="chevron-right" class="mi-xs"></i>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>
</div>

<?php
$this->registerJs(<<<'JS'
$(document).on('click', '.svc-row-item.is-coming-soon', function (e) {
    e.preventDefault();
    Swal.fire({
        icon: 'info',
        title: 'อยู่ระหว่างพัฒนา',
        text: 'เมนูนี้กำลังอยู่ระหว่างพัฒนา จะเปิดให้ใช้งานเร็วๆ นี้',
        confirmButtonText: 'รับทราบ',
        confirmButtonColor: 'var(--mobile-primary)',
    });
});
JS, \yii\web\View::POS_END);
?>
