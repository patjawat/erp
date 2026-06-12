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

$userName  = Yii::$app->user->isGuest ? 'ผู้ใช้' : (Yii::$app->user->identity->username ?? 'ผู้ใช้');
$fullName  = $userName;
$roleLabel = 'MEMBER';
$avatarUrl = null;
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    $emp = Yii::$app->user->identity->employee;
    try { $avatarUrl = $emp->ShowAvatar(); } catch (\Throwable $e) { $avatarUrl = null; }
    try { if (!empty($emp->fullname)) $fullName = $emp->fullname; } catch (\Throwable $e) {}
    try {
        if (!empty($emp->positionType) && !empty($emp->positionType->title)) {
            $roleLabel = $emp->positionType->title;
        }
    } catch (\Throwable $e) {}
}

$pendingLeaveApprovals    = $pendingLeaveApprovals ?? [];
$recentLeaveRequests      = $recentLeaveRequests ?? [];
$recentMeetings           = $recentMeetings ?? [];
$officialDocumentsPreview = $officialDocumentsPreview ?? [];
$officialUnreadCount      = (int) ($officialUnreadCount ?? 0);

$formatDateTimeText = static function ($datetime): string {
    if (empty($datetime)) return '-';
    $timestamp = strtotime((string) $datetime);
    if (!$timestamp) return '-';
    return ThaiDateHelper::formatThaiDate(date('Y-m-d', $timestamp)) . ' ' . date('H:i', $timestamp) . ' น.';
};

$leaveStatusMeta = static function ($leave): array {
    $statusCode  = (string) ($leave->status ?? '');
    $statusTitle = $leave->leaveStatus->title ?? $statusCode ?: 'รอดำเนินการ';
    if (in_array($statusCode, ['Approve', 'Pass'], true))             return ['icon' => 'check-circle', 'color' => 'success', 'label' => $statusTitle];
    if (in_array($statusCode, ['Reject', 'Cancel', 'ReqCancel'], true)) return ['icon' => 'x-circle',     'color' => 'danger',  'label' => $statusTitle];
    return ['icon' => 'clock', 'color' => 'warning', 'label' => $statusTitle];
};

// ── Notifications (top 4) ──────────────────────────────────────────────
$homeNotifications = [];
foreach ($pendingLeaveApprovals as $approve) {
    $leave = $approve->leave;
    if (!$leave) continue;
    $requesterName = $leave->employee->fullname ?? '-';
    $leaveType     = $leave->leaveType->title ?? 'ใบลา';
    $dateRange     = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $timestamp     = strtotime((string) ($approve->created_at ?: $leave->created_at)) ?: 0;
    $homeNotifications[] = [
        'url'       => Url::to(['/mobile/default/approve-leave', 'id' => $approve->id]),
        'icon'      => 'clock',
        'iconColor' => 'warning',
        'title'     => 'มีใบลารออนุมัติจากคุณ',
        'desc'      => trim($requesterName . ' · ' . $leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time'      => $formatDateTimeText($approve->created_at ?: $leave->created_at),
        'sortTs'    => $timestamp,
    ];
}
foreach ($recentLeaveRequests as $leave) {
    $meta      = $leaveStatusMeta($leave);
    $leaveType = $leave->leaveType->title ?? 'ใบลา';
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $timestamp = strtotime((string) ($leave->updated_at ?: $leave->created_at)) ?: 0;
    $homeNotifications[] = [
        'url'       => Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]),
        'icon'      => $meta['icon'],
        'iconColor' => $meta['color'],
        'title'     => 'สถานะใบลาของคุณ: ' . $meta['label'],
        'desc'      => trim($leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time'      => $formatDateTimeText($leave->updated_at ?: $leave->created_at),
        'sortTs'    => $timestamp,
    ];
}
usort($homeNotifications, static function (array $a, array $b) {
    return ($b['sortTs'] ?? 0) <=> ($a['sortTs'] ?? 0);
});
$homeNotifications = array_slice($homeNotifications, 0, 4);

