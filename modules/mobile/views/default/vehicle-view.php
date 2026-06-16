<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Vehicle $vehicle */
/** @var \app\modules\hr\models\Employees|null $employee */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'รายละเอียดการจองรถ';
$this->params['mobileSubtitle'] = (string) ($vehicle->code ?? '');

$status = (string) $vehicle->status;
$statusMap = [
    'Pending' => ['label' => 'รออนุมัติ', 'tone' => 'warning', 'icon' => 'clock'],
    'รออนุมัติ' => ['label' => 'รออนุมัติ', 'tone' => 'warning', 'icon' => 'clock'],
    'Approve' => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'icon' => 'check-circle-2'],
    'Pass' => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'icon' => 'check-circle-2'],
    'อนุมัติแล้ว' => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'icon' => 'check-circle-2'],
    'Cancel' => ['label' => 'ยกเลิก', 'tone' => 'danger', 'icon' => 'x-circle'],
    'Reject' => ['label' => 'ปฏิเสธ', 'tone' => 'danger', 'icon' => 'x-circle'],
    'ยกเลิก' => ['label' => 'ยกเลิก', 'tone' => 'danger', 'icon' => 'x-circle'],
    'ปฏิเสธ' => ['label' => 'ปฏิเสธ', 'tone' => 'danger', 'icon' => 'x-circle'],
];
$statusInfo = $statusMap[$status] ?? ['label' => $status ?: '-', 'tone' => 'secondary', 'icon' => 'circle'];

$formatDate = static function (?string $date): string {
    if (!$date) return '-';
    try {
        return ThaiDateHelper::formatThaiDate($date, 'medium');
    } catch (\Throwable $e) {
        $ts = strtotime((string) $date);
        return $ts ? date('d/m/Y', $ts) : (string) $date;
    }
};

$location = (string) $vehicle->location;
try {
    if ($vehicle->locationOrg && !empty($vehicle->locationOrg->title)) {
        $location = (string) $vehicle->locationOrg->title;
    }
} catch (\Throwable $e) {}

$data = is_array($vehicle->data_json ?? null) ? $vehicle->data_json : [];
$phone = (string) ($data['phone'] ?? '');
$passengers = (string) ($data['passengers'] ?? '');
$notes = (string) ($data['notes'] ?? ($data['coment'] ?? ''));
$driver = (string) ($data['driver'] ?? '');
$dateRange = ((string) $vehicle->date_start === (string) $vehicle->date_end || !$vehicle->date_end)
    ? $formatDate((string) $vehicle->date_start)
    : ($formatDate((string) $vehicle->date_start) . ' ถึง ' . $formatDate((string) $vehicle->date_end));
$timeRange = trim(substr((string) $vehicle->time_start, 0, 5) . ' - ' . substr((string) $vehicle->time_end, 0, 5), ' -') . ' น.';
$canEdit = in_array($status, ['Pending', 'รออนุมัติ'], true);
?>

<style>
.vv-root {
    margin: -1rem -1rem 0;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
.vv-scroll {
    padding: calc(var(--shell-h, 13rem) + var(--space-md)) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.vv-page {
    display: grid;
    gap: var(--space-md);
}
.vv-back {
    min-height: 2.75rem;
    border-radius: 12px;
    align-self: start;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
}
.vv-hero,
.vv-card {
    background: var(--surface);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}
.vv-hero {
    padding: var(--space-md);
    display: grid;
    gap: var(--space-sm);
}
.vv-hero-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
}
.vv-medal {
    width: 3rem;
    height: 3rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.vv-medal svg { width: 1.35rem; height: 1.35rem; }
.vv-medal.is-warning { background: var(--warning-soft); color: var(--warning); }
.vv-medal.is-success { background: var(--success-soft); color: var(--success); }
.vv-medal.is-danger { background: var(--danger-soft); color: var(--danger-strong); }
.vv-medal.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }
.vv-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
    line-height: 1.3;
}
.vv-code {
    margin: 0.2rem 0 0;
    color: var(--ink-4);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 600;
}
.vv-pill {
    width: fit-content;
    border-radius: 999px;
    padding: 0.35rem 0.7rem;
    font-size: var(--fs-xs);
    font-weight: 700;
}
.vv-pill.is-warning { background: var(--warning-soft); color: var(--warning); }
.vv-pill.is-success { background: var(--success-soft); color: var(--success); }
.vv-pill.is-danger { background: var(--danger-soft); color: var(--danger-strong); }
.vv-pill.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }
.vv-card {
    padding: var(--space-md);
}
.vv-card-title {
    margin: 0 0 var(--space-sm);
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
}
.vv-row {
    display: grid;
    grid-template-columns: minmax(7rem, auto) 1fr;
    gap: var(--space-sm);
    padding: var(--space-xs) 0;
    border-bottom: 1px solid var(--ink-line);
}
.vv-row:last-child { border-bottom: 0; }
.vv-label {
    color: var(--ink-4);
    font-size: var(--fs-sm);
    font-weight: 600;
}
.vv-value {
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 700;
    text-align: right;
    overflow-wrap: anywhere;
}
.vv-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xs);
}
.vv-actions .btn {
    min-height: 3rem;
    border-radius: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
}
</style>

