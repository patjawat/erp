<?php

use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Vehicle $model */
/** @var \app\modules\hr\models\Employees|null $employee */
/** @var array $saveErrors */
/** @var string|null $forceMode */
/** @var bool|null $isEdit */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle']  = 'จองรถราชการ';

$saveErrors = $saveErrors ?? [];

// ═══════════════════════════════════════════════════════════════════════
// Mode detection
// ═══════════════════════════════════════════════════════════════════════
// list    — entry: my requests with search + status filter
// wizard  — create: 5-step form
// success — exit:  confirmation after save
$hasFlashSuccess = Yii::$app->session->hasFlash('success');
$flashMessage    = $hasFlashSuccess ? (string) Yii::$app->session->getFlash('success') : '';
$flashCode       = '';
if ($flashMessage && preg_match('/รหัส\s*([A-Z0-9\-]+)/u', $flashMessage, $cm)) {
    $flashCode = $cm[1];
}
$actionParam = (string) (Yii::$app->request->get('action') ?? '');
$hasErrors   = !empty($saveErrors);
$isEdit       = (bool) ($isEdit ?? (!$model->isNewRecord));

$mode = 'list';
if (!empty($forceMode)) {
    $mode = (string) $forceMode;
} elseif ($hasFlashSuccess) {
    $mode = 'success';
} elseif ($actionParam === 'new' || $hasErrors) {
    $mode = 'wizard';
}

$this->params['mobileSubtitle'] = $mode === 'wizard'
    ? ($isEdit ? 'แก้ไขข้อมูลคำขอ' : 'กรอกข้อมูลทีละขั้นตอน')
    : ($mode === 'success' ? 'บันทึกคำขอเรียบร้อย' : 'รายการคำขอของฉัน');

// ═══════════════════════════════════════════════════════════════════════
// Query my bookings (only when list mode renders, to keep wizard fast)
// ═══════════════════════════════════════════════════════════════════════
$myBookings   = [];
$currentEmpId = isset($employee) && $employee ? (string) $employee->id : null;
if ($mode === 'list' && $currentEmpId) {
    try {
        $myBookings = \app\modules\booking\models\Vehicle::find()
            ->where(['emp_id' => $currentEmpId])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(50)
            ->all();
    } catch (\Throwable $e) {
        $myBookings = [];
    }
}

// Status → (label, tone, bucket) for list badges and filter chips
$statusInfo = static function (string $status): array {
    static $map = [
        'Pending' => ['lbl' => 'รออนุมัติ',  'tone' => 'warning', 'bucket' => 'pending'],
        'รออนุมัติ' => ['lbl' => 'รออนุมัติ',  'tone' => 'warning', 'bucket' => 'pending'],
        'Approve' => ['lbl' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
        'Pass'    => ['lbl' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
        'อนุมัติแล้ว' => ['lbl' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
        'Cancel'  => ['lbl' => 'ยกเลิก',     'tone' => 'danger',  'bucket' => 'cancelled'],
        'Reject'  => ['lbl' => 'ปฏิเสธ',     'tone' => 'danger',  'bucket' => 'cancelled'],
        'ยกเลิก'  => ['lbl' => 'ยกเลิก',     'tone' => 'danger',  'bucket' => 'cancelled'],
        'ปฏิเสธ'  => ['lbl' => 'ปฏิเสธ',     'tone' => 'danger',  'bucket' => 'cancelled'],
    ];
    return $map[$status] ?? ['lbl' => $status ?: '-', 'tone' => 'secondary', 'bucket' => 'other'];
};

// Status bucket counts for filter chip badges
$bucketCounts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'cancelled' => 0, 'other' => 0];
foreach ($myBookings as $b) {
    $info = $statusInfo((string) $b->status);
    $bucketCounts['all']++;
    $bucketCounts[$info['bucket']]++;
}

$formatThaiDate = static function (?string $d): string {
    if (!$d) return '—';
    try {
        return ThaiDateHelper::formatThaiDate($d, 'short');
    } catch (\Throwable $e) {
        $ts = strtotime((string) $d);
        return $ts ? date('d/m/Y', $ts) : (string) $d;
    }
};

// ═══════════════════════════════════════════════════════════════════════
// Wizard mode defaults (only loaded when wizard renders)
// ═══════════════════════════════════════════════════════════════════════
// Promote Gregorian-year defaults to Thai year so AppHelper::convertToGregorian
// (which always subtracts 543) round-trips correctly.
foreach (['date_start', 'date_end'] as $attr) {
    $v = (string) ($model->$attr ?? '');
    if ($v && preg_match('#^\d{2}/\d{2}/(\d{4})$#', $v, $m) && (int) $m[1] < 2400) {
        $model->$attr = substr($v, 0, 6) . ((int) $m[1] + 543);
    }
}

$requesterName = '—';
$requesterDept = '—';
$prefillPhone  = '';
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    $emp = Yii::$app->user->identity->employee;
    try { if (!empty($emp->fullname)) $requesterName = $emp->fullname; } catch (\Throwable $e) {}
    try { if (method_exists($emp, 'departmentName') && $emp->departmentName()) $requesterDept = $emp->departmentName(); } catch (\Throwable $e) {}
    try { if (!empty($emp->phone)) $prefillPhone = (string) $emp->phone; } catch (\Throwable $e) {}
}

$dataJson            = is_array($model->data_json ?? null) ? $model->data_json : [];
$existingGoType      = (int)    ($model->go_type ?? 1);
$existingDriver      = (string) ($dataJson['driver']     ?? '');
$existingPhone       = (string) ($dataJson['phone']      ?? $prefillPhone);
$existingPassengers  = (int)    ($dataJson['passengers'] ?? 1);
$existingVehicleType = (string) ($model->vehicle_type_id ?? 'general');
$existingPlate       = (string) ($model->license_plate   ?? '');
$existingUrgent      = (string) ($model->urgent          ?? 'ปกติ');
$existingNotes       = (string) ($dataJson['notes']      ?? '');

$baseUrl   = Url::to(['/mobile/default/booking-vehicle']);
$newUrl    = Url::to(['/mobile/default/booking-vehicle', 'action' => 'new']);
$detailUrl = $isEdit ? Url::to(['/mobile/default/vehicle-view', 'id' => $model->id]) : $baseUrl;
$submitText = $isEdit ? 'บันทึกการแก้ไข' : 'ส่งคำขอจองรถ';
?>

<style>
/* Full-bleed escape so the shared .app-shell reaches screen edges. */
.bv-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
}

/* Scroll area: top offset comes from .app-scroll (--shell-h). Bottom
   padding clears unified action bar + mobile bottom navbar. */
.bv-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 12rem);
}

/* One panel visible at a time; mode switching via [hidden]. */
.bv-mode[hidden] { display: none !important; }

/* ═══════════════════════════════════════════════════════════════════
   MODE 1 — LIST (entry)
   ═══════════════════════════════════════════════════════════════════ */
.bv-list-toolbar {
    /* Sits below the fixed hero+stats shell when the user scrolls. JS keeps
       --shell-h in sync with the shell's measured height. No negative
       margin: the parent .bv-root already escapes the page padding, so
       pulling left/right again would push the toolbar past the viewport. */
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md) var(--space-md);
    /* Soft hairline + drop shadow appears once cards scroll behind it */
    box-shadow: 0 1px 0 var(--ink-line),
                0 2px 8px rgba(15, 23, 42, 0.04);
}
.bv-search {
    display: block;
    width: 100%;
    box-sizing: border-box;
    border-radius: 12px;
    border: 1px solid transparent;
    padding: 0.625rem 2.5rem 0.625rem 2.5rem;
    min-height: 2.75rem; /* 44px touch target — fits snug under shell */
    font-size: var(--fs-md);
    color: var(--ink);
    background: var(--surface-2)
        url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><path d='m21 21-4.3-4.3'/></svg>")
        no-repeat 0.75rem center;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1),
                border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
    -webkit-appearance: none;
    appearance: none;
}
.bv-search::placeholder { color: var(--ink-4); }
.bv-search:focus {
    outline: 0;
    background-color: var(--surface);
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}
/* Hide WebKit's native clear (×) so it doesn't crowd the leading icon */
.bv-search::-webkit-search-cancel-button { -webkit-appearance: none; display: none; }

