<?php

use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\leave\models\Leave $model */
/** @var array $types */
/** @var array $stats */
/** @var string|null $draftRef */
/** @var string $leaveWorkSendInitText */
/** @var string $leaveSendInitAvatar */
/** @var array $approveChain */
/** @var \app\modules\hr\models\Employees|null $employee */
/** @var \app\modules\leave\models\Leave[] $myLeaves   รายการคำขอ (controller เตรียมให้) */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ขอลาออนไลน์';

$employee              = $employee ?? null;
$types                 = $types ?? [];
$stats                 = $stats ?? [];
$draftRef              = $draftRef ?? null;
$leaveWorkSendInitText = $leaveWorkSendInitText ?? '';
$leaveSendInitAvatar   = $leaveSendInitAvatar ?? '';
$approveChain          = $approveChain ?? [];
$myLeaves              = $myLeaves ?? [];
$fiscalYears           = $fiscalYears ?? [];
$filterYear            = (int) ($filterYear ?? 0);
$currentYear           = (int) ($currentYear ?? 0);

$isUpdate = $model instanceof \app\modules\leave\models\Leave && !$model->isNewRecord;

// Mode detection — list (default) / wizard (?action=new หรือมี edit)
$actionParam = (string) (Yii::$app->request->get('action') ?? '');
$mode = ($actionParam === 'new' || $isUpdate) ? 'wizard' : 'list';

$this->params['mobileSubtitle'] = $mode === 'wizard'
    ? 'กรอกข้อมูลทีละขั้นตอน'
    : 'รายการคำขอของฉัน';

// Leave-type options for wizard mode
$typeOptions = $mode === 'wizard' ? $model->listLeaveType() : [];

$attrWorkShift     = 'data_json[work_shift]';
$attrDateStartType = 'data_json[date_start_type]';
$attrDateEndType   = 'data_json[date_end_type]';

$initSatsun  = 0;
$initHoliday = 0;
$initTotal   = 0;
$remainingAnnualLeave = -1;
if ($isUpdate && is_array($model->data_json ?? null)) {
    $initSatsun  = (int) ($model->data_json['summary_sat_sun'] ?? $model->data_json['sat_sun_days'] ?? 0);
    $initHoliday = (int) ($model->data_json['summary_holiday'] ?? $model->data_json['holidays'] ?? 0);
    $initTotal   = (float) ($model->total_days ?? 0);
}
foreach ($stats as $row) {
    if (($row['code'] ?? '') === 'LT4') {
        $remainingAnnualLeave = max(0, (float) ($row['entitlement_days'] ?? 0) - (float) ($row['used_days'] ?? 0));
        break;
    }
}

$existingSigType = is_array($model->data_json ?? null) ? ($model->data_json['signature_type'] ?? 'canvas') : 'canvas';
$existingSigData = is_array($model->data_json ?? null) ? ($model->data_json['signature_data'] ?? '') : '';
$signatureSystemUrl = null;
if ($employee && method_exists($employee, 'SignatureShow')) {
    try { $signatureSystemUrl = $employee->SignatureShow(); } catch (\Throwable $e) {}
}

$existingWorkShift   = is_array($model->data_json ?? null) ? ($model->data_json['work_shift']      ?? 'normal') : 'normal';
$existingDateStartT  = is_array($model->data_json ?? null) ? ($model->data_json['date_start_type'] ?? '0')      : '0';
$existingDateEndT    = is_array($model->data_json ?? null) ? ($model->data_json['date_end_type']   ?? '0')      : '0';
$existingPlaceGo     = is_array($model->data_json ?? null) ? ($model->data_json['place_go']        ?? '')       : '';

$formatJs = <<<'JS'
var formatRepo = function (repo) {
    if (repo.loading) return repo.avatar;
    var markup = '<div class="row"><div class="col-12"><span>' + (repo.avatar || '') + '</span></div></div>';
    if (repo.description) markup += '<p>' + (repo.avatar || '') + '</p>';
    return '<div style="overflow:hidden;">' + markup + '</div>';
};
var formatRepoSelection = function (repo) { return repo.avatar || repo.text; };
JS;
$this->registerJs($formatJs, View::POS_HEAD);

$resultsJs = <<<'JS'
function (data, params) {
    params.page = params.page || 1;
    return {
        results: data.results,
        pagination: { more: (params.page * 30) < data.total_count }
    };
}
JS;

$baseUrl = Url::to(['/mobile/default/leave-request']);
$newUrl  = Url::to(['/mobile/default/leave-request', 'action' => 'new']);

// Hero stats overlay สำหรับ list mode
$bucketCounts = $mode === 'list' ? MobileBookingStatus::bucketCounts($myLeaves) : ['all' => 0, 'pending' => 0, 'approved' => 0, 'cancelled' => 0];
?>

<style>
/* ───── Leave root + scroll ───── */
.bl-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.bl-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 10rem); }
.bl-mode[hidden] { display: none !important; }

/* ───── List toolbar (filter year) ───── */
.bl-list-toolbar {
    position: sticky; top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}

/* ───── List cards ───── */
.bl-list { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-sm); }
.bl-list-card {
    display: flex; flex-direction: column; gap: var(--space-xs);
    padding: var(--space-md);
    border-radius: 14px; border: 1px solid var(--ink-line);
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    color: inherit; text-decoration: none;
}
.bl-list-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: var(--space-sm);
}
.bl-list-type {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink); line-height: 1.3;
    word-break: break-word;
}
.bl-list-pill {
    flex-shrink: 0; border-radius: 999px; padding: 4px 10px;
    font-size: var(--fs-2xs); font-weight: 700;
}
.bl-list-pill.is-warning { background: var(--warning-soft); color: var(--warning); }
.bl-list-pill.is-success { background: var(--success-soft); color: var(--success); }
.bl-list-pill.is-danger  { background: var(--danger-soft); color: var(--danger-strong); }
.bl-list-pill.is-secondary { background: color-mix(in oklch, var(--ink-4) 15%, transparent); color: var(--ink-3); }
.bl-list-meta { display: flex; flex-wrap: wrap; gap: var(--space-2xs) var(--space-md); font-size: var(--fs-xs); color: var(--ink-3); }
.bl-list-meta-item { display: inline-flex; align-items: center; gap: 4px; }
.bl-list-meta-item svg { width: 12px; height: 12px; color: var(--ink-4); }

