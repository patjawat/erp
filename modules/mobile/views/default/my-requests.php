<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $type */
/** @var \app\modules\booking\models\Meeting[] $meetings */
$this->params['current_page']   = $current_page ?? 'profile';
$this->params['mobileTitle']    = 'คำขอของฉัน';
$this->params['mobileSubtitle'] = 'ดูและติดตามสถานะคำขอทั้งหมด';

$tabs = [
    'all' => ['label' => 'ทั้งหมด', 'icon' => 'list'],
    'meeting' => ['label' => 'จองห้องประชุม', 'icon' => 'calendar'],
    'leave' => ['label' => 'ขอลา', 'icon' => 'calendar-off'],
    'vehicle' => ['label' => 'จองรถ', 'icon' => 'car'],
    'maintenance' => ['label' => 'แจ้งซ่อม', 'icon' => 'wrench'],
];

function statusBadgeClass($code) {
    $code = (string) $code;
    if (in_array($code, ['Pass', 'Approve', 'อนุมัติ'], true)) return 'success';
    if (in_array($code, ['Reject', 'Cancel', 'ยกเลิก'], true)) return 'danger';
    if (in_array($code, ['Pending', 'รอ'], true)) return 'warning';
    return 'secondary';
}
?>
<style>
.req-tabs { display: flex; gap: 0.25rem; overflow-x: auto; padding-bottom: 0.5rem; -webkit-overflow-scrolling: touch; }
.req-tabs::-webkit-scrollbar { height: 4px; }
.req-tab { flex-shrink: 0; padding: 0.5rem 0.75rem; border-radius: 999px; font-size: 0.8125rem; font-weight: 500; text-decoration: none; color: #6c757d; background: #f0f2f5; white-space: nowrap; transition: background 0.2s, color 0.2s; }
.req-tab:hover { color: inherit; }
.req-tab.active { background: var(--mobile-primary); color: #fff; }
.req-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); text-decoration: none; color: inherit; display: block; transition: box-shadow 0.2s; }
.req-card:hover { color: inherit; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.req-card .req-icon-wrap { width: 2.5rem; height: 2.5rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.req-card .req-icon-wrap.meeting { background: rgba(13, 110, 253, 0.12); }
.req-card .req-icon-wrap.meeting i { color: var(--mobile-primary); }
.req-card .req-card-text { min-width: 0; overflow: hidden; }
.req-card .req-card-text .fw-semibold { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.req-card .req-card-text .small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.req-empty { padding: 2.5rem 1rem; text-align: center; color: #6c757d; }
.req-empty i { width: 2.5rem; height: 2.5rem; opacity: 0.5; margin-bottom: 0.5rem; }
</style>

<div class="d-flex flex-column gap-3">
    <p class="small text-body-secondary mb-0">ติดตามสถานะคำขอจองห้องประชุม ขอลา จองรถ และแจ้งซ่อม</p>

    <!-- Tabs -->
    <nav class="req-tabs" role="tablist">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests', 'type' => $key])) ?>" class="req-tab <?= ($type === $key) ? 'active' : '' ?>">
                <i data-lucide="<?= $tab['icon'] ?>" style="width: 1rem; height: 1rem; vertical-align: -0.2em;"></i>
                <?= Html::encode($tab['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- List: จองห้องประชุม (จาก DB) -->
    <?php
    $showMeeting = ($type === 'all' || $type === 'meeting');
    $meetingList = $showMeeting ? $meetings : [];
    ?>
    <?php if ($showMeeting): ?>
        <?php if ($type === 'all' && !empty($meetingList)): ?>
            <h6 class="small fw-semibold text-body-secondary text-uppercase mb-2">จองห้องประชุม</h6>
        <?php endif; ?>
        <?php if (empty($meetingList)): ?>
            <div class="card border-0 rounded-3 shadow-sm">
                <div class="card-body req-empty">
                    <i data-lucide="calendar" class="d-block mx-auto"></i>
                    <p class="mb-2 small">ยังไม่มีคำขอจองห้องประชุม</p>
                    <a href="<?= Html::encode(Url::to(['/mobile/default/booking-meeting'])) ?>" class="btn btn-primary btn-sm" style="border-radius: 12px;">จองห้องประชุม</a>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($meetingList as $m): ?>
                    <?php
                    try {
                        $statusInfo = $m->getStatus($m->status);
                        $statusTitle = $statusInfo['title'] ?? $m->status;
                    } catch (\Throwable $e) {
                        $statusTitle = $m->status;
                    }
                    $badgeClass = statusBadgeClass($m->status);
                    $roomTitle = $m->room ? $m->room->title : $m->room_id;
                    $dateStr = $m->date_start ? ThaiDateHelper::formatThaiDate($m->date_start) : '—';
                    $timeStr = trim(substr($m->time_start ?? '', 0, 5) . '–' . substr($m->time_end ?? '', 0, 5), '–');
                    $viewUrl = Url::to(['/mobile/default/meeting-view', 'id' => $m->id]);
                    ?>
                    <a href="<?= Html::encode($viewUrl) ?>" class="card req-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="req-icon-wrap meeting">
                                <i data-lucide="calendar" style="width: 1.25rem; height: 1.25rem;"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0 req-card-text">
                                <div class="fw-semibold"><?= Html::encode($m->title ?: $m->code) ?></div>
                                <div class="small text-body-secondary">
                                    <?= Html::encode($roomTitle) ?> · <?= Html::encode($dateStr) ?> <?= $timeStr ? Html::encode($timeStr) . ' น.' : '' ?>
                                </div>
                                <div class="small text-body-secondary">รหัส <?= Html::encode($m->code) ?></div>
                            </div>
                            <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0"><?= Html::encode($statusTitle) ?></span>
                            <i data-lucide="chevron-right" class="text-secondary flex-shrink-0" style="width: 1rem; height: 1rem;"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ชนิดอื่น (ยังไม่มีข้อมูลจาก DB) -->
    <?php if ($type === 'leave'): ?>
        <div class="card border-0 rounded-3 shadow-sm">
            <div class="card-body req-empty">
                <i data-lucide="calendar-off" class="d-block mx-auto"></i>
                <p class="mb-2 small">ยังไม่มีคำขอลาในระบบมือถือ</p>
                <a href="<?= Html::encode(Url::to(['/mobile/default/leave-request'])) ?>" class="btn btn-primary btn-sm" style="border-radius: 12px;">ขอลาออนไลน์</a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($type === 'vehicle'): ?>
        <div class="card border-0 rounded-3 shadow-sm">
            <div class="card-body req-empty">
                <i data-lucide="car" class="d-block mx-auto"></i>
                <p class="mb-2 small">ยังไม่มีคำขอจองรถในระบบมือถือ</p>
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-vehicle'])) ?>" class="btn btn-primary btn-sm" style="border-radius: 12px;">จองรถราชการ</a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($type === 'maintenance'): ?>
        <div class="card border-0 rounded-3 shadow-sm">
            <div class="card-body req-empty">
                <i data-lucide="wrench" class="d-block mx-auto"></i>
                <p class="mb-2 small">ยังไม่มีคำขอแจ้งซ่อมในระบบมือถือ</p>
                <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request'])) ?>" class="btn btn-primary btn-sm" style="border-radius: 12px;">แจ้งซ่อม</a>
            </div>
        </div>
    <?php endif; ?>
</div>