// ── Recent requests (leaves + meetings, top 4) ────────────────────────
$recentRequestItems = [];
foreach ($recentLeaveRequests as $leave) {
    $meta      = $leaveStatusMeta($leave);
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $recentRequestItems[] = [
        'url'        => Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]),
        'icon'       => 'calendar-off',
        'title'      => $leave->leaveType->title ?? 'คำขอลา',
        'desc'       => $dateRange !== '' ? $dateRange : '-',
        'badgeClass' => $meta['color'],
        'badgeText'  => $meta['label'],
        'sortTs'     => strtotime((string) ($leave->created_at ?: $leave->updated_at)) ?: 0,
    ];
}
foreach ($recentMeetings as $meeting) {
    try {
        $statusInfo  = $meeting->getStatus($meeting->status);
        $statusTitle = $statusInfo['title'] ?? $meeting->status;
        $badgeClass  = $statusInfo['color'] ?? 'secondary';
    } catch (\Throwable $e) {
        $statusTitle = $meeting->status ?: '-';
        $badgeClass  = 'secondary';
    }
    $meetingDate = !empty($meeting->date_start) ? ThaiDateHelper::formatThaiDate((string) $meeting->date_start) : '-';
    $recentRequestItems[] = [
        'url'        => Url::to(['/mobile/default/meeting-view', 'id' => $meeting->id]),
        'icon'       => 'calendar',
        'title'      => $meeting->title ?: $meeting->code,
        'desc'       => trim(($meeting->room ? $meeting->room->title : $meeting->room_id) . ' · ' . $meetingDate),
        'badgeClass' => $badgeClass,
        'badgeText'  => $statusTitle,
        'sortTs'     => strtotime((string) ($meeting->created_at ?: $meeting->date_start)) ?: 0,
    ];
}
usort($recentRequestItems, static function (array $a, array $b) {
    return ($b['sortTs'] ?? 0) <=> ($a['sortTs'] ?? 0);
});
$recentRequestItems = array_slice($recentRequestItems, 0, 4);

// ── Stats (overlay card) ──────────────────────────────────────────────
$pendingCount = count($pendingLeaveApprovals);
$leaveDaysRemaining = '—'; // future: hook to leave-balance source
$recentCount  = count($recentRequestItems);
$notifBadge   = $pendingCount > 0;

$stats = [
    ['icon' => 'check-square',   'label' => 'รออนุมัติ',   'value' => $pendingCount,        'url' => Url::to(['/mobile/default/leave-approvals'])],
    ['icon' => 'calendar-off',   'label' => 'ใบลา',         'value' => $leaveDaysRemaining,  'url' => Url::to(['/mobile/default/leave-request'])],
    ['icon' => 'mail',           'label' => 'หนังสือใหม่',  'value' => $officialUnreadCount, 'url' => Url::to(['/mobile/default/news'])],
    ['icon' => 'history',        'label' => 'คำขอล่าสุด',  'value' => $recentCount,         'url' => Url::to(['/mobile/default/my-requests'])],
];

// ── Quick action tiles (8, grid 4×2) ──────────────────────────────────
$quickServices = [
    ['icon' => 'car',             'cat' => 'vehicle',     'label' => 'จองรถ',           'url' => Url::to(['/mobile/default/booking-vehicle'])],
    ['icon' => 'calendar',        'cat' => 'meeting',     'label' => 'จองห้องประชุม',   'url' => Url::to(['/mobile/default/booking-meeting'])],
    ['icon' => 'wrench',          'cat' => 'maintenance', 'label' => 'แจ้งซ่อม',        'url' => Url::to(['/mobile/default/maintenance-request'])],
    ['icon' => 'calendar-off',    'cat' => 'leave',       'label' => 'ขอลา',            'url' => Url::to(['/mobile/default/leave-request'])],
    ['icon' => 'clock',           'cat' => 'attendance',  'label' => 'ลงเวลา',          'url' => Url::to(['/mobile/default/attendance'])],
    ['icon' => 'qr-code',         'cat' => 'asset',       'label' => 'ครุภัณฑ์',        'url' => Url::to(['/mobile/default/scan'])],
    ['icon' => 'file-text',       'cat' => 'document',    'label' => 'เอกสารราชการ',    'url' => Url::to(['/mobile/default/news']), 'badge' => $officialUnreadCount > 0 ? $officialUnreadCount : null],
    ['icon' => 'clipboard-check', 'cat' => 'approval',    'label' => 'อนุมัติใบลา',     'url' => Url::to(['/mobile/default/leave-approvals']), 'badge' => $pendingCount > 0 ? $pendingCount : null],
];