.bl-list-empty {
    margin: var(--space-lg) var(--space-md) 0;
    padding: var(--space-2xl) var(--space-md); text-align: center;
    border-radius: 16px; background: var(--surface);
    box-shadow: var(--shadow-sm);
}
.bl-list-empty-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 4rem; height: 4rem; border-radius: 50%;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    margin-bottom: var(--space-md);
}
.bl-list-empty-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); margin: 0 0 var(--space-2xs); }
.bl-list-empty-text { font-size: var(--fs-sm); color: var(--ink-3); margin: 0 auto; max-width: 30ch; line-height: 1.55; }

/* ───── Wizard (mirror bv-/bm- patterns) ───── */
.bl-wizard {
    position: sticky; top: 0;
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md) var(--space-md) var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
    box-shadow: 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
    margin: 0 calc(-1 * var(--space-md));
}
.bl-wizard-track {
    position: relative; height: 4px;
    background: var(--surface-3); border-radius: 999px;
    overflow: hidden; margin-bottom: var(--space-sm);
}
.bl-wizard-fill {
    position: absolute; inset: 0 auto 0 0;
    width: 14.2857%;
    background: var(--mobile-primary);
    border-radius: 999px;
    transition: width 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bl-wizard-steps {
    list-style: none; margin: 0; padding: 0;
    display: grid; grid-template-columns: repeat(7, 1fr);
    gap: var(--space-2xs);
}
.bl-wizard-step {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    text-align: center; color: var(--ink-4);
    font-size: 0.6875rem; font-weight: 500; line-height: 1.2;
    min-height: 2.75rem;
}
.bl-wizard-pip {
    width: 1.5rem; height: 1.5rem; border-radius: 50%;
    background: var(--surface-3); color: var(--ink-4);
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.75rem;
    transition: background 240ms, color 240ms;
}
.bl-wizard-pip svg { width: 14px; height: 14px; }
.bl-wizard-step.is-active .bl-wizard-pip {
    background: var(--mobile-primary); color: #fff;
    box-shadow: 0 0 0 4px color-mix(in oklch, var(--mobile-primary) 12%, transparent);
}
.bl-wizard-step.is-active { color: var(--mobile-primary); font-weight: 600; }
.bl-wizard-step.is-done .bl-wizard-pip { background: var(--success); color: #fff; }
.bl-wizard-step.is-done { color: var(--ink-2); }
.bl-wizard-step.is-done .bl-wizard-pip-num { display: none; }
.bl-wizard-step:not(.is-done) .bl-wizard-pip-check { display: none; }

/* Compact stepper <380px */
.bl-stepper-compact { display: none; }
.bl-stepper-compact-label {
    display: flex; justify-content: space-between; align-items: baseline; gap: var(--space-xs);
    margin-top: var(--space-xs);
    font-size: var(--fs-xs); color: var(--ink-3);
}
.bl-stepper-compact-num strong { color: var(--mobile-primary); font-weight: 700; font-variant-numeric: tabular-nums; }
.bl-stepper-compact-name { color: var(--ink-2); font-size: var(--fs-sm); font-weight: 700; }
.bl-progress-track { position: relative; height: 4px; border-radius: 999px; background: var(--surface-3); overflow: hidden; }
.bl-progress-fill {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 14.2857%;
    background: linear-gradient(90deg, var(--mobile-primary) 0%, color-mix(in oklch, var(--mobile-primary) 70%, white) 100%);
    border-radius: 999px;
    transition: width 360ms cubic-bezier(0.22, 1, 0.36, 1);
}

.bl-body { padding-top: var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }
.bl-panel { display: flex; flex-direction: column; gap: var(--space-md); }
.bl-panel[hidden] { display: none !important; }
.bl-panel.is-active { animation: bl-step-enter 280ms cubic-bezier(0.22, 1, 0.36, 1); }
@keyframes bl-step-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bl-panel-head { padding: 0 var(--space-2xs); }
.bl-panel-eyebrow { font-size: var(--fs-xs); font-weight: 500; color: var(--mobile-primary); margin: 0 0 4px; letter-spacing: 0.02em; }
.bl-panel-title { font-size: var(--fs-xl); font-weight: 700; color: var(--ink); margin: 0 0 4px; line-height: 1.2; text-wrap: balance; }
.bl-panel-desc { font-size: var(--fs-sm); color: var(--ink-3); margin: 0; line-height: 1.45; }

.bl-card {
    background: var(--surface);
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: var(--shadow-md);
}
.bl-card .form-control, .bl-card .form-select {
    border-radius: 12px; padding: 0.75rem 1rem; min-height: 3rem;
}
.bl-card .form-label { font-weight: 500; font-size: var(--fs-sm); color: var(--ink-2); margin-bottom: var(--space-xs); }
.form-label.req::after { content: ' *'; color: var(--danger); font-weight: 700; }

/* Summary stats (step 2) */
.bl-summary-stats {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-sm);
    margin: var(--space-md) 0 var(--space-sm);
}
.bl-stat-box {
    background: var(--surface-2); border-radius: 12px; padding: var(--space-sm);
    text-align: center;
    display: flex; flex-direction: column; gap: 2px;
    position: relative; overflow: hidden;
    transition: box-shadow 280ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bl-stat-value {
    font-size: var(--fs-xl); font-weight: 700; color: var(--ink); line-height: 1.1;
    transition: opacity 240ms cubic-bezier(0.22, 1, 0.36, 1), transform 280ms cubic-bezier(0.22, 1, 0.36, 1);
    transform-origin: center;
}
.bl-stat-label { font-size: var(--fs-xs); color: var(--ink-3); }
.bl-stat-box.is-calculating .bl-stat-value { opacity: 0.45; }
.bl-stat-box.is-calculating::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(
        100deg,
        transparent 30%,
        color-mix(in oklch, var(--mobile-primary) 18%, transparent) 50%,
        transparent 70%
    );
    background-size: 220% 100%;
    animation: bl-shimmer 1.25s linear infinite;
    pointer-events: none;
}
@keyframes bl-shimmer {
    0%   { background-position: 220% 0; }
    100% { background-position: -120% 0; }
}
.bl-stat-box.is-fresh .bl-stat-value {
    animation: bl-stat-pop 420ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes bl-stat-pop {
    0%   { opacity: 0.4; transform: scale(0.94); }
    55%  { opacity: 1;   transform: scale(1.05); }
    100% { opacity: 1;   transform: scale(1); }
}

/* Zero-day inline hint — สำหรับกรณีดึง total ได้ 0 (เสาร์อาทิตย์ล้วน, ครึ่งวันชนกัน) */
.bl-zero-day-hint {
    display: flex; gap: var(--space-xs); align-items: flex-start;
    border-radius: 12px; padding: var(--space-xs) var(--space-sm);
    background: var(--warning-soft); color: var(--warning);
    margin-top: var(--space-sm);
    font-size: var(--fs-sm); line-height: 1.45;
    animation: bl-hint-in 240ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bl-zero-day-hint[hidden] { display: none !important; }
.bl-zero-day-hint svg { flex-shrink: 0; margin-top: 2px; }
.bl-zero-day-hint strong { font-weight: 700; }
@keyframes bl-hint-in {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bl-annual-alert {
    display: none; gap: var(--space-xs); align-items: flex-start;
    border-radius: 12px; padding: var(--space-xs) var(--space-sm);
    font-size: var(--fs-sm); margin-top: var(--space-sm);
}
.bl-annual-alert.is-info  { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.bl-annual-alert.is-warn  { background: var(--warning-soft); color: var(--warning); }
.bl-annual-alert.is-error { background: var(--danger-soft); color: var(--danger-strong); }

/* Step 6 signature canvas */
.bl-sig-canvas-wrap {
    border: 1px dashed var(--ink-line); border-radius: 12px;
    background: var(--surface);
    overflow: hidden;
}
.bl-sig-canvas-wrap canvas { width: 100%; height: auto; display: block; touch-action: none; cursor: crosshair; }
.bl-sig-system-preview { padding: var(--space-md); border: 1px solid var(--ink-line); border-radius: 12px; text-align: center; }
.bl-sig-system-preview img { max-height: 8rem; max-width: 100%; }

/* Step 7 summary */
.bl-summary-card {
    background: var(--surface); border-radius: 16px; box-shadow: var(--shadow-sm);
    overflow: hidden; padding: var(--space-md);
}
.bl-summary-row { display: flex; justify-content: space-between; gap: var(--space-md); padding: var(--space-xs) 0; border-bottom: 1px solid var(--ink-line); font-size: var(--fs-sm); }
.bl-summary-row:last-child { border-bottom: 0; }
.bl-summary-label { color: var(--ink-4); font-weight: 500; flex-shrink: 0; }
.bl-summary-value { color: var(--ink); font-weight: 600; text-align: right; word-break: break-word; }
.bl-summary-head { display: flex; align-items: center; padding-bottom: var(--space-xs); border-bottom: 1px solid var(--ink-line); margin-bottom: var(--space-xs); }
.bl-summary-title { font-size: var(--fs-md); font-weight: 700; color: var(--ink); margin: 0; display: flex; align-items: center; gap: var(--space-xs); }
.bl-summary-title svg { color: var(--mobile-primary); width: 1.125rem; height: 1.125rem; }
.bl-summary-body { padding-top: var(--space-xs); }

/* Approval workflow chain in step 7 */
.bl-workflow { display: grid; grid-template-columns: repeat(auto-fit, minmax(4.5rem, 1fr)); gap: var(--space-xs); position: relative; }
.bl-step-chip { display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center; }
.bl-step-dot {
    width: 2.25rem; height: 2.25rem; border-radius: 50%;
    background: var(--surface-2); overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.bl-step-dot img { width: 100%; height: 100%; object-fit: cover; }
.bl-step-name { font-size: var(--fs-xs); color: var(--ink-3); line-height: 1.25; }

.bl-confirm-card {
    background: var(--surface); border-radius: 16px; padding: var(--space-md);
    box-shadow: var(--shadow-md);
}
.bl-confirm-row {
    display: flex; align-items: flex-start; gap: var(--space-sm);
    padding: var(--space-sm); margin: 0;
    border-radius: 12px; cursor: pointer;
}
.bl-confirm-row input[type="checkbox"] {
    flex-shrink: 0; width: 1.25rem; height: 1.25rem; margin-top: 2px;
    accent-color: var(--mobile-primary); cursor: pointer;
}
.bl-confirm-text { font-size: var(--fs-sm); color: var(--ink); line-height: 1.55; }

.bl-step-error {
    display: none; margin-top: var(--space-sm);
    border-radius: 12px; background: var(--danger-soft); color: var(--danger-strong);
    padding: var(--space-xs) var(--space-sm);
    font-size: var(--fs-sm); line-height: 1.45;
}
.bl-step-error.is-visible { display: block; }

.bl-upload-hint {
    display: flex; gap: var(--space-xs); align-items: flex-start;
    padding: var(--space-xs) var(--space-sm); margin-bottom: var(--space-sm);
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    border-radius: 12px; font-size: var(--fs-xs);
}

/* ───── Action bar ───── */
.bl-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: grid; gap: var(--space-xs);
}
.bl-actions[data-mode="list"] { grid-template-columns: 1fr; }
.bl-actions[data-mode="wizard"][data-step="1"] { grid-template-columns: 1fr; }
.bl-actions[data-mode="wizard"]:not([data-step="1"]) { grid-template-columns: auto 1fr; }
.bl-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs);
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease-out,
                box-shadow 200ms ease-out;
}
.bl-actions .btn-prev { padding: 0 var(--space-md); background: var(--surface-2); border-color: var(--surface-2); color: var(--ink-2); }
.bl-mode-action[data-for-mode] { display: none; }
.bl-actions[data-mode="list"]   .bl-mode-action[data-for-mode~="list"]   { display: inline-flex; }
.bl-actions[data-mode="wizard"] .bl-mode-action[data-for-mode~="wizard"] { display: inline-flex; }
.bl-actions[data-mode="wizard"][data-step="1"] #bl-prev { display: none; }
.bl-actions[data-mode="wizard"] #bl-next   { display: inline-flex; }
.bl-actions[data-mode="wizard"] #bl-submit { display: none; }
.bl-actions[data-mode="wizard"][data-step="7"] #bl-next   { display: none; }
.bl-actions[data-mode="wizard"][data-step="7"] #bl-submit { display: inline-flex; }
.bl-actions .btn[disabled], .bl-actions .btn.is-busy { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 380px) {
    .bl-wizard { display: none; }
    .bl-stepper-compact { display: block; padding: var(--space-md) var(--space-md) var(--space-sm); }
}
@media (prefers-reduced-motion: reduce) {
    .bl-wizard-fill, .bl-progress-fill, .bl-wizard-pip { transition: none !important; }
    .bl-panel.is-active { animation: none !important; }
    .bl-stat-box.is-calculating::after { animation: none !important; opacity: 0.45; }
    .bl-stat-box.is-fresh .bl-stat-value { animation: none !important; }
    .bl-zero-day-hint { animation: none !important; }
    .bl-actions .btn { transition: none !important; }
}
</style>

<div class="bl-root" data-mode="<?= Html::encode($mode) ?>">

    <?php
    $heroStats = [];
    if ($mode === 'list' && (int) $bucketCounts['all'] >= 3) {
        $heroStats = [
            ['value' => (int) $bucketCounts['all'],      'label' => 'ทั้งหมด',     'tone' => 'primary'],
            ['value' => (int) $bucketCounts['pending'],  'label' => 'รออนุมัติ',   'tone' => 'warning'],
            ['value' => (int) $bucketCounts['approved'], 'label' => 'อนุมัติแล้ว', 'tone' => 'success'],
        ];
    }
    ?>

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => $isUpdate ? 'pencil' : 'calendar-off',
        'title'    => $isUpdate ? 'แก้ไขคำขอลา' : 'ขอลาออนไลน์',
        'subtitle' => $this->params['mobileSubtitle'],
        'stats'    => $heroStats,
    ]) ?>

    <div class="app-scroll bl-scroll">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success rounded-3 mx-3 mt-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
        <?php endif; ?>

        <?php if ($mode === 'list'): ?>
            <?= $this->render('_partials/_leave_list', [
                'myLeaves'    => $myLeaves,
                'fiscalYears' => $fiscalYears,
                'filterYear'  => $filterYear,
            ]) ?>
        <?php elseif ($mode === 'wizard'): ?>
            <?= $this->render('_partials/_leave_wizard', [
                'model'                 => $model,
                'isUpdate'              => $isUpdate,
                'typeOptions'           => $typeOptions,
                'approveChain'          => $approveChain,
                'draftRef'              => $draftRef,
                'leaveSendInitAvatar'   => $leaveSendInitAvatar,
                'leaveWorkSendInitText' => $leaveWorkSendInitText,
                'existingWorkShift'     => $existingWorkShift,
                'existingDateStartT'    => $existingDateStartT,
                'existingDateEndT'      => $existingDateEndT,
                'existingPlaceGo'       => $existingPlaceGo,
                'existingSigType'       => $existingSigType,
                'signatureSystemUrl'    => $signatureSystemUrl,
                'initSatsun'            => $initSatsun,
                'initHoliday'           => $initHoliday,
                'initTotal'             => $initTotal,
                'resultsJs'             => $resultsJs,
            ]) ?>
        <?php endif; ?>
    </div>

    <!-- Action bar -->
    <div class="bl-actions" id="bl-actions" data-mode="<?= Html::encode($mode) ?>" data-step="1">
        <!-- List mode -->
        <a href="<?= Html::encode($newUrl) ?>" class="btn btn-primary bl-mode-action" data-for-mode="list">
            <i data-lucide="plus" aria-hidden="true"></i>
            <span>สร้างคำขอลาใหม่</span>
        </a>

        <!-- Wizard mode -->
        <button type="button" class="btn btn-prev bl-mode-action" data-for-mode="wizard" id="bl-prev">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            <span>ย้อนกลับ</span>
        </button>
        <button type="button" class="btn btn-primary bl-mode-action" data-for-mode="wizard" id="bl-next">
            <span>ถัดไป</span>
            <i data-lucide="arrow-right" aria-hidden="true"></i>
        </button>
        <?= Html::submitButton(
            '<i data-lucide="send" aria-hidden="true"></i><span>ส่งคำขอลา</span>',
            [
                'class'    => 'btn btn-primary bl-mode-action',
                'id'       => 'bl-submit',
                'form'     => $isUpdate ? 'form-leave-update' : 'mobile-leave-request-form',
                'data'     => ['for-mode' => 'wizard'],
                'disabled' => true,
            ]
        ) ?>
    </div>
</div>

<?php
\app\widgets\datepicker\Assets::register($this);
$this->registerJs("if (typeof thaiDatepicker === 'function') thaiDatepicker('#leave-date_start,#leave-date_end');", View::POS_END);

$calDaysUrl     = Url::to(['/leave/leave/cal-days']);
$updateShiftUrl = Url::to(['/leave/leave/update-work-shift']);
$csrfParam      = \Yii::$app->request->csrfParam;
$csrfToken      = \Yii::$app->request->csrfToken;
$remainingAnnualLeaveJs = json_encode((float) $remainingAnnualLeave);
$wizardActiveJs = $mode === 'wizard' ? '1' : '0';

// Existing leave form logic (cal-days, pill mirror, submit confirm) — preserved verbatim
// Each IIFE guards with element-presence checks so list mode is a no-op.
$js = <<<JS
(function(){
    var calDaysUrl     = "{$calDaysUrl}";
    var updateShiftUrl = "{$updateShiftUrl}";
    var csrfParam      = "{$csrfParam}";
    var csrfToken      = "{$csrfToken}";
    var annualLeaveTypeCode = 'LT4';
    var annualLeaveRemaining = {$remainingAnnualLeaveJs};

    if (!document.getElementById('leave-create-form-leave_type_id')) return;

    function bindPillGroups() {
        document.querySelectorAll('.pill-group').forEach(function(group) {
            group.querySelectorAll('input[type="radio"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    group.querySelectorAll('.pill-option').forEach(function(opt) { opt.classList.remove('is-active'); });
                    var label = radio.closest('label');
                    if (label) label.classList.add('is-active');
                    var targetId = radio.dataset.pillTarget;
                    if (targetId) {
                        var t = document.getElementById(targetId);
                        if (t) { t.value = radio.value; t.dispatchEvent(new Event('change', { bubbles: true })); }
                    }
                });
            });
        });
    }

    function getDateStart()     { var el = document.getElementById('leave-date_start');      return el ? el.value.trim() : ''; }
    function getDateEnd()       { var el = document.getElementById('leave-date_end');        return el ? el.value.trim() : ''; }
    function getDateStartType() { var el = document.getElementById('leave-date_start_type'); return el ? parseFloat(el.value) || 0 : 0; }
    function getDateEndType()   { var el = document.getElementById('leave-date_end_type');   return el ? parseFloat(el.value) || 0 : 0; }
    function getWorkShift()     { var el = document.getElementById('leave-work_shift');      return el ? el.value : ''; }
    function getLeaveTypeId()   { var el = document.getElementById('leave-create-form-leave_type_id'); return el ? el.value : ''; }

    function formatLeaveDays(days) {
        var value = parseFloat(days);
        if (isNaN(value)) value = 0;
        return value.toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
    }
    function isAnnualLeaveSelected() { return getLeaveTypeId() === annualLeaveTypeCode; }

    function setSubmitBlocked(blocked) {
        var btn = document.getElementById('bl-submit');
        if (!btn) return;
        btn.dataset.annualBlocked = blocked ? '1' : '';
    }

    function updateAnnualLeaveWarning(total) {
        var alertEl = document.getElementById('leave-annual-leave-alert');
        if (!alertEl) return;
        alertEl.className = 'bl-annual-alert';
        if (!isAnnualLeaveSelected() || annualLeaveRemaining < 0) {
            alertEl.innerHTML = '';
            alertEl.style.display = 'none';
            setSubmitBlocked(false);
            return;
        }
        var totalVal  = parseFloat(total) || 0;
        var remaining = parseFloat(annualLeaveRemaining) || 0;
        var blocked   = remaining <= 0 || totalVal > remaining;
        var icon, text, cls;
        if (blocked) {
            icon = remaining <= 0 ? 'x-circle' : 'alert-triangle';
            cls  = 'is-error';
            text = remaining <= 0
                ? 'วันลาพักผ่อนคงเหลือ 0 วัน ไม่สามารถยื่นลาได้'
                : 'วันลาพักผ่อนไม่เพียงพอ (คงเหลือ ' + formatLeaveDays(remaining) + ' วัน, ขอใช้ ' + formatLeaveDays(totalVal) + ' วัน)';
        } else {
            icon = 'info'; cls = 'is-info';
            text = 'วันลาพักผ่อนคงเหลือ ' + formatLeaveDays(remaining) + ' วัน';
        }
        alertEl.className = 'bl-annual-alert ' + cls;
        alertEl.style.display = 'flex';
        alertEl.innerHTML = '<i data-lucide="' + icon + '" class="mi-sm"></i> <span>' + text + '</span>';
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        setSubmitBlocked(blocked);
    }

    function toggleTotalEditable() {
        var shiftEl = document.getElementById('leave-work_shift');
        var totalEl = document.getElementById('leave-summary-total');
        var showEl  = document.getElementById('leave-summary-total-show');
        var hintEl  = document.getElementById('leave-total-hint');
        if (!totalEl || !showEl) return;
        var isShift8 = shiftEl && shiftEl.value === 'shift';
        if (isShift8) {
            totalEl.removeAttribute('readonly'); totalEl.classList.remove('d-none'); totalEl.tabIndex = 0;
            showEl.classList.add('d-none');
            if (hintEl) hintEl.classList.remove('d-none');
        } else {
            totalEl.setAttribute('readonly', 'readonly'); totalEl.classList.add('d-none'); totalEl.tabIndex = -1;
            showEl.classList.remove('d-none');
            if (hintEl) hintEl.classList.add('d-none');
        }
    }

    function updateSummary(satsun, holiday, total) {
        var s = document.getElementById('leave-summary-satsun');
        var h = document.getElementById('leave-summary-holiday');
        var t = document.getElementById('leave-summary-total');
        var tShow = document.getElementById('leave-summary-total-show');
        var totalDaysInput = document.getElementById('leave-total_days');
        var totalBox = document.getElementById('leave-summary-total-box');
        var newTotal = total != null ? total : 0;
        if (s) s.textContent = satsun  != null ? satsun  : 0;
        if (h) h.textContent = holiday != null ? holiday : 0;
        if (t) t.value = newTotal;
        if (tShow) tShow.textContent = newTotal;
        if (totalDaysInput) {
            var nextVal = String(newTotal);
            var changed = totalDaysInput.value !== nextVal;
            totalDaysInput.value = nextVal;
            // เด้ง event เสมอ เพื่อให้ wizard re-validate ปุ่มถัดไป
            // (แก้ปัญหาเดิม: AJAX อัปเดต total แล้วปุ่มยัง disabled จนกว่าผู้ใช้จะแตะ pill)
            totalDaysInput.dispatchEvent(new Event('change', { bubbles: true }));
            if (changed && totalBox) {
                totalBox.classList.remove('is-fresh');
                void totalBox.offsetWidth;
                totalBox.classList.add('is-fresh');
            }
        }
        updateZeroDayHint(newTotal);
    }

    function updateZeroDayHint(total) {
        var hintEl = document.getElementById('leave-zero-day-hint');
        if (!hintEl) return;
        var hasDates = getDateStart() && getDateEnd();
        var totalVal = parseFloat(total) || 0;
        var show = hasDates && totalVal <= 0 && !window.__leaveCalcInFlight;
        hintEl.hidden = !show;
    }

    function setCalculating(on) {
        window.__leaveCalcInFlight = !!on;
        var totalBox = document.getElementById('leave-summary-total-box');
        var satBox   = document.getElementById('leave-summary-satsun');
        var holBox   = document.getElementById('leave-summary-holiday');
        if (totalBox) totalBox.classList.toggle('is-calculating', !!on);
        if (satBox && satBox.parentElement) satBox.parentElement.classList.toggle('is-calculating', !!on);
        if (holBox && holBox.parentElement) holBox.parentElement.classList.toggle('is-calculating', !!on);
        if (on) {
            var hintEl = document.getElementById('leave-zero-day-hint');
            if (hintEl) hintEl.hidden = true;
        }
    }

    function toggleDateEndType() {
        var s = getDateStart(); var e = getDateEnd();
        var endTypeEl = document.getElementById('leave-date_end_type');
        if (!endTypeEl) return;
        if (s && e && s.length >= 8 && e.length >= 8 && s === e) { endTypeEl.value = '0'; endTypeEl.disabled = true; }
        else { endTypeEl.disabled = false; }
    }

    function calDays() {
        var s = getDateStart(); var e = getDateEnd();
        if (!s || !e || s.length < 8 || e.length < 8) {
            setCalculating(false);
            updateSummary(0, 0, 0); updateAnnualLeaveWarning(0);
            return;
        }
        setCalculating(true);
        var params = new URLSearchParams({
            date_start: s, date_end: e,
            date_start_type: getDateStartType(), date_end_type: getDateEndType(),
            leave_type_id: getLeaveTypeId(), work_shift: getWorkShift(),
        });
        fetch(calDaysUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                setCalculating(false);
                if (res.status === 'error') {
                    updateSummary(0, 0, 0); updateAnnualLeaveWarning(0);
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message, timer: 4000, showConfirmButton: false });
                    return;
                }
                if (typeof res.remaining_annual_leave !== 'undefined') annualLeaveRemaining = parseFloat(res.remaining_annual_leave) || 0;
                updateSummary(res.satsunDays || 0, res.holiday || 0, res.total || 0);
                updateAnnualLeaveWarning(res.total || 0);
                var shiftEl = document.getElementById('leave-work_shift');
                if (shiftEl && shiftEl.value === '' && res.shift) {
                    shiftEl.value = res.shift;
                    var radio = document.querySelector('input[data-pill-target="leave-work_shift"][value="' + res.shift + '"]');
                    if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', { bubbles: true })); }
                }
            })
            .catch(function(){
                setCalculating(false);
                updateSummary(0, 0, 0); updateAnnualLeaveWarning(0);
            });
    }

    function updateWorkShift(val) {
        if (!val) { calDays(); return; }
        var fd = new FormData();
        fd.append('work_shift', val);
        fd.append(csrfParam, csrfToken);
        fetch(updateShiftUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(){ calDays(); })
            .catch(function(){ calDays(); });
    }

    function bindEvents() {
        var startEl = document.getElementById('leave-date_start');
        var endEl   = document.getElementById('leave-date_end');
        var shiftEl = document.getElementById('leave-work_shift');
        var startTypeEl = document.getElementById('leave-date_start_type');
        var endTypeEl   = document.getElementById('leave-date_end_type');
        function onDateChange() { toggleDateEndType(); calDays(); }
        if (startEl) { startEl.addEventListener('change', onDateChange); startEl.addEventListener('blur', onDateChange); }
        if (endEl)   { endEl.addEventListener('change',   onDateChange); endEl.addEventListener('blur',   onDateChange); }
        if (startTypeEl) startTypeEl.addEventListener('change', calDays);
        if (endTypeEl)   endTypeEl.addEventListener('change',   calDays);
        if (shiftEl) shiftEl.addEventListener('change', function(){ toggleTotalEditable(); updateWorkShift(this.value); });
        var totalEl = document.getElementById('leave-summary-total');
        if (totalEl) {
            totalEl.addEventListener('input', function() {
                var v = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
                var totalDaysInput = document.getElementById('leave-total_days');
                if (totalDaysInput) totalDaysInput.value = v;
                updateAnnualLeaveWarning(v);
            });
        }
        if (typeof jQuery !== 'undefined') {
            function onPickerChange() { setTimeout(function() { toggleDateEndType(); calDays(); }, 50); }
            try { jQuery('#leave-date_start, #leave-date_end').datetimepicker('setOptions', { onSelectDate: onPickerChange }); } catch (e) {}
            jQuery(document).on('changedatetime.xdsoft', '#leave-date_start,#leave-date_end', onPickerChange);
        }
        var leaveTypeEl = document.getElementById('leave-create-form-leave_type_id');
        if (leaveTypeEl) leaveTypeEl.addEventListener('change', function(){ calDays(); });
    }

    bindPillGroups();
    bindEvents();
    toggleDateEndType();
    toggleTotalEditable();
    calDays();
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, View::POS_END);

