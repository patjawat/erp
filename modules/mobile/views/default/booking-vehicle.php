<?php

use app\components\ThaiDateHelper;
use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Vehicle $model */
/** @var \app\modules\hr\models\Employees|null $employee */
/** @var \app\modules\booking\models\Vehicle[] $myBookings  รายการคำขอของผู้ใช้ปัจจุบัน (controller เตรียมให้) */
/** @var array $saveErrors */
/** @var string|null $forceMode */
/** @var bool|null $isEdit */
/** @var array $cars     รายการรถ (controller เตรียมให้ผ่าน MobileVehicleService::listCars) */
/** @var array $drivers  รายการคนขับ (controller เตรียมให้ผ่าน MobileVehicleService::listDrivers) */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle']  = 'จองรถราชการ';

$myBookings = $myBookings ?? [];
$saveErrors = $saveErrors ?? [];
$cars       = $cars ?? [];
$drivers    = $drivers ?? [];
$fiscalYears = $fiscalYears ?? [];
$filterYear = (int) ($filterYear ?? 0);
$currentYear = (int) ($currentYear ?? 0);
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}

// ═══════════════════════════════════════════════════════════════════════
// Mode detection — view คำนวณ mode จาก server-side state
// ═══════════════════════════════════════════════════════════════════════
// list    — entry: my requests with search + status filter
// wizard  — create/edit: 5-step form
// success — exit:  confirmation after save (flash success)
$hasFlashSuccess = Yii::$app->session->hasFlash('success');
$flashMessage    = $hasFlashSuccess ? (string) Yii::$app->session->getFlash('success') : '';
$flashCode       = '';
if ($flashMessage && preg_match('/รหัส\s*([A-Z0-9\-]+)/u', $flashMessage, $cm)) {
    $flashCode = $cm[1];
}
$actionParam = (string) (Yii::$app->request->get('action') ?? '');
$hasErrors   = !empty($saveErrors);
$isEdit      = (bool) ($isEdit ?? (!$model->isNewRecord));

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
    : ($mode === 'success' ? 'บันทึกคำขอเรียบร้อย' : 'รายการคำขอของฉัน' . ($filterYear > 0 ? ' ปีงบประมาณ ' . ($fiscalYears[$filterYear] ?? ('พ.ศ. ' . $filterYear)) : ''));

// Status bucket counts สำหรับ hero stats (presentation — ใช้ helper เดียวกับ partial)
$bucketCounts = MobileBookingStatus::bucketCounts($myBookings);

