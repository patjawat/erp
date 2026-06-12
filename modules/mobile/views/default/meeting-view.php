<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Meeting $meeting */
$this->params['current_page']   = $current_page ?? 'services';
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
} elseif (in_array($statusCode, ['Pending', 'รอ', 'รออนุมัติ'], true)) {
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
.mv-root {
    margin: -1rem -1rem 0;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
.mv-scroll {
    padding: calc(var(--shell-h, 13rem) + var(--space-md)) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.mv-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}
.mv-back,
.mv-actions .btn {
    min-height: 2.75rem;
    border-radius: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
}
.mv-back {
    align-self: flex-start;
}
.mv-back svg,
.mv-actions svg {
    width: 1.125rem;
    height: 1.125rem;
}
.meeting-view-card {
    background: var(--surface);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}
.meeting-view-card .detail-row { padding: 0.75rem 0; border-bottom: 1px solid var(--ink-line); display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; }
.meeting-view-card .detail-row:last-child { border-bottom: 0; }
.meeting-view-card .detail-label { color: var(--ink-4); flex-shrink: 0; font-weight: 600; }
.meeting-view-card .detail-value { color: var(--ink); font-weight: 700; text-align: right; overflow-wrap: anywhere; }
.mv-icon-card {
    width: 3rem;
    height: 3rem;
    border-radius: 14px;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mv-icon-card svg {
    width: 1.5rem;
    height: 1.5rem;
}
.mv-title {
    margin: 0 0 0.25rem;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.35;
}
.mv-code {
    margin: 0;
    color: var(--ink-4);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 600;
}
.mv-status {
    border-radius: 999px;
    font-weight: 700;
    padding: 0.35rem 0.65rem;
}
.mv-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xs);
}
</style>

<div class="mv-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'calendar-check',
        'title'    => 'รายละเอียดการจองห้องประชุม',
        'subtitle' => (string) ($meeting->code ?? ''),
    ]) ?>

    <div class="app-scroll mv-scroll">
        <div class="mv-body">
            <a href="<?= Html::encode(Url::to(['/mobile/default/booking-meeting'])) ?>" class="btn btn-outline-secondary mv-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปรายการ</span>
            </a>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success rounded-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger rounded-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
            <?php endif; ?>

            <div class="card meeting-view-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="mv-icon-card" aria-hidden="true">
                            <i data-lucide="calendar"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <h5 class="mv-title text-truncate"><?= Html::encode($meeting->title ?: $meeting->code) ?></h5>
                            <p class="mv-code">รหัส <?= Html::encode($meeting->code) ?></p>
                        </div>
                        <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle mv-status flex-shrink-0"><?= Html::encode($statusTitle) ?></span>
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

            <?php if (in_array($statusCode, ['Pending', 'รออนุมัติ'], true)): ?>
            <div class="mv-actions">
                <a href="<?= Html::encode(Url::to(['/mobile/default/meeting-update', 'id' => $meeting->id])) ?>" class="btn btn-primary w-100">
                    <i data-lucide="pencil" aria-hidden="true"></i>
                    <span>แก้ไข</span>
                </a>
                <?= Html::beginForm(['/mobile/default/meeting-cancel', 'id' => $meeting->id], 'post', [
                    'class' => 'm-0',
                    'onsubmit' => "return window.mobileConfirm ? window.mobileConfirm(this, 'ยืนยันยกเลิกคำขอจองห้องประชุม?') : confirm('ยืนยันยกเลิกคำขอจองห้องประชุม?');",
                ]) ?>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i data-lucide="x-circle" aria-hidden="true"></i>
                        <span>ยกเลิก</span>
                    </button>
                <?= Html::endForm() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