// ───── Wizard state machine ─────
$wizardJs = <<<JS
(function(){
    var WIZARD_ACTIVE = {$wizardActiveJs};
    if (!WIZARD_ACTIVE) return;

    var TOTAL_STEPS = 7;
    var STEP_NAMES = {
        1: 'ประเภทการลา', 2: 'ช่วงเวลา', 3: 'เหตุผลและสถานที่',
        4: 'มอบหมายงาน', 5: 'เอกสารแนบ', 6: 'ลายเซ็น', 7: 'ตรวจสอบและส่ง'
    };
    var currentStep = 1;

    var panels  = document.querySelectorAll('.bl-panel');
    var steps   = document.querySelectorAll('.bl-wizard-step');
    var fill    = document.getElementById('bl-wizard-fill');
    var actions = document.getElementById('bl-actions');
    var prevBtn = document.getElementById('bl-prev');
    var nextBtn = document.getElementById('bl-next');
    var submitBtn = document.getElementById('bl-submit');
    var confirmChk = document.getElementById('bl-confirm-chk');
    var formEl  = document.getElementById('mobile-leave-request-form') || document.getElementById('form-leave-update');
    var progressFillEl = document.getElementById('bl-progress-fill');
    var stepNumEl  = document.getElementById('bl-step-num');
    var stepNameEl = document.getElementById('bl-step-name');

    function setStepError(step, message) {
        var el = document.querySelector('[data-step-error="' + step + '"]');
        if (!el) return;
        el.textContent = message || '';
        el.classList.toggle('is-visible', !!message);
    }
    function clearStepErrors() {
        document.querySelectorAll('[data-step-error]').forEach(function(el){ el.textContent=''; el.classList.remove('is-visible'); });
    }

    function val(id) { var el = document.getElementById(id); return el ? (el.value || '').trim() : ''; }

    function validateStep(n, show) {
        var msg = '';
        if (n === 1) {
            if (!val('leave-create-form-leave_type_id')) msg = 'กรุณาเลือกประเภทการลา';
        } else if (n === 2) {
            if (!val('leave-date_start') || !val('leave-date_end')) {
                msg = 'กรุณาเลือกวันเริ่มและวันสิ้นสุด';
            } else if (window.__leaveCalcInFlight) {
                msg = 'กำลังคำนวณวันลา กรุณารอสักครู่';
            } else {
                var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
                if (total <= 0) msg = 'ช่วงเวลานี้รวมแล้วเป็น 0 วัน โปรดเลือกวันใหม่ หรือเปลี่ยน เต็มวัน/ครึ่งวัน';
            }
        } else if (n === 3) {
            var reasonEl = document.querySelector('[name="Leave[data_json][reason]"]');
            if (!reasonEl || !reasonEl.value.trim()) msg = 'กรุณาระบุสาเหตุการลา';
        } else if (n === 7) {
            if (!confirmChk || !confirmChk.checked) msg = 'กรุณาติ๊กยืนยันข้อมูลก่อนส่งคำขอ';
        }
        if (show) setStepError(n, msg);
        return !msg;
    }

    function updateSummaryText() {
        var typeEl = document.getElementById('leave-create-form-leave_type_id');
        var typeText = typeEl && typeEl.selectedIndex >= 0 ? (typeEl.options[typeEl.selectedIndex].text || '-') : '-';
        var dStart = val('leave-date_start'), dEnd = val('leave-date_end');
        var period = (dStart && dEnd) ? (dStart === dEnd ? dStart : (dStart + ' ถึง ' + dEnd)) : '-';
        var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
        var reasonEl = document.querySelector('[name="Leave[data_json][reason]"]');
        var reason = reasonEl ? reasonEl.value.trim() : '';
        var sendEl = document.querySelector('select[name="Leave[data_json][leave_work_send_id]"]');
        var sendText = '-';
        if (sendEl && sendEl.selectedIndex >= 0 && sendEl.options[sendEl.selectedIndex]) {
            sendText = sendEl.options[sendEl.selectedIndex].text || '-';
        }
        var set = function(id, v){ var el = document.getElementById(id); if (el) el.textContent = v || '-'; };
        set('bl-summary-type',   typeText);
        set('bl-summary-period', period);
        set('bl-summary-total',  total + ' วัน');
        set('bl-summary-reason', reason || '-');
        set('bl-summary-send',   sendText);
    }

    function goToStep(n) {
        n = Math.max(1, Math.min(TOTAL_STEPS, n));
        currentStep = n;
        panels.forEach(function(p){
            var isCurrent = Number(p.dataset.stepPanel) === n;
            p.hidden = !isCurrent;
            p.classList.toggle('is-active', isCurrent);
        });
        steps.forEach(function(s){
            var sStep = Number(s.dataset.step);
            var active = sStep === n;
            s.classList.toggle('is-active', active);
            s.classList.toggle('is-done', sStep < n);
            if (active) s.setAttribute('aria-current', 'step');
            else        s.removeAttribute('aria-current');
        });
        var pct = (n / TOTAL_STEPS * 100);
        if (fill) fill.style.width = pct.toFixed(4) + '%';
        if (progressFillEl) progressFillEl.style.width = pct.toFixed(4) + '%';
        if (stepNumEl)  stepNumEl.textContent = String(n);
        if (stepNameEl) stepNameEl.textContent = STEP_NAMES[n] || '';
        if (actions) actions.dataset.step = String(n);
        clearStepErrors();
        if (n === TOTAL_STEPS) updateSummaryText();
        updateActions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateActions() {
        if (nextBtn) nextBtn.disabled = !validateStep(currentStep, false);
        if (submitBtn) {
            var blocked = submitBtn.dataset.annualBlocked === '1';
            submitBtn.disabled = !(confirmChk && confirmChk.checked) || blocked;
        }
    }

    if (prevBtn) prevBtn.addEventListener('click', function(){ goToStep(currentStep - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ if (validateStep(currentStep, true)) goToStep(currentStep + 1); });
    if (confirmChk) confirmChk.addEventListener('change', updateActions);

    // re-update actions เมื่อ field เปลี่ยน (validation feedback realtime)
    if (formEl) {
        formEl.addEventListener('input',  updateActions);
        formEl.addEventListener('change', updateActions);
    }

    // Submit handler:
    // 1) Block ขั้นกลาง wizard (currentStep !== TOTAL_STEPS) → เปลี่ยนเป็น "ถัดไป"
    // 2) Hard guard: total > 0, annual leave ต้องไม่ติด blocked
    // 3) Swal confirm สไตล์เดียวกับ handleFormSubmit (icon question, สีปุ่ม success/secondary)
    //    เมื่อยืนยันแล้ว set flag form.dataset.confirmed='1' แล้ว submit ใหม่ — รอบที่สอง
    //    จะข้าม Swal เพราะ flag = '1' (กัน infinite loop)
    if (formEl) {
        formEl.addEventListener('submit', function(e){
            if (currentStep !== TOTAL_STEPS) {
                e.preventDefault();
                if (validateStep(currentStep, true)) goToStep(currentStep + 1);
                return;
            }
            var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
            if (total <= 0) {
                e.preventDefault();
                setStepError(7, 'วันลาต้องมากกว่า 0 กรุณากลับไปขั้นช่วงเวลา');
                return;
            }
            if (submitBtn && submitBtn.dataset.annualBlocked === '1') {
                e.preventDefault();
                setStepError(7, 'วันลาพักผ่อนไม่เพียงพอ');
                return;
            }
            if (this.dataset.confirmed === '1') {
                this.dataset.confirmed = '';
                return; // ปล่อยให้ submit ดำเนินต่อ
            }
            e.preventDefault();
            var form = this;
            if (typeof Swal === 'undefined') {
                if (window.confirm('ยืนยันการบันทึกคำขอลา? โปรดตรวจสอบข้อมูลก่อนกดยืนยัน')) {
                    form.dataset.confirmed = '1';
                    if (typeof form.requestSubmit === 'function') form.requestSubmit(submitBtn || undefined);
                    else form.submit();
                }
                return;
            }
            Swal.fire({
                title: 'ยืนยันการบันทึกคำขอลา?',
                text: 'โปรดตรวจสอบความถูกต้องของข้อมูลก่อนกดยืนยัน ระบบจะส่งคำขอให้ผู้บังคับบัญชาตรวจสอบ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-paper-plane me-1"></i> ยืนยันส่งคำขอ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: false,
            }).then(function(r){
                if (r.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังส่งคำขอลา',
                        text: 'ระบบกำลังบันทึกข้อมูลและส่งให้ผู้อนุมัติ...',
                        allowOutsideClick: false,
                        didOpen: function(){ Swal.showLoading(); },
                    });
                    form.dataset.confirmed = '1';
                    if (typeof form.requestSubmit === 'function') form.requestSubmit(submitBtn || undefined);
                    else form.submit();
                }
            });
        });
    }

    goToStep(1);
})();
JS;
$this->registerJs($wizardJs, View::POS_END);