$todayThai    = ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$hour         = (int) date('G');
$greetingWord = $hour < 12 ? 'อรุณสวัสดิ์' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
?>

<style>
/* ─────────────────────────────────────────────────────────────────────
   Full-bleed escape: this view sits inside .mobile-app-content which has
   horizontal padding 1rem. We use negative margin to break out for the
   blue hero, then re-apply normal spacing for everything below.
   ───────────────────────────────────────────────────────────────────── */
.home-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
    gap: 0;
}

/* ── Hero — drenched blue with bottom-corner curve ──────────────────── */
.home-hero {
    position: relative;
    padding: calc(env(safe-area-inset-top, 0px) + var(--space-md)) var(--space-md) calc(var(--space-2xl) + var(--space-md));
    color: #fff;
    background: linear-gradient(180deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
    border-bottom-left-radius: 32px;
    border-bottom-right-radius: 32px;
    overflow: hidden;
}
.home-hero::before {
    content: ''; position: absolute;
    top: -90px; right: -60px;
    width: 240px; height: 240px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    pointer-events: none;
}
.home-hero::after {
    content: ''; position: absolute;
    bottom: -40px; left: -30px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.06);
    pointer-events: none;
}

.home-hero-bar { display: flex; align-items: center; gap: var(--space-sm); position: relative; z-index: 1; }
.home-hero-user { display: flex; align-items: center; gap: var(--space-sm); flex-grow: 1; min-width: 0; }
.home-hero-avatar {
    width: 3rem; height: 3rem; border-radius: 50%;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), inset 0 0 0 3px rgba(255, 255, 255, 0.95);
}
.home-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
.home-hero-avatar i { color: var(--mobile-primary); }
.home-hero-name {
    font-size: var(--fs-md); font-weight: 700; color: #fff;
    margin: 0; line-height: 1.2; letter-spacing: -0.01em;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.home-hero-role {
    font-size: var(--fs-xs); color: rgba(255, 255, 255, 0.85);
    margin: 2px 0 0; font-weight: 500; letter-spacing: 0.02em;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.home-hero-actions { display: flex; align-items: center; gap: var(--space-xs); flex-shrink: 0; }
.home-hero-btn {
    width: 2.5rem; height: 2.5rem; border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    color: #fff; border: 0;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}
.home-hero-btn:hover, .home-hero-btn:focus { background: rgba(255, 255, 255, 0.28); color: #fff; }
.home-hero-btn.is-alert { background: var(--danger); }
.home-hero-btn.is-alert:hover { background: #dc2626; }
.home-hero-btn .home-hero-dot {
    position: absolute; top: 6px; right: 6px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--danger);
    box-shadow: 0 0 0 2px var(--mobile-primary-dark);
}

.home-hero-greeting {
    margin: var(--space-md) 0 0;
    font-size: var(--fs-xs); color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    position: relative; z-index: 1;
}

/* ── Stats overlay card (sits over hero curve) ───────────────────────── */
.home-stats {
    position: relative; z-index: 2;
    margin: -2.25rem var(--space-md) 0;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 28px rgba(13, 110, 253, 0.12), 0 2px 6px rgba(15, 23, 42, 0.04);
    padding: var(--space-md) var(--space-xs);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}
.home-stat {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px;
    text-decoration: none; color: inherit;
    padding: var(--space-2xs) var(--space-2xs);
    position: relative;
    text-align: center;
}
.home-stat + .home-stat::before {
    content: ''; position: absolute; left: 0; top: 18%; bottom: 18%;
    width: 1px; background: rgba(15, 23, 42, 0.07);
}
.home-stat-icon {
    width: 2.5rem; height: 2.5rem; border-radius: 50%;
    background: var(--warning-soft); color: var(--warning);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.home-stat-icon svg { width: 1.125rem; height: 1.125rem; }
.home-stat:nth-child(1) .home-stat-icon { background: var(--warning-soft); color: var(--warning); }
.home-stat:nth-child(2) .home-stat-icon { background: var(--success-soft);  color: var(--success); }
.home-stat:nth-child(3) .home-stat-icon { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.home-stat:nth-child(4) .home-stat-icon { background: rgba(168, 85, 247, 0.13); color: #7e22ce; }

.home-stat-label { font-size: 0.6875rem; color: var(--ink-3); font-weight: 500; line-height: 1.2; }
.home-stat-value { font-size: var(--fs-lg); font-weight: 800; color: var(--ink); line-height: 1; letter-spacing: -0.02em; }

/* ── Section under hero ──────────────────────────────────────────────── */
.home-below { padding: var(--space-md) var(--space-md) 0; display: flex; flex-direction: column; gap: var(--space-xl); }

/* ── Services grid 4×2 ──────────────────────────────────────────────── */
.home-services-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-sm) var(--space-2xs);
}
.home-service-tile {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px;
    text-decoration: none; color: inherit;
    padding: var(--space-xs) 0;
    position: relative;
    transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}
.home-service-tile:hover { color: inherit; transform: translateY(-1px); }
.home-service-tile:active { transform: scale(0.96); }
.home-service-tile .cat-medallion {
    width: 3.25rem; height: 3.25rem; border-radius: 50%;
    box-shadow: 0 2px 6px var(--ink-line);
}
.home-service-tile .cat-medallion svg { width: 1.5rem; height: 1.5rem; }
.home-service-label {
    font-size: 0.75rem; color: var(--ink); font-weight: 500;
    line-height: 1.25; text-align: center;
    max-width: 100%;
    overflow: hidden; text-overflow: ellipsis;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.home-service-badge {
    position: absolute; top: 0; right: 12%;
    min-width: 1.25rem; height: 1.25rem; padding: 0 5px;
    background: var(--danger); color: #fff;
    border-radius: 999px;
    font-size: 0.6875rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
    border: 2px solid #fff;
}

/* ── Modern section headline ────────────────────────────────────────── */
.m-headline {
    display: flex; align-items: flex-end; justify-content: space-between;
    gap: var(--space-md); padding: 0 var(--space-2xs);
    margin: 0 0 var(--space-sm);
}
.m-headline-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); margin: 0; line-height: 1.2; letter-spacing: -0.015em; }
.m-headline-sub { font-size: var(--fs-xs); color: var(--ink-4); margin: 3px 0 0; font-weight: 500; }
.m-headline-link {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--fs-sm); font-weight: 600; color: var(--mobile-primary);
    text-decoration: none;
    transition: color 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.m-headline-link svg { width: 14px; height: 14px; transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }
.m-headline-link:hover { color: var(--mobile-primary-dark); }
.m-headline-link:hover svg { transform: translateX(3px); }

/* ── Row item (notifications + recent) ──────────────────────────────── */
.m-row {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) 0;
    text-decoration: none; color: inherit;
    border-bottom: 1px solid var(--ink-line);
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.m-row:last-child { border-bottom: 0; }
.m-row:hover { color: inherit; background: rgba(13, 110, 253, 0.02); }
.m-row-medal { width: 2.5rem; height: 2.5rem; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.m-row-medal.is-warning { background: var(--warning-soft); color: var(--warning); }
.m-row-medal.is-success { background: var(--success-soft);  color: var(--success); }
.m-row-medal.is-danger  { background: var(--danger-soft);  color: var(--danger-strong); }
.m-row-medal.is-primary { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.m-row-medal.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }
.m-row-medal svg { width: 1.125rem; height: 1.125rem; }
.m-row-body { flex-grow: 1; min-width: 0; }
.m-row-title { font-size: var(--fs-sm); font-weight: 600; color: var(--ink); margin: 0; line-height: 1.3; }
.m-row-desc  { font-size: var(--fs-xs); color: var(--ink-3); margin: 2px 0 0; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.m-row-meta  { font-size: 0.6875rem; color: var(--ink-5); margin: 3px 0 0; }
.m-row-chevron { color: var(--ink-5); flex-shrink: 0; width: 18px; height: 18px; }
.m-row-pill { flex-shrink: 0; font-size: 0.6875rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
.m-row-pill.is-warning { background: var(--warning-soft); color: var(--warning); }
.m-row-pill.is-success { background: var(--success-soft);  color: var(--success); }
.m-row-pill.is-danger  { background: var(--danger-soft);  color: var(--danger-strong); }
.m-row-pill.is-primary { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.m-row-pill.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

/* ── Motion choreography ────────────────────────────────────────────── */
@keyframes m-enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes m-pulse-amber { 0%, 100% { box-shadow: 0 0 0 0 rgba(255, 159, 28, 0.45); } 70% { box-shadow: 0 0 0 7px rgba(255, 159, 28, 0); } }
@keyframes m-hero-blob   { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-12px, 8px) scale(1.08); } }

.home-root > * {
    animation: m-enter 420ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--i, 0) * 70ms);
}
.home-hero::before { animation: m-hero-blob 8s ease-in-out infinite; }
.home-hero::after  { animation: m-hero-blob 10s ease-in-out infinite reverse; }
.m-row-medal.is-warning { animation: m-pulse-amber 2.2s ease-in-out infinite; }

@media (prefers-reduced-motion: reduce) {
    .home-root > *, .home-hero::before, .home-hero::after, .m-row-medal.is-warning { animation: none !important; }
    .home-service-tile, .m-row, .home-hero-btn, .m-headline-link, .m-headline-link svg { transition: none !important; }
    .home-service-tile:hover { transform: none; }
}

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
#profileDrawer.show .pd-cta,
#profileDrawer.show .pd-menu,
#profileDrawer.show .pd-logout-wrap {
    animation: m-enter 320ms cubic-bezier(0.16, 1, 0.3, 1) backwards;
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

<div class="home-root">

    <!-- ── Hero (drenched blue) ────────────────────────────────────── -->
    <header class="home-hero" style="--i: 0">
        <div class="home-hero-bar">
            <div class="home-hero-user">
                <span class="home-hero-avatar" aria-hidden="true">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="48" height="48">
                    <?php else: ?>
                        <i data-lucide="user" class="mi-lg"></i>
                    <?php endif; ?>
                </span>
                <div class="min-w-0 flex-grow-1">
                    <p class="home-hero-name"><?= Html::encode($fullName) ?></p>
                    <p class="home-hero-role"><?= Html::encode($roleLabel) ?></p>
                </div>
            </div>
            <div class="home-hero-actions">
                <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>"
                   class="home-hero-btn <?= $notifBadge ? 'is-alert' : '' ?>" aria-label="แจ้งเตือน<?= $notifBadge ? ' (มีรายการรอ)' : '' ?>">
                    <i data-lucide="bell" class="mi-sm"></i>
                </a>
                <button type="button" class="home-hero-btn"
                   data-bs-toggle="offcanvas" data-bs-target="#profileDrawer"
                   aria-controls="profileDrawer" aria-label="เปิดเมนูโปรไฟล์">
                    <i data-lucide="menu" class="mi-sm"></i>
                </button>
            </div>
        </div>
        <p class="home-hero-greeting"><?= Html::encode($greetingWord) ?> · <?= Html::encode($todayThai) ?></p>
    </header>

    <!-- ── Stats overlay (overlaps hero curve) ──────────────────────── -->
    <nav class="home-stats" aria-label="สรุปสถานะของคุณ" style="--i: 1">
        <?php foreach ($stats as $s): ?>
            <a href="<?= Html::encode($s['url']) ?>" class="home-stat">
                <span class="home-stat-icon" aria-hidden="true"><i data-lucide="<?= Html::encode($s['icon']) ?>"></i></span>
                <span class="home-stat-label"><?= Html::encode($s['label']) ?></span>
                <span class="home-stat-value"><?= Html::encode((string) $s['value']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- ── Below the fold ──────────────────────────────────────────── -->
    <div class="home-below">

        <!-- บริการด่วน (4×2 grid) -->
        <section style="--i: 2">
            <header class="m-headline">
                <div>
                    <h3 class="m-headline-title">บริการด่วน</h3>
                    <p class="m-headline-sub">ใช้บ่อย เปิดถึงได้ในแตะเดียว</p>
                </div>
                <a href="<?= Html::encode(Url::to(['/mobile/default/services'])) ?>" class="m-headline-link">
                    ทั้งหมด <i data-lucide="arrow-right"></i>
                </a>
            </header>
            <div class="home-services-grid">
                <?php foreach ($quickServices as $svc): ?>
                    <a href="<?= Html::encode($svc['url']) ?>" class="home-service-tile">
                        <span class="cat-medallion cat-<?= Html::encode($svc['cat']) ?>" aria-hidden="true">
                            <i data-lucide="<?= Html::encode($svc['icon']) ?>"></i>
                        </span>
                        <span class="home-service-label"><?= Html::encode($svc['label']) ?></span>
                        <?php if (!empty($svc['badge'])): ?>
                            <span class="home-service-badge"><?= Html::encode((string) $svc['badge']) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- การแจ้งเตือน -->
        <section style="--i: 3">
            <header class="m-headline">
                <div>
                    <h3 class="m-headline-title">การแจ้งเตือน</h3>
                    <?php $notifCount = count($homeNotifications); ?>
                    <p class="m-headline-sub">
                        <?= $notifCount > 0 ? Html::encode((string) $notifCount) . ' รายการล่าสุด' : 'ยังไม่มีรายการใหม่' ?>
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

        <!-- คำขอล่าสุด -->
        <section style="--i: 4">
            <header class="m-headline">
                <div>
                    <h3 class="m-headline-title">คำขอล่าสุด</h3>
                    <p class="m-headline-sub">
                        <?= !empty($recentRequestItems) ? Html::encode((string) count($recentRequestItems)) . ' รายการ' : 'ยังไม่มีรายการ' ?>
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
                        $badgeToMedal = [
                            'success' => 'success', 'warning' => 'warning', 'danger' => 'danger',
                            'info' => 'primary', 'primary' => 'primary', 'secondary' => 'secondary',
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
</div>
<?php
// ── Profile drawer payload ──────────────────────────────────────────
$userEmail      = Yii::$app->user->isGuest ? '' : (Yii::$app->user->identity->email ?? '');
$departmentName = '';
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    try {
        $emp = Yii::$app->user->identity->employee;
        if (method_exists($emp, 'departmentName') && $emp->departmentName()) {
            $departmentName = $emp->departmentName();
        }
    } catch (\Throwable $e) {}
}
$canManageMeeting = Yii::$app->user->can('meeting');

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
        'label' => 'จัดการห้องประชุม',
        'url'   => Url::to(['/mobile/default/room-manage']),
    ];
}
$drawerMenu[] = [
    'icon'  => 'check-square',
    'tone'  => 'warning',
    'label' => 'งานที่ต้องอนุมัติ',
    'url'   => Url::to(['/mobile/default/leave-approvals']),
    'badge' => $pendingCount > 0 ? $pendingCount : null,
];
$drawerMenu[] = [
    'icon'  => 'bell',
    'tone'  => 'amber',
    'label' => 'การแจ้งเตือน',
    'url'   => Url::to(['/mobile/default/notifications']),
];

// Tone → icon background/foreground colours (inline so markup stays scannable)
$toneStyles = [
    'primary' => 'background: var(--mobile-primary-soft); color: var(--mobile-primary);',
    'success' => 'background: var(--success-soft); color: var(--success);',
    'warning' => 'background: var(--warning-soft); color: var(--warning);',
    'meeting' => 'background: rgba(168, 85, 247, 0.13); color: #7e22ce;',
    'amber'   => 'background: rgba(245, 158, 11, 0.13); color: #b45309;',
    'danger'  => 'background: var(--danger-soft); color: var(--danger-strong);',
];
?>

<!-- ── Profile Offcanvas — opens from the hero menu button ─────────────── -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="profileDrawer" aria-labelledby="profileDrawerLabel">

    <!-- Drenched-blue hero -->
    <header class="pd-hero">
        <button type="button" class="pd-close" data-bs-dismiss="offcanvas" aria-label="ปิด">
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
        <?php if ($departmentName): ?>
            <p class="pd-dept"><?= Html::encode($departmentName) ?></p>
        <?php endif; ?>
    </header>

    <div class="pd-body">

        <!-- Open full profile -->
        <a href="<?= Html::encode(Url::to(['/mobile/default/profile'])) ?>" class="pd-cta">
            <span>ดูโปรไฟล์เต็ม<?= $userEmail ? ' · ' . Html::encode($userEmail) : '' ?></span>
            <i data-lucide="arrow-right" aria-hidden="true"></i>
        </a>

        <!-- Menu items -->
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

        <!-- Logout -->
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
// Refresh lucide icons after Bootstrap shows the drawer (icons inside hidden
// offcanvas are sometimes skipped during initial createIcons pass).
document.getElementById('profileDrawer')?.addEventListener('shown.bs.offcanvas', function() {
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
});
JS
, \yii\web\View::POS_READY);
?>

<?php
// Telegram MiniApp auto-login lives in web/js/mobile-shared.js, gated by
// <body data-mobile-page="home"> (set in modules/mobile/views/layouts/main.php).
?>
