<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\mobile\services\MobileApprovalService;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve $approve */
/** @var object|null $parent */
/** @var array{title:string,requester:string,requesterAvatar:string,createdAt:string,summary:string} $meta */
/** @var app\modules\approveV2\models\Approve[] $timeline */
/** @var MobileApprovalService $service */

$this->params['current_page'] = $current_page ?? 'services';

$typeMeta    = MobileApprovalService::typeMeta();
$name        = (string) $approve->name;
$typeInfo    = $typeMeta[$name] ?? ['label' => $name, 'icon' => 'file-text', 'cat' => 'document'];
$typeLabel   = (string) $typeInfo['label'];
$typeIcon    = (string) $typeInfo['icon'];
$typeCat     = (string) $typeInfo['cat'];

$status      = $service->statusInfo((string) $approve->status);
$isPending   = (string) $approve->status === 'Pending';

$approveData = is_array($approve->data_json ?? null) ? $approve->data_json : [];
$levelLabel  = (string) ($approveData['label'] ?? $approve->title ?? ('ระดับ ' . (int) $approve->level));
$comment     = trim((string) ($approve->comment ?? ''));

$requesterName     = (string) ($meta['requester'] ?? '-');
$requesterAvatar   = (string) ($meta['requesterAvatar'] ?? '');
if ($requesterAvatar === '') {
    $requesterAvatar = \Yii::getAlias('@web') . '/img/placeholder_cid.png';
}
$requesterPosition = '';
try {
    if ($parent && property_exists($parent, 'employee') && $parent->employee && method_exists($parent->employee, 'positionName')) {
        $requesterPosition = (string) $parent->employee->positionName();
    }
} catch (\Throwable $e) { $requesterPosition = ''; }

$this->params['mobileTitle']    = 'อนุมัติ ' . $typeLabel;
$this->params['mobileSubtitle'] = $requesterName !== '-' ? 'ผู้ขอ ' . $requesterName : 'พิจารณาคำขอ';

$backUrl = Url::to(['/mobile/default/approvals', 'bucket' => 'pending']);

// Field rows for the request card — แต่ละประเภทใส่ข้อมูลที่เหมาะสม
$fieldRows = [];
try {
    if ($name === 'leave' && $parent) {
        $modelData = is_array($parent->data_json ?? null) ? $parent->data_json : [];
        $leaveType = (string) ($parent->leaveType->title ?? 'ใบลา');
        $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $parent->showLeaveDate())));
        $totalDays = (float) ($parent->total_days ?? 0);
        $reason    = trim((string) ($modelData['reason'] ?? ''));
        $fieldRows[] = ['label' => 'ประเภทการลา', 'value' => $leaveType, 'wide' => false];
        $fieldRows[] = ['label' => 'จำนวนวัน', 'value' => rtrim(rtrim(number_format($totalDays, 1), '0'), '.') . ' วัน', 'wide' => false];
        if ($dateRange !== '') $fieldRows[] = ['label' => 'ช่วงเวลาที่ลา', 'value' => $dateRange, 'wide' => true];
        if ($reason !== '')    $fieldRows[] = ['label' => 'เหตุผล', 'value' => $reason, 'wide' => true];
    } elseif ($name === 'vehicle' && $parent) {
        $vData = is_array($parent->data_json ?? null) ? $parent->data_json : [];
        $car = (string) ($parent->car ?? '');
        $code = (string) ($parent->code ?? '');
        $purpose = (string) ($vData['purpose'] ?? $parent->title ?? '');
        $fieldRows[] = ['label' => 'รหัสคำขอ', 'value' => $code ?: '-', 'wide' => false];
        $fieldRows[] = ['label' => 'รถยนต์', 'value' => $car ?: '-', 'wide' => false];
        if ($purpose !== '') $fieldRows[] = ['label' => 'วัตถุประสงค์', 'value' => $purpose, 'wide' => true];
    } elseif ($name === 'asset-move' && $parent) {
        $aData = is_array($parent->data_json ?? null) ? $parent->data_json : [];
        $from = (string) ($aData['from_location'] ?? $aData['from'] ?? '');
        $to   = (string) ($aData['to_location']   ?? $aData['to']   ?? '');
        if ($from !== '') $fieldRows[] = ['label' => 'จากตำแหน่ง', 'value' => $from, 'wide' => true];
        if ($to !== '')   $fieldRows[] = ['label' => 'ไปยังตำแหน่ง', 'value' => $to, 'wide' => true];
    } elseif ($name === 'purchase' && $parent) {
        $code = (string) ($parent->code ?? '');
        $title = (string) ($parent->title ?? '');
        $fieldRows[] = ['label' => 'รหัสคำขอ', 'value' => $code ?: '-', 'wide' => false];
        if ($title !== '') $fieldRows[] = ['label' => 'รายการ', 'value' => $title, 'wide' => true];
    } elseif ($name === 'development' && $parent) {
        $title = (string) ($parent->title ?? '');
        $topic = (string) ($parent->topic ?? '');
        if ($title !== '') $fieldRows[] = ['label' => 'หัวข้อ', 'value' => $title, 'wide' => true];
        if ($topic !== '') $fieldRows[] = ['label' => 'รายละเอียด', 'value' => $topic, 'wide' => true];
    }
} catch (\Throwable $e) { /* ignore */ }

