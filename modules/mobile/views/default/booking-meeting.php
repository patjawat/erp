<?php

use app\components\ThaiDateHelper;
use app\modules\booking\models\Meeting;
use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var Meeting $model */
/** @var array $rooms รายการห้องจาก booking (code => title) */
/** @var array $roomCards metadata ห้องประชุมสำหรับ mobile card */
/** @var array $roomLayouts รายการรูปแบบการจัดห้อง */
/** @var array $urgentOptions รายการความเร่งด่วน */
/** @var array $equipmentItems รายการอุปกรณ์ */
/** @var \app\modules\hr\models\Employees|null $employee */
/** @var Meeting[] $myBookings  รายการของผู้ใช้ปัจจุบัน (controller เตรียมให้) */
/** @var array $saveErrors */
/** @var bool|null $isEdit */
/** @var string|null $forceMode */

$rooms          = $rooms ?? [];
$roomCards      = $roomCards ?? [];
$roomLayouts    = $roomLayouts ?? [];
$urgentOptions  = $urgentOptions ?? [];
$equipmentItems = $equipmentItems ?? [];
$myBookings     = $myBookings ?? [];
$saveErrors     = $saveErrors ?? [];

$isEdit    = (bool) ($isEdit ?? (!$model->isNewRecord));
$forceMode = isset($forceMode) ? (string) $forceMode : null;

$hasErrors   = !empty($saveErrors);
$actionParam = (string) (Yii::$app->request->get('action') ?? '');

if ($forceMode) {
    $mode = $forceMode;
} elseif ($actionParam === 'new' || $hasErrors) {
    $mode = 'wizard';
} else {
    $mode = 'list';
}

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle']  = $isEdit ? 'แก้ไขการจองห้องประชุม' : 'จองห้องประชุม';

$editCode = $isEdit ? trim((string) ($model->code ?? '')) : '';
$this->params['mobileSubtitle'] = $mode === 'wizard'
    ? ($isEdit
        ? ('แก้ไขคำขอ' . ($editCode !== '' ? ' ' . $editCode : ''))
        : 'กรอกข้อมูลทีละขั้นตอน')
    : 'รายการจองห้องประชุมของฉัน';

// Status taxonomy + bucket counts (presentation — ใช้ helper เดียวกับ vehicle)
$bucketCounts = MobileBookingStatus::bucketCounts($myBookings);

// Thai date formatter (presentation — partial รับเป็น callable)
$formatThaiDate = static function (?string $date): string {
    if (!$date) return '-';
    try {
        return ThaiDateHelper::formatThaiDate($date, 'short');
    } catch (\Throwable $e) {
        $ts = strtotime((string) $date);
        return $ts ? date('d/m/Y', $ts) : (string) $date;
    }
};

$baseUrl   = Url::to(['/mobile/default/booking-meeting']);
$newUrl    = Url::to(['/mobile/default/booking-meeting', 'action' => 'new']);
$cancelEditUrl = $isEdit && !empty($model->id)
    ? Url::to(['/mobile/default/meeting-view', 'id' => $model->id])
    : $baseUrl;
$formAction = $isEdit && !empty($model->id)
    ? Url::to(['/mobile/default/meeting-update', 'id' => $model->id])
    : $baseUrl;
$submitText = $isEdit ? 'บันทึกการแก้ไข' : 'ส่งคำขอจองห้อง';

$modelData = is_array($model->data_json ?? null) ? $model->data_json : [];
$selectedEquipment = array_map('strval', (array) ($modelData['equipment'] ?? []));
$requesterName = 'ไม่พบข้อมูลผู้จอง';
$requesterDept = 'ไม่ระบุหน่วยงาน';
if (!empty($employee)) {
    try { $requesterName = (string) ($employee->fullname ?? $employee->fullname() ?? $requesterName); } catch (\Throwable $e) {}
    try { $requesterDept = method_exists($employee, 'departmentName') ? (string) $employee->departmentName() : $requesterDept; } catch (\Throwable $e) {}
}

$dateInputId      = Html::getInputId($model, 'date_start');
$dateEndInputId   = Html::getInputId($model, 'date_end');
$timeStartInputId = Html::getInputId($model, 'time_start');
$timeEndInputId   = Html::getInputId($model, 'time_end');
$roomInputId      = Html::getInputId($model, 'room_id');
$layoutInputId    = Html::getInputId($model, 'room_layout_id');
$urgentInputId    = Html::getInputId($model, 'urgent');
$periodInputId    = Html::getInputId($model, 'data_json[period_time]');
$phoneInputId     = Html::getInputId($model, 'data_json[phone]');
$titleInputId     = Html::getInputId($model, 'title');
$peopleInputId    = Html::getInputId($model, 'emp_number');
$detailsInputId   = Html::getInputId($model, 'data_json[meeting_details]');

$thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$quickDates = [];
foreach ([0 => 'วันนี้', 1 => 'พรุ่งนี้', 2 => 'มะรืนนี้'] as $offset => $label) {
    $ts = strtotime('+' . $offset . ' day');
    $quickDates[] = [
        'label' => $label,
        'date'  => date('j', $ts) . ' ' . $thaiMonths[(int) date('n', $ts)],
        'value' => date('d/m/', $ts) . ((int) date('Y', $ts) + 543),
    ];
}