.bv-list {
    padding: var(--space-md) var(--space-md) 0;
    display: flex; flex-direction: column;
    gap: var(--space-sm);
}
.bv-list-card {
    background: var(--surface);
    border-radius: 14px;
    box-shadow: var(--shadow-sm);
    padding: var(--space-md);
    display: flex; flex-direction: column;
    gap: var(--space-xs);
    text-decoration: none; color: inherit;
}
.bv-list-card[hidden] { display: none; }
.bv-list-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: var(--space-sm);
}
.bv-list-code {
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    color: var(--ink-4);
    font-weight: 500;
    letter-spacing: 0.02em;
}
.bv-list-pill {
    flex-shrink: 0;
    font-size: var(--fs-2xs);
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
}
.bv-list-pill.is-warning   { background: var(--warning-soft); color: var(--warning); }
.bv-list-pill.is-success   { background: var(--success-soft); color: var(--success); }
.bv-list-pill.is-danger    { background: var(--danger-soft);  color: var(--danger-strong); }
.bv-list-pill.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

.bv-list-title {
    font-size: var(--fs-md);
    font-weight: 600;
    color: var(--ink);
    margin: 0;
    line-height: 1.35;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bv-list-meta {
    display: flex; flex-wrap: wrap;
    gap: var(--space-2xs) var(--space-md);
    font-size: var(--fs-xs);
    color: var(--ink-3);
}
.bv-list-meta-item {
    display: inline-flex; align-items: center; gap: 4px;
}
.bv-list-meta-item svg {
    width: 12px; height: 12px;
    color: var(--ink-4);
}
.bv-list-urgent {
    font-size: var(--fs-2xs);
    font-weight: 600;
    color: var(--danger-strong);
    background: var(--danger-soft);
    padding: 2px 8px;
    border-radius: 6px;
}

/* Empty state */
.bv-list-empty {
    background: var(--surface);
    border-radius: 16px;
    padding: var(--space-2xl) var(--space-md);
    text-align: center;
    margin: var(--space-lg) var(--space-md) 0;
    box-shadow: var(--shadow-sm);
}
.bv-list-empty-icon {
    width: 4rem; height: 4rem;
    border-radius: 50%;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: var(--space-md);
}
.bv-list-empty-title {
    font-size: var(--fs-lg);
    font-weight: 700;
    color: var(--ink);
    margin: 0 0 var(--space-2xs);
    letter-spacing: -0.01em;
}
.bv-list-empty-text {
    font-size: var(--fs-sm);
    color: var(--ink-3);
    margin: 0 auto;
    max-width: 28ch;
    line-height: 1.55;
}

.bv-list-no-results {
    background: var(--surface-2);
    border-radius: 12px;
    padding: var(--space-md);
    text-align: center;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    margin: 0 var(--space-md);
}
.bv-list-no-results[hidden] { display: none; }

/* ═══════════════════════════════════════════════════════════════════
   MODE 3 — SUCCESS (exit)
   ═══════════════════════════════════════════════════════════════════ */
.bv-success {
    padding: var(--space-xl) var(--space-md) 0;
    display: flex; flex-direction: column;
    gap: var(--space-md);
}
.bv-success-card {
    background: var(--surface);
    border-radius: 20px;
    padding: var(--space-xl) var(--space-md);
    text-align: center;
    box-shadow: var(--shadow-md);
    overflow: hidden;
    position: relative;
}
.bv-success-card::before {
    content: ''; position: absolute;
    top: -60px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: var(--success-soft);
    pointer-events: none;
    z-index: 0;
}
.bv-success-icon {
    position: relative; z-index: 1;
    width: 5rem; height: 5rem;
    border-radius: 50%;
    background: var(--success);
    color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: var(--space-md);
    box-shadow: 0 8px 24px rgba(21, 128, 61, 0.3);
    animation: bv-success-pop 480ms cubic-bezier(0.22, 1, 0.36, 1) backwards;
}
.bv-success-icon svg { width: 2.25rem; height: 2.25rem; stroke-width: 2.5; }
.bv-success-title {
    position: relative; z-index: 1;
    font-size: var(--fs-xl);
    font-weight: 700;
    color: var(--ink);
    margin: 0 0 var(--space-xs);
    letter-spacing: -0.015em;
}
.bv-success-text {
    position: relative; z-index: 1;
    font-size: var(--fs-sm);
    color: var(--ink-3);
    margin: 0 auto var(--space-md);
    max-width: 32ch;
    line-height: 1.55;
}
.bv-success-code {
    position: relative; z-index: 1;
    display: inline-flex; align-items: center;
    gap: var(--space-xs);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-md);
    font-weight: 700;
    letter-spacing: 0.04em;
}
.bv-success-code svg { width: 14px; height: 14px; }

.bv-success-fineprint {
    background: var(--surface-2);
    border-radius: 12px;
    padding: var(--space-md);
    font-size: var(--fs-sm);
    color: var(--ink-3);
    line-height: 1.55;
}
.bv-success-fineprint strong { color: var(--ink-2); }

@keyframes bv-success-pop {
    from { opacity: 0; transform: scale(0.6); }
    to   { opacity: 1; transform: scale(1); }
}

/* ═══════════════════════════════════════════════════════════════════
   MODE 2 — WIZARD (create)
   ═══════════════════════════════════════════════════════════════════ */