$fieldRows[] = ['label' => 'ขั้นตอนที่รออนุมัติ', 'value' => $levelLabel, 'wide' => true, 'primary' => true];
if (!empty($meta['createdAt']) && $meta['createdAt'] !== '-') {
    $fieldRows[] = ['label' => 'วันที่ส่งคำขอ', 'value' => (string) $meta['createdAt'], 'wide' => false];
}
?>

<style>
.avd-root {
    margin: -1rem -1rem 0;
}
.avd-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9rem);
}
.avd-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.avd-back {
    min-height: 2.75rem;
    width: fit-content;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 700;
    box-shadow: 0 1px 0 var(--ink-line);
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.avd-back svg { width: 1.125rem; height: 1.125rem; }
.avd-card {
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.avd-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-md);
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
    background: linear-gradient(180deg, var(--surface) 0%, var(--surface-2) 100%);
}
.avd-card-title {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.3;
}
.avd-card-title svg {
    width: 1.125rem;
    height: 1.125rem;
    color: var(--mobile-primary);
}
.avd-card-body { padding: var(--space-md); }
.avd-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
}
.avd-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.avd-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.avd-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.avd-pill[data-tone="info"]      { background: color-mix(in oklch, oklch(0.55 0.13 240) 14%, transparent); color: oklch(0.45 0.13 240); }
.avd-pill[data-tone="secondary"], .avd-pill[data-tone="primary"] {
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.avd-person {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}
.avd-person-avatar {
    position: relative;
    width: 4rem;
    height: 4rem;
    border-radius: 18px;
    flex: 0 0 auto;
    overflow: hidden;
    border: 1px solid var(--ink-line);
    background: var(--surface-2);
    box-shadow: 0 8px 22px color-mix(in oklch, var(--ink) 8%, transparent);
}
.avd-person-avatar img { width: 100%; height: 100%; display: block; object-fit: cover; }
.avd-person-avatar-badge {
    position: absolute;
    right: -2px; bottom: -2px;
    width: 1.5rem; height: 1.5rem;
    border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface);
    background: var(--mobile-primary); color: #fff;
}
.avd-person-avatar-badge svg { width: 0.85rem; height: 0.85rem; }
.avd-person-name { margin: 0; color: var(--ink); font-size: var(--fs-lg); font-weight: 800; line-height: 1.3; text-wrap: pretty; }
.avd-person-role { margin: 2px 0 0; color: var(--ink-3); font-size: var(--fs-xs); line-height: 1.4; }
.avd-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-sm);
    margin-top: var(--space-md);
}
.avd-field { min-width: 0; }
.avd-field.is-wide { grid-column: 1 / -1; }
.avd-label { color: var(--ink-4); font-size: var(--fs-2xs); font-weight: 800; line-height: 1.35; letter-spacing: 0.02em; }
.avd-value {
    margin-top: 3px;
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 700;
    line-height: 1.5;
    overflow-wrap: anywhere;
}
.avd-value.is-primary { color: var(--mobile-primary); }
.avd-summary {
    margin-top: var(--space-md);
    padding: var(--space-sm) var(--space-md);
    background: var(--surface-2);
    border-radius: 12px;
    color: var(--ink-2);
    font-size: var(--fs-sm);
    line-height: 1.55;
    border-left: 3px solid var(--mobile-primary);
}