$periodValue = (string) ($modelData['period_time'] ?? 'กำหนดเอง');
$periodPresets = [
    ['value' => 'เต็มวัน', 'label' => 'เต็มวัน', 'time' => '08:00 - 16:00', 'start' => '08:00', 'end' => '16:00'],
    ['value' => 'ครึ่งวันเช้า', 'label' => 'ครึ่งวันเช้า', 'time' => '08:00 - 12:00', 'start' => '08:00', 'end' => '12:00'],
    ['value' => 'ครึ่งวันบ่าย', 'label' => 'ครึ่งวันบ่าย', 'time' => '13:30 - 16:00', 'start' => '13:30', 'end' => '16:00'],
    ['value' => 'กำหนดเอง', 'label' => 'กำหนดเอง', 'time' => 'เลือกเวลาเอง', 'start' => '', 'end' => ''],
];
?>

<style>
.bm-root {
    margin-left: -1rem;
    margin-right: -1rem;
    margin-top: -1rem;
}
.bm-scroll {
    padding: calc(var(--shell-h, 13rem) + var(--space-md)) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + 10rem);
}
.bm-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}
.bm-success,
.bm-alert {
    border-radius: 14px;
    border: 1px solid transparent;
    padding: var(--space-sm) var(--space-md);
    display: flex;
    gap: var(--space-xs);
    align-items: flex-start;
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.bm-success { background: var(--success-soft); border-color: rgba(25, 135, 84, 0.18); color: var(--success); }
.bm-alert { background: var(--danger-soft); border-color: rgba(220, 53, 69, 0.18); color: var(--danger-strong); }
.bm-alert ul { margin: 0; padding-left: 1.1rem; }
.bm-stepper {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: var(--space-2xs);
}
.bm-step-tab {
    min-width: 0;
    border: 1px solid var(--ink-line);
    border-radius: 12px;
    background: var(--surface);
    color: var(--ink-3);
    padding: var(--space-xs) 0.35rem;
    text-align: center;
    font-size: 0.6875rem;
    font-weight: 600;
    line-height: 1.25;
}
.bm-step-tab span {
    display: block;
    color: inherit;
}
.bm-step-tab.is-active {
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    border-color: rgba(13, 110, 253, 0.24);
}
.bm-step-tab.is-done {
    color: var(--success);
    border-color: rgba(25, 135, 84, 0.22);
}
.bm-mode[hidden] { display: none !important; }
.bm-panel[hidden] { display: none !important; }
.bm-panel {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}
.bm-list-toolbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    border-radius: 14px;
    padding: var(--space-sm);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px rgba(15, 23, 42, 0.04);
}
.bm-search {
    display: block;
    width: 100%;
    min-height: 2.75rem;
    border: 1px solid transparent;
    border-radius: 12px;
    background: var(--surface-2)
        url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><path d='m21 21-4.3-4.3'/></svg>")
        no-repeat 0.75rem center;
    color: var(--ink);
    font-size: var(--fs-md);
    padding: 0.65rem 0.9rem 0.65rem 2.45rem;
    -webkit-appearance: none;
    appearance: none;
}
.bm-search::placeholder { color: var(--ink-4); }
.bm-search:focus {
    outline: 0;
    background-color: var(--surface);
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}
.bm-search::-webkit-search-cancel-button { -webkit-appearance: none; display: none; }
.bm-list {
    display: grid;
    gap: var(--space-sm);
}
.bm-list-card {
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    padding: var(--space-md);
    display: grid;
    gap: var(--space-xs);
    color: inherit;
    text-decoration: none;
}
.bm-list-card[hidden] { display: none; }
.bm-list-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
}
.bm-list-code {
    color: var(--ink-4);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 600;
}
.bm-list-pill {
    flex-shrink: 0;
    border-radius: 999px;
    padding: 0.25rem 0.6rem;
    font-size: var(--fs-2xs);
    font-weight: 700;
}
.bm-list-pill.is-warning { background: var(--warning-soft); color: var(--warning); }
.bm-list-pill.is-success { background: var(--success-soft); color: var(--success); }
.bm-list-pill.is-danger { background: var(--danger-soft); color: var(--danger-strong); }
.bm-list-pill.is-secondary { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }
.bm-list-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.35;
    word-break: break-word;
}
.bm-list-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem var(--space-md);
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.bm-list-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.bm-list-meta-item svg {
    width: 0.8rem;
    height: 0.8rem;
    color: var(--ink-4);
    flex-shrink: 0;
}
.bm-list-empty {
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    padding: var(--space-2xl) var(--space-md);
    text-align: center;
}
.bm-list-empty-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-md);
}
.bm-list-empty-icon svg { width: 1.75rem; height: 1.75rem; }
.bm-list-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
}
.bm-list-empty-text {
    margin: 0 auto;
    max-width: 30ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}
