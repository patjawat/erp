<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $pendingLeaveApprovals */
/** @var app\modules\leave\models\Leave[] $recentLeaveRequests */
/** @var app\modules\booking\models\Meeting[] $recentMeetings */
$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'บริการออนไลน์';
$this->params['mobileSubtitle'] = 'หน้าหลัก';

$userName = Yii::$app->user->isGuest ? 'ผู้ใช้' : (Yii::$app->user->identity->username ?? 'ผู้ใช้');
$avatarUrl = null;
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    try {
        $avatarUrl = Yii::$app->user->identity->employee->ShowAvatar();
    } catch (\Throwable $e) {
        $avatarUrl = null;
    }
}

$pendingLeaveApprovals = $pendingLeaveApprovals ?? [];
$recentLeaveRequests = $recentLeaveRequests ?? [];
$recentMeetings = $recentMeetings ?? [];
$officialDocumentsPreview = $officialDocumentsPreview ?? [];
$officialUnreadCount = (int) ($officialUnreadCount ?? 0);

$formatDateTimeText = static function ($datetime): string {
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime((string) $datetime);
    if (!$timestamp) {
        return '-';
    }

    return ThaiDateHelper::formatThaiDate(date('Y-m-d', $timestamp)) . ' ' . date('H:i', $timestamp) . ' น.';
};

$leaveStatusMeta = static function ($leave): array {
    $statusCode = (string) ($leave->status ?? '');
    $statusTitle = $leave->leaveStatus->title ?? $statusCode ?: 'รอดำเนินการ';

    if (in_array($statusCode, ['Approve', 'Pass'], true)) {
        return ['icon' => 'check-circle', 'color' => 'success', 'label' => $statusTitle];
    }
    if (in_array($statusCode, ['Reject', 'Cancel', 'ReqCancel'], true)) {
        return ['icon' => 'x-circle', 'color' => 'danger', 'label' => $statusTitle];
    }

    return ['icon' => 'clock', 'color' => 'warning', 'label' => $statusTitle];
};

$homeNotifications = [];
foreach ($pendingLeaveApprovals as $approve) {
    $leave = $approve->leave;
    if (!$leave) {
        continue;
    }

    $requesterName = $leave->employee->fullname ?? '-';
    $leaveType = $leave->leaveType->title ?? 'ใบลา';
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $timestamp = strtotime((string) ($approve->created_at ?: $leave->created_at)) ?: 0;

    $homeNotifications[] = [
        'url' => Url::to(['/mobile/default/approve-leave', 'id' => $approve->id]),
        'icon' => 'clock',
        'iconColor' => 'warning',
        'title' => 'มีใบลารออนุมัติจากคุณ',
        'desc' => trim($requesterName . ' · ' . $leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time' => $formatDateTimeText($approve->created_at ?: $leave->created_at),
        'sortTs' => $timestamp,
    ];
}

foreach ($recentLeaveRequests as $leave) {
    $meta = $leaveStatusMeta($leave);
    $leaveType = $leave->leaveType->title ?? 'ใบลา';
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $timestamp = strtotime((string) ($leave->updated_at ?: $leave->created_at)) ?: 0;

    $homeNotifications[] = [
        'url' => Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]),
        'icon' => $meta['icon'],
        'iconColor' => $meta['color'],
        'title' => 'สถานะใบลาของคุณ: ' . $meta['label'],
        'desc' => trim($leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time' => $formatDateTimeText($leave->updated_at ?: $leave->created_at),
        'sortTs' => $timestamp,
    ];
}

usort($homeNotifications, static function (array $a, array $b) {
    return ($b['sortTs'] ?? 0) <=> ($a['sortTs'] ?? 0);
});
$homeNotifications = array_slice($homeNotifications, 0, 4);

$recentRequestItems = [];
foreach ($recentLeaveRequests as $leave) {
    $meta = $leaveStatusMeta($leave);
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $recentRequestItems[] = [
        'url' => Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]),
        'icon' => 'calendar-off',
        'title' => $leave->leaveType->title ?? 'คำขอลา',
        'desc' => $dateRange !== '' ? $dateRange : '-',
        'badgeClass' => $meta['color'],
        'badgeText' => $meta['label'],
        'sortTs' => strtotime((string) ($leave->created_at ?: $leave->updated_at)) ?: 0,
    ];
}

