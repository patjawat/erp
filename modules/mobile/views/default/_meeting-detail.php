<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var \app\modules\booking\models\Meeting $meeting */
/** @var \app\modules\booking\models\Room|null $room */

// ── Status bucket → presentation class ─────────────────────────────────
$status = (string) $meeting->status;
if (in_array($status, ['Pass', 'Approve'], true))      $bucket = 'passed';
elseif (in_array($status, ['Cancel', 'Reject'], true)) $bucket = 'cancelled';
elseif ($status === 'Pending')                         $bucket = 'pending';
else                                                   $bucket = 'other';

$statusIcon = [
    'pending'   => 'clock',
    'passed'    => 'check-circle-2',
    'cancelled' => 'x-circle',
    'other'     => 'circle',
][$bucket];

try {
    $statusInfo  = $meeting->getStatus($meeting->status);
    $statusTitle = $statusInfo['title'] ?? $meeting->status;
} catch (\Throwable $e) {
    $statusTitle = $status;
}

$roomTitle = $room ? $room->title : $meeting->room_id;
$dateStr   = $meeting->date_start ? ThaiDateHelper::formatThaiDate($meeting->date_start) : '—';
$timeStr   = trim(substr($meeting->time_start ?? '', 0, 5) . ' - ' . substr($meeting->time_end ?? '', 0, 5), ' -');

$requesterName = '—';
try {
    if ($meeting->employee) {
        $requesterName = $meeting->employee->fullname ?? $meeting->emp_id;
    }
} catch (\Throwable $e) {}
?>

<style>
/* ── Modal detail content — self-contained styles ──────────────────── */
.md-detail { background: #f5f7fa; margin: -1rem; padding: var(--space-md, 1rem); }
.md-d-hero {
    background: #fff; border-radius: 16px;
    padding: var(--space-md, 1rem);
    margin-bottom: var(--space-md, 1rem);
    display: flex; align-items: flex-start; gap: var(--space-sm, 0.75rem);
}
.md-d-medal {
    width: 3rem; height: 3rem; border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.md-d-medal svg { width: 1.25rem; height: 1.25rem; }
.md-d-medal.is-pending   { background: var(--warning-soft);  color: var(--warning); }
.md-d-medal.is-passed    { background: var(--success-soft);   color: var(--success); }
.md-d-medal.is-cancelled { background: var(--danger-soft);   color: var(--danger-strong); }
.md-d-medal.is-other     { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

.md-d-title {
    font-size: 1.375rem; font-weight: 700; color: var(--ink);
    margin: 0; line-height: 1.25;
}
.md-d-pill {
    display: inline-block; margin-top: 0.4rem;
    font-size: 0.75rem; font-weight: 600;
    padding: 4px 10px; border-radius: 999px;
}
.md-d-pill.is-pending   { background: var(--warning-soft);  color: var(--warning); }
.md-d-pill.is-passed    { background: var(--success-soft);   color: var(--success); }
.md-d-pill.is-cancelled { background: var(--danger-soft);   color: var(--danger-strong); }
.md-d-pill.is-other     { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

.md-d-section {
    background: #fff; border-radius: 14px;
    padding: var(--space-md, 1rem);
    margin-bottom: 0.5rem;
}
.md-d-section-label {
    font-size: 0.75rem; color: var(--ink-4);
    font-weight: 600; letter-spacing: 0.02em;
    margin: 0 0 0.5rem;
}
.md-d-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; padding: 0.5rem 0;
    border-bottom: 1px solid var(--ink-line);
}
.md-d-row:last-child { border-bottom: 0; }
.md-d-row dt {
    font-size: 0.875rem; color: var(--ink-4); font-weight: 500;
    margin: 0; flex-shrink: 0;
}
.md-d-row dd {
    font-size: 0.875rem; color: var(--ink); font-weight: 600;
    margin: 0; text-align: right;
    overflow-wrap: anywhere;
}
.md-d-row dd.md-d-time { color: #0d6efd; }
</style>

<div class="md-detail">

    <!-- Hero block: status + title + pill -->
    <section class="md-d-hero">
        <span class="md-d-medal is-<?= Html::encode($bucket) ?>" aria-hidden="true">
            <i data-lucide="<?= Html::encode($statusIcon) ?>"></i>
        </span>
        <div class="min-w-0 flex-grow-1">
            <h2 class="md-d-title"><?= Html::encode($meeting->title ?: $meeting->code) ?></h2>
            <span class="md-d-pill is-<?= Html::encode($bucket) ?>"><?= Html::encode($statusTitle) ?></span>
        </div>
    </section>

    <!-- Booking details -->
    <section class="md-d-section">
        <p class="md-d-section-label">ข้อมูลการจอง</p>
        <dl class="m-0">
            <div class="md-d-row"><dt>ห้องประชุม</dt> <dd><?= Html::encode($roomTitle) ?></dd></div>
            <div class="md-d-row"><dt>วันที่</dt>      <dd><?= Html::encode($dateStr) ?></dd></div>
            <?php if ($timeStr): ?>
                <div class="md-d-row"><dt>เวลา</dt> <dd class="md-d-time"><?= Html::encode($timeStr) ?> น.</dd></div>
            <?php endif; ?>
            <div class="md-d-row"><dt>รหัส</dt> <dd><?= Html::encode($meeting->code) ?></dd></div>
        </dl>
    </section>

    <!-- Requester block -->
    <section class="md-d-section">
        <p class="md-d-section-label">ผู้ขอ</p>
        <dl class="m-0">
            <div class="md-d-row"><dt>ชื่อผู้ขอ</dt> <dd><?= Html::encode($requesterName) ?></dd></div>
            <?php if (!empty($meeting->emp_number)): ?>
                <div class="md-d-row"><dt>ผู้เข้าร่วม</dt> <dd><?= Html::encode((string) $meeting->emp_number) ?> คน</dd></div>
            <?php endif; ?>
        </dl>
    </section>

</div>
