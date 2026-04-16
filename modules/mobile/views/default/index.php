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
.home-greeting-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.home-greeting-avatar { width: 3rem; height: 3rem; border-radius: 50%; background: rgba(13, 110, 253, 0.12); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.home-greeting-avatar img { width: 100%; height: 100%; object-fit: cover; }
.home-greeting-avatar i { color: var(--mobile-primary); }
.home-quick-action { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem 0.5rem; min-height: 5rem; transition: box-shadow 0.2s ease, transform 0.15s ease; background: #fff; }
.home-quick-action:hover { color: inherit; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.home-quick-action:active { transform: scale(0.98); }
.home-quick-action .qa-icon { width: 2.25rem; height: 2.25rem; margin-bottom: 0.35rem; color: var(--mobile-primary); }
.home-section-title { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.75rem; }
.home-request-item { border-radius: 12px; padding: 0.65rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.home-request-item:last-child { border-bottom: 0; }
</style>

<div class="d-flex flex-column gap-3">
    <!-- Greeting + Avatar -->
    <div class="card home-greeting-card">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="home-greeting-avatar flex-shrink-0">
                <?php if ($avatarUrl): ?>
                    <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="48" height="48">
                <?php else: ?>
                    <i data-lucide="user" style="width: 1.75rem; height: 1.75rem;"></i>
                <?php endif; ?>
            </div>
            <div class="min-w-0 flex-grow-1">
                <p class="mb-0 small text-body-secondary">สวัสดี</p>
                <h2 class="h5 fw-semibold mb-0 text-truncate"><?= \yii\bootstrap5\Html::encode($userName) ?></h2>
            </div>
        </div>
    </div>

    <div id="debug" style="position:fixed;bottom:0;left:0;background:#000;color:#0f0;padding:10px;z-index:9999;font-size:12px;max-height:200px;overflow:auto;"></div>


    <!-- Quick Actions: จองรถ, จองห้องประชุม, แจ้งซ่อม, ขอลา (ซ้าย→ขวา บน→ล่าง) -->
    <div>
        <h3 class="home-section-title d-flex align-items-center gap-2">
            <i data-lucide="zap" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
            บริการด่วน
        </h3>
        <div class="row g-2">
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-vehicle'])) ?>" class="card home-quick-action">
                    <i data-lucide="car" class="qa-icon"></i>
                    <span class="fw-semibold small">จองรถ</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-meeting'])) ?>" class="card home-quick-action">
                    <i data-lucide="calendar" class="qa-icon"></i>
                    <span class="fw-semibold small">จองห้องประชุม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request'])) ?>" class="card home-quick-action">
                    <i data-lucide="wrench" class="qa-icon"></i>
                    <span class="fw-semibold small">แจ้งซ่อม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/leave-request'])) ?>" class="card home-quick-action">
                    <i data-lucide="calendar-off" class="qa-icon"></i>
                    <span class="fw-semibold small">ขอลา</span>
                </a>
            </div>
            <div class="col-12">
                <!-- <a href="<?= Html::encode(Url::to(['/mobile/default/attendance'])) ?>" class="card home-quick-action flex-row justify-content-center gap-2 py-3"> -->
                <a href="https://sstas.tphcp.go.th/kiosk" class="card home-quick-action flex-row justify-content-center gap-2 py-3">
                    <i data-lucide="clock" class="qa-icon mb-0"></i>
                    <span class="fw-semibold small">ลงเวลาเข้า-ออกงาน</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card mobile-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="bell" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    การแจ้งเตือน
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <?php if (empty($homeNotifications)): ?>
                <div class="text-center text-muted py-3 small">ยังไม่มีการแจ้งเตือนล่าสุด</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($homeNotifications as $item): ?>
                        <a href="<?= Html::encode($item['url']) ?>" class="d-flex align-items-start gap-2 py-1 text-decoration-none text-dark">
                            <i data-lucide="<?= Html::encode($item['icon']) ?>" class="text-<?= Html::encode($item['iconColor']) ?> flex-shrink-0 mt-1" style="width: 1.125rem; height: 1.125rem;"></i>
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

    <!-- Announcements -->
    <div class="card mobile-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="megaphone" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    ข่าวสารและประกาศ
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ระบบขอลาออนไลน์เปิดให้บริการแล้ว</span>
                    <p class="mb-0 small text-body-secondary">ส่งคำขอลาและติดตามสถานะได้ผ่านแอป</p>
                </a>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ประชุมใหญ่ประจำปี 2568</span>
                    <p class="mb-0 small text-body-secondary">ขอเชิญพนักงานเข้าร่วมประชุมใหญ่ วันศุกร์ที่ 15 มี.ค. 2568 ณ ห้องประชุมใหญ่ ชั้น 3</p>
                </a>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ปรับปรุงหน้าจองห้องประชุม</span>
                    <p class="mb-0 small text-body-secondary">เพิ่มการแสดงผลปฏิทินและห้องว่างแบบรายชั่วโมง ให้จองได้สะดวกขึ้น</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Recent requests -->
    <div class="card mobile-card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="history" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    คำขอล่าสุด
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <?php if (empty($recentRequestItems)): ?>
                <div class="text-center text-muted py-3 small">ยังไม่มีคำขอล่าสุดในระบบมือถือ</div>
            <?php else: ?>
                <?php foreach ($recentRequestItems as $item): ?>
                    <a href="<?= Html::encode($item['url']) ?>" class="home-request-item d-flex align-items-center gap-2 text-decoration-none text-dark">
                        <i data-lucide="<?= Html::encode($item['icon']) ?>" class="text-body-secondary" style="width: 1.25rem; height: 1.25rem;"></i>
                        <div class="flex-grow-1 min-w-0">
                            <span class="small fw-medium"><?= Html::encode($item['title']) ?></span>
                            <p class="mb-0 small text-body-secondary"><?= Html::encode($item['desc']) ?></p>
                        </div>
                        <span class="badge bg-<?= Html::encode($item['badgeClass']) ?> bg-opacity-10 text-<?= Html::encode($item['badgeClass']) ?> border border-<?= Html::encode($item['badgeClass']) ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($item['badgeText']) ?></span>
                    </a>
                <?php endforeach; ?>
                <p class="small text-body-secondary mb-0 mt-1">ติดตามสถานะคำขอล่าสุดของคุณได้ที่นี่</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
use yii\web\View;

$js = <<<JS

(function () {

    // 🔥 กันยิงซ้ำ
    if (window.__tg_login_done) return;
    window.__tg_login_done = true;

    // 🔥 กัน reload loop
    if (localStorage.getItem("tg_logged_in") === "1") {
        console.log("Already logged in");
        return;
    }

    const tg = window.Telegram?.WebApp;

    if (!tg) {
        console.log("Not running inside Telegram Mini App");
        return;
    }

    tg.ready();
    tg.expand();

    const user = tg.initDataUnsafe?.user;

    if (!user || !user.id) {
        console.log("No Telegram user detected");
        return;
    }

    const debugBox = document.getElementById("debug");

    function log(msg) {
        console.log(msg);
        if (debugBox) {
            debugBox.innerText = JSON.stringify(msg, null, 2);
        }
    }

    $.ajax({
        url: "/mobile/auth/telegram-login",
        type: "POST",
        dataType: "json",
        data: {
            telegram_id: user.id,
            initData: tg.initData ?? ''
        },
        headers: {
            'X-CSRF-Token': yii.getCsrfToken()
        },

        success: function(res) {

            log(res);

            if (res.success) {

                // 🔥 mark login success
                localStorage.setItem("tg_logged_in", "1");

                setTimeout(() => {
                    window.location.href = res.redirect || "/mobile/default/index";
                }, 200);

            } else {

                localStorage.removeItem("tg_logged_in");

                setTimeout(() => {
                    window.location.href = "/mobile/auth/login";
                }, 200);

            }
        },

        error: function(err) {

            log({
                error: true,
                message: "AJAX error",
                detail: err
            });

        }
    });

})();
JS;

$this->registerJs($js, View::POS_END);
?>