foreach ($recentMeetings as $meeting) {
    try {
        $statusInfo = $meeting->getStatus($meeting->status);
        $statusTitle = $statusInfo['title'] ?? $meeting->status;
        $badgeClass = $statusInfo['color'] ?? 'secondary';
    } catch (\Throwable $e) {
        $statusTitle = $meeting->status ?: '-';
        $badgeClass = 'secondary';
    }

    $meetingDate = !empty($meeting->date_start) ? ThaiDateHelper::formatThaiDate((string) $meeting->date_start) : '-';
    $recentRequestItems[] = [
        'url' => Url::to(['/mobile/default/meeting-view', 'id' => $meeting->id]),
        'icon' => 'calendar',
        'title' => $meeting->title ?: $meeting->code,
        'desc' => trim(($meeting->room ? $meeting->room->title : $meeting->room_id) . ' · ' . $meetingDate),
        'badgeClass' => $badgeClass,
        'badgeText' => $statusTitle,
        'sortTs' => strtotime((string) ($meeting->created_at ?: $meeting->date_start)) ?: 0,
    ];
}

usort($recentRequestItems, static function (array $a, array $b) {
    return ($b['sortTs'] ?? 0) <=> ($a['sortTs'] ?? 0);
});
$recentRequestItems = array_slice($recentRequestItems, 0, 4);
?>
<style>
/* ── Greeting hero — drenched soft gradient + radial accent ──────────────── */
.home-greeting-card {
    position: relative;
    overflow: hidden;
    padding: var(--space-lg) var(--space-md);
    background:
        radial-gradient(120% 100% at 100% 0%, oklch(0.93 0.06 250) 0%, transparent 55%),
        radial-gradient(140% 100% at 0% 100%, oklch(0.95 0.05 280) 0%, transparent 60%),
        linear-gradient(135deg, #fff 0%, oklch(0.98 0.02 250) 100%);
    border-radius: 22px;
    box-shadow: 0 4px 24px rgba(13, 110, 253, 0.08);
}
.home-greeting-card::after {
    content: '';
    position: absolute; top: -40px; right: -30px;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(13, 110, 253, 0.18) 0%, transparent 65%);
    pointer-events: none; z-index: 0;
}
.home-greeting-card .card-body {
    position: relative; z-index: 1;
    padding: 0;
    display: flex; align-items: center; gap: var(--space-md);
}
.home-greeting-avatar {
    width: 3.75rem; height: 3.75rem;
    border-radius: 50%; overflow: hidden; flex-shrink: 0;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.18), inset 0 0 0 2px rgba(255, 255, 255, 0.9);
}
.home-greeting-avatar img { width: 100%; height: 100%; object-fit: cover; }
.home-greeting-avatar i { color: var(--mobile-primary); }
.home-greet-eyebrow {
    font-size: var(--fs-xs); color: var(--mobile-primary-dark); margin: 0;
    font-weight: 600; letter-spacing: 0.02em;
}
.home-greet-name {
    font-size: var(--fs-2xl); font-weight: 800; color: #0f172a;
    margin: 2px 0 0; line-height: 1.15;
    letter-spacing: -0.02em; text-wrap: balance;
}
.home-greet-date {
    font-size: var(--fs-xs); color: #475569; margin: 6px 0 0;
    font-weight: 500;
}

/* ── Modern section headline — replaces .section-title ──────────────────── */
.m-headline {
    display: flex; align-items: flex-end; justify-content: space-between;
    gap: var(--space-md); padding: 0 var(--space-2xs);
    margin: 0 0 var(--space-sm);
}
.m-headline-title {
    font-size: var(--fs-lg); font-weight: 700; color: #0f172a;
    margin: 0; line-height: 1.2; letter-spacing: -0.015em;
}
.m-headline-sub {
    font-size: var(--fs-xs); color: #64748b;
    margin: 3px 0 0; font-weight: 500;
}
.m-headline-link {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--fs-sm); font-weight: 600; color: var(--mobile-primary);
    text-decoration: none;
    transition: color 0.15s ease;
}
.m-headline-link svg { width: 14px; height: 14px; transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1); }
.m-headline-link:hover { color: var(--mobile-primary-dark); }
.m-headline-link:hover svg { transform: translateX(3px); }