<div class="vv-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'car',
        'title'    => 'รายละเอียดการจองรถ',
        'subtitle' => (string) ($vehicle->code ?? ''),
    ]) ?>

    <div class="app-scroll vv-scroll">
        <div class="vv-page">
            <a href="<?= Html::encode(Url::to(['/mobile/default/booking-vehicle'])) ?>" class="btn btn-outline-secondary vv-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปรายการ</span>
            </a>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success rounded-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger rounded-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
            <?php endif; ?>

            <section class="vv-hero">
                <div class="vv-hero-head">
                    <span class="vv-medal is-<?= Html::encode($statusInfo['tone']) ?>" aria-hidden="true">
                        <i data-lucide="<?= Html::encode($statusInfo['icon']) ?>"></i>
                    </span>
                    <div class="min-w-0">
                        <h1 class="vv-title"><?= Html::encode($location !== '' ? 'ไป ' . $location : 'คำขอจองรถ') ?></h1>
                        <p class="vv-code"><?= Html::encode((string) $vehicle->code) ?></p>
                    </div>
                </div>
                <span class="vv-pill is-<?= Html::encode($statusInfo['tone']) ?>"><?= Html::encode($statusInfo['label']) ?></span>
            </section>

            <section class="vv-card">
                <h2 class="vv-card-title">ข้อมูลการเดินทาง</h2>
                <div class="vv-row"><span class="vv-label">วันที่</span><span class="vv-value"><?= Html::encode($dateRange) ?></span></div>
                <div class="vv-row"><span class="vv-label">เวลา</span><span class="vv-value"><?= Html::encode($timeRange) ?></span></div>
                <div class="vv-row"><span class="vv-label">ประเภท</span><span class="vv-value"><?= Html::encode($vehicle->viewGoType()) ?></span></div>
                <div class="vv-row"><span class="vv-label">จุดหมาย</span><span class="vv-value"><?= Html::encode($location ?: '-') ?></span></div>
                <div class="vv-row"><span class="vv-label">วัตถุประสงค์</span><span class="vv-value"><?= Html::encode((string) $vehicle->reason) ?></span></div>
            </section>

            <section class="vv-card">
                <h2 class="vv-card-title">ข้อมูลคำขอ</h2>
                <div class="vv-row"><span class="vv-label">ผู้ขอ</span><span class="vv-value"><?= Html::encode((string) ($employee->fullname ?? '-')) ?></span></div>
                <?php if ($phone !== ''): ?><div class="vv-row"><span class="vv-label">เบอร์ติดต่อ</span><span class="vv-value"><?= Html::encode($phone) ?></span></div><?php endif; ?>
                <?php if ($passengers !== ''): ?><div class="vv-row"><span class="vv-label">ผู้โดยสาร</span><span class="vv-value"><?= Html::encode($passengers) ?> คน</span></div><?php endif; ?>
                <div class="vv-row"><span class="vv-label">ความเร่งด่วน</span><span class="vv-value"><?= Html::encode((string) $vehicle->urgent) ?></span></div>
                <?php if ($driver !== ''): ?><div class="vv-row"><span class="vv-label">ผู้ขับ</span><span class="vv-value"><?= Html::encode($driver) ?></span></div><?php endif; ?>
                <?php if ($notes !== ''): ?><div class="vv-row"><span class="vv-label">หมายเหตุ</span><span class="vv-value"><?= Html::encode($notes) ?></span></div><?php endif; ?>
            </section>

            <?php if ($canEdit): ?>
                <div class="vv-actions">
                    <a href="<?= Html::encode(Url::to(['/mobile/default/vehicle-update', 'id' => $vehicle->id])) ?>" class="btn btn-primary">
                        <i data-lucide="pencil" aria-hidden="true"></i>
                        <span>แก้ไข</span>
                    </a>
                    <?= Html::beginForm(['/mobile/default/vehicle-cancel', 'id' => $vehicle->id], 'post', [
                        'id'    => 'vv-cancel-form',
                        'class' => 'm-0',
                        // กัน global loader (mobile-shared.js) ขึ้น overlay บัง SwAL dialog
                        'data'  => ['no-loader' => 'true'],
                    ]) ?>
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i data-lucide="x-circle" aria-hidden="true"></i>
                            <span>ยกเลิกการจอง</span>
                        </button>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Cancel confirmation: pattern เดียวกับ meeting-view.php (กัน Telegram WebView quirks)
$this->registerJs(<<<'JS'
(function(){
    var form = document.getElementById('vv-cancel-form');
    if (!form) return;
    form.addEventListener('submit', function(e){
        if (form.dataset.confirmed === '1') return;
        e.preventDefault();
        var doSubmit = function(){
            form.dataset.confirmed = '1';
            if (typeof window.showMobileNavLoading === 'function') {
                window.showMobileNavLoading('กำลังบันทึก...');
            }
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันยกเลิกคำขอ?',
                text: 'การยกเลิกไม่สามารถกู้คืน',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยกเลิกการจอง',
                cancelButtonText: 'ไม่, เก็บไว้',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(function(r){ if (r.isConfirmed) doSubmit(); });
        } else {
            if (window.confirm('ยืนยันยกเลิกคำขอจองรถ? การยกเลิกไม่สามารถกู้คืน')) doSubmit();
        }
    });
})();
JS
, \yii\web\View::POS_READY); ?>
