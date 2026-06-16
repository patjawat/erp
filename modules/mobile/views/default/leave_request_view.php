<?php

use app\components\ThaiDateHelper;
use app\modules\mobile\services\MobileBookingStatus;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\leave\models\Leave $model */

$this->params['current_page']    = $current_page ?? 'profile';
$this->params['mobileTitle']     = 'รายละเอียดใบลา';
$this->params['mobileSubtitle']  = 'ติดตามสถานะและรายละเอียดคำขอ';

$attachments    = $model->getAttachmentList();
$approvals      = $model->listApprove();
$leaveTypeName  = '';
try { $leaveTypeName = (string) ($model->leaveType->title ?? ''); } catch (\Throwable $e) {}
if ($leaveTypeName === '') { $leaveTypeName = 'การลา'; }

$createdAtText = '-';
if (!empty($model->created_at)) {
    $createdAtText = ThaiDateHelper::formatThaiDate((string) $model->created_at, 'long')
        . ' ' . date('H:i', strtotime((string) $model->created_at)) . ' น.';
}

// ── ข้อมูลผู้ขอ ────────────────────────────────────────────────
$requesterName     = '-';
$requesterPosition = '';
$requesterAvatarUrl = \Yii::getAlias('@web') . '/img/placeholder_cid.png';
try {
    $requesterEmployee = $model->employee ?? null;
    if ($requesterEmployee) {
        $requesterName = (string) ($requesterEmployee->fullname ?? '-');
        if (method_exists($requesterEmployee, 'positionName')) {
            $requesterPosition = (string) $requesterEmployee->positionName();
        }
        if (method_exists($requesterEmployee, 'ShowAvatar')) {
            $avatar = (string) $requesterEmployee->ShowAvatar();
            if ($avatar !== '') {
                $requesterAvatarUrl = $avatar;
            }
        }
    }
} catch (\Throwable $e) { /* keep defaults */ }

$contactPhone   = $model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '';
$contactAddress = $model->data_json['address'] ?? '';
$placeGo        = $model->data_json['place_go'] ?? '';
$reason         = $model->data_json['reason'] ?? '';
$totalDays      = (float) ($model->total_days ?? 0);
$status         = (string) $model->status;

$dStart = (string) $model->date_start;
$dEnd   = (string) $model->date_end;
$dateRangeText = '—';
try {
    if ($dStart && $dEnd) {
        $sStr = ThaiDateHelper::formatThaiDate($dStart, 'long');
        $eStr = ThaiDateHelper::formatThaiDate($dEnd, 'long');
        $dateRangeText = ($dStart === $dEnd) ? $sStr : ($sStr . ' ถึง ' . $eStr);
    }
} catch (\Throwable $e) {
    $dateRangeText = $dStart . ($dEnd && $dEnd !== $dStart ? ' ถึง ' . $dEnd : '');
}

// แมพ status → tone/label โดยเพิ่ม ReqCancel/Save/Checking ที่ taxonomy กลางยังไม่ครอบ
$reqCancelStatuses = ['ReqCancel'];
$checkingStatuses  = ['Checking', 'Checking1_pass', 'Checking2_pass', 'Checkup_pass',
                      'Checking1_reject', 'Checking2_reject', 'Checkup_reject'];
$statusInfo = MobileBookingStatus::info($status);
if (in_array($status, $reqCancelStatuses, true)) {
    $statusInfo = ['label' => 'รออนุมัติยกเลิก', 'tone' => 'warning', 'bucket' => 'pending'];
} elseif (in_array($status, $checkingStatuses, true)) {
    $statusInfo = ['label' => 'อยู่ระหว่างตรวจสอบ', 'tone' => 'warning', 'bucket' => 'pending'];
} elseif ($status === 'Save' || $status === 'Draft') {
    $statusInfo = ['label' => 'บันทึกร่าง', 'tone' => 'secondary', 'bucket' => 'other'];
}
$statusLabel = (string) ($statusInfo['label'] ?? $status);
$statusTone  = (string) ($statusInfo['tone']  ?? 'secondary');

// สิทธิ์การกระทำ
$canEdit       = in_array($status, ['Pending', 'Save', 'Draft'], true);
$canReqCancel  = in_array($status, ['Pending', 'Approve'], true);

