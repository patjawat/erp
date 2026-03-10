<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Meeting[] $meetings */
/** @var string[] $ownedRoomCodes */
$this->params['current_page']   = $current_page ?? 'profile';
$this->params['mobileTitle']    = 'จัดการห้องประชุม';
$this->params['mobileSubtitle'] = 'อนุมัติหรือยกเลิกคำขอจองห้องที่คุณดูแล';

$meetings = $meetings ?? [];
$ownedRoomCodes = $ownedRoomCodes ?? [];

function statusBadgeClass($code) {
    $code = (string) $code;
    if (in_array($code, ['Pass', 'Approve', 'อนุมัติ'], true)) return 'success';
    if (in_array($code, ['Reject', 'Cancel', 'ยกเลิก'], true)) return 'danger';
    if (in_array($code, ['Pending', 'รอ'], true)) return 'warning';
    return 'secondary';
}
?>
<style>
.room-manage-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.room-manage-card .detail-row { padding: 0.75rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.room-manage-card .detail-row:last-child { border-bottom: 0; }
.btn-action-meeting { border-radius: 12px; }
</style>

<div class="d-flex flex-column gap-3">
    <a href="<?= Html::encode(Url::to(['/mobile/default/profile'])) ?>" class="btn btn-outline-secondary align-self-start" style="border-radius: 12px;">
        <i data-lucide="arrow-left" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i> กลับ
    </a>

    <?php if (empty($ownedRoomCodes)): ?>
        <div class="card room-manage-card">
            <div class="card-body text-center py-4">
                <i data-lucide="layout-grid" style="width: 3rem; height: 3rem; color: #adb5bd;" class="mb-3"></i>
                <h6 class="fw-semibold mb-2">ไม่พบห้องที่คุณดูแล</h6>
                <p class="small text-body-secondary mb-0">หากคุณเป็นผู้ดูแลห้องประชุม ระบบจะแสดงรายการจองที่นี่</p>
            </div>
        </div>
    <?php elseif (empty($meetings)): ?>
        <div class="card room-manage-card">
            <div class="card-body text-center py-4">
                <i data-lucide="calendar-check" style="width: 3rem; height: 3rem; color: #adb5bd;" class="mb-3"></i>
                <h6 class="fw-semibold mb-2">ยังไม่มีคำขอจอง</h6>
                <p class="small text-body-secondary mb-0">คำขอจองห้องที่คุณดูแลจะแสดงในหน้านี้</p>
            </div>
        </div>
    <?php else: ?>
        <p class="small text-body-secondary mb-0">รายการจองห้องประชุมที่คุณเป็นผู้ดูแล — อนุมัติหรือยกเลิกได้ที่การ์ดด้านล่าง</p>

        <div class="d-flex flex-column gap-2">
            <?php foreach ($meetings as $m): ?>
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
                $requesterName = '—';
                try {
                    if ($m->employee) {
                        $requesterName = $m->employee->fullname ?? $m->emp_id;
                    }
                } catch (\Throwable $e) {}
                $isPending = (string) $m->status === 'Pending';
                ?>
                <div class="card room-manage-card" data-meeting-id="<?= (int) $m->id ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate"><?= Html::encode($m->title ?: $m->code) ?></div>
                                <div class="small text-body-secondary"><?= Html::encode($roomTitle) ?> · <?= Html::encode($dateStr) ?> <?= $timeStr ? Html::encode($timeStr) . ' น.' : '' ?></div>
                            </div>
                            <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0"><?= Html::encode($statusTitle) ?></span>
                        </div>
                        <div class="detail-row small text-body-secondary">
                            รหัส <?= Html::encode($m->code) ?> · ผู้ขอ <?= Html::encode($requesterName) ?>
                        </div>
                        <?php if ($isPending): ?>
                        <div class="d-flex gap-2 mt-2 pt-2 border-top border-secondary border-opacity-10">
                            <button type="button" class="btn btn-success btn-action-meeting flex-grow-1" data-id="<?= (int) $m->id ?>" data-status="Pass" data-text="อนุมัติการจอง">
                                <i data-lucide="check-circle" style="width: 1.1rem; height: 1.1rem; vertical-align: -0.2em;"></i> อนุมัติ
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-action-meeting flex-grow-1" data-id="<?= (int) $m->id ?>" data-status="Cancel" data-text="ยกเลิกการจอง">
                                <i data-lucide="x-circle" style="width: 1.1rem; height: 1.1rem; vertical-align: -0.2em;"></i> ยกเลิก
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($meetings)): ?>
<?php
$confirmUrl = Url::to(['/mobile/default/meeting-confirm']);
$csrfParam = \yii\helpers\Json::encode(Yii::$app->request->csrfParam);
$csrfToken = \yii\helpers\Json::encode(Yii::$app->request->csrfToken);
$js = <<<JS
(function() {
    var confirmUrl = "{$confirmUrl}";
    var csrfParam = {$csrfParam};
    var csrfToken = {$csrfToken};
    document.querySelectorAll('.btn-action-meeting').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var status = this.getAttribute('data-status');
            var text = this.getAttribute('data-text') || (status === 'Pass' ? 'อนุมัติการจอง' : 'ยกเลิกการจอง');
            if (!confirm(text + ' ใช่หรือไม่?')) return;
            var card = this.closest('[data-meeting-id]');
            var formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            formData.append(csrfParam, csrfToken);
            fetch(confirmUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (res.ok) {
                    if (card) card.remove();
                    alert(res.message || 'ดำเนินการเรียบร้อย');
                } else {
                    alert(res.message || 'ดำเนินการไม่สำเร็จ');
                }
            }).catch(function() { alert('เกิดข้อผิดพลาด'); });
        });
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php endif; ?>