.bv-wizard {
    position: sticky;
    top: 0;
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md) var(--space-md) var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    margin: 0 calc(-1 * var(--space-md));
}
.bv-wizard-track {
    position: relative;
    height: 4px;
    background: var(--surface-3);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: var(--space-sm);
}
.bv-wizard-fill {
    position: absolute; inset: 0 auto 0 0;
    width: 20%;
    background: var(--mobile-primary);
    border-radius: 999px;
    transition: width 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bv-wizard-steps {
    list-style: none;
    margin: 0; padding: 0;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: var(--space-2xs);
}
.bv-wizard-step {
    display: flex; flex-direction: column; align-items: center;
    gap: 4px;
    text-align: center;
    color: var(--ink-4);
    font-size: 0.6875rem;
    font-weight: 500;
    line-height: 1.2;
}
.bv-wizard-pip {
    width: 1.5rem; height: 1.5rem;
    border-radius: 50%;
    background: var(--surface-3);
    color: var(--ink-4);
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.75rem;
    transition: background 240ms cubic-bezier(0.22, 1, 0.36, 1),
                color      240ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bv-wizard-pip svg { width: 14px; height: 14px; }
.bv-wizard-step.is-active .bv-wizard-pip {
    background: var(--mobile-primary);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
}
.bv-wizard-step.is-active { color: var(--mobile-primary); font-weight: 600; }
.bv-wizard-step.is-done .bv-wizard-pip {
    background: var(--success); color: #fff;
}
.bv-wizard-step.is-done { color: var(--ink-2); }
.bv-wizard-step.is-done .bv-wizard-pip-num { display: none; }
.bv-wizard-step:not(.is-done) .bv-wizard-pip-check { display: none; }

@media (max-width: 380px) {
    .bv-wizard-step span:not(.bv-wizard-pip):not(.bv-wizard-pip-num):not(.bv-wizard-pip-check) {
        display: none;
    }
    .bv-wizard-steps { grid-template-columns: repeat(5, auto); justify-content: space-between; }
}

.bv-panel {
    display: flex; flex-direction: column;
    gap: var(--space-md);
}
.bv-panel[hidden] { display: none !important; }
.bv-panel.is-active {
    animation: bv-step-enter 320ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes bv-step-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bv-panel-head { padding: 0 var(--space-2xs); }
.bv-panel-eyebrow {
    font-size: var(--fs-xs); font-weight: 500;
    color: var(--mobile-primary);
    margin: 0 0 4px;
    letter-spacing: 0.02em;
}
.bv-panel-title {
    font-size: var(--fs-xl); font-weight: 700;
    color: var(--ink);
    margin: 0 0 4px; letter-spacing: -0.015em; line-height: 1.2;
    text-wrap: balance;
}
.bv-panel-desc {
    font-size: var(--fs-sm); color: var(--ink-3);
    margin: 0; line-height: 1.45;
    max-width: 38ch;
}

.bv-card {
    background: var(--surface);
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: var(--shadow-md);
}
.bv-card .form-control, .bv-card .form-select {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    min-height: 3rem;
}
.bv-card .form-label {
    font-weight: 500; font-size: var(--fs-sm);
    color: var(--ink-2);
    margin-bottom: var(--space-xs);
}
.bv-card .invalid-feedback {
    display: block; font-size: var(--fs-xs);
    margin-top: var(--space-2xs);
    color: var(--danger-strong);
}
.form-label.is-req::after { content: ' *'; color: var(--danger); font-weight: 700; }
.bv-card > .mb-3:last-child,
.bv-card > div:last-child { margin-bottom: 0 !important; }

/* Read-only key/value (Step 2 requester info) */
.bv-kv-list { display: flex; flex-direction: column; gap: var(--space-sm); }
.bv-kv {
    display: flex; align-items: center; gap: var(--space-md);
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--ink-line);
}
.bv-kv:last-child { border-bottom: 0; }
.bv-kv-icon {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bv-kv-icon svg { width: 1rem; height: 1rem; }
.bv-kv-body { flex-grow: 1; min-width: 0; }
.bv-kv-key { font-size: var(--fs-xs); color: var(--ink-4); font-weight: 500; }
.bv-kv-val { font-size: var(--fs-md); color: var(--ink); font-weight: 600; word-break: break-word; }

/* Select2 (Krajee) inside bv-card */
.bv-card .select2-container--krajee-bs5 .select2-selection,
.bv-card .select2-container--krajee     .select2-selection {
    border-radius: 12px; min-height: 3rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid #dee2e6; box-shadow: none;
}
.bv-card .select2-container--krajee-bs5 .select2-selection__rendered,
.bv-card .select2-container--krajee     .select2-selection__rendered {
    line-height: 2.15rem; padding-left: 0; color: var(--ink);
}
.bv-card .select2-container--krajee-bs5 .select2-selection__placeholder,
.bv-card .select2-container--krajee     .select2-selection__placeholder { color: var(--ink-4); }
.bv-card .select2-container--krajee-bs5.select2-container--focus .select2-selection,
.bv-card .select2-container--krajee.select2-container--focus     .select2-selection {
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
}
.bv-card .select2-selection__arrow { height: 3rem !important; }
.select2-dropdown {
    border-radius: 12px !important; box-shadow: var(--shadow-lg);
    border-color: var(--ink-line) !important; overflow: hidden;
}
.select2-search--dropdown .select2-search__field {
    border-radius: 10px !important;
    border: 1px solid #dee2e6 !important;
    padding: 0.6rem 0.75rem !important;
    font-size: var(--fs-md); min-height: 2.75rem;
}
.select2-results__option {
    padding: 0.75rem 1rem !important;
    font-size: var(--fs-sm); min-height: 2.75rem;
    display: flex; align-items: center;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted,
.select2-container--krajee     .select2-results__option--highlighted {
    background: var(--mobile-primary-soft) !important;
    color: var(--mobile-primary) !important;
}

.bv-row { display: grid; gap: var(--space-md); }
@media (min-width: 360px) {
    .bv-row.bv-row-2 { grid-template-columns: 1fr 1fr; }
}

/* Step 4 summary card */
.bv-summary-card {
    background: var(--surface);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.bv-summary-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: var(--space-md);
    padding: var(--space-md) var(--space-md) var(--space-xs);
}
.bv-summary-title {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink);
    margin: 0; letter-spacing: -0.01em;
    display: flex; align-items: center; gap: var(--space-xs);
}
.bv-summary-title svg { color: var(--mobile-primary); width: 1.125rem; height: 1.125rem; }
.bv-summary-edit {
    background: transparent; border: 0;
    color: var(--mobile-primary);
    font-size: var(--fs-sm); font-weight: 600;
    padding: 8px 12px; border-radius: 8px;
    min-height: 44px; cursor: pointer;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bv-summary-edit:hover, .bv-summary-edit:focus { background: var(--mobile-primary-soft); }
.bv-summary-body { padding: 0 var(--space-md) var(--space-md); }
.bv-summary-dl {
    display: grid;
    grid-template-columns: minmax(7rem, auto) 1fr;
    gap: var(--space-xs) var(--space-md);
    margin: 0;
}
.bv-summary-dl dt {
    font-size: var(--fs-sm); color: var(--ink-4);
    font-weight: 500; align-self: start;
}
.bv-summary-dl dd {
    font-size: var(--fs-sm); color: var(--ink);
    font-weight: 600; margin: 0;
    word-break: break-word;
}
.bv-summary-dl dd.is-empty { color: var(--ink-5); font-weight: 500; font-style: italic; }

.bv-completeness {
    display: flex; align-items: center; gap: var(--space-xs);
    padding: var(--space-sm) var(--space-md);
    background: var(--success-soft);
    color: var(--success);
    border-radius: 12px;
    font-size: var(--fs-sm); font-weight: 600;
}
.bv-completeness.is-missing { background: var(--warning-soft); color: var(--warning); }
.bv-completeness svg { width: 1.125rem; height: 1.125rem; flex-shrink: 0; }

/* Step 5 confirm card */
.bv-confirm-card {
    background: var(--surface);
    border-radius: 16px; box-shadow: var(--shadow-md);
    padding: var(--space-md);
}
.bv-confirm-row {
    display: flex; align-items: flex-start; gap: var(--space-sm);
    padding: var(--space-sm); margin: 0;
    border-radius: 12px; cursor: pointer;
    transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bv-confirm-row:hover { background: var(--surface-2); }
.bv-confirm-row input[type="checkbox"] {
    flex-shrink: 0; width: 1.25rem; height: 1.25rem;
    margin-top: 2px; accent-color: var(--mobile-primary);
    cursor: pointer;
}
.bv-confirm-text { font-size: var(--fs-sm); color: var(--ink); line-height: 1.55; }
.bv-confirm-fineprint {
    margin-top: var(--space-sm); padding-top: var(--space-sm);
    border-top: 1px solid var(--ink-line);
    font-size: var(--fs-xs); color: var(--ink-3); line-height: 1.5;
}

/* Error summary alert */
.bv-error-summary {
    background: var(--danger-soft);
    border-radius: 12px;
    padding: var(--space-sm) var(--space-md);
    color: var(--danger-strong); font-size: var(--fs-sm);
    display: flex; gap: var(--space-xs); align-items: flex-start;
}
.bv-error-summary ul { margin: 0; padding-left: 1.25rem; }
.bv-field-error { border-color: var(--danger) !important; }

@media (max-width: 360px) {
    .pill-option { padding: var(--space-xs) 0.4rem; font-size: var(--fs-xs); }
}

/* ═══════════════════════════════════════════════════════════════════
   UNIFIED ACTION BAR
   ═══════════════════════════════════════════════════════════════════ */
.bv-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface);
    padding: var(--space-md);
    box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: grid;
    gap: var(--space-xs);
}
.bv-actions[data-mode="list"]    { grid-template-columns: 1fr; }
.bv-actions[data-mode="success"] { grid-template-columns: 1fr 1fr; }
.bv-actions[data-mode="wizard"][data-step="1"] { grid-template-columns: 1fr; }
.bv-actions[data-mode="wizard"]:not([data-step="1"]) { grid-template-columns: auto 1fr; }
.bv-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs);
    transition: opacity 150ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bv-actions .btn-prev {
    padding-left: var(--space-md); padding-right: var(--space-md);
    color: var(--ink-2); background: var(--surface-2);
    border-color: var(--surface-2);
}
.bv-actions .btn-prev:hover { background: var(--surface-3); color: var(--ink); }
.bv-actions .btn-prev svg { width: 1rem; height: 1rem; }
.bv-actions .btn-primary svg { width: 1rem; height: 1rem; }
.bv-actions .btn[disabled],
.bv-actions .btn.is-busy { opacity: 0.6; cursor: wait; }

.bv-mode-action[data-for-mode] { display: none; }
.bv-actions[data-mode="list"]    .bv-mode-action[data-for-mode~="list"]    { display: inline-flex; }
.bv-actions[data-mode="wizard"]  .bv-mode-action[data-for-mode~="wizard"]  { display: inline-flex; }
.bv-actions[data-mode="success"] .bv-mode-action[data-for-mode~="success"] { display: inline-flex; }
/* Wizard Prev button hidden on step 1 (data-step controls visibility) */
.bv-actions[data-mode="wizard"][data-step="1"] #bv-prev { display: none; }
.bv-actions[data-mode="wizard"] #bv-next   { display: inline-flex; }
.bv-actions[data-mode="wizard"] #bv-submit { display: none; }
.bv-actions[data-mode="wizard"][data-step="5"] #bv-next   { display: none; }
.bv-actions[data-mode="wizard"][data-step="5"] #bv-submit { display: inline-flex; }

@media (prefers-reduced-motion: reduce) {
    .bv-wizard-fill, .bv-wizard-pip { transition: none !important; }
    .bv-panel.is-active, .bv-success-icon { animation: none !important; }
}
</style>

<div class="bv-root" data-mode="<?= Html::encode($mode) ?>">

    <?php
    // Hero shell stats — only meaningful in list mode (3 status buckets).
    // In wizard/success modes we hide them so the hero stays focused on the
    // current step / success message.
    $heroStats = [];
    if ($mode === 'list' && !empty($myBookings)) {
        $heroStats = [
            ['value' => (int) $bucketCounts['all'],      'label' => 'ทั้งหมด',
             'tone' => 'primary', 'clickable' => true, 'isActive' => true,
             'data' => ['status-filter' => 'all']],
            ['value' => (int) $bucketCounts['pending'],  'label' => 'รออนุมัติ',
             'tone' => 'warning', 'clickable' => true,
             'data' => ['status-filter' => 'pending']],
            ['value' => (int) $bucketCounts['approved'], 'label' => 'อนุมัติแล้ว',
             'tone' => 'success', 'clickable' => true,
             'data' => ['status-filter' => 'approved']],
        ];
    }
    ?>

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'car',
        'title'    => 'จองรถราชการ',
        'subtitle' => $this->params['mobileSubtitle'],
        'stats'    => $heroStats,
    ]) ?>

    <div class="app-scroll bv-scroll">

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert">
                <i data-lucide="alert-circle" class="mi-sm mi-baseline me-1"></i>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════════════════════
             MODE 1 — LIST (entry: my requests + search)
             ═══════════════════════════════════════════════════════════════ -->
        <section class="bv-mode bv-mode-list" data-mode-section="list" <?= $mode !== 'list' ? 'hidden' : '' ?>>

            <?php if (!empty($myBookings)): ?>
                <div class="bv-list-toolbar rounded-3 mx-3 mt-4 mb-0">
                    <input type="search"
                    id="bv-list-search"
                    class="bv-search"
                    placeholder="ค้นหารหัส, สถานที่, วัตถุประสงค์"
                    autocomplete="off"
                    aria-label="ค้นหารายการคำขอ">
                </div>
            <?php endif; ?>

            <?php if (empty($myBookings)): ?>
                <div class="bv-list-empty">
                    <span class="bv-list-empty-icon" aria-hidden="true">
                        <i data-lucide="car" class="mi-xl"></i>
                    </span>
                    <p class="bv-list-empty-title">ยังไม่มีคำขอจองรถ</p>
                    <p class="bv-list-empty-text">เริ่มคำขอแรกของคุณ เจ้าหน้าที่จะตรวจสอบและจัดสรรรถให้ตามเวลาที่ระบุ</p>
                </div>
            <?php else: ?>
                <div class="bv-list" id="bv-list">
                    <?php foreach ($myBookings as $b):
                        $info       = $statusInfo((string) $b->status);
                        $bucket     = $info['bucket'];
                        $tone       = $info['tone'];
                        $statusLbl  = $info['lbl'];
                        $locTitle   = ($b->locationOrg && !empty($b->locationOrg->title)) ? $b->locationOrg->title : (string) $b->location;
                        $reasonTxt  = trim((string) $b->reason);
                        $title      = $locTitle !== '' ? 'ไป ' . $locTitle : ($reasonTxt !== '' ? $reasonTxt : 'คำขอจองรถ');
                        $startThai  = $formatThaiDate((string) $b->date_start);
                        $endThai    = $formatThaiDate((string) $b->date_end);
                        $datesTxt   = ($b->date_start === $b->date_end || !$b->date_end) ? $startThai : ($startThai . ' → ' . $endThai);
                        $timeTxt    = trim(substr((string) $b->time_start, 0, 5));
                        $isUrgent   = in_array((string) $b->urgent, ['ด่วน', 'ด่วนที่สุด'], true);

                        // searchable string (lowercased) for client filter
                        $search = mb_strtolower(implode(' ', array_filter([
                            (string) $b->code,
                            $locTitle,
                            $reasonTxt,
                            $statusLbl,
                            $datesTxt,
                        ])), 'UTF-8');
                    ?>
                        <a class="bv-list-card"
                           href="<?= Html::encode(Url::to(['/mobile/default/vehicle-view', 'id' => $b->id])) ?>"
                           data-status="<?= Html::encode($bucket) ?>"
                           data-search="<?= Html::encode($search) ?>">
                            <header class="bv-list-card-head">
                                <span class="bv-list-code"><?= Html::encode((string) $b->code) ?></span>
                                <span class="bv-list-pill is-<?= Html::encode($tone) ?>"><?= Html::encode($statusLbl) ?></span>
                            </header>
                            <h3 class="bv-list-title"><?= Html::encode($title) ?></h3>
                            <div class="bv-list-meta">
                                <span class="bv-list-meta-item">
                                    <i data-lucide="calendar" aria-hidden="true"></i>
                                    <?= Html::encode($datesTxt) ?>
                                </span>
                                <?php if ($timeTxt): ?>
                                    <span class="bv-list-meta-item">
                                        <i data-lucide="clock" aria-hidden="true"></i>
                                        <?= Html::encode($timeTxt) ?> น.
                                    </span>
                                <?php endif; ?>
                                <?php if ($isUrgent): ?>
                                    <span class="bv-list-urgent"><?= Html::encode((string) $b->urgent) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="bv-list-no-results" id="bv-list-no-results" role="status" hidden>
                    ไม่พบรายการที่ตรงกับการค้นหา
                </p>
            <?php endif; ?>
        </section>

        <!-- ════════════════════════════════════════════════════════════════
             MODE 3 — SUCCESS (exit: confirmation after save)
             ═══════════════════════════════════════════════════════════════ -->
        <section class="bv-mode bv-mode-success" data-mode-section="success" <?= $mode !== 'success' ? 'hidden' : '' ?>>
            <div class="bv-success">
                <div class="bv-success-card">
                    <span class="bv-success-icon" aria-hidden="true">
                        <i data-lucide="check"></i>
                    </span>
                    <h2 class="bv-success-title">บันทึกคำขอเรียบร้อย</h2>
                    <p class="bv-success-text">คำขอจองรถถูกส่งให้เจ้าหน้าที่ตรวจสอบแล้ว ระบบจะแจ้งผลผ่านการแจ้งเตือน</p>
                    <?php if ($flashCode): ?>
                        <span class="bv-success-code">
                            <i data-lucide="hash" aria-hidden="true"></i>
                            <?= Html::encode($flashCode) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="bv-success-fineprint">
                    <strong>ต่อไปนี้คืออะไร</strong><br>
                    เจ้าหน้าที่งานยานพาหนะจะตรวจสอบและจัดสรรรถให้ตามเวลาที่ระบุ
                    คุณสามารถดูสถานะคำขอได้จากหน้ารายการ
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════════════════
             MODE 2 — WIZARD (create: 5-step form)
             ═══════════════════════════════════════════════════════════════ -->
        <section class="bv-mode bv-mode-wizard" data-mode-section="wizard" <?= $mode !== 'wizard' ? 'hidden' : '' ?>>

            <nav class="bv-wizard" id="bv-wizard" aria-label="ขั้นตอนการจองรถ">
                <div class="bv-wizard-track" role="progressbar"
                     aria-valuemin="1" aria-valuemax="5" aria-valuenow="1"
                     aria-label="ความคืบหน้า">
                    <div class="bv-wizard-fill" id="bv-wizard-fill"></div>
                </div>
                <ol class="bv-wizard-steps">
                    <?php foreach ([
                        1 => 'เดินทาง',
                        2 => 'ผู้ขอ',
                        3 => 'รถและคนขับ',
                        4 => 'ตรวจสอบ',
                        5 => 'ยืนยัน',
                    ] as $num => $lbl): ?>
                        <li class="bv-wizard-step <?= $num === 1 ? 'is-active' : '' ?>" data-step="<?= $num ?>">
                            <span class="bv-wizard-pip">
                                <span class="bv-wizard-pip-num"><?= $num ?></span>
                                <i data-lucide="check" class="bv-wizard-pip-check"></i>
                            </span>
                            <span><?= Html::encode($lbl) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <div class="bv-body px-3">

                <?php if (!empty($saveErrors)): ?>
                    <div class="bv-error-summary mb-3" role="alert">
                        <i data-lucide="alert-triangle" class="mi-sm flex-shrink-0 mt-1"></i>
                        <div>
                            <strong class="d-block mb-1">กรุณาตรวจสอบฟิลด์ที่กรอก</strong>
                            <ul>
                                <?php foreach ($saveErrors as $attr => $msg): ?>
                                    <li><?= Html::encode(is_string($msg) ? $msg : (string) reset((array) $msg)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id'      => 'mobile-booking-vehicle-form',
                    'method'  => 'post',
                    'options' => ['novalidate' => 'novalidate'],
                    'fieldConfig' => [
                        'options'      => ['class' => 'mb-3'],
                        'labelOptions' => ['class' => 'form-label'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ],
                ]); ?>

                <input type="hidden"
                       name="<?= Html::encode(Html::getInputName($model, 'refer_type')) ?>"
                       value="<?= Html::encode((string) ($model->refer_type ?? 'normal')) ?>">

                <!-- ─── Step 1: ข้อมูลการเดินทาง ─── -->
                <section class="bv-panel is-active" data-step-panel="1" data-step-title="การเดินทาง">
                    <header class="bv-panel-head">
                        <p class="bv-panel-eyebrow">ขั้นตอนที่ 1 จาก 5</p>
                        <h2 class="bv-panel-title">ข้อมูลการเดินทาง</h2>
                        <p class="bv-panel-desc">วัน เวลา จุดหมาย และวัตถุประสงค์ของการใช้รถ</p>
                    </header>

                    <div class="bv-card">
                        <div class="mb-3">
                            <label class="form-label is-req">ประเภทการเดินทาง</label>
                            <div class="pill-group" role="radiogroup" aria-label="ประเภทการเดินทาง" aria-required="true">
                                <?php foreach ([1 => 'ไปกลับวันเดียว', 2 => 'ค้างคืน'] as $val => $lab): ?>
                                    <?php $checked = $existingGoType === $val; ?>
                                    <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                        <input type="radio"
                                               name="<?= Html::encode(Html::getInputName($model, 'go_type')) ?>"
                                               value="<?= $val ?>"
                                               <?= $checked ? 'checked' : '' ?>
                                               data-pill-target="bv-go-type">
                                        <?= Html::encode($lab) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bv-go-type" value="<?= $existingGoType ?>">
                        </div>

                        <div class="bv-row bv-row-2 mb-3">
                            <?= $form->field($model, 'date_start', [
                                'template'     => '{label}{input}{error}',
                                'options'      => ['class' => 'mb-0'],
                                'labelOptions' => ['class' => 'form-label is-req'],
                            ])->widget(DatepickerThai::class, [
                                'options' => ['class' => 'form-control', 'placeholder' => 'วันเริ่ม', 'autocomplete' => 'off', 'aria-required' => 'true', 'required' => true],
                            ])->label('วันที่ใช้งาน') ?>
                            <?= $form->field($model, 'time_start', [
                                'template'     => '{label}{input}{error}',
                                'options'      => ['class' => 'mb-0'],
                                'labelOptions' => ['class' => 'form-label is-req'],
                            ])->input('time', ['step' => 300, 'aria-required' => 'true', 'required' => true])->label('เวลาออกเดินทาง') ?>
                        </div>

                        <div class="bv-row bv-row-2 mb-3" id="bv-end-row">
                            <?= $form->field($model, 'date_end', [
                                'template'     => '{label}{input}{error}',
                                'options'      => ['class' => 'mb-0'],
                            ])->widget(DatepickerThai::class, [
                                'options' => ['class' => 'form-control', 'placeholder' => 'วันสิ้นสุด', 'autocomplete' => 'off'],
                            ])->label('วันสิ้นสุด') ?>
                            <?= $form->field($model, 'time_end', [
                                'template'     => '{label}{input}{error}',
                                'options'      => ['class' => 'mb-0'],
                                'labelOptions' => ['class' => 'form-label is-req'],
                            ])->input('time', ['step' => 300, 'aria-required' => 'true', 'required' => true])->label('เวลาเดินทางกลับ') ?>
                        </div>

                        <?= $form->field($model, 'location', [
                            'labelOptions' => ['class' => 'form-label is-req'],
                        ])->widget(Select2::class, [
                            'data'    => $model->ListOrg(),
                            'options' => [
                                'placeholder'   => 'ค้นหาหรือพิมพ์จุดหมายปลายทาง',
                                'aria-required' => 'true',
                                'aria-label'    => 'จุดหมายปลายทาง',
                                'required'      => true,
                            ],
                            'pluginOptions' => [
                                'tags'                    => true,
                                'allowClear'              => true,
                                'minimumResultsForSearch' => 0,
                                'tokenSeparators'         => [],
                                'language'                => [
                                    'noResults'     => new JsExpression('function(){ return "ไม่พบในรายการ — กด Enter เพื่อใช้ข้อความนี้"; }'),
                                    'searching'     => new JsExpression('function(){ return "กำลังค้นหา..."; }'),
                                    'inputTooShort' => new JsExpression('function(){ return "พิมพ์อย่างน้อย 1 ตัวอักษร"; }'),
                                ],
                            ],
                        ])->label('จุดหมายปลายทาง') ?>

                        <?= $form->field($model, 'reason', [
                            'labelOptions' => ['class' => 'form-label is-req'],
                            'options'      => ['class' => 'mb-0'],
                        ])->textarea([
                            'rows' => 3,
                            'placeholder' => 'ระบุวัตถุประสงค์ของการใช้รถ',
                            'aria-required' => 'true',
                            'required' => true,
                        ])->label('วัตถุประสงค์การใช้รถ') ?>
                    </div>
                </section>

                <!-- ─── Step 2: รายละเอียดผู้ขอ ─── -->
                <section class="bv-panel" data-step-panel="2" data-step-title="ผู้ขอใช้รถ" hidden>
                    <header class="bv-panel-head">
                        <p class="bv-panel-eyebrow">ขั้นตอนที่ 2 จาก 5</p>
                        <h2 class="bv-panel-title">รายละเอียดผู้ขอใช้รถ</h2>
                        <p class="bv-panel-desc">ตรวจสอบข้อมูลผู้ขอและช่องทางติดต่อ</p>
                    </header>

                    <div class="bv-card">
                        <div class="bv-kv-list">
                            <div class="bv-kv">
                                <span class="bv-kv-icon" aria-hidden="true"><i data-lucide="user"></i></span>
                                <div class="bv-kv-body">
                                    <p class="bv-kv-key mb-0">ผู้ขอใช้รถ</p>
                                    <p class="bv-kv-val mb-0"><?= Html::encode($requesterName) ?></p>
                                </div>
                            </div>
                            <div class="bv-kv">
                                <span class="bv-kv-icon" aria-hidden="true"><i data-lucide="building-2"></i></span>
                                <div class="bv-kv-body">
                                    <p class="bv-kv-key mb-0">หน่วยงาน</p>
                                    <p class="bv-kv-val mb-0"><?= Html::encode($requesterDept) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bv-card">
                        <div class="mb-3">
                            <label for="bv-phone" class="form-label is-req">เบอร์โทรศัพท์ติดต่อ</label>
                            <input type="tel"
                                   id="bv-phone"
                                   name="<?= Html::encode(Html::getInputName($model, 'data_json[phone]')) ?>"
                                   value="<?= Html::encode($existingPhone) ?>"
                                   class="form-control"
                                   inputmode="tel"
                                   autocomplete="tel"
                                   placeholder="08X-XXX-XXXX"
                                   aria-required="true"
                                   required>
                        </div>

                        <div class="mb-0">
                            <label for="bv-passengers" class="form-label is-req">จำนวนผู้โดยสาร</label>
                            <input type="number"
                                   id="bv-passengers"
                                   name="<?= Html::encode(Html::getInputName($model, 'data_json[passengers]')) ?>"
                                   value="<?= (int) $existingPassengers ?>"
                                   class="form-control"
                                   inputmode="numeric"
                                   min="1" max="99"
                                   aria-required="true"
                                   required>
                        </div>
                    </div>
                </section>

                <!-- ─── Step 3: รถและคนขับ ─── -->
                <section class="bv-panel" data-step-panel="3" data-step-title="รถและคนขับ" hidden>
                    <header class="bv-panel-head">
                        <p class="bv-panel-eyebrow">ขั้นตอนที่ 3 จาก 5</p>
                        <h2 class="bv-panel-title">รายละเอียดรถและคนขับ</h2>
                        <p class="bv-panel-desc">เลือกประเภทรถและระบุผู้ขับขี่ตามต้องการ</p>
                    </header>

                    <div class="bv-card">
                        <div class="mb-3">
                            <label class="form-label is-req">ประเภทรถ</label>
                            <div class="pill-group" role="radiogroup" aria-label="ประเภทรถ" aria-required="true">
                                <?php foreach (['general' => 'รถยนต์ทั่วไป', 'ambulance' => 'รถพยาบาล'] as $val => $lab): ?>
                                    <?php $checked = $existingVehicleType === $val; ?>
                                    <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                        <input type="radio"
                                               name="<?= Html::encode(Html::getInputName($model, 'vehicle_type_id')) ?>"
                                               value="<?= Html::encode($val) ?>"
                                               <?= $checked ? 'checked' : '' ?>
                                               data-pill-target="bv-vehicle-type">
                                        <?= Html::encode($lab) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bv-vehicle-type" value="<?= Html::encode($existingVehicleType) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="bv-plate" class="form-label">ทะเบียน / หมายเลขรถที่ต้องการ
                                <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                            </label>
                            <input type="text"
                                   id="bv-plate"
                                   name="<?= Html::encode(Html::getInputName($model, 'license_plate')) ?>"
                                   value="<?= Html::encode($existingPlate) ?>"
                                   class="form-control"
                                   placeholder="เว้นว่างให้เจ้าหน้าที่จัดสรรอัตโนมัติ">
                        </div>

                        <div class="mb-3">
                            <label class="form-label is-req">ระดับความเร่งด่วน</label>
                            <div class="pill-group" role="radiogroup" aria-label="ระดับความเร่งด่วน" aria-required="true">
                                <?php foreach (['ปกติ' => 'ปกติ', 'ด่วน' => 'ด่วน', 'ด่วนที่สุด' => 'ด่วนที่สุด'] as $val => $lab): ?>
                                    <?php $checked = $existingUrgent === $val; ?>
                                    <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                        <input type="radio"
                                               name="<?= Html::encode(Html::getInputName($model, 'urgent')) ?>"
                                               value="<?= Html::encode($val) ?>"
                                               <?= $checked ? 'checked' : '' ?>
                                               data-pill-target="bv-urgent">
                                        <?= Html::encode($lab) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bv-urgent" value="<?= Html::encode($existingUrgent) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ผู้ขับรถ
                                <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                            </label>
                            <div class="pill-group" role="radiogroup" aria-label="ผู้ขับรถ">
                                <?php foreach (['' => 'ไม่ระบุ', 'self' => 'ขับเอง', 'driver' => 'พนักงาน'] as $val => $lab): ?>
                                    <?php $checked = $existingDriver === (string) $val; ?>
                                    <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                        <input type="radio"
                                               name="<?= Html::encode(Html::getInputName($model, 'data_json[driver]')) ?>"
                                               value="<?= Html::encode((string) $val) ?>"
                                               <?= $checked ? 'checked' : '' ?>
                                               data-pill-target="bv-driver">
                                        <?= Html::encode($lab) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bv-driver" value="<?= Html::encode($existingDriver) ?>">
                        </div>

                        <div class="mb-0">
                            <label for="bv-notes" class="form-label">หมายเหตุเพิ่มเติม
                                <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                            </label>
                            <textarea id="bv-notes"
                                      name="<?= Html::encode(Html::getInputName($model, 'data_json[notes]')) ?>"
                                      class="form-control"
                                      rows="3"
                                      placeholder="ข้อมูลเพิ่มเติมที่เจ้าหน้าที่ควรทราบ"><?= Html::encode($existingNotes) ?></textarea>
                        </div>
                    </div>
                </section>

                <!-- ─── Step 4: ตรวจสอบ ─── -->
                <section class="bv-panel" data-step-panel="4" data-step-title="ตรวจสอบข้อมูล" hidden>
                    <header class="bv-panel-head">
                        <p class="bv-panel-eyebrow">ขั้นตอนที่ 4 จาก 5</p>
                        <h2 class="bv-panel-title">ตรวจสอบข้อมูล</h2>
                        <p class="bv-panel-desc">ยืนยันความถูกต้องก่อนส่งคำขอ กดแก้ไขเพื่อกลับไปแก้</p>
                    </header>

                    <div class="bv-summary-card">
                        <header class="bv-summary-head">
                            <h3 class="bv-summary-title"><i data-lucide="calendar-range"></i> การเดินทาง</h3>
                            <button type="button" class="bv-summary-edit" data-jump-step="1">
                                <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                            </button>
                        </header>
                        <div class="bv-summary-body">
                            <dl class="bv-summary-dl" data-summary="trip"></dl>
                        </div>
                    </div>

                    <div class="bv-summary-card">
                        <header class="bv-summary-head">
                            <h3 class="bv-summary-title"><i data-lucide="user"></i> ผู้ขอใช้รถ</h3>
                            <button type="button" class="bv-summary-edit" data-jump-step="2">
                                <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                            </button>
                        </header>
                        <div class="bv-summary-body">
                            <dl class="bv-summary-dl" data-summary="requester"></dl>
                        </div>
                    </div>

                    <div class="bv-summary-card">
                        <header class="bv-summary-head">
                            <h3 class="bv-summary-title"><i data-lucide="car"></i> รถและคนขับ</h3>
                            <button type="button" class="bv-summary-edit" data-jump-step="3">
                                <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                            </button>
                        </header>
                        <div class="bv-summary-body">
                            <dl class="bv-summary-dl" data-summary="vehicle"></dl>
                        </div>
                    </div>

                    <div class="bv-completeness" id="bv-completeness" role="status" aria-live="polite">
                        <i data-lucide="check-circle"></i>
                        <span id="bv-completeness-text">ข้อมูลครบถ้วน พร้อมส่งคำขอ</span>
                    </div>
                </section>

                <!-- ─── Step 5: ยืนยัน ─── -->
                <section class="bv-panel" data-step-panel="5" data-step-title="ยืนยันการจอง" hidden>
                    <header class="bv-panel-head">
                        <p class="bv-panel-eyebrow">ขั้นตอนสุดท้าย</p>
                        <h2 class="bv-panel-title">ยืนยันการจอง</h2>
                        <p class="bv-panel-desc">ตรวจสอบความถูกต้องครั้งสุดท้ายก่อนกดส่งคำขอ</p>
                    </header>

                    <div class="bv-confirm-card">
                        <label class="bv-confirm-row" for="bv-confirm-chk">
                            <input type="checkbox" id="bv-confirm-chk" aria-describedby="bv-confirm-fineprint">
                            <span class="bv-confirm-text">
                                ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้องและขอใช้รถเพื่อปฏิบัติงานราชการตามวัตถุประสงค์ที่ระบุ
                            </span>
                        </label>
                        <p class="bv-confirm-fineprint" id="bv-confirm-fineprint">
                            เจ้าหน้าที่จะตรวจสอบคำขอและแจ้งผลผ่านระบบ
                            หากต้องการแก้ไขหลังส่งคำขอ ให้ติดต่องานยานพาหนะ
                        </p>
                    </div>
                </section>

                <?php ActiveForm::end(); ?>
            </div>
        </section>

    </div>

    <!-- ════════════════════════════════════════════════════════════════
         UNIFIED bottom action bar — buttons swap by mode
         ═══════════════════════════════════════════════════════════════ -->
    <div class="bv-actions" id="bv-actions" data-mode="<?= Html::encode($mode) ?>" data-step="1">

        <!-- Mode 1 (list): single CTA -->
        <a href="<?= Html::encode($newUrl) ?>"
           class="btn btn-primary bv-mode-action"
           data-for-mode="list">
            <i data-lucide="plus"></i>
            <span>สร้างคำขอใหม่</span>
        </a>

        <!-- Mode 2 (wizard): Prev + Next + Submit -->
        <button type="button" class="btn btn-prev bv-mode-action" data-for-mode="wizard" id="bv-prev">
            <i data-lucide="arrow-left"></i>
            <span>ย้อนกลับ</span>
        </button>
        <button type="button" class="btn btn-primary bv-mode-action" data-for-mode="wizard" id="bv-next">
            <span>ถัดไป</span>
            <i data-lucide="arrow-right"></i>
        </button>
        <?= Html::submitButton(
            '<i data-lucide="send"></i> <span>' . Html::encode($submitText) . '</span>',
            [
                'class'    => 'btn btn-primary bv-mode-action',
                'id'       => 'bv-submit',
                'name'     => 'action',
                'value'    => 'submit',
                'form'     => 'mobile-booking-vehicle-form',
                'data'     => ['for-mode' => 'wizard'],
                'disabled' => true,
            ]
        ) ?>

        <!-- Mode 3 (success): 2 buttons -->
        <a href="<?= Html::encode($baseUrl) ?>"
           class="btn btn-prev bv-mode-action"
           data-for-mode="success">
            <i data-lucide="list"></i>
            <span>ดูคำขอทั้งหมด</span>
        </a>
        <a href="<?= Html::encode($newUrl) ?>"
           class="btn btn-primary bv-mode-action"
           data-for-mode="success">
            <i data-lucide="plus"></i>
            <span>สร้างใหม่</span>
        </a>
    </div>
