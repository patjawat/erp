<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var array $approvalInfo */
/** @var app\modules\approveV2\models\Approve[] $pendingLeaveApprovals */
/** @var app\modules\leave\models\Leave[] $recentLeaveRequests */
/** @var app\modules\booking\models\Meeting[] $recentMeetings */
/** @var bool $canManageMeeting */
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
$canManageMeeting         = (bool) ($canManageMeeting ?? false);
$approvalInfo             = is_array($approvalInfo ?? null) ? $approvalInfo : [];

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
        'url'       => Url::to(['/mobile/default/approval-view', 'id' => $approve->id]),
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
$approvalTabs = [
    ['key' => 'leave',       'label' => 'ขอลา',                 'icon' => 'calendar',  'url' => Url::to(['/mobile/default/approvals', 'bucket' => 'pending', 'type' => 'leave'])],
    ['key' => 'development', 'label' => 'อบรม/ประชุม/ดูงาน', 'icon' => 'user-star', 'url' => Url::to(['/mobile/default/approvals', 'bucket' => 'pending', 'type' => 'development'])],
];
$approvalGroups = [];
foreach ($approvalTabs as $tab) {
    $count = (int) ($approvalInfo[$tab['key']]['total'] ?? 0);
    $approvalGroups[] = $tab + ['count' => $count];
}
$activeApprovalGroups = array_values(array_filter($approvalGroups, static function (array $group): bool {
    return ($group['count'] ?? 0) > 0;
}));
$pendingCount = array_sum(array_map(static function (array $group): int {
    return (int) ($group['count'] ?? 0);
}, $approvalGroups));
$pendingApprovalUrl = Url::to(['/mobile/default/approvals', 'bucket' => 'pending']);
// Expose counts to the shared profile drawer (rendered by main layout).
$this->params['profileBadges'] = [
    'pending' => isset($pendingCount) ? (int) $pendingCount : 0,
    'recent'  => isset($recentRequestItems) ? count($recentRequestItems) : 0,
];
$approvalSummaryText = !empty($activeApprovalGroups)
    ? implode(' · ', array_map(static function (array $group): string {
        return $group['label'] . ' ' . number_format((int) $group['count']);
    }, array_slice($activeApprovalGroups, 0, 3)))
    : 'เปิดดูรายการจากศูนย์อนุมัติ';
$leaveDaysRemaining = '—'; // future: hook to leave-balance source
$recentCount  = count($recentRequestItems);
$notifBadge   = count($pendingLeaveApprovals) > 0;

$stats = [
    ['icon' => 'check-square',   'label' => 'รออนุมัติ',   'value' => $pendingCount,        'url' => $pendingApprovalUrl],
    ['icon' => 'calendar-off',   'label' => 'ใบลา',         'value' => $leaveDaysRemaining,  'url' => Url::to(['/mobile/default/leave-request'])],
    ['icon' => 'mail',           'label' => 'หนังสือใหม่',  'value' => $officialUnreadCount, 'url' => Url::to(['/mobile/default/news'])],
    ['icon' => 'history',        'label' => 'คำขอล่าสุด',  'value' => $recentCount,         'url' => Url::to(['/mobile/default/my-requests'])],
];

// ── Quick action tiles — base high-frequency services.
$quickServices = [
    ['icon' => 'car',             'cat' => 'vehicle',     'label' => 'จองรถ',           'url' => Url::to(['/mobile/default/booking-vehicle', 'action' => 'new'])],
    ['icon' => 'calendar',        'cat' => 'meeting',     'label' => 'จองห้องประชุม',   'url' => Url::to(['/mobile/default/booking-meeting', 'action' => 'new'])],
    ['icon' => 'wrench',          'cat' => 'maintenance', 'label' => 'แจ้งซ่อม',        'url' => Url::to(['/mobile/default/repair-request'])],
    ['icon' => 'calendar-off',    'cat' => 'leave',       'label' => 'ขอลา',            'url' => Url::to(['/mobile/default/leave-request'])],
    ['icon' => 'clock',           'cat' => 'attendance',  'label' => 'ลงเวลา',          'url' => Url::to(['/mobile/default/attendance'])],
    ['icon' => 'qr-code',         'cat' => 'asset',       'label' => 'ครุภัณฑ์',        'url' => Url::to(['/mobile/default/scan'])],
    ['icon' => 'layout-dashboard','cat' => 'document',    'label' => 'ภาพรวม',          'url' => Url::to(['/mobile/default/overview'])],
    ['icon' => 'clipboard-check', 'cat' => 'approval',    'label' => 'งานอนุมัติ',      'url' => $pendingApprovalUrl, 'badge' => $pendingCount > 0 ? $pendingCount : null],
];

$todayThai    = ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$hour         = (int) date('G');
$greetingWord = $hour < 12 ? 'อรุณสวัสดิ์' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
?>

<style>
/* ─────────────────────────────────────────────────────────────────────
   Full-bleed escape: this view sits inside .mobile-app-content which has
   horizontal padding 1rem. We use negative margin to break out so the
   shell (hero + stats) reaches screen edges.
   ───────────────────────────────────────────────────────────────────── */
.home-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
    gap: 0;
}

/* Fixed shell + scroll area: see _head.php (.app-shell, .app-scroll).
   Home uses its own .home-hero / .home-stats markup (different from the
   simple icon+title pattern) wrapped in the shared shell wrapper. */

/* ── Hero — drenched blue with bottom-corner curve. Top padding accounts
   for status-bar safe-area plus a generous breathing zone now that the
   sticky mobile-header has been removed from the layout. */