// Signature canvas JS (preserved from original)
$existingSigDataJs = json_encode($existingSigData);
$sigJs = <<<JS
(function(){
    var canvas  = document.getElementById('sig-canvas');
    var typeInp = document.getElementById('sig-type-input');
    var dataInp = document.getElementById('sig-data-input');
    if (!canvas || !typeInp || !dataInp) return;
    var ctx = canvas.getContext('2d');
    var drawing = false; var hasDrawn = false;
    function scaleXY(clientX, clientY) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width; var scaleY = canvas.height / rect.height;
        return [(clientX - rect.left) * scaleX, (clientY - rect.top) * scaleY];
    }
    function startDraw(x, y) { drawing = true; ctx.beginPath(); ctx.moveTo(x, y); }
    function moveDraw(x, y) {
        if (!drawing) return;
        ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#1a1a2e';
        ctx.lineTo(x, y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(x, y); hasDrawn = true;
    }
    function endDraw() { drawing = false; ctx.beginPath(); }
    canvas.addEventListener('mousedown',  function(e){ var p=scaleXY(e.clientX,e.clientY); startDraw(p[0],p[1]); });
    canvas.addEventListener('mousemove',  function(e){ var p=scaleXY(e.clientX,e.clientY); moveDraw(p[0],p[1]); });
    canvas.addEventListener('mouseup',    endDraw);
    canvas.addEventListener('mouseleave', endDraw);
    canvas.addEventListener('touchstart', function(e){ e.preventDefault(); var t=e.touches[0]; var p=scaleXY(t.clientX,t.clientY); startDraw(p[0],p[1]); }, {passive:false});
    canvas.addEventListener('touchmove',  function(e){ e.preventDefault(); var t=e.touches[0]; var p=scaleXY(t.clientX,t.clientY); moveDraw(p[0],p[1]);  }, {passive:false});
    canvas.addEventListener('touchend',   endDraw);
    var clearBtn = document.getElementById('sig-clear-btn');
    if (clearBtn) clearBtn.addEventListener('click', function(){ ctx.clearRect(0, 0, canvas.width, canvas.height); dataInp.value = ''; hasDrawn = false; });
    document.querySelectorAll('.btn-sig-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var t = this.dataset.sigType;
            typeInp.value = t;
            document.querySelectorAll('.btn-sig-tab').forEach(function(b){ b.classList.remove('is-active'); b.setAttribute('aria-selected', 'false'); });
            this.classList.add('is-active'); this.setAttribute('aria-selected', 'true');
            document.getElementById('sig-panel-canvas').classList.toggle('d-none', t !== 'canvas');
            document.getElementById('sig-panel-system').classList.toggle('d-none', t !== 'system');
            if (t === 'system') dataInp.value = '';
        });
    });
    var existingData = {$existingSigDataJs};
    if (existingData && typeof existingData === 'string' && existingData.startsWith('data:image')) {
        var img = new Image();
        img.onload = function(){ ctx.drawImage(img, 0, 0, canvas.width, canvas.height); hasDrawn = true; };
        img.src = existingData;
    }
    function captureSignature() {
        if (typeInp.value === 'canvas' && hasDrawn) dataInp.value = canvas.toDataURL('image/png');
    }
    var formCreate = document.getElementById('mobile-leave-request-form');
    var formUpdate = document.getElementById('form-leave-update');
    [formCreate, formUpdate].forEach(function(f){ if (f) f.addEventListener('submit', captureSignature, true); });
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('beforeSubmit', '#mobile-leave-request-form, #form-leave-update', captureSignature);
    }
})();
JS;
$this->registerJs($sigJs, View::POS_END);