.bm-list-no-results {
    border-radius: 12px;
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-sm);
    padding: var(--space-md);
    text-align: center;
}
.bm-list-no-results[hidden] { display: none; }
.bm-section-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
}
.bm-section-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 12px;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bm-section-icon svg { width: 1.1rem; height: 1.1rem; }
.bm-section-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
    line-height: 1.25;
}
.bm-section-sub {
    margin: 0.15rem 0 0;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.bm-time-strip {
    border: 1px solid rgba(13, 110, 253, 0.14);
    border-radius: 14px;
    background: linear-gradient(180deg, var(--mobile-primary-soft) 0%, var(--surface) 100%);
    padding: var(--space-sm);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: var(--space-xs);
    align-items: center;
}
.bm-time-strip-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 12px;
    background: var(--surface);
    color: var(--mobile-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-sm);
}
.bm-time-strip-icon svg { width: 1.15rem; height: 1.15rem; }
.bm-time-label {
    margin: 0;
    font-size: var(--fs-sm);
    font-weight: 700;
    color: var(--ink);
    line-height: 1.35;
}
.bm-time-sub {
    margin: 0.1rem 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.bm-chip-grid,
.bm-period-grid,
.bm-layout-grid,
.bm-equipment-grid {
    display: grid;
    gap: var(--space-xs);
}
.bm-chip-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.bm-period-grid,
.bm-layout-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.bm-chip,
.bm-radio-chip,
.bm-equipment-chip {
    min-height: 2.75rem;
    border-radius: 12px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    color: var(--ink);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.55rem 0.65rem;
    font-size: var(--fs-sm);
    font-weight: 600;
    text-align: center;
    line-height: 1.25;
    transition: border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
                background 160ms cubic-bezier(0.16, 1, 0.3, 1),
                color 160ms cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bm-chip small,
.bm-radio-chip small {
    display: block;
    margin-top: 0.15rem;
    color: var(--ink-4);
    font-size: 0.6875rem;
    font-weight: 500;
}
.bm-chip.is-active,
.bm-radio-chip.is-active,
.bm-equipment-chip.is-active {
    border-color: rgba(13, 110, 253, 0.38);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.08);
}
.bm-chip:focus-visible,
.bm-radio-chip:focus-visible,
.bm-room-card:focus-visible,
.bm-refresh:focus-visible,
.bm-actions .btn:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.bm-field-stack {
    display: grid;
    gap: var(--space-sm);
}
.bm-point-grid {
    display: grid;
    gap: var(--space-sm);
}
.bm-point-card {
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    padding: var(--space-sm);
    display: grid;
    gap: var(--space-xs);
}
.bm-point-head {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 700;
}
.bm-point-head i,
.bm-point-head svg {
    width: 1rem;
    height: 1rem;
    color: var(--mobile-primary);
}
.bm-inline-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-xs);
}
.bm-field-note {
    margin: -0.35rem 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.bm-status-note {
    border-radius: 12px;
    padding: var(--space-xs) var(--space-sm);
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-sm);
    display: flex;
    align-items: flex-start;
    gap: var(--space-xs);
}
.bm-status-note.is-ready {
    background: rgba(25, 135, 84, 0.10);
    color: var(--success);
}
.bm-status-note.is-error {
    background: var(--danger-soft);
    color: var(--danger-strong);
}
.bm-refresh {
    width: 100%;
    min-height: 2.75rem;
    border-radius: 12px;
    border: 1px solid rgba(13, 110, 253, 0.24);
    background: var(--surface);
    color: var(--mobile-primary);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
}
.bm-refresh svg { width: 1rem; height: 1rem; }
.bm-refresh.is-loading { opacity: 0.72; cursor: wait; }
.bm-room-list {
    display: grid;
    gap: var(--space-sm);
}
.bm-room-card {
    width: 100%;
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    color: inherit;
    padding: var(--space-sm);
    text-align: left;
    display: grid;
    gap: var(--space-xs);
    transition: border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1),
                background 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bm-room-card.is-selected {
    border-color: rgba(25, 135, 84, 0.5);
    background: linear-gradient(180deg, rgba(25, 135, 84, 0.08) 0%, var(--surface) 100%);
    box-shadow: 0 8px 22px rgba(25, 135, 84, 0.10);
}
.bm-room-card.is-unavailable {
    opacity: 0.68;
}
.bm-room-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-sm);
}
.bm-room-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.35;
}
.bm-room-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem var(--space-sm);
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.bm-room-meta span,
.bm-summary-row span {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.bm-room-meta svg,
.bm-summary-row svg { width: 0.85rem; height: 0.85rem; flex-shrink: 0; }
.bm-room-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}
.bm-tag {
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--ink-3);
    padding: 0.25rem 0.5rem;
    font-size: 0.6875rem;
    font-weight: 600;
}
.bm-badge {
    border-radius: 999px;
    padding: 0.3rem 0.55rem;
    font-size: 0.6875rem;
    font-weight: 700;
    white-space: nowrap;
    background: var(--surface-2);
    color: var(--ink-3);
}
.bm-badge.is-available { background: rgba(25, 135, 84, 0.12); color: var(--success); }
.bm-badge.is-unavailable { background: var(--danger-soft); color: var(--danger-strong); }
.bm-badge.is-selected { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.bm-empty {
    border: 1px dashed var(--ink-line);
    border-radius: 14px;
    padding: var(--space-xl) var(--space-md);
    text-align: center;
    color: var(--ink-3);
    background: var(--surface);
}
.bm-requester {
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    padding: var(--space-sm);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: var(--space-xs);
    background: var(--surface);
}
.bm-requester-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 12px;
    background: var(--surface-2);
    color: var(--ink-3);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bm-requester p { margin: 0; }
.bm-requester-name { color: var(--ink); font-size: var(--fs-sm); font-weight: 700; }
.bm-requester-dept { color: var(--ink-3); font-size: var(--fs-xs); }
.bm-equipment-chip {
    justify-content: flex-start;
    text-align: left;
    min-height: 2.5rem;
}
.bm-equipment-chip input {
    flex-shrink: 0;
    width: 1rem;
    height: 1rem;
    accent-color: var(--mobile-primary);
}
.bm-summary-card {
    border: 1px solid rgba(13, 110, 253, 0.16);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    padding: var(--space-md);
    display: grid;
    gap: var(--space-sm);
}
.bm-summary-row {
    display: grid;
    gap: 0.15rem;
    padding-bottom: var(--space-xs);
    border-bottom: 1px solid var(--ink-line);
}
.bm-summary-row:last-child { border-bottom: 0; padding-bottom: 0; }
.bm-summary-label {
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 600;
}
.bm-summary-value {
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 700;
    line-height: 1.45;
    word-break: break-word;
}
.bm-confirm {
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    padding: var(--space-sm);
    display: flex;
    align-items: flex-start;
    gap: var(--space-xs);
}
.bm-confirm input {
    width: 1.2rem;
    height: 1.2rem;
    margin-top: 0.1rem;
    accent-color: var(--mobile-primary);
    flex-shrink: 0;
}
.bm-confirm label {
    color: var(--ink);
    font-size: var(--fs-sm);
    line-height: 1.5;
}
.bm-step-error {
    display: none;
    border-radius: 12px;
    background: var(--danger-soft);
    color: var(--danger-strong);
    padding: var(--space-xs) var(--space-sm);
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.bm-step-error.is-visible { display: block; }
.bm-actions {
    position: fixed;
    left: 0;
    right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    z-index: 1031;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: var(--space-xs);
    background: var(--surface);
    border-top: 1px solid var(--ink-line);
    box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
    padding: var(--space-md);
}
.bm-actions[data-mode="list"] { grid-template-columns: 1fr; }
.bm-actions[data-mode="wizard"][data-step="1"] { grid-template-columns: 1fr; }
.bm-actions[data-mode="wizard"]:not([data-step="1"]) { grid-template-columns: auto 1fr; }
.bm-mode-action { display: none !important; }
.bm-actions[data-mode="list"] .bm-mode-action[data-for-mode~="list"],
.bm-actions[data-mode="wizard"] .bm-mode-action[data-for-mode~="wizard"] { display: inline-flex !important; }
.bm-actions[data-mode="wizard"][data-step="1"] .bm-prev { display: none !important; }
.bm-actions .btn {
    min-height: 3rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-size: var(--fs-md);
    font-weight: 700;
}
.bm-actions .btn svg { width: 1rem; height: 1rem; }
.bm-prev {
    padding-left: var(--space-md);
    padding-right: var(--space-md);
    background: var(--surface-2);
    border-color: var(--surface-2);
    color: var(--ink-2);
}
.bm-actions[data-mode="wizard"] .bm-submit { display: none !important; }
.bm-actions[data-mode="wizard"][data-step="4"] .bm-next { display: none !important; }
.bm-actions[data-mode="wizard"][data-step="4"] .bm-submit { display: inline-flex !important; }
.bm-actions .btn[disabled],
.bm-actions .btn.is-busy { opacity: 0.58; cursor: not-allowed; }
.bm-cancel-edit { display: none !important; }
.bm-actions[data-mode="wizard"][data-step="1"][data-is-edit="1"] { grid-template-columns: auto 1fr; }
.bm-actions[data-mode="wizard"][data-step="1"][data-is-edit="1"] .bm-cancel-edit { display: inline-flex !important; }
@media (max-width: 360px) {
    .bm-chip-grid { grid-template-columns: 1fr; }
    .bm-period-grid,
    .bm-layout-grid { grid-template-columns: 1fr; }
    .bm-step-tab { font-size: 0.625rem; padding-left: 0.2rem; padding-right: 0.2rem; }
}
@media (prefers-reduced-motion: reduce) {
    .bm-chip,
    .bm-radio-chip,
    .bm-room-card,
    .bm-refresh,
    .bm-actions .btn { transition: none !important; }
}
</style>

<div class="bm-root" data-mode="<?= Html::encode($mode) ?>">

    <?php
    $heroStats = [];
    if ($mode === 'list') {
        $heroStats = [
            ['value' => (int) $bucketCounts['all'], 'label' => 'ทั้งหมด', 'tone' => 'primary', 'clickable' => true, 'isActive' => true, 'data' => ['status-filter' => 'all']],
            ['value' => (int) $bucketCounts['pending'], 'label' => 'รออนุมัติ', 'tone' => 'warning', 'clickable' => true, 'data' => ['status-filter' => 'pending']],
            ['value' => (int) $bucketCounts['approved'], 'label' => 'อนุมัติแล้ว', 'tone' => 'success', 'clickable' => true, 'data' => ['status-filter' => 'approved']],
        ];
    }
    ?>

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => $isEdit ? 'pencil' : 'calendar-check',
        'title'    => $isEdit ? 'แก้ไขการจอง' : 'จองห้องประชุม',
        'subtitle' => $this->params['mobileSubtitle'],
        'stats'    => $heroStats,
    ]) ?>

    <div class="app-scroll bm-scroll">
        <div class="bm-body">

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="bm-success" role="status">
                    <i data-lucide="check-circle" class="mi-sm mt-1" aria-hidden="true"></i>
                    <div><?= Yii::$app->session->getFlash('success') ?></div>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="bm-alert" role="alert">
                    <i data-lucide="alert-circle" class="mi-sm mt-1" aria-hidden="true"></i>
                    <div><?= Yii::$app->session->getFlash('error') ?></div>
                </div>
            <?php endif; ?>

            <?php if ($mode === 'list'): ?>
                <?= $this->render('_partials/_meeting_list', [
                    'myBookings'     => $myBookings,
                    'rooms'          => $rooms,
                    'formatThaiDate' => $formatThaiDate,
                ]) ?>
            <?php elseif ($mode === 'wizard'): ?>
                <?= $this->render('_partials/_meeting_wizard', [
                    'model'             => $model,
                    'saveErrors'        => $saveErrors,
                    'formAction'        => $formAction,
                    'roomCards'         => $roomCards,
                    'roomLayouts'       => $roomLayouts,
                    'urgentOptions'     => $urgentOptions,
                    'equipmentItems'    => $equipmentItems,
                    'quickDates'        => $quickDates,
                    'periodPresets'     => $periodPresets,
                    'periodValue'       => $periodValue,
                    'selectedEquipment' => $selectedEquipment,
                    'requesterName'     => $requesterName,
                    'requesterDept'     => $requesterDept,
                    'dateInputId'       => $dateInputId,
                    'dateEndInputId'    => $dateEndInputId,
                    'timeStartInputId'  => $timeStartInputId,
                    'timeEndInputId'    => $timeEndInputId,
                    'roomInputId'       => $roomInputId,
                    'layoutInputId'     => $layoutInputId,
                    'urgentInputId'     => $urgentInputId,
                    'periodInputId'     => $periodInputId,
                    'titleInputId'      => $titleInputId,
                    'peopleInputId'     => $peopleInputId,
                    'phoneInputId'      => $phoneInputId,
                    'detailsInputId'    => $detailsInputId,
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bm-actions" id="bm-actions"
         data-mode="<?= Html::encode($mode) ?>"
         data-step="1"
         <?= $isEdit ? 'data-is-edit="1"' : '' ?>>
        <a href="<?= Html::encode($newUrl) ?>"
           class="btn btn-primary bm-mode-action"
           data-for-mode="list">
            <i data-lucide="plus" aria-hidden="true"></i>
            <span>จองห้องประชุม</span>
        </a>

        <?php if ($isEdit): ?>
            <a href="<?= Html::encode($cancelEditUrl) ?>"
               class="btn bm-prev bm-cancel-edit bm-mode-action"
               data-for-mode="wizard">
                <i data-lucide="x" aria-hidden="true"></i>
                <span>ยกเลิก</span>
            </a>
        <?php endif; ?>
        <button type="button" class="btn bm-prev bm-mode-action" data-for-mode="wizard" id="bm-prev">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            <span>ย้อนกลับ</span>
        </button>
        <button type="button" class="btn btn-primary bm-next bm-mode-action" data-for-mode="wizard" id="bm-next" disabled>
            <span>ถัดไป</span>
            <i data-lucide="arrow-right" aria-hidden="true"></i>
        </button>
        <?= Html::submitButton(
            '<i data-lucide="send" aria-hidden="true"></i><span>' . Html::encode($submitText) . '</span>',
            [
                'class'    => 'btn btn-primary bm-submit bm-mode-action',
                'id'       => 'bm-submit',
                'name'     => 'action',
                'value'    => 'submit',
                'form'     => 'mobile-booking-meeting-form',
                'data'     => ['for-mode' => 'wizard'],
                'disabled' => true,
            ]
        ) ?>
    </div>
</div>

<?php
$availabilityUrl = Url::to(['/mobile/default/meeting-room-availability']);
$roomCardsJson = Json::htmlEncode($roomCards);
$roomLayoutsJson = Json::htmlEncode($roomLayouts);
$urgentOptionsJson = Json::htmlEncode($urgentOptions);

$js = <<<JS
(function() {
    var roomMeta = {$roomCardsJson};
    var roomLayouts = {$roomLayoutsJson};
    var urgentOptions = {$urgentOptionsJson};
    var currentStep = 1;
    var maxStepVisited = 1;
    var lastCheckedKey = '';
    var selectedRoom = '';

    var formEl = document.getElementById('mobile-booking-meeting-form');
    var actionsEl = document.getElementById('bm-actions');
    var prevBtn = document.getElementById('bm-prev');
    var nextBtn = document.getElementById('bm-next');
    var submitBtn = document.getElementById('bm-submit');
    var checkBtn = document.getElementById('bm-check-availability');
    var confirmCheck = document.getElementById('bm-confirm-check');

    var dateInput = document.getElementById('{$dateInputId}');
    var dateEndInput = document.getElementById('{$dateEndInputId}');
    var timeStartInput = document.getElementById('{$timeStartInputId}');
    var timeEndInput = document.getElementById('{$timeEndInputId}');
    var roomInput = document.getElementById('{$roomInputId}');
    var layoutInput = document.getElementById('{$layoutInputId}');
    var urgentInput = document.getElementById('{$urgentInputId}');
    var periodInput = document.getElementById('{$periodInputId}');
    var titleInput = document.getElementById('{$titleInputId}');
    var peopleInput = document.getElementById('{$peopleInputId}');
    var phoneInput = document.getElementById('{$phoneInputId}');
    var detailsInput = document.getElementById('{$detailsInputId}');

    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function text(el, value) { if (el) el.textContent = value; }
    function val(el) { return el ? String(el.value || '').trim() : ''; }
    function currentKey() { return [val(dateInput), val(dateEndInput), val(timeStartInput), val(timeEndInput)].join('|'); }
    function escapeText(s) {
        var div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }
    function setStepError(step, message) {
        var el = qs('[data-step-error="' + step + '"]');
        if (!el) return;
        el.textContent = message || '';
        el.classList.toggle('is-visible', !!message);
    }
    function clearStepErrors() {
        qsa('[data-step-error]').forEach(function(el) {
            el.textContent = '';
            el.classList.remove('is-visible');
        });
    }
    function formatDateThai(dateValue) {
        if (!dateValue) return 'ยังไม่ได้เลือกวันที่';
        var p = dateValue.split('/');
        if (p.length !== 3) return dateValue;
        var months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return String(parseInt(p[0], 10)) + ' ' + (months[parseInt(p[1], 10)] || p[1]) + ' ' + p[2];
    }
    function parseThaiDate(dateValue) {
        var p = (dateValue || '').split('/');
        if (p.length !== 3) return null;
        var day = parseInt(p[0], 10);
        var month = parseInt(p[1], 10);
        var year = parseInt(p[2], 10);
        if (!day || !month || !year) return null;
        if (year > 2400) year -= 543;
        return year * 10000 + month * 100 + day;
    }
    function dateRangeLabel() {
        var start = formatDateThai(val(dateInput));
        var end = formatDateThai(val(dateEndInput));
        return val(dateInput) === val(dateEndInput) ? start : start + ' ถึง ' + end;
    }
    function timeRangeLabel() {
        var start = val(timeStartInput);
        var end = val(timeEndInput);
        return start && end ? start + ' - ' + end + ' น.' : 'ยังไม่ได้เลือกเวลา';
    }
    function isTimeRangeValid() {
        var start = val(timeStartInput);
        var end = val(timeEndInput);
        var dateStart = parseThaiDate(val(dateInput));
        var dateEnd = parseThaiDate(val(dateEndInput));
        if (!start || !end || !dateStart || !dateEnd) return false;
        if (dateEnd < dateStart) return false;
        return dateEnd > dateStart || start < end;
    }
    function getRoomTitle(code) {
        return roomMeta[code] && roomMeta[code].title ? roomMeta[code].title : (code || '-');
    }
    function getChoiceLabel(input, options) {
        var v = val(input);
        return options && options[v] ? options[v] : (v || '-');
    }
    function selectedEquipmentLabels() {
        return qsa('input[name="Meeting[data_json][equipment][]"]:checked').map(function(input) {
            var label = input.closest('label');
            return label ? label.textContent.trim() : input.value;
        });
    }

    (function initListFilters() {
        var search = document.getElementById('bm-list-search');
        var list = document.getElementById('bm-booking-list');
        var empty = document.getElementById('bm-list-no-results');
        var stats = qsa('.app-stat[data-status-filter]');
        if (!list) return;

        var currentStatus = 'all';
        var currentQuery = '';

        function applyFilters() {
            var query = currentQuery.toLowerCase().trim();
            var shown = 0;
            qsa('.bm-list-card', list).forEach(function(card) {
                var matchStatus = currentStatus === 'all' || card.dataset.status === currentStatus;
                var matchSearch = !query || (card.dataset.search || '').indexOf(query) !== -1;
                var visible = matchStatus && matchSearch;
                card.hidden = !visible;
                if (visible) shown++;
            });
            if (empty) empty.hidden = shown > 0;
        }

        if (search) {
            search.addEventListener('input', function() {
                currentQuery = search.value || '';
                applyFilters();
            });
        }
        stats.forEach(function(stat) {
            stat.addEventListener('click', function(e) {
                e.preventDefault();
                stats.forEach(function(item) { item.classList.remove('is-active'); });
                stat.classList.add('is-active');
                currentStatus = stat.dataset.statusFilter || 'all';
                applyFilters();
            });
        });
    })();

    function syncTimeUi() {
        var statusEl = document.getElementById('bm-time-status');
        var ready = val(dateInput) && isTimeRangeValid();
        if (statusEl) {
            statusEl.classList.toggle('is-ready', ready);
            statusEl.classList.toggle('is-error', !!(val(dateInput) && val(dateEndInput) && val(timeStartInput) && val(timeEndInput) && !isTimeRangeValid()));
            text(statusEl.querySelector('span'), ready
                ? 'พร้อมตรวจสอบห้องว่าง: ' + dateRangeLabel() + ' · ' + timeRangeLabel()
                : (val(dateInput) && val(dateEndInput) && val(timeStartInput) && val(timeEndInput) ? 'ตรวจสอบวันเวลาเริ่มและสิ้นสุดอีกครั้ง' : 'เลือกจุดเริ่มต้นและจุดสิ้นสุดเพื่อไปขั้นตอนเลือกห้อง'));
        }
        text(document.getElementById('bm-strip-main'), dateRangeLabel());
        text(document.getElementById('bm-strip-sub'), timeRangeLabel());
        if (lastCheckedKey && lastCheckedKey !== currentKey()) {
            lastCheckedKey = '';
            markRoomsUnknown();
        }
        updateSummary();
        updateActions();
    }

    function markRoomsUnknown() {
        qsa('[data-room-card]').forEach(function(card) {
            card.dataset.availability = 'unknown';
            card.classList.remove('is-unavailable');
            var badge = qs('[data-room-status]', card);
            if (badge) {
                badge.className = 'bm-badge';
                badge.textContent = 'รอตรวจสอบ';
            }
        });
    }

    function setSelectedRoom(code) {
        selectedRoom = code || '';
        if (roomInput) roomInput.value = selectedRoom;
        qsa('[data-room-card]').forEach(function(card) {
            var active = card.dataset.roomCode === selectedRoom;
            card.classList.toggle('is-selected', active);
            card.setAttribute('aria-checked', active ? 'true' : 'false');
            var badge = qs('[data-room-status]', card);
            if (badge && active && card.dataset.availability === 'available') {
                badge.className = 'bm-badge is-selected';
                badge.textContent = 'เลือกแล้ว';
            } else if (badge && card.dataset.availability === 'available') {
                badge.className = 'bm-badge is-available';
                badge.textContent = 'ว่าง';
            }
        });
        updateSummary();
        updateActions();
    }

    function applyAvailability(rooms) {
        var map = {};
        (rooms || []).forEach(function(r) { map[String(r.code)] = r; });
        qsa('[data-room-card]').forEach(function(card) {
            var code = card.dataset.roomCode;
            var info = map[code];
            var available = info ? !!info.available : false;
            card.dataset.availability = available ? 'available' : 'unavailable';
            card.classList.toggle('is-unavailable', !available);
            var badge = qs('[data-room-status]', card);
            if (badge) {
                if (available) {
                    badge.className = card.classList.contains('is-selected') ? 'bm-badge is-selected' : 'bm-badge is-available';
                    badge.textContent = card.classList.contains('is-selected') ? 'เลือกแล้ว' : 'ว่าง';
                } else {
                    badge.className = 'bm-badge is-unavailable';
                    badge.textContent = 'ไม่ว่าง';
                }
            }
            if (!available && code === selectedRoom) setSelectedRoom('');
        });
        updateActions();
    }

    function checkAvailability() {
        if (!validateStep(1, true)) return Promise.resolve(false);
        if (!checkBtn) return Promise.resolve(false);
        checkBtn.classList.add('is-loading');
        checkBtn.disabled = true;
        text(checkBtn.querySelector('span'), 'กำลังตรวจสอบ...');
        var params = new URLSearchParams({
            meeting_date: val(dateInput),
            meeting_date_end: val(dateEndInput),
            time_start: val(timeStartInput),
            time_end: val(timeEndInput)
        });
        return fetch('{$availabilityUrl}' + '?' + params.toString(), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok && data.rooms) {
                    lastCheckedKey = currentKey();
                    applyAvailability(data.rooms);
                    setStepError(2, '');
                    return true;
                }
                setStepError(2, data.message || 'ไม่สามารถตรวจสอบห้องว่างได้');
                return false;
            })
            .catch(function() {
                setStepError(2, 'ไม่สามารถโหลดข้อมูลห้องว่างได้ กรุณาลองอีกครั้ง');
                return false;
            })
            .finally(function() {
                checkBtn.classList.remove('is-loading');
                checkBtn.disabled = false;
                text(checkBtn.querySelector('span'), 'ตรวจสอบห้องว่าง');
                updateActions();
            });
    }

    function validateStep(step, show) {
        var message = '';
        if (step === 1) {
            var dateStart = parseThaiDate(val(dateInput));
            var dateEnd = parseThaiDate(val(dateEndInput));
            if (!val(dateInput) || !val(dateEndInput)) message = 'กรุณาเลือกวันเริ่มและวันสิ้นสุด';
            else if (!val(timeStartInput) || !val(timeEndInput)) message = 'กรุณาเลือกเวลาเริ่มและเวลาสิ้นสุด';
            else if (!dateStart || !dateEnd) message = 'รูปแบบวันที่ไม่ถูกต้อง';
            else if (dateEnd < dateStart) message = 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มต้น';
            else if (!isTimeRangeValid()) message = 'เวลาสิ้นสุดต้องหลังเวลาเริ่ม เมื่อจองภายในวันเดียวกัน';
        } else if (step === 2) {
            if (lastCheckedKey !== currentKey()) message = 'กรุณาตรวจสอบห้องว่างก่อนเลือกห้อง';
            else if (!selectedRoom) message = 'กรุณาเลือกห้องประชุมที่ว่าง';
            else {
                var card = qsa('[data-room-card]').filter(function(item) {
                    return item.dataset.roomCode === selectedRoom;
                })[0];
                if (!card || card.dataset.availability !== 'available') message = 'ห้องที่เลือกไม่ว่างในช่วงเวลานี้';
            }
        } else if (step === 3) {
            if (!val(titleInput)) message = 'กรุณาระบุหัวข้อประชุม';
            else if (!val(peopleInput) || parseInt(val(peopleInput), 10) < 1) message = 'กรุณาระบุจำนวนผู้เข้าร่วม';
            else if (!val(phoneInput)) message = 'กรุณาระบุเบอร์ติดต่อ';
        } else if (step === 4) {
            if (!confirmCheck || !confirmCheck.checked) message = 'กรุณาติ๊กยืนยันข้อมูลก่อนส่งคำขอ';
        }
        if (show) setStepError(step, message);
        return !message;
    }

    function firstInvalidStep() {
        for (var i = 1; i <= 4; i++) {
            if (!validateStep(i, false)) return i;
        }
        return 0;
    }

    function goToStep(step) {
        step = Math.max(1, Math.min(4, step));
        currentStep = step;
        maxStepVisited = Math.max(maxStepVisited, step);
        qsa('[data-step-panel]').forEach(function(panel) {
            panel.hidden = Number(panel.dataset.stepPanel) !== currentStep;
        });
        qsa('[data-step-jump]').forEach(function(tab) {
            var s = Number(tab.dataset.stepJump);
            tab.classList.toggle('is-active', s === currentStep);
            tab.classList.toggle('is-done', s < currentStep && validateStep(s, false));
        });
        if (actionsEl) actionsEl.dataset.step = String(currentStep);
        clearStepErrors();
        if (currentStep === 2 && currentKey() && lastCheckedKey !== currentKey()) {
            checkAvailability();
        }
        if (currentStep === 4) updateSummary();
        updateActions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateActions() {
        if (nextBtn) nextBtn.disabled = !validateStep(currentStep, false);
        if (submitBtn) submitBtn.disabled = !(validateStep(1, false) && validateStep(2, false) && validateStep(3, false) && validateStep(4, false));
    }

    function updateSummary() {
        var roomTitle = selectedRoom ? getRoomTitle(selectedRoom) : '-';
        var title = val(titleInput) || '-';
        var people = val(peopleInput) ? val(peopleInput) + ' คน' : '-';
        var phone = val(phoneInput) || '-';
        var layout = getChoiceLabel(layoutInput, roomLayouts);
        var urgent = getChoiceLabel(urgentInput, urgentOptions);
        var equipment = selectedEquipmentLabels();
        var details = val(detailsInput);

        text(document.getElementById('bm-summary-time'), dateRangeLabel() + ' · ' + timeRangeLabel());
        text(document.getElementById('bm-summary-room'), roomTitle + (layout !== '-' ? ' · ' + layout : ''));
        text(document.getElementById('bm-summary-detail'), title + ' · ' + people + ' · โทร ' + phone + (urgent !== '-' ? ' · ' + urgent : ''));
        text(document.getElementById('bm-summary-extra'), (equipment.length ? equipment.join(', ') : 'ไม่ระบุอุปกรณ์') + (details ? ' · ' + details : ''));
    }

    qsa('[data-date-value]').forEach(function(btn) {
        btn.classList.toggle('is-active', dateInput && btn.dataset.dateValue === dateInput.value);
        btn.addEventListener('click', function() {
            if (dateInput) {
                dateInput.value = btn.dataset.dateValue;
                dateInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (dateEndInput) {
                dateEndInput.value = btn.dataset.dateValue;
                dateEndInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            qsa('[data-date-value]').forEach(function(b) { b.classList.toggle('is-active', b === btn); });
        });
    });

    qsa('[data-period-value]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (periodInput) periodInput.value = btn.dataset.periodValue || 'กำหนดเอง';
            if (btn.dataset.timeStart && timeStartInput) timeStartInput.value = btn.dataset.timeStart;
            if (btn.dataset.timeEnd && timeEndInput) timeEndInput.value = btn.dataset.timeEnd;
            qsa('[data-period-value]').forEach(function(b) { b.classList.toggle('is-active', b === btn); });
            syncTimeUi();
        });
    });

    [dateInput, dateEndInput, timeStartInput, timeEndInput].forEach(function(input) {
        if (!input) return;
        input.addEventListener('change', function() {
            if ((input === timeStartInput || input === timeEndInput) && periodInput) {
                periodInput.value = 'กำหนดเอง';
                qsa('[data-period-value]').forEach(function(b) { b.classList.toggle('is-active', b.dataset.periodValue === 'กำหนดเอง'); });
            }
            syncTimeUi();
        });
        input.addEventListener('input', syncTimeUi);
    });

    qsa('[data-room-card]').forEach(function(card) {
        card.addEventListener('click', function() {
            if (card.dataset.availability === 'unavailable') {
                setStepError(2, 'ห้องนี้ไม่ว่างในช่วงเวลาที่เลือก');
                return;
            }
            setStepError(2, '');
            setSelectedRoom(card.dataset.roomCode || '');
        });
    });

    qsa('[data-choice-input]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(btn.dataset.choiceInput);
            if (!input) return;
            input.value = btn.dataset.choiceValue || '';
            var group = btn.closest('[data-choice-group]');
            qsa('[data-choice-input]', group).forEach(function(b) { b.classList.toggle('is-active', b === btn); });
            updateSummary();
        });
    });

    qsa('input, textarea').forEach(function(input) {
        input.addEventListener('input', function() {
            var equipmentChip = input.closest ? input.closest('.bm-equipment-chip') : null;
            if (equipmentChip) equipmentChip.classList.toggle('is-active', input.checked);
            updateSummary();
            updateActions();
        });
        input.addEventListener('change', function() {
            var equipmentChip = input.closest ? input.closest('.bm-equipment-chip') : null;
            if (equipmentChip) equipmentChip.classList.toggle('is-active', input.checked);
            updateSummary();
            updateActions();
        });
    });

    if (checkBtn) checkBtn.addEventListener('click', checkAvailability);
    if (confirmCheck) confirmCheck.addEventListener('change', updateActions);
    if (prevBtn) prevBtn.addEventListener('click', function() { goToStep(currentStep - 1); });
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (!validateStep(currentStep, true)) return;
            goToStep(currentStep + 1);
        });
    }
    qsa('[data-step-jump]').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = Number(tab.dataset.stepJump);
            if (target <= currentStep || target <= maxStepVisited) {
                goToStep(target);
            }
        });
    });

    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            var invalid = firstInvalidStep();
            if (invalid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                goToStep(invalid);
                validateStep(invalid, true);
                return false;
            }
        }, true);
    }

    if (typeof handleFormSubmit === 'function') {
        handleFormSubmit('#mobile-booking-meeting-form', null, function(response) {});
    }

    if (roomInput && roomInput.value) setSelectedRoom(roomInput.value);
    syncTimeUi();
    updateSummary();
    updateActions();
})();
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