$editUrl       = Url::to(['/mobile/default/leave-request-edit', 'id' => $model->id]);
$reqCancelUrl  = Url::to(['/leave/leave/req-cancel', 'id' => $model->id]);
$backUrl       = Url::to(['/mobile/default/my-requests', 'type' => 'leave']);
$csrfParam     = Yii::$app->request->csrfParam;
$csrfToken     = Yii::$app->request->csrfToken;
?>

<style>
/* ───── Leave detail view (lv-) ───── */
.lv-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.lv-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9.5rem); }

/* Hero overlay (slot ของ _hero_shell.overlayHtml) — ใช้ pattern เดียวกับ .app-stats:
   ลอยทับโค้ง hero ด้วย negative margin, z-index 2, อยู่ใน fixed app-shell
   ตัว container ภายนอกรับ margin/shadow/background; ตัวลูก (.lv-hero-overlay) คุมเลย์เอาท์ในการ์ด */
.lv-hero-overlay-shell {
    position: relative; z-index: 2;
    margin: -1.75rem var(--space-md) 0;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12), 0 2px 6px rgba(15, 23, 42, 0.04);
    padding: var(--space-md);
    animation: lv-overlay-in 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes lv-overlay-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.lv-hero-overlay {
    display: flex; flex-direction: column; gap: var(--space-xs);
}
.lv-hero-top {
    display: flex; align-items: center; justify-content: space-between; gap: var(--space-sm);
}
.lv-hero-type {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink);
    line-height: 1.25; word-break: break-word;
}
.lv-status-pill {
    flex-shrink: 0;
    border-radius: 999px; padding: 4px 12px;
    font-size: var(--fs-2xs); font-weight: 700;
    display: inline-flex; align-items: center; gap: 4px;
    line-height: 1.4;
}
.lv-status-pill[data-tone="warning"]   { background: var(--warning-soft); color: var(--warning); }
.lv-status-pill[data-tone="success"]   { background: var(--success-soft); color: var(--success); }
.lv-status-pill[data-tone="danger"]    { background: var(--danger-soft);  color: var(--danger-strong); }
.lv-status-pill[data-tone="secondary"] {
    background: color-mix(in oklch, var(--ink-4) 16%, transparent);
    color: var(--ink-3);
}
.lv-status-pill svg { width: 12px; height: 12px; }

.lv-hero-days {
    display: flex; align-items: baseline; gap: 6px;
    color: var(--mobile-primary);
}
.lv-hero-days-num {
    font-size: 2rem; font-weight: 800; line-height: 1;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}
.lv-hero-days-lbl { font-size: var(--fs-sm); color: var(--ink-3); font-weight: 600; }
.lv-hero-range {
    font-size: var(--fs-sm); color: var(--ink-2); line-height: 1.45;
}

/* Stack of detail cards */
.lv-body { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }
.lv-card {
    background: var(--surface);
    border-radius: 16px;
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
}
.lv-card-head {
    display: flex; align-items: center; gap: var(--space-xs);
    padding-bottom: var(--space-xs); margin-bottom: var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
}
.lv-card-title {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink);
    margin: 0; display: inline-flex; align-items: center; gap: var(--space-xs);
}
.lv-card-title svg {
    width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);
}