/* ── Quick action tile — slightly larger, more breathing room ──────────── */
.home-quick-action {
    text-decoration: none; color: inherit;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: var(--space-md) var(--space-xs);
    min-height: 6rem;
    background: #fff;
    transition: transform 0.15s ease, box-shadow 0.2s ease;
    position: relative; overflow: hidden;
}
.home-quick-action::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(120% 100% at 50% 0%, rgba(13, 110, 253, 0.04), transparent 60%);
    pointer-events: none; opacity: 0; transition: opacity 0.2s ease;
}
.home-quick-action:hover { color: inherit; box-shadow: 0 6px 24px rgba(13, 110, 253, 0.1); transform: translateY(-1px); }
.home-quick-action:hover::before { opacity: 1; }
.home-quick-action:active { transform: scale(0.97); }
.home-quick-action .cat-medallion { width: 2.75rem; height: 2.75rem; margin-bottom: var(--space-xs); border-radius: 14px; }
.home-quick-action > span:last-child { font-size: var(--fs-sm); color: #0f172a; font-weight: 600; }

/* ── Status row item — colored medallion + 2-line content + chevron ─── */
.m-row {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) 0;
    text-decoration: none; color: inherit;
    border-bottom: 1px solid rgba(15, 23, 42, 0.05);
    transition: background 0.15s ease;
}
.m-row:last-child { border-bottom: 0; }
.m-row:hover { color: inherit; background: rgba(13, 110, 253, 0.02); }
.m-row-medal {
    width: 2.5rem; height: 2.5rem; border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.m-row-medal.is-warning { background: rgba(255, 159, 28, 0.13); color: #b54708; }
.m-row-medal.is-success { background: rgba(34, 197, 94, 0.13);  color: #15803d; }
.m-row-medal.is-danger  { background: rgba(239, 68, 68, 0.13);  color: #b91c1c; }
.m-row-medal.is-primary { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.m-row-medal.is-secondary { background: rgba(100, 116, 139, 0.13); color: #475569; }
.m-row-medal svg { width: 1.125rem; height: 1.125rem; }
.m-row-body { flex-grow: 1; min-width: 0; }
.m-row-title { font-size: var(--fs-sm); font-weight: 600; color: #0f172a; margin: 0; line-height: 1.3; }
.m-row-desc  { font-size: var(--fs-xs); color: #475569; margin: 2px 0 0; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.m-row-meta  { font-size: var(--fs-2xs); color: #94a3b8; margin: 3px 0 0; }
.m-row-chevron { color: #cbd5e1; flex-shrink: 0; width: 18px; height: 18px; }
.m-row-pill {
    flex-shrink: 0;
    font-size: var(--fs-2xs); font-weight: 600;
    padding: 4px 10px; border-radius: 999px; letter-spacing: 0.01em;
}
.m-row-pill.is-warning { background: rgba(255, 159, 28, 0.13); color: #b54708; }
.m-row-pill.is-success { background: rgba(34, 197, 94, 0.13);  color: #15803d; }
.m-row-pill.is-danger  { background: rgba(239, 68, 68, 0.13);  color: #b91c1c; }
.m-row-pill.is-primary { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.m-row-pill.is-secondary { background: rgba(100, 116, 139, 0.13); color: #475569; }

/* ── Motion choreography ─────────────────────────────────────────────── */
@keyframes m-enter {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes m-pulse-amber {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 159, 28, 0.45); }
    70%      { box-shadow: 0 0 0 7px rgba(255, 159, 28, 0); }
}
@keyframes m-hero-glow {
    0%, 100% { opacity: 0.7; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.08); }
}

/* Section-level stagger entrance — applied to direct children of .mobile-stack.
   Uses backwards fill so content starts hidden via the keyframes' "from" state,
   guaranteeing the reveal fires even when DOM transitions would pause. */
.mobile-stack > * {
    animation: m-enter 420ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 75ms);
}

/* Hero radial accent breathes — subtle ambient pulse, only on the corner glow.
   Long duration + low contrast keeps it ambient, not attention-grabbing. */
.home-greeting-card::after {
    animation: m-hero-glow 6s ease-in-out infinite;
}

/* Pending status (warm amber medallion) pulses — conveys "this needs action". */
.m-row-medal.is-warning {
    animation: m-pulse-amber 2.2s ease-in-out infinite;
}

/* Snappier hover/active curves on existing micro-interactions */
.home-quick-action { transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 220ms cubic-bezier(0.16, 1, 0.3, 1); }
.home-quick-action::before { transition: opacity 200ms cubic-bezier(0.16, 1, 0.3, 1); }
.m-row { transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1); }
.m-headline-link { transition: color 160ms cubic-bezier(0.16, 1, 0.3, 1); }
.m-headline-link svg { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }

/* ── Reduced motion: kill all entrance and ambient pulses ───────────── */
@media (prefers-reduced-motion: reduce) {
    .mobile-stack > *,
    .home-greeting-card::after,
    .m-row-medal.is-warning {
        animation: none !important;
    }
    .home-quick-action,
    .home-quick-action::before,
    .m-headline-link svg,
    .m-row {
        transition: none !important;
    }
    .home-quick-action:hover { transform: none; }
}
</style>

<?php
$todayThai = \app\components\ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$hour = (int) date('G');
$greetingWord = $hour < 12 ? 'อรุณสวัสดิ์' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
?>
<div class="mobile-stack">
    <!-- Greeting hero -->
    <div class="card home-greeting-card" style="--i: 0">
        <div class="card-body">
            <div class="home-greeting-avatar">
                <?php if ($avatarUrl): ?>
                    <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="52" height="52">
                <?php else: ?>
                    <i data-lucide="user" class="mi-lg"></i>
                <?php endif; ?>
            </div>
            <div class="min-w-0 flex-grow-1">
                <p class="home-greet-eyebrow"><?= Html::encode($greetingWord) ?></p>
                <h2 class="home-greet-name text-truncate"><?= \yii\bootstrap5\Html::encode($userName) ?></h2>
                <p class="home-greet-date"><?= Html::encode($todayThai) ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <section style="--i: 1">
        <header class="m-headline">
            <div>
                <h3 class="m-headline-title">บริการด่วน</h3>
                <p class="m-headline-sub">ใช้บ่อย เปิดถึงได้ในแตะเดียว</p>
            </div>
        </header>
        <div class="row g-2">
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-vehicle'])) ?>" class="card home-quick-action">
                    <span class="cat-medallion cat-vehicle" aria-hidden="true"><i data-lucide="car" class="mi-md"></i></span>
                    <span class="fw-semibold small">จองรถ</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-meeting'])) ?>" class="card home-quick-action">
                    <span class="cat-medallion cat-meeting" aria-hidden="true"><i data-lucide="calendar" class="mi-md"></i></span>
                    <span class="fw-semibold small">จองห้องประชุม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request'])) ?>" class="card home-quick-action">
                    <span class="cat-medallion cat-maintenance" aria-hidden="true"><i data-lucide="wrench" class="mi-md"></i></span>
                    <span class="fw-semibold small">แจ้งซ่อม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/leave-request'])) ?>" class="card home-quick-action">
                    <span class="cat-medallion cat-leave" aria-hidden="true"><i data-lucide="calendar-off" class="mi-md"></i></span>
                    <span class="fw-semibold small">ขอลา</span>
                </a>
            </div>
            <div class="col-12">
                <a href="<?= Html::encode(Url::to(['/mobile/default/attendance'])) ?>" class="card home-quick-action flex-row justify-content-center gap-2 py-3">
                    <span class="cat-medallion cat-attendance mb-0" aria-hidden="true"><i data-lucide="clock" class="mi-md"></i></span>
                    <span class="fw-semibold small">ลงเวลาเข้า-ออกงาน</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Notifications -->
    <section style="--i: 2">
        <header class="m-headline">
            <div>
                <h3 class="m-headline-title">การแจ้งเตือน</h3>
                <?php $notifCount = count($homeNotifications); ?>
                <p class="m-headline-sub">
                    <?= $notifCount > 0 ? Html::encode($notifCount) . ' รายการล่าสุด' : 'ยังไม่มีรายการใหม่' ?>
                </p>
            </div>
            <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="m-headline-link">
                ดูทั้งหมด <i data-lucide="arrow-right"></i>
            </a>
        </header>
        <div class="card mobile-card">
            <div class="card-body">
                <?php if (empty($homeNotifications)): ?>
                    <div class="text-center text-muted py-3 small">ยังไม่มีการแจ้งเตือนล่าสุด</div>
                <?php else: ?>
                    <?php foreach ($homeNotifications as $item): ?>
                        <a href="<?= Html::encode($item['url']) ?>" class="m-row">
                            <span class="m-row-medal is-<?= Html::encode($item['iconColor']) ?>" aria-hidden="true">
                                <i data-lucide="<?= Html::encode($item['icon']) ?>"></i>
                            </span>
                            <div class="m-row-body">
                                <p class="m-row-title"><?= Html::encode($item['title']) ?></p>
                                <p class="m-row-desc"><?= Html::encode($item['desc']) ?></p>
                                <p class="m-row-meta"><?= Html::encode($item['time']) ?></p>
                            </div>
                            <i data-lucide="chevron-right" class="m-row-chevron" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Official documents -->
    <section style="--i: 3">
        <header class="m-headline">
            <div>
                <h3 class="m-headline-title">หนังสือราชการ</h3>
                <p class="m-headline-sub">
                    <?= $officialUnreadCount > 0
                        ? 'ยังไม่อ่าน ' . Html::encode((string) $officialUnreadCount) . ' ฉบับ'
                        : 'อ่านครบแล้ว' ?>
                </p>
            </div>
            <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="m-headline-link">
                ดูทั้งหมด <i data-lucide="arrow-right"></i>
            </a>
        </header>
        <div class="card mobile-card">
            <div class="card-body">
                <?php if (empty($officialDocumentsPreview)): ?>
                    <div class="text-center py-3">
                        <i data-lucide="mail-check" class="d-block mx-auto mb-2 text-success mi-xl"></i>
                        <p class="fw-semibold text-dark mb-1" style="font-size: var(--fs-md);">คุณอ่านหนังสือราชการครบแล้ว</p>
                        <p class="small text-body-secondary mb-0">ไม่มีหนังสือฉบับใหม่ที่ยังไม่ได้อ่านในขณะนี้</p>
                    </div>
                <?php else: ?>
                    <?= $this->render('_official_documents_cards', [
                        'documents' => $officialDocumentsPreview,
                        'compact' => true,
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Recent Activity / Recent requests -->
    <section style="--i: 4">
        <header class="m-headline">
            <div>
                <h3 class="m-headline-title">คำขอล่าสุด</h3>
                <p class="m-headline-sub">
                    <?= !empty($recentRequestItems)
                        ? Html::encode((string) count($recentRequestItems)) . ' รายการ'
                        : 'ยังไม่มีรายการ' ?>
                </p>
            </div>
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests'])) ?>" class="m-headline-link">
                ดูทั้งหมด <i data-lucide="arrow-right"></i>
            </a>
        </header>
        <div class="card mobile-card">
            <div class="card-body">
            <?php if (empty($recentRequestItems)): ?>
                <div class="text-center py-3">
                    <i data-lucide="inbox" class="d-block mx-auto mb-2 text-body-secondary mi-xl"></i>
                    <p class="small text-body-secondary mb-0">ยังไม่มีคำขอล่าสุดในระบบมือถือ</p>
                </div>
            <?php else: ?>
                <?php
                // map status colour → row medallion accent
                $badgeToMedal = [
                    'success'   => 'success',
                    'warning'   => 'warning',
                    'danger'    => 'danger',
                    'info'      => 'primary',
                    'primary'   => 'primary',
                    'secondary' => 'secondary',
                ];
                ?>
                <?php foreach ($recentRequestItems as $item): ?>
                    <?php $medalClass = $badgeToMedal[$item['badgeClass']] ?? 'primary'; ?>
                    <a href="<?= Html::encode($item['url']) ?>" class="m-row">
                        <span class="m-row-medal is-<?= Html::encode($medalClass) ?>" aria-hidden="true">
                            <i data-lucide="<?= Html::encode($item['icon']) ?>"></i>
                        </span>
                        <div class="m-row-body">
                            <p class="m-row-title"><?= Html::encode($item['title']) ?></p>
                            <p class="m-row-desc"><?= Html::encode($item['desc']) ?></p>
                        </div>
                        <span class="m-row-pill is-<?= Html::encode($medalClass) ?>"><?= Html::encode($item['badgeText']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php
// Telegram MiniApp auto-login lives in web/js/mobile-shared.js, gated by
// <body data-mobile-page="home"> (set in modules/mobile/views/layouts/main.php).
?>
