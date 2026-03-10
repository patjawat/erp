<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Meeting $meeting */
$this->params['current_page']   = $current_page ?? 'profile';
$this->params['mobileTitle']   = 'รายละเอียดการจองห้องประชุม';
$this->params['mobileSubtitle'] = $meeting->code ?? '';

try {
    $statusInfo = $meeting->getStatus($meeting->status);
    $statusTitle = $statusInfo['title'] ?? $meeting->status;
} catch (\Throwable $e) {
    $statusTitle = $meeting->status;
}
$statusCode = (string) $meeting->status;
if (in_array($statusCode, ['Pass', 'Approve', 'อนุมัติ'], true)) {
    $badgeClass = 'success';
} elseif (in_array($statusCode, ['Reject', 'Cancel', 'ยกเลิก'], true)) {
    $badgeClass = 'danger';
} elseif (in_array($statusCode, ['Pending', 'รอ'], true)) {
    $badgeClass = 'warning';
} else {
    $badgeClass = 'secondary';
}

$roomTitle = $meeting->room ? $meeting->room->title : $meeting->room_id;
$dateStr = $meeting->date_start ? ThaiDateHelper::formatThaiDate($meeting->date_start) : '—';
$timeStr = $meeting->viewTime();
$timeFull = $timeStr['full'] ?? (trim(substr($meeting->time_start ?? '', 0, 5) . '–' . substr($meeting->time_end ?? '', 0, 5), '–') . ' น.');
$createdAt = $meeting->viewCreated();
$createdStr = $createdAt['full'] ?? '—';
$phone = is_array($meeting->data_json) && isset($meeting->data_json['phone']) ? $meeting->data_json['phone'] : null;
?>
<style>
.meeting-view-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.meeting-view-card .detail-row { padding: 0.75rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; }
.meeting-view-card .detail-row:last-child { border-bottom: 0; }
.meeting-view-card .detail-label { color: #6c757d; flex-shrink: 0; }
.meeting-view-card .detail-value { font-weight: 500; text-align: right; }
.btn-back-mobile { border-radius: 12px; }
</style>

<div class="d-flex flex-column gap-3">
    <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests', 'type' => 'meeting'])) ?>" class="btn btn-outline-secondary btn-back-mobile align-self-start">
        <i data-lucide="arrow-left" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i> กลับไปคำขอของฉัน
    </a>

    <div class="card meeting-view-card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 3rem; height: 3rem; background: rgba(13, 110, 253, 0.12);">
                    <i data-lucide="calendar" style="width: 1.5rem; height: 1.5rem; color: var(--mobile-primary);"></i>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <h5 class="fw-semibold mb-1 text-truncate"><?= Html::encode($meeting->title ?: $meeting->code) ?></h5>
                    <p class="small text-body-secondary mb-0">รหัส <?= Html::encode($meeting->code) ?></p>
                </div>
                <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0"><?= Html::encode($statusTitle) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">ห้องประชุม</span>
                <span class="detail-value"><?= Html::encode($roomTitle) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">วันที่</span>
                <span class="detail-value"><?= Html::encode($dateStr) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">เวลา</span>
                <span class="detail-value"><?= Html::encode($timeFull) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">จำนวนผู้เข้าร่วม</span>
                <span class="detail-value"><?= (int) ($meeting->emp_number ?? 0) ?> คน</span>
            </div>
            <?php if ($phone !== null && $phone !== ''): ?>
            <div class="detail-row">
                <span class="detail-label">เบอร์ติดต่อ</span>
                <span class="detail-value"><?= Html::encode($phone) ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label">ส่งคำขอเมื่อ</span>
                <span class="detail-value small"><?= Html::encode($createdStr) ?></span>
            </div>
        </div>
    </div>

    <?php if ($statusCode === 'Pending'): ?>
    <a href="<?= Html::encode(Url::to(['/mobile/default/meeting-update', 'id' => $meeting->id])) ?>" class="btn btn-outline-primary w-100" style="border-radius: 12px;">
        <i data-lucide="pencil" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
        แก้ไขการจอง
    </a>
    <?php endif; ?>
</div>