/* Person block — avatar + ชื่อผู้ขอ + ตำแหน่ง (อยู่ส่วนบนของ card "ข้อมูลคำขอ") */
.lv-person {
    display: flex; align-items: center; gap: var(--space-sm);
    padding-bottom: var(--space-sm); margin-bottom: var(--space-sm);
    border-bottom: 1px dashed var(--ink-line);
}
.lv-person-avatar {
    position: relative;
    width: 3.25rem; height: 3.25rem; border-radius: 16px;
    flex: 0 0 auto;
    overflow: hidden;
    border: 1px solid var(--ink-line);
    background: var(--surface-2);
    box-shadow: 0 4px 14px color-mix(in oklch, var(--ink) 7%, transparent);
}
.lv-person-avatar img {
    width: 100%; height: 100%; display: block; object-fit: cover;
}
.lv-person-avatar-badge {
    position: absolute; right: -2px; bottom: -2px;
    width: 1.125rem; height: 1.125rem; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface);
    background: var(--mobile-primary); color: #fff;
}
.lv-person-avatar-badge svg { width: 0.625rem; height: 0.625rem; }
.lv-person-meta { min-width: 0; flex: 1; }
.lv-person-label {
    color: var(--ink-4); font-size: var(--fs-2xs); font-weight: 700;
    line-height: 1.3; letter-spacing: 0.02em;
}
.lv-person-name {
    margin: 2px 0 0; color: var(--ink);
    font-size: var(--fs-md); font-weight: 800; line-height: 1.3;
    text-wrap: pretty; word-break: break-word;
}
.lv-person-role {
    margin: 2px 0 0; color: var(--ink-3);
    font-size: var(--fs-xs); line-height: 1.4;
}
@media (prefers-reduced-motion: no-preference) {
    .lv-person-avatar { animation: lv-person-rise 320ms cubic-bezier(0.16, 1, 0.3, 1) both; }
}
@keyframes lv-person-rise {
    from { opacity: 0; transform: translateY(4px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Definition list — label/value rows */
.lv-dl {
    display: grid; grid-template-columns: minmax(7.5rem, 35%) 1fr;
    gap: var(--space-xs) var(--space-md);
    font-size: var(--fs-sm);
}
.lv-dl dt { color: var(--ink-4); font-weight: 500; margin: 0; }
.lv-dl dd {
    color: var(--ink); font-weight: 600; margin: 0; word-break: break-word;
    text-wrap: pretty;
}
.lv-dl dd.is-muted { color: var(--ink-4); font-weight: 500; }

.lv-reason {
    background: var(--surface-2); border-radius: 12px;
    padding: var(--space-sm) var(--space-md);
    font-size: var(--fs-sm); color: var(--ink); line-height: 1.55;
    white-space: pre-wrap; word-break: break-word;
}

/* Approval timeline */
.lv-approvals { display: flex; flex-direction: column; gap: var(--space-sm); }
.lv-approval-row {
    display: flex; align-items: flex-start; gap: var(--space-sm);
    padding: var(--space-sm); border-radius: 12px;
    background: var(--surface-2);
}
.lv-approval-row[data-tone="success"] { background: var(--success-soft); }
.lv-approval-row[data-tone="danger"]  { background: var(--danger-soft); }
.lv-approval-row[data-tone="warning"] { background: var(--warning-soft); }
.lv-approval-dot {
    width: 2rem; height: 2rem; border-radius: 50%;
    background: var(--surface); flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    font-weight: 700; color: var(--ink-3);
}
.lv-approval-dot img { width: 100%; height: 100%; object-fit: cover; }
.lv-approval-meta { flex: 1; min-width: 0; }
.lv-approval-title { font-size: var(--fs-sm); font-weight: 700; color: var(--ink); line-height: 1.35; }
.lv-approval-name  { font-size: var(--fs-xs); color: var(--ink-3); margin-top: 2px; line-height: 1.4; }
.lv-approval-time  { font-size: var(--fs-2xs); color: var(--ink-4); margin-top: 4px; }
.lv-approval-status {
    font-size: var(--fs-2xs); font-weight: 700; padding: 3px 10px;
    border-radius: 999px; align-self: flex-start; flex-shrink: 0;
    line-height: 1.4;
}
.lv-approval-status[data-tone="success"] { background: var(--surface); color: var(--success); }
.lv-approval-status[data-tone="warning"] { background: var(--surface); color: var(--warning); }
.lv-approval-status[data-tone="danger"]  { background: var(--surface); color: var(--danger-strong); }
.lv-approval-status[data-tone="secondary"] { background: var(--surface); color: var(--ink-3); }

/* Attachments */
.lv-files { display: flex; flex-direction: column; gap: var(--space-xs); }
.lv-file-link {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    border-radius: 12px; background: var(--surface-2);
    color: var(--ink); text-decoration: none;
    transition: background 200ms cubic-bezier(0.22, 1, 0.36, 1);
    min-height: 3rem;
}
.lv-file-link:active { background: color-mix(in oklch, var(--mobile-primary) 12%, var(--surface-2)); }
.lv-file-icon {
    width: 2rem; height: 2rem; border-radius: 8px;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lv-file-icon svg { width: 1rem; height: 1rem; }
.lv-file-name {
    flex: 1; min-width: 0;
    font-size: var(--fs-sm); font-weight: 600; color: var(--ink);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.lv-file-ext {
    font-size: var(--fs-2xs); font-weight: 700; color: var(--ink-4);
    text-transform: uppercase; letter-spacing: 0.04em;
}

.lv-empty {
    text-align: center; padding: var(--space-md) 0;
    color: var(--ink-4); font-size: var(--fs-sm);
}

/* Action bar */
.lv-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: flex; flex-direction: column; gap: var(--space-xs);
}
.lv-actions-row { display: flex; gap: var(--space-xs); }
.lv-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs); flex: 1;
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease-out, transform 120ms ease-out;
}
.lv-actions .btn:active { transform: scale(0.985); }
.lv-actions .btn-edit {
    background: var(--mobile-primary); color: #fff; border: 0;
}
.lv-actions .btn-cancel {
    background: var(--danger-soft); color: var(--danger-strong); border: 0;
}
.lv-actions .btn-back {
    background: var(--surface-2); color: var(--ink-2); border: 0;
}
.lv-actions .btn.is-busy { opacity: 0.7; pointer-events: none; }

@media (prefers-reduced-motion: reduce) {
    .lv-hero-overlay-shell { animation: none !important; transform: none !important; }
    .lv-actions .btn { transition: none !important; }
    .lv-file-link { transition: none !important; }
}
</style>

<?php
// สร้าง overlay HTML แล้วส่งเป็น slot ให้ _hero_shell ใส่ภายใน .app-shell
// (ลอยทับโค้ง hero ด้วย negative margin ตาม pattern .app-stats)
ob_start(); ?>
<div class="lv-hero-overlay-shell">
    <div class="lv-hero-overlay" aria-label="ภาพรวมใบลา">
        <div class="lv-hero-top">
            <div class="lv-hero-type"><?= Html::encode($leaveTypeName) ?></div>
            <span class="lv-status-pill" data-tone="<?= Html::encode($statusTone) ?>">
                <?= Html::encode($statusLabel) ?>
            </span>
        </div>
        <div class="lv-hero-days">
            <span class="lv-hero-days-num"><?= rtrim(rtrim(number_format($totalDays, 1), '0'), '.') ?></span>
            <span class="lv-hero-days-lbl">วันลา</span>
        </div>
        <div class="lv-hero-range"><?= Html::encode($dateRangeText) ?></div>
    </div>
</div>
<?php $overlayHtml = ob_get_clean(); ?>

<div class="lv-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => 'calendar-check-2',
        'title'       => 'รายละเอียดใบลา',
        'subtitle'    => $this->params['mobileSubtitle'],
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll has-overlay lv-scroll">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success rounded-3 mx-3 mt-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
        <?php endif; ?>

        <div class="lv-body">

            <!-- ข้อมูลคำขอ -->
            <section class="lv-card" aria-labelledby="lv-card-info">
                <header class="lv-card-head">
                    <h2 class="lv-card-title" id="lv-card-info">
                        <i data-lucide="file-text"></i>
                        ข้อมูลคำขอ
                    </h2>
                </header>

                <!-- ผู้ขอลา (avatar + ชื่อ + ตำแหน่ง) -->
                <div class="lv-person" aria-label="ผู้ขอลา">
                    <span class="lv-person-avatar" aria-hidden="true">
                        <?= Html::img($requesterAvatarUrl, [
                            'alt'      => '',
                            'loading'  => 'eager',
                            'decoding' => 'async',
                        ]) ?>
                        <span class="lv-person-avatar-badge">
                            <i data-lucide="user" aria-hidden="true"></i>
                        </span>
                    </span>
                    <div class="lv-person-meta">
                        <div class="lv-person-label">ผู้ขอลา</div>
                        <p class="lv-person-name"><?= Html::encode($requesterName) ?></p>
                        <?php if ($requesterPosition !== ''): ?>
                            <p class="lv-person-role"><?= Html::encode($requesterPosition) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <dl class="lv-dl">
                    <dt>เลขที่คำขอ</dt>
                    <dd>#<?= Html::encode((string) $model->id) ?></dd>

                    <dt>วันที่ส่งคำขอ</dt>
                    <dd><?= Html::encode($createdAtText) ?></dd>

                    <dt>ประเภทการลา</dt>
                    <dd><?= Html::encode($leaveTypeName) ?></dd>

                    <dt>ช่วงเวลาที่ลา</dt>
                    <dd><?= Html::encode($dateRangeText) ?></dd>

                    <?php if ($placeGo): ?>
                        <dt>สถานที่ไป</dt>
                        <dd><?= Html::encode((string) $placeGo) ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <!-- เหตุผล -->
            <section class="lv-card" aria-labelledby="lv-card-reason">
                <header class="lv-card-head">
                    <h2 class="lv-card-title" id="lv-card-reason">
                        <i data-lucide="message-square-text"></i>
                        เหตุผลการลา
                    </h2>
                </header>
                <?php if ($reason): ?>
                    <div class="lv-reason"><?= Html::encode((string) $reason) ?></div>
                <?php else: ?>
                    <div class="lv-empty">ไม่มีข้อมูลเหตุผล</div>
                <?php endif; ?>
            </section>

            <!-- ติดต่อระหว่างลา -->
            <?php if ($contactPhone || $contactAddress): ?>
                <section class="lv-card" aria-labelledby="lv-card-contact">
                    <header class="lv-card-head">
                        <h2 class="lv-card-title" id="lv-card-contact">
                            <i data-lucide="phone"></i>
                            ติดต่อระหว่างลา
                        </h2>
                    </header>
                    <dl class="lv-dl">
                        <?php if ($contactPhone): ?>
                            <dt>เบอร์โทรศัพท์</dt>
                            <dd>
                                <a href="tel:<?= Html::encode(preg_replace('/[^\d+]/', '', (string) $contactPhone)) ?>"
                                   class="text-decoration-none" style="color: var(--mobile-primary);">
                                    <?= Html::encode((string) $contactPhone) ?>
                                </a>
                            </dd>
                        <?php endif; ?>
                        <?php if ($contactAddress): ?>
                            <dt>ที่อยู่</dt>
                            <dd><?= Html::encode((string) $contactAddress) ?></dd>
                        <?php endif; ?>
                    </dl>
                </section>
            <?php endif; ?>

            <!-- ลำดับการอนุมัติ -->
            <section class="lv-card" aria-labelledby="lv-card-approve">
                <header class="lv-card-head">
                    <h2 class="lv-card-title" id="lv-card-approve">
                        <i data-lucide="git-branch"></i>
                        ลำดับการอนุมัติ
                    </h2>
                </header>
                <?php if (!empty($approvals)): ?>
                    <div class="lv-approvals">
                        <?php foreach ($approvals as $item):
                            $aStatus = (string) ($item->status ?? '');
                            $aTone = 'secondary';
                            $aLabel = $aStatus;
                            switch ($aStatus) {
                                case 'Pass':    case 'Approve': $aTone = 'success'; $aLabel = 'อนุมัติแล้ว'; break;
                                case 'Pending': $aTone = 'warning'; $aLabel = 'รอ'; break;
                                case 'Reject':  $aTone = 'danger';  $aLabel = 'ปฏิเสธ'; break;
                                case 'None':    $aTone = 'secondary'; $aLabel = 'ยังไม่ถึงคิว'; break;
                            }
                            $rowTone = $aStatus === 'Pending' ? 'warning' : 'secondary';
                            $approverName = $item->employee->fullname ?? 'รอมอบหมาย';
                            $titleText = $item->title ?: ($item->data_json['label'] ?? 'ผู้อนุมัติ');
                            $avatarSrc = '';
                            try { $avatarSrc = $item->employee ? $item->employee->showAvatar() : ''; } catch (\Throwable $e) {}
                            $approveTime = method_exists($item, 'viewApproveDate') ? $item->viewApproveDate() : '';
                        ?>
                            <div class="lv-approval-row" data-tone="<?= Html::encode($rowTone) ?>">
                                <span class="lv-approval-dot" aria-hidden="true">
                                    <?php if ($avatarSrc): ?>
                                        <img src="<?= Html::encode($avatarSrc) ?>" alt="">
                                    <?php else: ?>
                                        <i data-lucide="user" style="width:1rem;height:1rem;"></i>
                                    <?php endif; ?>
                                </span>
                                <div class="lv-approval-meta">
                                    <div class="lv-approval-title"><?= Html::encode((string) $titleText) ?></div>
                                    <div class="lv-approval-name"><?= Html::encode((string) $approverName) ?></div>
                                    <?php if ($approveTime): ?>
                                        <div class="lv-approval-time">ทำรายการ <?= Html::encode($approveTime) ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="lv-approval-status" data-tone="<?= Html::encode($aTone) ?>">
                                    <?= Html::encode($aLabel) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="lv-empty">ยังไม่มีลำดับการอนุมัติ</div>
                <?php endif; ?>
            </section>

            <!-- เอกสารแนบ -->
            <section class="lv-card" aria-labelledby="lv-card-files">
                <header class="lv-card-head">
                    <h2 class="lv-card-title" id="lv-card-files">
                        <i data-lucide="paperclip"></i>
                        เอกสารแนบ
                    </h2>
                </header>
                <?php if (!empty($attachments)): ?>
                    <div class="lv-files">
                        <?php foreach ($attachments as $attachment):
                            $fileName = (string) $attachment->file_name;
                            $ext = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
                        ?>
                            <a class="lv-file-link"
                               href="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $attachment->id])) ?>"
                               target="_blank" rel="noopener noreferrer">
                                <span class="lv-file-icon" aria-hidden="true">
                                    <i data-lucide="file"></i>
                                </span>
                                <span class="lv-file-name"><?= Html::encode($fileName) ?></span>
                                <span class="lv-file-ext"><?= Html::encode($ext) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="lv-empty">ไม่มีเอกสารแนบ</div>
                <?php endif; ?>
            </section>

        </div>
    </div>

    <!-- Sticky action bar: แก้ไข / ขอยกเลิก / กลับ -->
    <div class="lv-actions" role="toolbar" aria-label="การกระทำกับใบลา">
        <?php if ($canEdit || $canReqCancel): ?>
            <div class="lv-actions-row">
                <?php if ($canEdit): ?>
                    <a href="<?= Html::encode($editUrl) ?>" class="btn btn-edit">
                        <i data-lucide="pencil" aria-hidden="true"></i>
                        <span>แก้ไขคำขอลา</span>
                    </a>
                <?php endif; ?>
                <?php if ($canReqCancel): ?>
                    <button type="button" class="btn btn-cancel" id="lv-req-cancel-btn"
                            data-url="<?= Html::encode($reqCancelUrl) ?>"
                            data-csrf-param="<?= Html::encode($csrfParam) ?>"
                            data-csrf-token="<?= Html::encode($csrfToken) ?>">
                        <i data-lucide="x-circle" aria-hidden="true"></i>
                        <span>ขอยกเลิกการลา</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <a href="<?= Html::encode($backUrl) ?>" class="btn btn-back">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            <span>กลับไปคำขอของฉัน</span>
        </a>
    </div>
</div>

<?php
// ───── ขอยกเลิกการลา flow — Swal confirm style เดียวกับ handleFormSubmit ─────
$cancelJs = <<<'JS'
(function(){
    var btn = document.getElementById('lv-req-cancel-btn');
    if (!btn) return;
    btn.addEventListener('click', function(){
        if (btn.classList.contains('is-busy')) return;
        var url       = btn.dataset.url || '';
        var csrfParam = btn.dataset.csrfParam || '';
        var csrfToken = btn.dataset.csrfToken || '';
        if (!url) return;

        function runCancel() {
            btn.classList.add('is-busy');
            Swal.fire({
                title: 'กำลังส่งคำขอยกเลิก',
                text: 'ระบบกำลังบันทึกการขอยกเลิกของคุณ...',
                allowOutsideClick: false,
                didOpen: function(){ Swal.showLoading(); },
            });
            var fd = new FormData();
            fd.append(csrfParam, csrfToken);
            fetch(url, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
            .then(function(r){ return r.json().catch(function(){ return { status: 'error' }; }); })
            .then(function(res){
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งคำขอยกเลิกเรียบร้อย',
                        text: 'ระบบจะแจ้งผู้อนุมัติให้พิจารณายกเลิก',
                        timer: 1600,
                        showConfirmButton: false,
                    }).then(function(){ window.location.reload(); });
                } else {
                    btn.classList.remove('is-busy');
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถส่งคำขอยกเลิกได้',
                        text: (res && res.message) ? res.message : 'กรุณาลองใหม่อีกครั้ง',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33',
                    });
                }
            })
            .catch(function(){
                btn.classList.remove('is-busy');
                Swal.fire({
                    icon: 'error',
                    title: 'การเชื่อมต่อขัดข้อง',
                    text: 'ไม่สามารถติดต่อ Server ได้ กรุณาลองใหม่',
                    confirmButtonText: 'รับทราบ',
                });
            });
        }

        if (typeof Swal === 'undefined') {
            if (window.confirm('ยืนยันขอยกเลิกการลานี้?')) runCancel();
            return;
        }

        Swal.fire({
            title: 'ยืนยันขอยกเลิกการลา?',
            text: 'คำขอนี้จะถูกส่งให้ผู้อนุมัติพิจารณายกเลิก หลังกดยืนยันแล้วไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-x-circle me-1"></i> ยืนยันยกเลิก',
            cancelButtonText: 'ไม่ใช่ตอนนี้',
            reverseButtons: false,
        }).then(function(r){
            if (r.isConfirmed) runCancel();
        });
    });
})();
JS;
$this->registerJs($cancelJs, View::POS_END);
