<?php
/** @var yii\web\View $this */
/** @var string $current_page */
/** @var bool $canManageMeeting */

use yii\helpers\Html;
use yii\helpers\Url;

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'บริการ';
$this->params['mobileSubtitle'] = 'เลือกบริการที่ต้องการใช้งาน';

// Admin tools — gated by RBAC permission. Pass `canManageMeeting` falls back
// to the live auth check so the view also works if rendered out-of-band.
$canManageMeeting = (bool) ($canManageMeeting ?? Yii::$app->user->can('meeting'));

$adminTools = [];
if ($canManageMeeting) {
    $adminTools[] = [
        'icon'  => 'calendar-check',
        'cat'   => 'meeting',
        'label' => 'จัดการห้องประชุม',
        'desc'  => 'ดูคำขอจอง อนุมัติ หรือยกเลิก',
        'url'   => Url::to(['/mobile/default/room-manage']),
    ];
}

/**
 * Featured = primary services with their own large medallion card.
 * Secondary = lower-frequency or admin-flavoured services in a compact row list.
 * Categories drive icon colour via the cat-medallion system in _head.php.
 */
$featured = [
    ['icon' => 'car',         'cat' => 'vehicle',     'label' => 'จองรถราชการ',  'desc' => 'จองรถสำหรับเดินทางราชการ',  'url' => Url::to(['/mobile/default/booking-vehicle'])],
    ['icon' => 'calendar',    'cat' => 'meeting',     'label' => 'จองห้องประชุม', 'desc' => 'ตรวจสอบและจองห้องประชุม',   'url' => Url::to(['/mobile/default/booking-meeting'])],
    ['icon' => 'wrench',      'cat' => 'maintenance', 'label' => 'แจ้งซ่อม',       'desc' => 'แจ้งซ่อมอุปกรณ์หรือสถานที่', 'url' => Url::to(['/mobile/default/maintenance-request'])],
    ['icon' => 'user-check',  'cat' => 'leave',       'label' => 'ขอลาออนไลน์',   'desc' => 'ส่งคำขอลาประเภทต่างๆ',     'url' => Url::to(['/mobile/default/leave-request'])],
];

$secondary = [
    ['icon' => 'clock',           'cat' => 'attendance', 'label' => 'ลงเวลาเข้า-ออกงาน',  'url' => Url::to(['/mobile/default/attendance'])],
    ['icon' => 'clipboard-check', 'cat' => 'approval',   'label' => 'อนุมัติใบลา',         'url' => Url::to(['/mobile/default/leave-approvals'])],
    ['icon' => 'qr-code',         'cat' => 'asset',      'label' => 'ตรวจสอบครุภัณฑ์',    'url' => Url::to(['/mobile/default/scan'])],
    ['icon' => 'package',         'cat' => 'supply',     'label' => 'ขอเบิกพัสดุ',          'url' => '#'],
    ['icon' => 'file-text',       'cat' => 'document',   'label' => 'เอกสารราชการ',       'url' => '#'],
    ['icon' => 'alert-circle',    'cat' => 'issue',      'label' => 'แจ้งปัญหาระบบ',       'url' => '#'],
];
?>
<style>
/* Featured service card — larger, two-line layout (icon + title + desc) */
.svc-feature {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-xs);
    text-decoration: none;
    color: inherit;
    background: #fff;
    padding: var(--space-md);
    min-height: 7.5rem;
    transition: box-shadow 0.2s ease, transform 0.15s ease;
}
.svc-feature:hover { color: inherit; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
.svc-feature:active { transform: scale(0.98); }
.svc-feature .cat-medallion { width: 2.5rem; height: 2.5rem; border-radius: 12px; }
.svc-feature .svc-feature-name { font-size: var(--fs-md); font-weight: 600; color: #1a1f2c; line-height: 1.25; margin-top: var(--space-2xs); }
.svc-feature .svc-feature-desc { font-size: var(--fs-xs); color: #6c757d; line-height: 1.35; margin: 0; }
</style>

<div class="mobile-stack">
    <!-- Primary 2×2 grid: high-frequency services -->
    <section>
        <h3 class="section-title">
            <i data-lucide="star"></i>
            บริการหลัก
        </h3>
        <div class="row g-2">
            <?php foreach ($featured as $s): ?>
                <div class="col-6">
                    <a href="<?= Html::encode($s['url']) ?>" class="card svc-feature">
                        <span class="cat-medallion cat-<?= Html::encode($s['cat']) ?>" aria-hidden="true">
                            <i data-lucide="<?= Html::encode($s['icon']) ?>" class="mi-md"></i>
                        </span>
                        <span class="svc-feature-name"><?= Html::encode($s['label']) ?></span>
                        <p class="svc-feature-desc"><?= Html::encode($s['desc']) ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Secondary compact row list -->
    <section>
        <h3 class="section-title">
            <i data-lucide="layout-grid"></i>
            บริการอื่น
        </h3>
        <div class="row-list">
            <?php foreach ($secondary as $s): ?>
                <a href="<?= Html::encode($s['url']) ?>" class="row-item">
                    <span class="cat-medallion cat-<?= Html::encode($s['cat']) ?>" aria-hidden="true" style="width: 2.25rem; height: 2.25rem; border-radius: 10px;">
                        <i data-lucide="<?= Html::encode($s['icon']) ?>" class="mi-sm"></i>
                    </span>
                    <span class="row-title"><?= Html::encode($s['label']) ?></span>
                    <i data-lucide="chevron-right" class="row-chevron mi-sm" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($adminTools)): ?>
    <!-- Admin tools — visible only when user manages at least one resource -->
    <section aria-label="เครื่องมือสำหรับผู้ดูแล">
        <h3 class="section-title">
            <i data-lucide="shield-check"></i>
            เครื่องมือผู้ดูแล
        </h3>
        <div class="row-list">
            <?php foreach ($adminTools as $t): ?>
                <a href="<?= Html::encode($t['url']) ?>" class="row-item">
                    <span class="cat-medallion cat-<?= Html::encode($t['cat']) ?>" aria-hidden="true" style="width: 2.25rem; height: 2.25rem; border-radius: 10px;">
                        <i data-lucide="<?= Html::encode($t['icon']) ?>" class="mi-sm"></i>
                    </span>
                    <span class="row-title">
                        <?= Html::encode($t['label']) ?>
                        <span class="d-block row-desc" style="font-size: var(--fs-xs); color: #64748b; font-weight: 400; margin-top: 2px;"><?= Html::encode($t['desc']) ?></span>
                    </span>
                    <i data-lucide="chevron-right" class="row-chevron mi-sm" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