.home-hero {
    position: relative;
    padding: calc(env(safe-area-inset-top, 0px) + 4.5rem) var(--space-md) calc(var(--space-2xl) + var(--space-md));
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

/* ── Approval hub cue ──────────────────────────────────────────────── */
.home-approval-card {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-md);
    background: color-mix(in oklch, var(--surface) 86%, var(--cat-approval-bg));
    border: 1px solid var(--mobile-primary-soft-border);
    border-radius: 18px;
    box-shadow: var(--shadow-md);
    color: inherit; text-decoration: none;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.home-approval-card:hover,
.home-approval-card:focus {
    color: inherit;
    border-color: var(--mobile-primary-soft-strong);
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.12);
    transform: translateY(-1px);
}
.home-approval-card:active {
    transform: scale(0.99);
}
.home-approval-card:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 3px;
}
.home-approval-medal {
    width: 3rem; height: 3rem; border-radius: 16px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: var(--cat-approval-bg);
    color: var(--cat-approval-fg);
}
.home-approval-medal svg {
    width: 1.35rem; height: 1.35rem;
}
.home-approval-body {
    min-width: 0; flex: 1;
}
.home-approval-kicker {
    display: block;
    margin: 0 0 2px;
    color: var(--mobile-primary);
    font-size: var(--fs-xs);
    font-weight: 700;
    line-height: 1.2;
}
.home-approval-title {
    display: block;
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.25;
}
.home-approval-desc {
    display: block;
    margin: 4px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.home-approval-chips {
    display: flex; gap: 6px;
    margin-top: var(--space-xs);
    overflow-x: auto;
    scrollbar-width: none;
}
.home-approval-chips::-webkit-scrollbar {
    display: none;
}
.home-approval-chip {
    display: inline-flex; align-items: center; gap: 4px;
    flex: 0 0 auto;
    padding: 4px 8px;
    border-radius: 999px;
    background: var(--surface);
    border: 1px solid var(--ink-line);
    color: var(--ink-3);
    font-size: var(--fs-2xs);
    font-weight: 600;
    line-height: 1.2;
}
.home-approval-chip b {
    color: var(--danger-strong);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.home-approval-arrow {
    width: 2rem; height: 2rem; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: var(--surface);
    color: var(--mobile-primary);
    box-shadow: var(--shadow-sm);
    transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.home-approval-card:hover .home-approval-arrow,
.home-approval-card:focus .home-approval-arrow {
    transform: translateX(2px);
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
    .home-service-tile, .home-approval-card, .home-approval-arrow, .m-row, .home-hero-btn, .m-headline-link, .m-headline-link svg { transition: none !important; }
    .home-service-tile:hover, .home-approval-card:hover, .home-approval-card:focus { transform: none; }
}

/* Profile drawer styles live in @app/modules/mobile/views/layouts/_partials/_profile_drawer.php */
</style>

<div class="home-root">

    <!-- Fixed shell: Hero + Stats stay at viewport top while page scrolls -->
    <div class="app-shell">

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

    <!-- ── Stats overlay (overlaps hero curve, inside fixed shell) ──── -->
    <nav class="home-stats" aria-label="สรุปสถานะของคุณ" style="--i: 1">
        <?php foreach ($stats as $s): ?>
            <a href="<?= Html::encode($s['url']) ?>" class="home-stat">
                <span class="home-stat-icon" aria-hidden="true"><i data-lucide="<?= Html::encode($s['icon']) ?>"></i></span>
                <span class="home-stat-label"><?= Html::encode($s['label']) ?></span>
                <span class="home-stat-value"><?= Html::encode((string) $s['value']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    </div><!-- /.app-shell -->

    <!-- ── Scroll area (content under the fixed shell) ─────────────── -->
    <div class="app-scroll has-stats">
    <!-- ── Below the fold ──────────────────────────────────────────── -->
    <div class="home-below">

        <!-- บริการด่วน -->
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

        <?php if ($pendingCount > 0): ?>
        <!-- งานอนุมัติรวมจาก approveV2 tab menu -->
        <section style="--i: 3">
            <a href="<?= Html::encode($pendingApprovalUrl) ?>"
               class="home-approval-card"
               aria-label="เปิดงานที่ต้องอนุมัติ <?= Html::encode((string) $pendingCount) ?> รายการ">
                <span class="home-approval-medal" aria-hidden="true">
                    <i data-lucide="clipboard-check"></i>
                </span>
                <span class="home-approval-body">
                    <span class="home-approval-kicker">ศูนย์อนุมัติ</span>
                    <span class="home-approval-title">งานรออนุมัติ <?= Html::encode(number_format($pendingCount)) ?> รายการ</span>
                    <span class="home-approval-desc"><?= Html::encode($approvalSummaryText) ?></span>
                    <?php if (!empty($activeApprovalGroups)): ?>
                        <span class="home-approval-chips" aria-hidden="true">
                            <?php foreach (array_slice($activeApprovalGroups, 0, 4) as $group): ?>
                                <span class="home-approval-chip">
                                    <?= Html::encode($group['label']) ?> <b><?= Html::encode(number_format((int) $group['count'])) ?></b>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>
                </span>
                <span class="home-approval-arrow" aria-hidden="true">
                    <i data-lucide="chevron-right"></i>
                </span>
            </a>
        </section>
        <?php endif; ?>

        <!-- การแจ้งเตือน -->
        <section style="--i: 4">
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
        <section style="--i: 5">
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

    </div><!-- /.home-below -->
    </div><!-- /.app-scroll -->
</div>
<?php
// Profile drawer (#profileDrawer) is rendered by layouts/_partials/_profile_drawer.php
// which is included from the main layout, so it's available on every mobile page.
// Telegram MiniApp auto-login lives in web/js/mobile-shared.js, gated by
// <body data-mobile-page="home"> (set in modules/mobile/views/layouts/main.php).
?>