/* Timeline */
.avd-timeline { display: flex; flex-direction: column; gap: 0; padding: var(--space-2xs) 0; }
.avd-tl-row {
    display: grid;
    grid-template-columns: 2.25rem 1fr auto;
    gap: var(--space-sm);
    padding: var(--space-xs) 0;
}
.avd-tl-rail {
    position: relative;
    display: flex; justify-content: center;
}
.avd-tl-rail::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 2px;
    background: var(--ink-line);
}
.avd-tl-row:first-child .avd-tl-rail::before { top: 1.1rem; }
.avd-tl-row:last-child .avd-tl-rail::before  { bottom: calc(100% - 1.1rem); }
.avd-tl-dot {
    position: relative;
    width: 1.5rem;
    height: 1.5rem;
    margin-top: 0.35rem;
    border-radius: 999px;
    background: var(--surface);
    border: 2px solid var(--ink-line);
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ink-4);
    z-index: 1;
    transition: transform 200ms cubic-bezier(0.16, 1, 0.3, 1), border-color 200ms cubic-bezier(0.16, 1, 0.3, 1), background 200ms cubic-bezier(0.16, 1, 0.3, 1);
}
.avd-tl-dot svg { width: 0.85rem; height: 0.85rem; }
.avd-tl-dot[data-tone="warning"]  { background: var(--warning); border-color: var(--warning); color: #fff; }
.avd-tl-dot[data-tone="success"]  { background: var(--success); border-color: var(--success); color: #fff; }
.avd-tl-dot[data-tone="danger"]   { background: var(--danger);  border-color: var(--danger);  color: #fff; }
.avd-tl-dot[data-tone="info"]     { background: oklch(0.55 0.13 240); border-color: oklch(0.55 0.13 240); color: #fff; }
.avd-tl-row.is-current .avd-tl-dot {
    box-shadow: 0 0 0 4px color-mix(in oklch, var(--warning) 18%, transparent);
}
.avd-tl-body { min-width: 0; }
.avd-tl-title { color: var(--ink); font-size: var(--fs-sm); font-weight: 800; line-height: 1.4; }
.avd-tl-person { margin-top: 2px; color: var(--ink-3); font-size: var(--fs-xs); line-height: 1.4; }
.avd-tl-note {
    margin-top: 4px;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.5;
    background: var(--surface-2);
    border-radius: 8px;
    padding: 4px 8px;
}
.avd-tl-status { align-self: flex-start; margin-top: 0.45rem; }

/* Sticky action bar */
.avd-actions {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    z-index: var(--z-sticky, 50);
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--space-xs);
    padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
    background: color-mix(in oklch, var(--surface) 92%, transparent);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 -1px 0 var(--ink-line), 0 -10px 30px color-mix(in oklch, var(--ink) 8%, transparent);
}
.avd-actions .btn {
    min-height: 3.25rem;
    border-radius: 14px;
    font-size: var(--fs-md);
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    line-height: 1.1;
    padding: 0 var(--space-xs);
    transition: transform 140ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 140ms cubic-bezier(0.16, 1, 0.3, 1);
}
.avd-actions .btn svg { width: 1.125rem; height: 1.125rem; }
.avd-actions .btn:active { transform: translateY(1px) scale(0.985); }
.avd-actions .btn-primary { box-shadow: 0 4px 12px color-mix(in oklch, var(--mobile-primary) 35%, transparent); }
.avd-status-banner {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-md);
    border-radius: 14px;
    font-size: var(--fs-sm);
    font-weight: 700;
}
.avd-status-banner[data-tone="success"] { background: var(--success-soft); color: var(--success); }
.avd-status-banner[data-tone="danger"]  { background: var(--danger-soft);  color: var(--danger-strong); }
.avd-status-banner[data-tone="info"]    { background: color-mix(in oklch, oklch(0.55 0.13 240) 12%, transparent); color: oklch(0.45 0.13 240); }
.avd-status-banner svg { width: 1.375rem; height: 1.375rem; flex-shrink: 0; }

/* Reason modal */
.avd-modal-form { display: flex; flex-direction: column; gap: var(--space-sm); }
.avd-modal-textarea {
    width: 100%;
    min-height: 7rem;
    padding: var(--space-sm) var(--space-md);
    border-radius: 12px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    color: var(--ink);
    font-size: var(--fs-md);
    line-height: 1.5;
    resize: vertical;
    transition: border-color 160ms ease-out, box-shadow 160ms ease-out;
}
.avd-modal-textarea:focus {
    outline: none;
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft);
}
.avd-modal-hint { color: var(--ink-3); font-size: var(--fs-xs); line-height: 1.5; }

@media (hover: hover) {
    .avd-back:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
    .avd-actions .btn:hover { box-shadow: var(--shadow-md); }
}
@media (prefers-reduced-motion: reduce) {
    .avd-back, .avd-card, .avd-tl-row, .avd-tl-dot, .avd-actions, .avd-actions .btn {
        transition: none !important;
        animation: none !important;
    }
    .avd-back:hover { transform: none !important; }
}
@media (prefers-reduced-motion: no-preference) {
    .avd-card {
        animation: avd-card-in 260ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--avd-i, 0) * 40ms);
    }
    .avd-tl-row {
        animation: avd-row-in 240ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--avd-tl, 0) * 50ms + 220ms);
    }
    .avd-actions {
        animation: avd-bar-up 320ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: 120ms;
    }
    .avd-tl-row.is-current .avd-tl-dot {
        animation: avd-pulse 1800ms cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .avd-status-banner svg {
        animation: avd-bounce 600ms cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
}
@keyframes avd-card-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes avd-row-in {
    from { opacity: 0; transform: translateX(-6px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes avd-bar-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes avd-pulse {
    0%, 100% { box-shadow: 0 0 0 4px color-mix(in oklch, var(--warning) 18%, transparent); }
    50%      { box-shadow: 0 0 0 8px color-mix(in oklch, var(--warning) 10%, transparent); }
}
@keyframes avd-bounce {
    0%   { opacity: 0; transform: scale(0.4); }
    60%  { opacity: 1; transform: scale(1.15); }
    100% { transform: scale(1); }
}

/* Success drawing animation (Swal-injected, scoped to .avd-checkmark) */
.avd-checkmark { width: 80px; height: 80px; border-radius: 50%; display: block; stroke-width: 4; stroke: var(--success); stroke-miterlimit: 10; box-shadow: inset 0 0 0 var(--success); animation: avd-fill .35s ease-in-out .35s forwards, avd-scale .25s ease-in-out .7s both; margin: 0 auto var(--space-md); }
.avd-checkmark-circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 4; stroke-miterlimit: 10; stroke: var(--success); fill: none; animation: avd-stroke .55s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
.avd-checkmark-check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: avd-stroke .25s cubic-bezier(0.65, 0, 0.45, 1) .65s forwards; }
@keyframes avd-stroke { 100% { stroke-dashoffset: 0; } }
@keyframes avd-scale  { 0%,100% { transform: none; } 50% { transform: scale3d(1.08,1.08,1); } }
@keyframes avd-fill   { 100% { box-shadow: inset 0 0 0 40px color-mix(in oklch, var(--success) 12%, transparent); } }
</style>

<div class="avd-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'       => $typeIcon,
        'title'      => $typeLabel,
        'subtitle'   => $requesterName !== '-' ? 'ผู้ขอ ' . $requesterName : 'รายละเอียดคำขอ',
        'stats'      => [
            ['value' => (string) $meta['createdAt'], 'label' => 'วันที่ส่งคำขอ', 'tone' => 'primary'],
            ['value' => (string) $status['label'],   'label' => 'สถานะปัจจุบัน', 'tone' => $status['tone']],
        ],
        'statsLabel' => 'สรุปงานอนุมัติ',
    ]) ?>

    <div class="app-scroll has-stats avd-scroll">
        <div class="avd-body">
            <a href="<?= Html::encode($backUrl) ?>" class="btn btn-outline-secondary avd-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปรายการ</span>
            </a>

            <?php if (!$isPending): ?>
                <div class="avd-status-banner" data-tone="<?= Html::encode($status['tone']) ?>" role="status">
                    <i data-lucide="<?= $status['tone'] === 'success' ? 'check-circle-2' : ($status['tone'] === 'danger' ? 'x-circle' : 'info') ?>" aria-hidden="true"></i>
                    <div>
                        <div>รายการนี้ถูก<?= Html::encode($status['label']) ?>แล้ว</div>
                        <?php if ($comment !== ''): ?>
                            <div style="margin-top:4px;font-weight:500;color:inherit;opacity:0.85"><?= Html::encode($comment) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <section class="avd-card" style="--avd-i: 0" aria-labelledby="avd-req-title">
                <header class="avd-card-head">
                    <h2 class="avd-card-title" id="avd-req-title">
                        <i data-lucide="file-text" aria-hidden="true"></i>
                        ข้อมูลคำขอ
                    </h2>
                    <span class="avd-pill" data-tone="<?= Html::encode($status['tone']) ?>">
                        <?= Html::encode($status['label']) ?>
                    </span>
                </header>
                <div class="avd-card-body">
                    <div class="avd-person">
                        <span class="avd-person-avatar" aria-hidden="true">
                            <?= Html::img($requesterAvatar, [
                                'alt' => '',
                                'loading' => 'eager',
                                'decoding' => 'async',
                            ]) ?>
                            <span class="avd-person-avatar-badge">
                                <i data-lucide="user-check" aria-hidden="true"></i>
                            </span>
                        </span>
                        <div class="min-w-0">
                            <p class="avd-person-name"><?= Html::encode($requesterName) ?></p>
                            <?php if ($requesterPosition !== ''): ?>
                                <p class="avd-person-role"><?= Html::encode($requesterPosition) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($fieldRows)): ?>
                        <div class="avd-grid">
                            <?php foreach ($fieldRows as $row): ?>
                                <div class="avd-field<?= !empty($row['wide']) ? ' is-wide' : '' ?>">
                                    <div class="avd-label"><?= Html::encode((string) $row['label']) ?></div>
                                    <div class="avd-value<?= !empty($row['primary']) ? ' is-primary' : '' ?>">
                                        <?= Html::encode((string) $row['value']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['summary']) && $meta['summary'] !== '' && empty($fieldRows)): ?>
                        <div class="avd-summary"><?= Html::encode((string) $meta['summary']) ?></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="avd-card" style="--avd-i: 1" aria-labelledby="avd-tl-title">
                <header class="avd-card-head">
                    <h2 class="avd-card-title" id="avd-tl-title">
                        <i data-lucide="workflow" aria-hidden="true"></i>
                        ลำดับการอนุมัติ
                    </h2>
                    <span class="avd-pill" data-tone="primary">
                        <?= (int) count($timeline) ?> ขั้นตอน
                    </span>
                </header>
                <div class="avd-card-body">
                    <?php if (empty($timeline)): ?>
                        <p class="avd-modal-hint" style="margin:0">ยังไม่มีข้อมูลลำดับการอนุมัติ</p>
                    <?php else: ?>
                        <div class="avd-timeline" role="list">
                            <?php foreach ($timeline as $tIdx => $tItem):
                                $tStatus = $service->statusInfo((string) $tItem->status);
                                $tData   = is_array($tItem->data_json ?? null) ? $tItem->data_json : [];
                                $tLabel  = (string) ($tData['label'] ?? $tItem->title ?? ('ระดับ ' . (int) $tItem->level));
                                $tPerson = '';
                                try {
                                    $tPerson = (string) ($tItem->employee->fullname ?? '');
                                } catch (\Throwable $e) { $tPerson = ''; }
                                $tComment = trim((string) ($tItem->comment ?? ''));
                                $isCurrent = ((int) $tItem->id === (int) $approve->id);
                                $isPast    = (string) $tItem->status !== 'Pending' && (string) $tItem->status !== 'None';
                                $dotIcon = match (true) {
                                    $tStatus['tone'] === 'success' => 'check',
                                    $tStatus['tone'] === 'danger'  => 'x',
                                    $tStatus['tone'] === 'info'    => 'rotate-ccw',
                                    $isCurrent                     => 'clock-3',
                                    default                        => 'circle',
                                };
                            ?>
                                <div class="avd-tl-row<?= $isCurrent ? ' is-current' : '' ?>" role="listitem" style="--avd-tl: <?= (int) min($tIdx, 8) ?>">
                                    <div class="avd-tl-rail">
                                        <span class="avd-tl-dot" data-tone="<?= $isPast || $isCurrent ? Html::encode($tStatus['tone']) : '' ?>">
                                            <i data-lucide="<?= Html::encode($dotIcon) ?>" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <div class="avd-tl-body">
                                        <div class="avd-tl-title"><?= Html::encode($tLabel) ?></div>
                                        <div class="avd-tl-person"><?= Html::encode($tPerson !== '' ? $tPerson : 'รอมอบหมาย') ?></div>
                                        <?php if ($tComment !== ''): ?>
                                            <div class="avd-tl-note"><i data-lucide="message-square" class="me-1" style="width:0.75rem;height:0.75rem;display:inline-block;vertical-align:middle"></i> <?= Html::encode($tComment) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="avd-pill avd-tl-status" data-tone="<?= Html::encode($tStatus['tone']) ?>">
                                        <?= Html::encode($tStatus['label']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <?php if ($isPending): ?>
        <div class="avd-actions" role="group" aria-label="ดำเนินการอนุมัติ">
            <button type="button" class="btn btn-outline-danger avd-act" data-action="Reject">
                <i data-lucide="x-circle" aria-hidden="true"></i>
                ไม่อนุมัติ
            </button>
            <button type="button" class="btn btn-outline-warning avd-act" data-action="SendBack">
                <i data-lucide="rotate-ccw" aria-hidden="true"></i>
                ส่งคืน
            </button>
            <button type="button" class="btn btn-primary avd-act" data-action="Pass">
                <i data-lucide="check-circle-2" aria-hidden="true"></i>
                อนุมัติ
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
$updateUrl   = Url::to(['/mobile/default/approval-update', 'id' => (int) $approve->id]);
$redirectUrl = Url::to(['/mobile/default/approvals', 'bucket' => 'pending']);
$typeLabelJs = addslashes($typeLabel);
$js = <<<JS
(function () {
    const updateUrl   = '{$updateUrl}';
    const redirectUrl = '{$redirectUrl}';
    const typeLabel   = '{$typeLabelJs}';

    const actionMeta = {
        Pass:     { label: 'อนุมัติ',      icon: 'success',  confirmText: 'ยืนยันการอนุมัติ', needsReason: false, confirmBtn: 'อนุมัติ', confirmColor: 'var(--mobile-primary)' },
        Reject:   { label: 'ไม่อนุมัติ',   icon: 'error',    confirmText: 'ยืนยันไม่อนุมัติ', needsReason: true,  confirmBtn: 'ไม่อนุมัติ', confirmColor: 'var(--danger)' },
        SendBack: { label: 'ส่งคืนแก้ไข', icon: 'warning',  confirmText: 'ยืนยันส่งคืน',    needsReason: true,  confirmBtn: 'ส่งคืน',   confirmColor: 'var(--warning)' },
    };

    function showSuccess(message) {
        return Swal.fire({
            html: '<svg class="avd-checkmark" viewBox="0 0 52 52" aria-hidden="true">'
                + '<circle class="avd-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>'
                + '<path class="avd-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>'
                + '</svg>'
                + '<p style="margin:0;color:var(--ink);font-size:1.05rem;font-weight:800">' + (message || 'บันทึกสำเร็จ') + '</p>'
                + '<p style="margin:.35rem 0 0;color:var(--ink-3);font-size:.85rem">กำลังกลับสู่รายการ…</p>',
            showConfirmButton: false,
            timer: 1500,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: { popup: 'avd-swal-success' },
        });
    }

    function submitApproval(action, comment) {
        const meta = actionMeta[action];
        Swal.fire({
            title: 'กำลังบันทึก…',
            html: '<div style="color:var(--ink-3);font-size:.9rem">โปรดรอสักครู่</div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            type: 'POST',
            url: updateUrl,
            data: { status: action, comment: comment || '' },
            dataType: 'json',
        }).done(function (resp) {
            if (resp && resp.status === 'success') {
                showSuccess(resp.message || meta.label + 'เรียบร้อย').then(() => {
                    window.location.href = resp.redirect || redirectUrl;
                });
                return;
            }
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: (resp && resp.message) ? resp.message : 'ไม่สามารถบันทึกข้อมูลได้',
                confirmButtonText: 'ตกลง',
            });
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อไม่สำเร็จ',
                text: 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองอีกครั้ง',
                confirmButtonText: 'ตกลง',
            });
        });
    }

    function openConfirm(action) {
        const meta = actionMeta[action];

        if (!meta.needsReason) {
            Swal.fire({
                title: meta.confirmText,
                html: '<div style="color:var(--ink-3);font-size:.95rem;line-height:1.5">' + meta.label + ' ' + typeLabel + ' ของผู้ขอนี้ใช่หรือไม่?</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: meta.confirmBtn,
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                confirmButtonColor: meta.confirmColor,
            }).then((result) => {
                if (result.isConfirmed) submitApproval(action, '');
            });
            return;
        }

        Swal.fire({
            title: meta.confirmText,
            html:
                '<form class="avd-modal-form" autocomplete="off" onsubmit="return false;">'
                + '<label for="avd-reason" style="display:block;text-align:left;font-weight:700;font-size:.85rem;color:var(--ink-2)">'
                + 'เหตุผล <span style="color:var(--danger)">*</span></label>'
                + '<textarea id="avd-reason" class="avd-modal-textarea" placeholder="กรุณาระบุเหตุผล&hellip;" maxlength="500"></textarea>'
                + '<p class="avd-modal-hint" style="text-align:left;margin:0">ระบบจะบันทึกเหตุผลนี้ลงในประวัติการอนุมัติ และแจ้งผู้ขอ</p>'
                + '</form>',
            showCancelButton: true,
            confirmButtonText: meta.confirmBtn,
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            confirmButtonColor: meta.confirmColor,
            focusConfirm: false,
            didOpen: () => {
                const ta = document.getElementById('avd-reason');
                if (ta) {
                    setTimeout(() => ta.focus(), 200);
                    ta.addEventListener('input', function () {
                        Swal.resetValidationMessage();
                    });
                }
            },
            preConfirm: () => {
                const ta = document.getElementById('avd-reason');
                const value = (ta && ta.value || '').trim();
                if (value === '') {
                    Swal.showValidationMessage('กรุณาระบุเหตุผลก่อนบันทึก');
                    return false;
                }
                if (value.length < 3) {
                    Swal.showValidationMessage('เหตุผลสั้นเกินไป กรุณาเขียนให้ชัดเจน');
                    return false;
                }
                return value;
            },
        }).then((result) => {
            if (result.isConfirmed && result.value) submitApproval(action, result.value);
        });
    }

    $('body').on('click', '.avd-act', function () {
        const action = $(this).data('action');
        if (!actionMeta[action]) return;
        openConfirm(action);
    });
})();
JS;
$this->registerJs($js, View::POS_END);
?>
