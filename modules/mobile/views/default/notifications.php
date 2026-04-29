<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $pendingLeaveApprovals */
/** @var app\modules\leave\models\Leave[] $recentLeaveRequests */
$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'การแจ้งเตือน';
$this->params['mobileSubtitle'] = 'รายการแจ้งเตือนทั้งหมด';

$pendingLeaveApprovals = $pendingLeaveApprovals ?? [];
$recentLeaveRequests = $recentLeaveRequests ?? [];

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

$items = [];
foreach ($pendingLeaveApprovals as $approve) {
    $leave = $approve->leave;
    if (!$leave) {
        continue;
    }

    $requesterName = $leave->employee->fullname ?? '-';
    $leaveType = $leave->leaveType->title ?? 'ใบลา';
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $items[] = [
        'icon' => 'clock',
        'iconColor' => 'warning',
        'title' => 'มีใบลารออนุมัติจากคุณ',
        'desc' => trim($requesterName . ' · ' . $leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time' => $formatDateTimeText($approve->created_at ?: $leave->created_at),
        'url' => Url::to(['/mobile/default/approve-leave', 'id' => $approve->id]),
        'sortTs' => strtotime((string) ($approve->created_at ?: $leave->created_at)) ?: 0,
    ];
}

foreach ($recentLeaveRequests as $leave) {
    $meta = $leaveStatusMeta($leave);
    $leaveType = $leave->leaveType->title ?? 'ใบลา';
    $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
    $items[] = [
        'icon' => $meta['icon'],
        'iconColor' => $meta['color'],
        'title' => 'สถานะใบลาของคุณ: ' . $meta['label'],
        'desc' => trim($leaveType . ($dateRange !== '' ? ' · ' . $dateRange : '')),
        'time' => $formatDateTimeText($leave->updated_at ?: $leave->created_at),
        'url' => Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]),
        'sortTs' => strtotime((string) ($leave->updated_at ?: $leave->created_at)) ?: 0,
    ];
}

usort($items, static function (array $a, array $b) {
    return ($b['sortTs'] ?? 0) <=> ($a['sortTs'] ?? 0);
});
?>
<style>
.notif-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.notif-item { padding: 0.875rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; align-items: flex-start; gap: 0.75rem; text-decoration: none; color: inherit; }
.notif-item:last-child { border-bottom: 0; }
.notif-item:hover { color: inherit; background: rgba(0,0,0,0.02); }
.notif-item .notif-icon { width: 2.25rem; height: 2.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-item .notif-icon i { width: 1.125rem; height: 1.125rem; }
.notif-item .notif-icon.text-success { background: rgba(25, 135, 84, 0.12); }
.notif-item .notif-icon.text-warning { background: rgba(255, 193, 7, 0.2); }
.notif-item .notif-icon.text-info { background: rgba(13, 202, 240, 0.15); }
</style>

<div class="d-flex flex-column gap-3">
    <p class="small text-body-secondary mb-0">รายการแจ้งเตือนจากงานอนุมัติและสถานะใบลาล่าสุดของคุณ</p>

    <?php if (empty($items)): ?>
        <div class="card notif-card">
            <div class="card-body py-5 text-center text-muted">
                <div class="fw-semibold text-dark mb-2">ยังไม่มีการแจ้งเตือน</div>
                <div class="small">เมื่อมีงานอนุมัติหรือสถานะใบลาเปลี่ยน รายการจะปรากฏที่หน้านี้</div>
            </div>
        </div>
    <?php else: ?>
        <div class="card notif-card">
            <div class="card-body p-0">
                <?php foreach ($items as $item): ?>
                    <a href="<?= Html::encode($item['url']) ?>" class="notif-item px-3">
                        <div class="notif-icon text-<?= Html::encode($item['iconColor']) ?>">
                            <i data-lucide="<?= Html::encode($item['icon']) ?>"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <span class="fw-medium small d-block"><?= Html::encode($item['title']) ?></span>
                            <p class="mb-0 small text-body-secondary"><?= Html::encode($item['desc']) ?></p>
                            <span class="small text-body-secondary"><?= Html::encode($item['time']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center py-2">
        <a href="<?= Html::encode(Url::to(['/mobile/default/index'])) ?>" class="btn btn-outline-primary" style="border-radius: 12px;">
            <i data-lucide="arrow-left" class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.2em;"></i>
            กลับหน้าหลัก
        </a>
    </div>
</div>