// Thai date formatter (presentation — partial รับเป็น callable)
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
// Wizard mode defaults (presentation layer — derived ค่าใน model ให้พร้อม render)
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
if (!empty($employee)) {
    try { if (!empty($employee->fullname)) $requesterName = $employee->fullname; } catch (\Throwable $e) {}
    try { if (method_exists($employee, 'departmentName') && $employee->departmentName()) $requesterDept = $employee->departmentName(); } catch (\Throwable $e) {}
    try { if (!empty($employee->phone)) $prefillPhone = (string) $employee->phone; } catch (\Throwable $e) {}
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

$baseUrl    = Url::to(['/mobile/default/booking-vehicle']);
$newUrl     = Url::to(['/mobile/default/booking-vehicle', 'action' => 'new']);
$detailUrl  = $isEdit ? Url::to(['/mobile/default/vehicle-view', 'id' => $model->id]) : $baseUrl;
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
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
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
    grid-template-columns: repeat(7, 1fr);
    gap: var(--space-2xs);
}
/* Compact stepper (progress bar + ชื่อขั้น) สำหรับ <380px */
.bv-stepper-compact { display: none; }
.bv-stepper-compact-label {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: var(--space-xs); margin-top: var(--space-xs);
    font-size: var(--fs-xs); color: var(--ink-3);
}
.bv-stepper-compact-num strong {
    color: var(--mobile-primary); font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.bv-stepper-compact-name {
    color: var(--ink-2); font-size: var(--fs-sm); font-weight: 700;
}
.bv-progress-track {
    position: relative; height: 4px; border-radius: 999px;
    background: var(--surface-3); overflow: hidden;
}
.bv-progress-fill {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 14.2857%;
    background: linear-gradient(90deg, var(--mobile-primary) 0%, color-mix(in oklch, var(--mobile-primary) 70%, white) 100%);
    border-radius: 999px;
    transition: width 360ms cubic-bezier(0.22, 1, 0.36, 1);
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
    /* ที่ <380px สลับเป็น compact stepper (progress bar + ชื่อขั้นปัจจุบัน)
       แทน 7-tab grid ที่จะแน่นเกินไป */
    .bv-wizard { display: none; }
    .bv-stepper-compact { display: block; padding: var(--space-md) var(--space-md) var(--space-sm); }
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
.bv-actions[data-mode="wizard"][data-step="7"] #bv-next   { display: none; }
.bv-actions[data-mode="wizard"][data-step="7"] #bv-submit { display: inline-flex; }

/* ───── Step error message (a11y aria-live) ───── */
.bv-step-error {
    display: none;
    margin-top: var(--space-sm);
    border-radius: 12px;
    background: var(--danger-soft);
    color: var(--danger-strong);
    padding: var(--space-xs) var(--space-sm);
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.bv-step-error.is-visible { display: block; }

/* ───── Step 4/5 pick cards (car + driver) ───── */
.bv-pick-clear {
    display: flex; align-items: center; justify-content: center;
    gap: var(--space-2xs);
    width: 100%;
    min-height: 2.75rem;
    margin-bottom: var(--space-md);
    padding: var(--space-xs) var(--space-md);
    border-radius: 12px;
    border: 1px dashed var(--ink-line);
    background: var(--surface);
    color: var(--ink-2);
    font-size: var(--fs-sm); font-weight: 600;
    transition: border-color 160ms, background 160ms;
}
.bv-pick-clear svg { width: 1rem; height: 1rem; }
.bv-pick-clear.is-active {
    border-style: solid;
    border-color: color-mix(in oklch, var(--mobile-primary) 38%, transparent);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}

.bv-pick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
    gap: var(--space-sm);
}
.bv-pick-card {
    display: flex; flex-direction: column;
    padding: 0; overflow: hidden;
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    color: inherit;
    box-shadow: var(--shadow-sm);
    text-align: left;
    transition: border-color 160ms cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 160ms cubic-bezier(0.22, 1, 0.36, 1),
                transform 160ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bv-pick-card.is-active {
    border-color: color-mix(in oklch, var(--mobile-primary) 50%, transparent);
    box-shadow: 0 6px 18px color-mix(in oklch, var(--mobile-primary) 14%, transparent);
}
.bv-pick-thumb {
    display: block;
    width: 100%;
    aspect-ratio: 4 / 3;
    background: var(--surface-2);
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    color: var(--ink-4);
}
.bv-pick-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
.bv-pick-thumb svg { width: 1.75rem; height: 1.75rem; }
.bv-pick-body {
    display: flex; flex-direction: column; gap: 4px;
    padding: var(--space-sm);
}
.bv-pick-title {
    font-size: var(--fs-sm); font-weight: 700;
    color: var(--ink); line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.bv-pick-meta {
    display: flex; flex-wrap: wrap; align-items: center;
    gap: 0.25rem var(--space-xs);
    font-size: var(--fs-xs); color: var(--ink-3);
}
.bv-pick-plate {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 6px;
    background: var(--surface-2);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: var(--ink-2);
}
.bv-pick-tag {
    color: var(--ink-4);
    font-size: 0.6875rem;
}

/* Driver pick variant — รูป avatar กลม, layout horizontal-ish */
.bv-pick-grid-driver {
    grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
}
.bv-pick-card-driver {
    flex-direction: row;
    align-items: center;
    padding: var(--space-sm);
    gap: var(--space-sm);
}
.bv-pick-avatar {
    flex-shrink: 0;
    width: 3rem; height: 3rem;
    border-radius: 50%;
    background: var(--surface-2);
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    color: var(--ink-4);
}
.bv-pick-avatar img { display: block; width: 100%; height: 100%; object-fit: cover; }
.bv-pick-avatar svg { width: 1.5rem; height: 1.5rem; }
.bv-pick-card-driver .bv-pick-body { padding: 0; min-width: 0; flex-grow: 1; }
.bv-pick-card-driver .bv-pick-title { white-space: normal; word-break: break-word; }
.bv-pick-phone {
    display: inline-flex; align-items: center; gap: 0.25rem;
}
.bv-pick-phone svg { width: 0.8rem; height: 0.8rem; }

/* Empty state (เมื่อไม่มีรถ/ไม่มีคนขับในระบบ) */
.bv-empty {
    padding: var(--space-xl) var(--space-md);
    text-align: center;
    color: var(--ink-3);
    border: 1px dashed var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
}

/* hint text ใต้ input */
.bv-field-hint {
    margin: 6px 0 0; font-size: var(--fs-xs); color: var(--ink-4);
}

/* ───── Motion: pick card pulse + tick ───── */
.bv-pick-card.is-pulse {
    animation: bv-pick-pulse 220ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes bv-pick-pulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.03); }
    100% { transform: scale(1); }
}

@media (prefers-reduced-motion: reduce) {
    .bv-wizard-fill, .bv-wizard-pip { transition: none !important; }
    .bv-panel.is-active, .bv-success-icon { animation: none !important; }
    .bv-pick-card, .bv-pick-clear, .bv-progress-fill { transition: none !important; }
    .bv-pick-card.is-pulse { animation: none !important; }
}
</style>

<div class="bv-root" data-mode="<?= Html::encode($mode) ?>">

    <?php
    // Hero shell stats — only meaningful in list mode (3 status buckets).
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

        <?php if ($mode === 'list'): ?>
            <?= $this->render('_partials/_vehicle_list', [
                'myBookings'     => $myBookings,
                'formatThaiDate' => $formatThaiDate,
                'fiscalYears'    => $fiscalYears,
                'filterYear'     => $filterYear,
            ]) ?>
        <?php elseif ($mode === 'success'): ?>
            <?= $this->render('_partials/_vehicle_success', [
                'flashCode' => $flashCode,
            ]) ?>
        <?php elseif ($mode === 'wizard'): ?>
            <?= $this->render('_partials/_vehicle_wizard', [
                'model'               => $model,
                'saveErrors'          => $saveErrors,
                'existingGoType'      => $existingGoType,
                'existingDriver'      => $existingDriver,
                'existingPhone'       => $existingPhone,
                'existingPassengers'  => $existingPassengers,
                'existingVehicleType' => $existingVehicleType,
                'existingPlate'       => $existingPlate,
                'existingUrgent'      => $existingUrgent,
                'existingNotes'       => $existingNotes,
                'requesterName'       => $requesterName,
                'requesterDept'       => $requesterDept,
                'cars'                => $cars ?? [],
                'drivers'             => $drivers ?? [],
            ]) ?>
        <?php endif; ?>

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
    var TOTAL_STEPS = 7;
    var STEP_NAMES = {
        1: 'วันเวลาเดินทาง',
        2: 'จุดหมายและวัตถุประสงค์',
        3: 'ผู้ขอใช้รถ',
        4: 'เลือกรถ',
        5: 'คนขับรถ',
        6: 'รายละเอียดเพิ่มเติม',
        7: 'ตรวจสอบและยืนยัน'
    };
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

    // Compact stepper refs (เฉพาะ <380px)
    var progressFillEl = document.getElementById('bv-progress-fill');
    var stepNumEl      = document.getElementById('bv-step-num');
    var stepNameEl     = document.getElementById('bv-step-name');

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
            var active = sStep === n;
            s.classList.toggle('is-active', active);
            s.classList.toggle('is-done',   sStep < n);
            if (active) s.setAttribute('aria-current', 'step');
            else        s.removeAttribute('aria-current');
        });

        var pct = (n / TOTAL_STEPS) * 100;
        if (fill)  fill.style.width = pct + '%';
        if (track) track.setAttribute('aria-valuenow', String(n));
        if (actions) actions.dataset.step = String(n);

        // Compact stepper sync (สำหรับ <380px)
        if (progressFillEl) progressFillEl.style.width = pct.toFixed(4) + '%';
        if (stepNumEl) stepNumEl.textContent = String(n);
        if (stepNameEl) stepNameEl.textContent = STEP_NAMES[n] || '';

        var isLast = (n === TOTAL_STEPS);
        if (submitBtn) submitBtn.disabled = isLast ? !(confirmChk && confirmChk.checked) : true;

        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        if (isLast) populateSummary();

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
            // skip visible-but-conditionally-hidden inputs (closest [hidden] wrapper)
            if (el.closest('[hidden]')) return;
            if (el.willValidate && !el.checkValidity()) {
                if (!firstInvalid) firstInvalid = el;
                el.classList.add('bv-field-error');
            }
        });
        // Step 2: validate location ที่เป็น Select2 (Yii-rendered)
        if (n === 2) {
            var loc = panel.querySelector('select[id\$="-location"]');
            if (loc && !loc.value && !firstInvalid) firstInvalid = loc;
        }
        // Step 4 (เลือกรถ): optional — ไม่ blocking (license_plate hidden เป็นค่าเดียวกัน)
        // Step 5 (คนขับ): ถ้าเลือก "พนักงาน" ต้องเลือกพนักงานจริง
        if (n === 5) {
            var driverChoice = document.getElementById('bv-driver');
            var driverIdEl   = document.getElementById('bv-driver-id');
            if (driverChoice && String(driverChoice.value) === 'driver') {
                if (!driverIdEl || !driverIdEl.value) {
                    if (!firstInvalid) firstInvalid = driverIdEl;
                }
            }
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

        // Driver summary: ถ้าเลือก "พนักงาน" + มี driver_id → แสดงชื่อจาก selected card
        var driverDetail = driver;
        var driverIdEl   = document.getElementById('bv-driver-id');
        if (driver === 'พนักงานขับรถ' && driverIdEl && driverIdEl.value) {
            var picked = document.querySelector('[data-pick-group="driver"] .bv-pick-card.is-active .bv-pick-title');
            if (picked) driverDetail = 'พนักงาน: ' + picked.textContent.trim();
        }

        dl('trip', [
            { key: 'ประเภท',     val: goType },
            { key: 'วันที่ออก',   val: tripDate },
            { key: tripEnd ? 'เดินทางกลับ' : '',  val: tripEnd },
        ].filter(function(r) { return r.key; }));

        dl('destination', [
            { key: 'จุดหมาย',     val: location },
            { key: 'วัตถุประสงค์', val: reason },
        ]);

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
            { key: 'ผู้ขับ',    val: driverDetail },
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
        'vehicle-date_end': 1, 'vehicle-time_end': 1,
        'vehicle-location': 2, 'vehicle-reason': 2,
        'vehicle-license_plate': 4,
        'vehicle-driver_id': 5,
        'vehicle-vehicle_type_id': 6, 'vehicle-urgent': 6
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

    // ════════════════════════════════════════════════════════════════════
    // Step 4 — car picker / "ไม่ระบุรถ" + Step 5 — driver pill + driver picker
    // ════════════════════════════════════════════════════════════════════
    (function initPickers() {
        var plateInput      = document.getElementById('bv-plate');
        var plateDisplay    = document.getElementById('bv-plate-display');
        var carClearBtn     = document.getElementById('bv-car-clear');
        var driverPillHidden= document.getElementById('bv-driver');
        var driverIdHidden  = document.getElementById('bv-driver-id');
        var selfPlateWrap   = document.getElementById('bv-self-plate-wrap');
        var driverPickWrap  = document.getElementById('bv-driver-pick-wrap');

        function pulseCard(el) {
            if (!el) return;
            el.classList.remove('is-pulse');
            void el.offsetWidth;
            el.classList.add('is-pulse');
            setTimeout(function(){ el.classList.remove('is-pulse'); }, 230);
        }

        function syncCarSelection(plate) {
            if (plateInput) plateInput.value = plate || '';
            document.querySelectorAll('[data-pick-group="car"] .bv-pick-card').forEach(function(card){
                var match = String(card.dataset.pickValue || '') === String(plate || '');
                card.classList.toggle('is-active', match);
                if (card.getAttribute('role') === 'radio') card.setAttribute('aria-checked', match ? 'true' : 'false');
            });
            if (carClearBtn) {
                var unset = !plate;
                carClearBtn.classList.toggle('is-active', unset);
                carClearBtn.setAttribute('aria-pressed', unset ? 'true' : 'false');
            }
            // Mirror ไปยัง self-plate input ด้วย ถ้าผู้ใช้กำลังอยู่ใน self-drive
            if (plateDisplay && driverPillHidden && driverPillHidden.value === 'self') {
                plateDisplay.value = plate || '';
            }
        }

        function syncDriverChoice(choice) {
            if (selfPlateWrap)  selfPlateWrap.hidden  = choice !== 'self';
            if (driverPickWrap) driverPickWrap.hidden = choice !== 'driver';
            // ล้าง driver_id เมื่อไม่ใช่ "พนักงาน"
            if (choice !== 'driver' && driverIdHidden) {
                driverIdHidden.value = '';
                document.querySelectorAll('[data-pick-group="driver"] .bv-pick-card').forEach(function(c){
                    c.classList.remove('is-active');
                    if (c.getAttribute('role') === 'radio') c.setAttribute('aria-checked', 'false');
                });
            }
            // เมื่อเลือก self → mirror ทะเบียนจาก plateInput ไป display เพื่อให้แก้ได้
            if (choice === 'self' && plateDisplay && plateInput) {
                plateDisplay.value = plateInput.value || '';
            }
        }

        // Car picker click
        document.querySelectorAll('[data-pick-group="car"] .bv-pick-card').forEach(function(card){
            card.addEventListener('click', function(){
                syncCarSelection(card.dataset.pickValue || '');
                pulseCard(card);
            });
        });

        // "ไม่ระบุรถ" click
        if (carClearBtn) {
            carClearBtn.addEventListener('click', function(){
                syncCarSelection('');
            });
        }

        // Driver pill change — listen ที่ radio + hidden mirror
        document.querySelectorAll('input[name="' + (driverPillHidden ? driverPillHidden.name || 'Vehicle[data_json][driver]' : 'Vehicle[data_json][driver]') + '"]').forEach(function(radio){
            radio.addEventListener('change', function(){
                if (this.checked) syncDriverChoice(this.value);
            });
        });
        if (driverPillHidden) {
            driverPillHidden.addEventListener('change', function(){
                syncDriverChoice(this.value);
            });
        }

        // Driver picker click
        document.querySelectorAll('[data-pick-group="driver"] .bv-pick-card').forEach(function(card){
            card.addEventListener('click', function(){
                if (driverIdHidden) driverIdHidden.value = card.dataset.pickValue || '';
                document.querySelectorAll('[data-pick-group="driver"] .bv-pick-card').forEach(function(c){
                    var sel = c === card;
                    c.classList.toggle('is-active', sel);
                    if (c.getAttribute('role') === 'radio') c.setAttribute('aria-checked', sel ? 'true' : 'false');
                });
                pulseCard(card);
            });
        });

        // Self-plate text input → mirror ไปยัง plateInput
        if (plateDisplay && plateInput) {
            plateDisplay.addEventListener('input', function(){
                plateInput.value = this.value;
                // ล้าง car selection ใน step 4 ถ้า text ไม่ตรงกับใบไหน
                var found = false;
                document.querySelectorAll('[data-pick-group="car"] .bv-pick-card').forEach(function(c){
                    var match = String(c.dataset.pickValue || '') === String(this.value || '');
                    c.classList.toggle('is-active', match);
                    if (c.getAttribute('role') === 'radio') c.setAttribute('aria-checked', match ? 'true' : 'false');
                    if (match) found = true;
                }.bind(this));
                if (carClearBtn) {
                    carClearBtn.classList.toggle('is-active', !this.value && !found);
                }
            });
        }

        // Sync ตอนเริ่มต้น (สำหรับ edit mode)
        if (driverPillHidden) syncDriverChoice(driverPillHidden.value);
    })();

    // Initial wizard render (only if wizard mode active)
    if (WIZARD_ACTIVE) setStep(1);
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
