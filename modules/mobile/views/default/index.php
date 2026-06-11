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
/* Greeting hero — wider hierarchy: small label, large name, date stamp */
.home-greeting-card { padding: var(--space-md) var(--space-md) var(--space-lg); }
.home-greeting-card .card-body { padding: 0; display: flex; align-items: center; gap: var(--space-md); }
.home-greeting-avatar { width: 3.25rem; height: 3.25rem; border-radius: 50%; background: rgba(13, 110, 253, 0.12); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
.home-greeting-avatar img { width: 100%; height: 100%; object-fit: cover; }
.home-greeting-avatar i { color: var(--mobile-primary); }
.home-greet-eyebrow { font-size: var(--fs-xs); color: #6c757d; margin: 0; letter-spacing: 0.02em; }
.home-greet-name { font-size: var(--fs-xl); font-weight: 700; color: #1a1f2c; margin: 0; line-height: 1.2; letter-spacing: -0.01em; text-wrap: balance; }
.home-greet-date { font-size: var(--fs-xs); color: #6c757d; margin: 0.25rem 0 0; }

/* Quick action tile — tighter padding for thumb scan, color medallion handles weight */
.home-quick-action { text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-md) var(--space-xs); min-height: 5.5rem; transition: box-shadow 0.2s ease, transform 0.15s ease; background: #fff; }
.home-quick-action:hover { color: inherit; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.home-quick-action:active { transform: scale(0.98); }
.home-quick-action .cat-medallion { width: 2.75rem; height: 2.75rem; margin-bottom: var(--space-xs); border-radius: 12px; }
.home-quick-action span:last-child { font-size: var(--fs-sm); color: #1a1f2c; }

/* Recent request row — replaces home-section-title with section-title utility */
.home-request-item { padding: var(--space-sm) 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.home-request-item:last-child { border-bottom: 0; }
</style>

<?php
$todayThai = \app\components\ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$hour = (int) date('G');
$greetingWord = $hour < 12 ? 'อรุณสวัสดิ์' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
?>
<div class="mobile-stack">
    <!-- Greeting hero -->
    <div class="card home-greeting-card">
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
    <section>
        <h3 class="section-title">
            <i data-lucide="zap"></i>
            บริการด่วน
        </h3>
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
    <section>
        <h3 class="section-title">
            <i data-lucide="bell"></i>
            การแจ้งเตือน
            <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="section-action">ดูทั้งหมด</a>
        </h3>
        <div class="card mobile-card">
            <div class="card-body">
                <?php if (empty($homeNotifications)): ?>
                    <div class="text-center text-muted py-3 small">ยังไม่มีการแจ้งเตือนล่าสุด</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($homeNotifications as $item): ?>
                            <a href="<?= Html::encode($item['url']) ?>" class="d-flex align-items-start gap-2 py-1 text-decoration-none text-dark">
                                <i data-lucide="<?= Html::encode($item['icon']) ?>" class="text-<?= Html::encode($item['iconColor']) ?> flex-shrink-0 mt-1 mi-sm"></i>
                                <div class="min-w-0 flex-grow-1">
                                    <span class="fw-medium small d-block"><?= Html::encode($item['title']) ?></span>
                                    <p class="mb-0 small text-body-secondary"><?= Html::encode($item['desc']) ?></p>
                                    <span class="small text-body-secondary"><?= Html::encode($item['time']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Official documents -->
    <section>
        <h3 class="section-title">
            <i data-lucide="file-text"></i>
            หนังสือราชการ
            <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="section-action">ดูทั้งหมด</a>
        </h3>
        <div class="card mobile-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <p class="small text-body-secondary mb-0">หนังสือที่ส่งมาถึงคุณและยังไม่ได้อ่าน</p>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-semibold text-primary fs-5"><?= Html::encode((string) $officialUnreadCount) ?></div>
                        <div class="small text-body-secondary">ยังไม่อ่าน</div>
                    </div>
                </div>

                <?php if (empty($officialDocumentsPreview)): ?>
                    <div class="text-center text-muted py-3 small">
                        <i data-lucide="mail-open" class="d-block mx-auto mb-2 text-body-secondary mi-xl"></i>
                        <div class="fw-medium text-dark mb-1">คุณอ่านหนังสือราชการครบแล้ว</div>
                        <div class="mb-3">ไม่มีหนังสือฉบับใหม่ที่ยังไม่ได้อ่านในขณะนี้</div>
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
    <section>
        <h3 class="section-title">
            <i data-lucide="history"></i>
            คำขอล่าสุด
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests'])) ?>" class="section-action">ดูทั้งหมด</a>
        </h3>
        <div class="card mobile-card">
            <div class="card-body">
            <?php if (empty($recentRequestItems)): ?>
                <div class="text-center text-muted py-3 small">ยังไม่มีคำขอล่าสุดในระบบมือถือ</div>
            <?php else: ?>
                <?php foreach ($recentRequestItems as $item): ?>
                    <a href="<?= Html::encode($item['url']) ?>" class="home-request-item d-flex align-items-center gap-2 text-decoration-none text-dark">
                        <i data-lucide="<?= Html::encode($item['icon']) ?>" class="text-body-secondary mi-md"></i>
                        <div class="flex-grow-1 min-w-0">
                            <span class="small fw-medium"><?= Html::encode($item['title']) ?></span>
                            <p class="mb-0 small text-body-secondary"><?= Html::encode($item['desc']) ?></p>
                        </div>
                        <span class="badge bg-<?= Html::encode($item['badgeClass']) ?> bg-opacity-10 text-<?= Html::encode($item['badgeClass']) ?> border border-<?= Html::encode($item['badgeClass']) ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($item['badgeText']) ?></span>
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