</div>

<?php
$wizardActive = ($mode === 'wizard') ? '1' : '0';
$js = <<<JS
(function() {
    'use strict';

    var WIZARD_ACTIVE = {$wizardActive};

    // ── Pill group: mirror radio to hidden input + active state ──────────
    document.querySelectorAll('.pill-group').forEach(function(group) {
        group.querySelectorAll('input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (!this.checked) return;
                group.querySelectorAll('.pill-option').forEach(function(opt) { opt.classList.remove('is-active'); });
                var label = this.closest('label');
                if (label) label.classList.add('is-active');
                var targetId = this.dataset.pillTarget;
                if (targetId) {
                    var t = document.getElementById(targetId);
                    if (t) { t.value = this.value; t.dispatchEvent(new Event('change', { bubbles: true })); }
                }
            });
        });
    });

    // ── go_type toggles end-date row (ค้างคืน = show, ไปกลับ = hide date_end)
    var goTypeEl = document.getElementById('bv-go-type');
    var endRow   = document.getElementById('bv-end-row');
    function syncEndDate() {
        if (!goTypeEl || !endRow) return;
        var isOvernight = String(goTypeEl.value) === '2';
        var dateEndField = endRow.querySelector('.field-vehicle-date_end, [class*="field-vehicle-date_end"]');
        if (dateEndField) dateEndField.style.display = isOvernight ? '' : 'none';
    }
    if (goTypeEl) goTypeEl.addEventListener('change', syncEndDate);
    syncEndDate();

    // ════════════════════════════════════════════════════════════════════
    // MODE 1 — list filter (search + hero stats overlay as filter chips)
    // ════════════════════════════════════════════════════════════════════
    (function() {
        var search = document.getElementById('bv-list-search');
        var list   = document.getElementById('bv-list');
        var empty  = document.getElementById('bv-list-no-results');
        var stats  = document.querySelectorAll('.app-stat[data-status-filter]');
        if (!list) return;

        var currentStatus = 'all';
        var currentQuery  = '';

        function applyFilters() {
            var q = currentQuery.toLowerCase().trim();
            var cards = list.querySelectorAll('.bv-list-card');
            var shown = 0;
            cards.forEach(function(card) {
                var matchStatus = currentStatus === 'all' || card.dataset.status === currentStatus;
                var matchSearch = !q || (card.dataset.search || '').indexOf(q) !== -1;
                var show = matchStatus && matchSearch;
                card.hidden = !show;
                if (show) shown++;
            });
            if (empty) empty.hidden = shown > 0;
        }

        if (search) {
            search.addEventListener('input', function() {
                currentQuery = this.value || '';
                applyFilters();
            });
        }
        // Hero stats overlay → click to filter. Intercept click so the
        // <button> doesn't accidentally submit anything.
        stats.forEach(function(stat) {
            stat.addEventListener('click', function(e) {
                e.preventDefault();
                stats.forEach(function(s) { s.classList.remove('is-active'); });
                this.classList.add('is-active');
                currentStatus = this.dataset.statusFilter || 'all';
                applyFilters();
            });
        });
    })();

    // ════════════════════════════════════════════════════════════════════
    // MODE 2 — wizard state machine (only runs when wizard mode is active)
    // ════════════════════════════════════════════════════════════════════
    var TOTAL_STEPS = 5;
    var currentStep = 1;

    var panels     = document.querySelectorAll('.bv-panel');
    var steps      = document.querySelectorAll('.bv-wizard-step');
    var fill       = document.getElementById('bv-wizard-fill');
    var track      = document.querySelector('.bv-wizard-track');
    var actions    = document.getElementById('bv-actions');
    var prevBtn    = document.getElementById('bv-prev');
    var nextBtn    = document.getElementById('bv-next');
    var submitBtn  = document.getElementById('bv-submit');
    var confirmChk = document.getElementById('bv-confirm-chk');
    var scrollEl   = document.querySelector('.app-scroll');
    var formEl     = document.getElementById('mobile-booking-vehicle-form');

    function setStep(n) {
        n = Math.max(1, Math.min(TOTAL_STEPS, n));
        currentStep = n;

        panels.forEach(function(p) {
            var pStep = Number(p.dataset.stepPanel);
            var active = pStep === n;
            if (active) { p.hidden = false; p.classList.add('is-active'); }
            else        { p.hidden = true;  p.classList.remove('is-active'); }
        });
        steps.forEach(function(s) {
            var sStep = Number(s.dataset.step);
            s.classList.toggle('is-active', sStep === n);
            s.classList.toggle('is-done',   sStep < n);
        });

        var pct = (n / TOTAL_STEPS) * 100;
        if (fill)  fill.style.width = pct + '%';
        if (track) track.setAttribute('aria-valuenow', String(n));
        if (actions) actions.dataset.step = String(n);

        var isLast = (n === TOTAL_STEPS);
        if (submitBtn) submitBtn.disabled = isLast ? !(confirmChk && confirmChk.checked) : true;

        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        if (n === 4) populateSummary();

        if (scrollEl) {
            var top = document.getElementById('bv-wizard');
            if (top) top.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function validateStep(n) {
        var panel = document.querySelector('[data-step-panel="' + n + '"]');
        if (!panel) return true;
        var firstInvalid = null;
        var inputs = panel.querySelectorAll('input, select, textarea');
        inputs.forEach(function(el) {
            el.classList.remove('bv-field-error');
            if (el.type === 'hidden' || el.disabled || el.hidden) return;
            if (el.willValidate && !el.checkValidity()) {
                if (!firstInvalid) firstInvalid = el;
                el.classList.add('bv-field-error');
            }
        });
        if (n === 1) {
            var loc = panel.querySelector('select[id\$="-location"]');
            if (loc && !loc.value && !firstInvalid) firstInvalid = loc;
        }
        if (firstInvalid) {
            try { firstInvalid.focus({ preventScroll: false }); } catch (e) {}
            if (firstInvalid.tagName === 'SELECT') {
                var s2 = firstInvalid.closest('.form-group, .mb-3, .field-vehicle-location');
                if (s2) {
                    var rendered = s2.querySelector('.select2-selection');
                    if (rendered) rendered.focus();
                }
            }
            return false;
        }
        return true;
    }

    // Summary populator
    function val(sel) { var el = document.querySelector(sel); return el ? (el.value || '').trim() : ''; }
    function textOfSelect(sel) {
        var el = document.querySelector(sel);
        if (!el || el.tagName !== 'SELECT') return val(sel);
        var o = el.options[el.selectedIndex];
        return o ? o.text.trim() : '';
    }
    function pillLabel(name) {
        var p = document.querySelector('input[name="' + name + '"]:checked');
        if (!p) return '';
        var l = p.closest('label');
        return l ? l.textContent.trim() : p.value;
    }
    function dl(target, rows) {
        var node = document.querySelector('[data-summary="' + target + '"]');
        if (!node) return;
        node.innerHTML = '';
        rows.forEach(function(r) {
            var dt = document.createElement('dt'); dt.textContent = r.key;
            var dd = document.createElement('dd'); dd.textContent = r.val || 'ไม่ระบุ';
            if (!r.val) dd.classList.add('is-empty');
            node.appendChild(dt); node.appendChild(dd);
        });
    }
    function getReadonlyKv(keyText) {
        var kvs = document.querySelectorAll('.bv-kv');
        for (var i = 0; i < kvs.length; i++) {
            var k = kvs[i].querySelector('.bv-kv-key');
            if (k && k.textContent.trim() === keyText) {
                var v = kvs[i].querySelector('.bv-kv-val');
                return v ? v.textContent.trim() : '';
            }
        }
        return '';
    }
    function populateSummary() {
        var goType    = pillLabel('Vehicle[go_type]') || 'ไม่ระบุ';
        var dateStart = val('#vehicle-date_start');
        var timeStart = val('#vehicle-time_start');
        var dateEnd   = val('#vehicle-date_end');
        var timeEnd   = val('#vehicle-time_end');
        var location  = textOfSelect('select[id\$="-location"]') || val('select[id\$="-location"]');
        var reason    = val('#vehicle-reason');
        var phone     = val('#bv-phone');
        var passengers = val('#bv-passengers');
        var vehicleType = pillLabel('Vehicle[vehicle_type_id]');
        var plate     = val('#bv-plate');
        var urgent    = pillLabel('Vehicle[urgent]');
        var driver    = pillLabel('Vehicle[data_json][driver]');
        var notes     = val('#bv-notes');

        var tripDate = dateStart + (timeStart ? ' · ' + timeStart + ' น.' : '');
        var tripEnd  = '';
        if (goTypeEl && String(goTypeEl.value) === '2') {
            tripEnd = (dateEnd || '—') + (timeEnd ? ' · ' + timeEnd + ' น.' : '');
        } else if (timeEnd) {
            tripEnd = 'กลับ ' + timeEnd + ' น.';
        }

        dl('trip', [
            { key: 'ประเภท',     val: goType },
            { key: 'วันที่ออก',   val: tripDate },
            { key: tripEnd ? 'เดินทางกลับ' : '',  val: tripEnd },
            { key: 'จุดหมาย',     val: location },
            { key: 'วัตถุประสงค์', val: reason },
        ].filter(function(r) { return r.key; }));

        dl('requester', [
            { key: 'ผู้ขอ',     val: getReadonlyKv('ผู้ขอใช้รถ') },
            { key: 'หน่วยงาน', val: getReadonlyKv('หน่วยงาน') },
            { key: 'เบอร์โทร', val: phone },
            { key: 'ผู้โดยสาร', val: passengers ? passengers + ' คน' : '' },
        ]);
        dl('vehicle', [
            { key: 'ประเภทรถ', val: vehicleType },
            { key: 'ทะเบียน',   val: plate },
            { key: 'ระดับด่วน', val: urgent },
            { key: 'ผู้ขับ',    val: driver },
            { key: 'หมายเหตุ',  val: notes },
        ]);

        var required = [goType, dateStart, timeStart, location, reason, phone, passengers, vehicleType, urgent];
        var missing = required.filter(function(v) { return !v; }).length;
        var chip = document.getElementById('bv-completeness');
        var chipText = document.getElementById('bv-completeness-text');
        if (chip) {
            chip.classList.toggle('is-missing', missing > 0);
            var iconHolder = chip.querySelector('svg, i');
            if (iconHolder) iconHolder.outerHTML = '<i data-lucide="' + (missing > 0 ? 'alert-triangle' : 'check-circle') + '"></i>';
        }
        if (chipText) {
            chipText.textContent = missing > 0
                ? ('ยังขาดข้อมูล ' + missing + ' ช่อง — กดแก้ไขเพื่อกลับไปแก้')
                : 'ข้อมูลครบถ้วน พร้อมส่งคำขอ';
        }
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
    }

    if (WIZARD_ACTIVE && nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (!validateStep(currentStep)) return;
            setStep(currentStep + 1);
        });
    }
    if (WIZARD_ACTIVE && prevBtn) {
        prevBtn.addEventListener('click', function() { setStep(currentStep - 1); });
    }
    document.querySelectorAll('.bv-summary-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var t = Number(this.dataset.jumpStep);
            if (t >= 1 && t <= TOTAL_STEPS) setStep(t);
        });
    });
    if (confirmChk && submitBtn) {
        confirmChk.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });
    }

    // Double-submit guard + handleFormSubmit hookup
    var submitting = false;
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            if (WIZARD_ACTIVE && currentStep !== TOTAL_STEPS) {
                e.preventDefault();
                if (validateStep(currentStep)) setStep(currentStep + 1);
                return false;
            }
            if (submitting) { e.preventDefault(); e.stopImmediatePropagation(); return false; }
            submitting = true;
            if (submitBtn) submitBtn.classList.add('is-busy');
            setTimeout(function() {
                submitting = false;
                if (submitBtn) submitBtn.classList.remove('is-busy');
            }, 400);
        });
    }
    if (typeof handleFormSubmit === 'function') {
        handleFormSubmit('#mobile-booking-vehicle-form', null, function(response) {});
    }

    // ════════════════════════════════════════════════════════════════════
    // Server-side error reporting (banner + auto-jump to step)
    // ════════════════════════════════════════════════════════════════════
    var FIELD_TO_STEP = {
        'vehicle-go_type': 1, 'vehicle-date_start': 1, 'vehicle-time_start': 1,
        'vehicle-date_end': 1, 'vehicle-time_end': 1, 'vehicle-location': 1, 'vehicle-reason': 1,
        'vehicle-vehicle_type_id': 3, 'vehicle-license_plate': 3, 'vehicle-urgent': 3
    };
    var FIELD_LABEL = {
        'vehicle-go_type': 'ประเภทการเดินทาง', 'vehicle-date_start': 'วันที่ใช้งาน',
        'vehicle-time_start': 'เวลาออกเดินทาง', 'vehicle-date_end': 'วันที่สิ้นสุด',
        'vehicle-time_end': 'เวลาเดินทางกลับ', 'vehicle-location': 'จุดหมายปลายทาง',
        'vehicle-reason': 'วัตถุประสงค์การใช้รถ', 'vehicle-vehicle_type_id': 'ประเภทรถ',
        'vehicle-license_plate': 'ทะเบียนรถ', 'vehicle-urgent': 'ระดับความเร่งด่วน',
        'go_type': 'ประเภทการเดินทาง', 'date_start': 'วันที่ใช้งาน', 'time_start': 'เวลาออกเดินทาง',
        'date_end': 'วันที่สิ้นสุด', 'time_end': 'เวลาเดินทางกลับ', 'location': 'จุดหมายปลายทาง',
        'reason': 'วัตถุประสงค์การใช้รถ', 'vehicle_type_id': 'ประเภทรถ',
        'license_plate': 'ทะเบียนรถ', 'urgent': 'ระดับความเร่งด่วน'
    };
    function fieldLabel(id) { return FIELD_LABEL[id] || ('ฟิลด์ ' + id); }
    function fieldStep(id)  { return FIELD_TO_STEP[id] || FIELD_TO_STEP['vehicle-' + id] || null; }

    function renderErrorBanner(messages, jumpStep) {
        var body = document.querySelector('.bv-mode-wizard .bv-body');
        if (!body) return;
        var existing = document.getElementById('bv-server-errors');
        if (existing) existing.remove();
        if (!messages || !messages.length) return;
        var box = document.createElement('div');
        box.id = 'bv-server-errors';
        box.className = 'bv-error-summary mb-3';
        box.setAttribute('role', 'alert');
        box.innerHTML =
            '<i data-lucide="alert-triangle" class="mi-sm flex-shrink-0 mt-1"></i>' +
            '<div><strong class="d-block mb-1">กรอกข้อมูลไม่ครบ หรือไม่ถูกต้อง</strong>' +
            '<ul>' + messages.map(function(m) { return '<li>' + m + '</li>'; }).join('') + '</ul></div>';
        body.insertBefore(box, body.firstChild);
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        if (jumpStep) setStep(jumpStep);
    }
    function clearErrorBanner() {
        var existing = document.getElementById('bv-server-errors');
        if (existing) existing.remove();
    }
    var formContainer = document.querySelector('.bv-mode-wizard .bv-body');
    if (formContainer) {
        ['input', 'change'].forEach(function(evt) {
            formContainer.addEventListener(evt, clearErrorBanner, true);
        });
    }
    [nextBtn, prevBtn].forEach(function(b) { if (b) b.addEventListener('click', clearErrorBanner); });
    document.querySelectorAll('.bv-summary-edit').forEach(function(b) { b.addEventListener('click', clearErrorBanner); });

    if (window.jQuery && formEl) {
        \$(formEl).on('afterValidate', function(e, messages, errorAttributes) {
            if (!errorAttributes || !errorAttributes.length) return;
            var firstStep = null;
            var msgs = [];
            errorAttributes.forEach(function(err) {
                var id = err.id || '';
                var step = fieldStep(id);
                if (step && (!firstStep || step < firstStep)) firstStep = step;
                var txt = (messages && messages[id] && messages[id][0]) || 'ต้องระบุ';
                msgs.push(fieldLabel(id) + ': ' + txt);
            });
            renderErrorBanner(msgs, firstStep);
        });
        \$(document).on('ajaxComplete.bvDebug', function(e, xhr, settings) {
            if (!settings || !settings.url || settings.url.indexOf('booking-vehicle') === -1) return;
            var res = xhr.responseJSON;
            if (!res && xhr.responseText) { try { res = JSON.parse(xhr.responseText); } catch (e2) {} }
            if (!res) return;
            if (res.errors && typeof res.errors === 'object' && !Array.isArray(res.errors)) {
                var firstStep = null, msgs = [];
                Object.keys(res.errors).forEach(function(id) {
                    var step = fieldStep(id);
                    if (step && (!firstStep || step < firstStep)) firstStep = step;
                    var arr = res.errors[id];
                    var txt = (Array.isArray(arr) && arr.length) ? arr[0] : 'ต้องระบุ';
                    msgs.push(fieldLabel(id) + ': ' + txt);
                });
                if (msgs.length) renderErrorBanner(msgs, firstStep);
            } else if (res.status === 'error' && res.message) {
                renderErrorBanner([res.message], null);
            }
        });
    }

    // Initial wizard render (only if wizard mode active)
    if (WIZARD_ACTIVE) setStep(1);
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
