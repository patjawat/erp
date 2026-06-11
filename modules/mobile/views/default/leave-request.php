<?php

use app\components\ApproveLevelResolver;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;
use kartik\widgets\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\leave\models\Leave $model */
/** @var array $types */
/** @var array $stats */
/** @var string|null $draftRef */
/** @var string $leaveWorkSendInitText */
/** @var \app\modules\hr\models\Employees|null $employee */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ขอลาออนไลน์';
$this->params['mobileSubtitle'] = 'กรอกข้อมูลและส่งคำขอลา';

$employee              = $employee ?? null;
$types                 = $types ?? [];
$stats                 = $stats ?? [];
$draftRef              = $draftRef ?? null;
$leaveWorkSendInitText = $leaveWorkSendInitText ?? '';

$isUpdate = $model instanceof \app\modules\leave\models\Leave && !$model->isNewRecord;

// Leave-type options — read from the model (works in create + update without
// requiring the controller to pass a $types array).
$typeOptions = $model->listLeaveType();

// data_json sub-attribute names (match desktop _form so existing rules/load work)
$attrWorkShift     = 'data_json[work_shift]';
$attrReason        = 'data_json[reason]';
$attrPhone         = 'data_json[leave_contact_phone]';
$attrPlaceGo       = 'data_json[place_go]';
$attrAddress       = 'data_json[address]';
$attrLeaveSend     = 'data_json[leave_work_send_id]';
$attrLeaveName     = 'data_json[leave_work_send_name]';
$attrDateStartType = 'data_json[date_start_type]';
$attrDateEndType   = 'data_json[date_end_type]';

// Summary initial values
$initSatsun  = 0;
$initHoliday = 0;
$initTotal   = 0;
// -1 sentinel = stats unavailable, disable annual-leave warning client-side
$remainingAnnualLeave = -1;
if ($isUpdate && is_array($model->data_json ?? null)) {
    $initSatsun  = (int) ($model->data_json['summary_sat_sun'] ?? $model->data_json['sat_sun_days'] ?? 0);
    $initHoliday = (int) ($model->data_json['summary_holiday'] ?? $model->data_json['holidays'] ?? 0);
    $initTotal   = (float) ($model->total_days ?? 0);
}
foreach ($stats as $row) {
    if (($row['code'] ?? '') === 'LT4') {
        $remainingAnnualLeave = max(
            0,
            (float) ($row['entitlement_days'] ?? 0) - (float) ($row['used_days'] ?? 0)
        );
        break;
    }
}

// Restore existing signature state (update mode)
$existingSigType = is_array($model->data_json ?? null) ? ($model->data_json['signature_type'] ?? 'canvas') : 'canvas';
$existingSigData = is_array($model->data_json ?? null) ? ($model->data_json['signature_data'] ?? '') : '';
$signatureSystemUrl = null;
if ($employee && method_exists($employee, 'SignatureShow')) {
    try { $signatureSystemUrl = $employee->SignatureShow(); } catch (\Throwable $e) {}
}

$existingWorkShift   = is_array($model->data_json ?? null) ? ($model->data_json['work_shift']        ?? 'normal') : 'normal';
$existingDateStartT  = is_array($model->data_json ?? null) ? ($model->data_json['date_start_type']   ?? '0')      : '0';
$existingDateEndT    = is_array($model->data_json ?? null) ? ($model->data_json['date_end_type']     ?? '0')      : '0';
$existingPlaceGo     = is_array($model->data_json ?? null) ? ($model->data_json['place_go']          ?? '')       : '';

// Format for the Select2 result dropdown (matches desktop)
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

$leaveSendInitAvatar = '';
try {
    if ($isUpdate && !empty($model->data_json['leave_work_send_id'])) {
        $emp = Employees::find()->where(['id' => $model->data_json['leave_work_send_id']])->one();
        if ($emp) $leaveSendInitAvatar = $emp->getAvatar(false);
    }
} catch (\Throwable $e) {}
?>

<style>
/* Approval workflow ribbon */
.lr-workflow { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-2xs); position: relative; }
.lr-step { display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; }
.lr-step::after {
    content: ''; position: absolute; top: 1.125rem;
    left: calc(50% + 1.5rem); width: calc(100% - 3rem); height: 2px;
    background: #dee2e6; z-index: 0;
}
.lr-step:last-child::after { display: none; }
.lr-step.is-done::after { background: var(--mobile-primary); opacity: 0.4; }
.lr-step .lr-dot {
    width: 2.25rem; height: 2.25rem; border-radius: 50%;
    background: #dee2e6; color: #6c757d; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    position: relative; z-index: 1; overflow: hidden;
}
.lr-step .lr-dot img { width: 100%; height: 100%; object-fit: cover; }
.lr-step.is-done .lr-dot { background: var(--mobile-primary); color: #fff; }
.lr-step .lr-label {
    font-size: var(--fs-xs); color: #6c757d; margin-top: var(--space-2xs);
    line-height: 1.25; max-width: 100%;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.lr-step.is-done .lr-label { color: var(--mobile-primary); font-weight: 500; }
@media (max-width: 360px) {
    .lr-step::after { display: none; }
    .lr-step .lr-label { font-size: 0.7188rem; }
}

/* Date row — picker + half-day pills stacked on mobile */
.lr-date-row { display: grid; gap: var(--space-xs); }
.lr-date-row .form-control { border-radius: 12px; padding: 0.75rem 1rem; }

/* Required asterisk */
.req::after { content: ' *'; color: #dc3545; }

/* Signature canvas — full-width inside section */
.lr-sig-canvas-wrap {
    border: 1px solid #dee2e6; border-radius: 12px; background: #fff;
    overflow: hidden; touch-action: none;
}
.lr-sig-canvas-wrap canvas { display: block; width: 100%; height: 180px; cursor: crosshair; }
.lr-sig-system-preview {
    border: 1px solid #dee2e6; border-radius: 12px; background: #f8f9fa;
    padding: var(--space-md); text-align: center;
}
.lr-sig-system-preview img { max-height: 100px; max-width: 100%; }

/* Annual leave warning */
.lr-annual-alert { display: none; padding: var(--space-sm) var(--space-md); border-radius: 12px; font-size: var(--fs-sm); margin-top: var(--space-sm); }
.lr-annual-alert.is-info   { display: flex; gap: var(--space-xs); align-items: center; background: var(--mobile-primary-soft); color: #084298; }
.lr-annual-alert.is-warn   { display: flex; gap: var(--space-xs); align-items: center; background: #fff4e6; color: #b54708; }
.lr-annual-alert.is-error  { display: flex; gap: var(--space-xs); align-items: center; background: #fde8e8; color: #b42318; }

/* Section helper — tighten gap between section-title and its first card */
.lr-section .card { margin-top: 0; }

/* Form row spacing inside leave-card */
.leave-card .mb-3 { margin-bottom: var(--space-md) !important; }
.leave-card .mb-3:last-child { margin-bottom: 0 !important; }

/* select2 sizing on mobile — taller, no double border */
.lr-section .select2-container--krajee .select2-selection--single {
    height: 3rem; border-radius: 12px; padding: 0.25rem 0.75rem;
}
.lr-section .select2-container--krajee .select2-selection__rendered { line-height: 2.5rem; }
.lr-section .select2-container--krajee .select2-selection__arrow { height: 3rem; }

/* ─── Mobile file upload — overrides Kartik FileInput for thumb UX ─── */
.mobile-upload-hint {
    display: flex; align-items: center; gap: var(--space-xs);
    padding: var(--space-sm) var(--space-md);
    background: var(--mobile-primary-soft);
    border: 1px dashed var(--mobile-primary-soft-border);
    border-radius: 12px;
    color: #084298; font-size: var(--fs-xs);
    margin-bottom: var(--space-sm);
}
.mobile-upload-hint svg { color: var(--mobile-primary); flex-shrink: 0; }

.mobile-upload .file-input { margin: 0; }
/* Hide only the read-only caption box (we don't need it on mobile — thumbnails show filenames) */
.mobile-upload .file-caption,
.mobile-upload .kv-fileinput-caption,
.mobile-upload .file-caption-name { display: none !important; }
/* Let the Kartik input-group stack vertically so the Browse / Upload / Remove buttons take full width */
.mobile-upload .input-group {
    display: flex !important;
    flex-direction: column;
    background: transparent;
    border: 0;
    padding: 0;
    margin: 0;
    width: 100%;
}
.mobile-upload .input-group-btn,
.mobile-upload .input-group-append {
    display: flex !important;
    flex-direction: column;
    width: 100%;
    gap: var(--space-xs);
}

/* Browse / upload / remove buttons row → full-width vertical stack */
.mobile-upload .btn-file,
.mobile-upload .fileinput-upload-button,
.mobile-upload .fileinput-remove {
    width: 100% !important;
    min-height: 3rem;
    border-radius: 12px !important;
    font-size: var(--fs-md);
    font-weight: 600;
    margin: 0 0 var(--space-xs) 0 !important;
    padding: 0.65rem 1rem !important;
    display: flex !important; align-items: center; justify-content: center; gap: var(--space-xs);
}
.mobile-upload .btn-file {
    background: #fff; color: var(--mobile-primary);
    border: 2px dashed var(--mobile-primary-soft-border) !important;
}
.mobile-upload .btn-file:hover, .mobile-upload .btn-file:focus {
    background: var(--mobile-primary-soft); color: var(--mobile-primary-dark);
    border-color: var(--mobile-primary) !important;
}
.mobile-upload .fileinput-upload-button {
    background: var(--mobile-primary); color: #fff; border: 0 !important;
}
.mobile-upload .fileinput-upload-button:hover, .mobile-upload .fileinput-upload-button:focus {
    background: var(--mobile-primary-dark); color: #fff;
}
.mobile-upload .fileinput-remove {
    background: #fff; color: #6c757d;
    border: 1px solid #dee2e6 !important;
}
.mobile-upload .fileinput-remove:hover { background: #f8f9fa; color: #1a1f2c; }

/* Preview area — clean grid on mobile, larger touch cards */
.mobile-upload .file-preview {
    border: 0; padding: 0; margin-top: var(--space-sm); background: transparent;
}
.mobile-upload .file-preview-thumbnails {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-xs);
}
@media (max-width: 360px) {
    .mobile-upload .file-preview-thumbnails { grid-template-columns: repeat(2, 1fr); }
}
.mobile-upload .file-preview-frame {
    margin: 0 !important; padding: 0 !important;
    border-radius: 12px !important;
    border: 1px solid #e9ecef !important;
    overflow: hidden; background: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    aspect-ratio: 1; display: flex; flex-direction: column;
    position: relative;
}
.mobile-upload .file-preview-image,
.mobile-upload .kv-preview-data {
    width: 100% !important; height: 100% !important;
    object-fit: cover !important;
    border-radius: 0 !important;
}
.mobile-upload .file-preview-other-frame {
    display: flex; align-items: center; justify-content: center;
    height: 100%; padding: var(--space-md);
}
.mobile-upload .file-preview-other-frame i { font-size: 2rem; }
.mobile-upload .file-footer-caption {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 0.25rem 0.5rem;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0));
    color: #fff; font-size: var(--fs-xs);
    text-align: left; line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mobile-upload .file-actions {
    position: absolute; top: var(--space-2xs); right: var(--space-2xs);
    display: flex; gap: 2px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 8px;
    padding: 2px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}
.mobile-upload .file-actions .btn {
    width: 28px; height: 28px;
    padding: 0; min-width: 28px;
    display: flex; align-items: center; justify-content: center;
    border: 0; background: transparent;
    color: #495057; font-size: 0.875rem;
}
.mobile-upload .file-actions .btn:hover { background: rgba(0, 0, 0, 0.08); color: #1a1f2c; }
.mobile-upload .file-actions .btn-kv-delete:hover,
.mobile-upload .file-actions .btn-kv-remove:hover { color: #dc3545; background: rgba(220, 53, 69, 0.1); }

/* Upload progress bar */
.mobile-upload .progress {
    height: 0.5rem !important; border-radius: 999px !important;
    background: #e9ecef; margin: var(--space-xs) 0;
}
.mobile-upload .progress-bar { background: var(--mobile-primary); border-radius: 999px; }
.mobile-upload .kv-upload-progress { padding: 0 !important; }
</style>

<div class="mobile-stack">

    <!-- Approval workflow preview -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="git-branch"></i>
            ขั้นตอนการอนุมัติ
        </h3>
        <div class="card mobile-card">
            <div class="card-body">
                <?php $listApprove = ApproveLevelResolver::resolve('leave', $model->emp_id); ?>
                <?php if (empty($listApprove)): ?>
                    <p class="small text-body-secondary mb-0 text-center py-2">ยังไม่ได้ตั้งค่าผู้อนุมัติสำหรับบุคลากรนี้</p>
                <?php else: ?>
                    <div class="lr-workflow">
                        <?php foreach ($listApprove as $step): ?>
                            <?php $approveEmployee = Employees::findOne(['id' => $step['emp_id']]); ?>
                            <div class="lr-step">
                                <div class="lr-dot">
                                    <?php if ($approveEmployee): ?>
                                        <?= Html::img($approveEmployee->showAvatar(), [
                                            'alt' => $approveEmployee->fullname,
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                                <span class="lr-label">
                                    <?= Html::encode($step['title'] ?? $step['label']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php $form = ActiveForm::begin([
        'id' => $isUpdate ? 'form-leave-update' : 'mobile-leave-request-form',
        'action' => $isUpdate ? ['/leave/leave/update', 'id' => $model->id] : null,
        'options' => ['enctype' => 'multipart/form-data'],
        'enableAjaxValidation' => $isUpdate,
        'validationUrl' => $isUpdate ? ['/leave/leave/update-validation', 'id' => $model->id] : null,
    ]); ?>

    <!-- Hidden carriers -->
    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'thai_year')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?php if ($isUpdate): ?>
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <!-- Section 1: ประเภทการลา -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="file-text"></i>
            ประเภทการลา
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-medium req" for="leave-create-form-leave_type_id">ประเภทการลา</label>
                    <?= $form->field($model, 'leave_type_id', [
                        'template' => '{input}{error}',
                    ])->dropDownList($typeOptions, [
                        'id'     => 'leave-create-form-leave_type_id',
                        'class'  => 'form-select',
                        'prompt' => 'เลือกประเภทการลา',
                    ])->label(false) ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium d-flex align-items-baseline justify-content-between gap-2">
                        <span>ประเภทของเวร</span>
                        <span class="text-body-secondary" style="font-size: var(--fs-xs);">เวร 8: ไม่นับวันหยุด</span>
                    </label>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทของเวร">
                        <?php foreach (['normal' => 'เวรเช้า', 'shift' => 'เวร 8 ชั่วโมง'] as $val => $lab): ?>
                            <?php $checked = $existingWorkShift === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(\yii\helpers\Html::getInputName($model, $attrWorkShift)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    id="leave-work_shift-<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-work_shift"
                                >
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-work_shift" value="<?= Html::encode($existingWorkShift) ?>">
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: ช่วงเวลา -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="calendar-range"></i>
            ช่วงเวลาที่ลา
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <!-- Start date -->
                <div class="mb-3 lr-date-row">
                    <label class="form-label fw-medium req">ตั้งแต่วันที่</label>
                    <?= $form->field($model, 'date_start', ['template' => '{input}{error}'])->widget(DatepickerThai::class, [
                        'options' => [
                            'id' => 'leave-date_start',
                            'placeholder' => 'เลือกวันที่เริ่มลา',
                            'class' => 'form-control',
                        ],
                    ])->label(false) ?>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทวันเริ่มลา">
                        <?php foreach (['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'] as $val => $lab): ?>
                            <?php $checked = (string) $existingDateStartT === (string) $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(\yii\helpers\Html::getInputName($model, $attrDateStartType)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-date_start_type"
                                >
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-date_start_type" value="<?= Html::encode($existingDateStartT) ?>">
                </div>

                <!-- End date -->
                <div class="mb-3 lr-date-row">
                    <label class="form-label fw-medium req">ถึงวันที่</label>
                    <?= $form->field($model, 'date_end', ['template' => '{input}{error}'])->widget(DatepickerThai::class, [
                        'options' => [
                            'id' => 'leave-date_end',
                            'placeholder' => 'เลือกวันที่สิ้นสุด',
                            'class' => 'form-control',
                        ],
                    ])->label(false) ?>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทวันสิ้นสุด">
                        <?php foreach (['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'] as $val => $lab): ?>
                            <?php $checked = (string) $existingDateEndT === (string) $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(\yii\helpers\Html::getInputName($model, $attrDateEndType)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-date_end_type"
                                >
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-date_end_type" value="<?= Html::encode($existingDateEndT) ?>">
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: สรุปวันลา (auto-calculated) -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="calculator"></i>
            สรุปวันลา
            <span class="ms-auto" style="font-size: var(--fs-xs); color: #6c757d; font-weight: 500;">คำนวณอัตโนมัติ</span>
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="stat-box">
                            <span class="stat-value" id="leave-summary-satsun"><?= (int) $initSatsun ?></span>
                            <span class="stat-label">เสาร์-อาทิตย์</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <span class="stat-value" id="leave-summary-holiday"><?= (int) $initHoliday ?></span>
                            <span class="stat-label">นักขัตฤกษ์</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box" id="leave-summary-total-box">
                            <span class="stat-value" id="leave-summary-total-show"><?= (float) $initTotal ?></span>
                            <input type="text" id="leave-summary-total" class="form-control text-center fw-bold d-none" value="<?= (float) $initTotal ?>" readonly tabindex="-1" inputmode="decimal" aria-label="สรุปวันลา (แก้ไขได้สำหรับเวร 8)">
                            <span class="stat-label">
                                <span>รวมวันลา</span>
                                <span id="leave-total-hint" class="text-info d-none">(แก้ไขได้)</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="leave-annual-leave-alert" class="lr-annual-alert" role="alert" aria-live="polite"></div>
            </div>
        </div>
        <?= $form->field($model, 'total_days')->hiddenInput(['id' => 'leave-total_days'])->label(false) ?>
    </section>

    <!-- Section 4: ผู้รับมอบหมายงาน -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="user-plus"></i>
            มอบหมายงานให้
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <div class="mb-0">
                    <label class="form-label fw-medium">เลือกผู้รับมอบหมาย</label>
                    <?= $form->field($model, $attrLeaveSend, ['template' => '{input}{error}'])->widget(Select2::classname(), [
                        'initValueText' => $leaveSendInitAvatar ?: ($leaveWorkSendInitText ?: ''),
                        'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'id' => 'leave-work_send_id'],
                        'size' => Select2::LARGE,
                        'pluginEvents' => [
                            'select2:select' => 'function(e) {
                                var d = e.params && e.params.data ? e.params.data : {};
                                jQuery("#leave-work_send_name").val(d.fullname || d.text || "");
                            }',
                            'select2:unselect' => 'function() { jQuery("#leave-work_send_name").val(""); }',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'ajax' => [
                                'url' => Url::to(['/depdrop/employee-by-id']),
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params){ return {q: params.term, page: params.page}; }'),
                                'processResults' => new JsExpression($resultsJs),
                                'cache' => true,
                            ],
                            'escapeMarkup' => new JsExpression('function(m){ return m; }'),
                            'templateSelection' => new JsExpression('function(r){ return r.text || r.id; }'),
                            'templateResult' => new JsExpression('formatRepo'),
                        ],
                    ])->label(false) ?>
                    <?= $form->field($model, $attrLeaveName)->hiddenInput(['id' => 'leave-work_send_name'])->label(false) ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 5: เหตุผล + สถานที่ -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="message-square"></i>
            รายละเอียดการลา
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-medium req">สาเหตุการลา</label>
                    <?= $form->field($model, $attrReason, ['template' => '{input}{error}'])->textarea([
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'ระบุสาเหตุการลา เช่น ป่วยเป็นไข้หวัด, ติดธุระทางครอบครัว',
                    ])->label(false) ?>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-medium">สถานที่ไป</label>
                    <div class="pill-group" role="radiogroup" aria-label="สถานที่ไประหว่างลา">
                        <?php foreach (['ภายในจังหวัด' => 'ในจังหวัด', 'ต่างจังหวัด' => 'ต่างจังหวัด', 'ต่างประเทศ' => 'ต่างประเทศ'] as $val => $lab): ?>
                            <?php $checked = $existingPlaceGo === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(\yii\helpers\Html::getInputName($model, $attrPlaceGo)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-place-go"
                                >
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-place-go" value="<?= Html::encode($existingPlaceGo) ?>">
                </div>

                <?= $form->field($model, $attrPhone)->hiddenInput()->label(false) ?>
                <?= $form->field($model, $attrAddress)->hiddenInput()->label(false) ?>
            </div>
        </div>
    </section>

    <!-- Section 6: เอกสารแนบ -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="paperclip"></i>
            เอกสารแนบ / ใบรับรองแพทย์
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <p class="mobile-upload-hint" role="note">
                    <i data-lucide="info" class="mi-sm"></i>
                    <span>แนบใบรับรองแพทย์, รูปถ่าย, PDF, Word หรือ Excel ก็ได้</span>
                </p>
                <div class="mobile-upload">
                    <?= $isUpdate ? $model->Upload('leave_file') : FileManagerHelper::FileUpload($draftRef, 'leave_file') ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 7: ลายเซ็น -->
    <section class="lr-section">
        <h3 class="section-title">
            <i data-lucide="signature"></i>
            ลายเซ็น
        </h3>
        <div class="card leave-card">
            <div class="card-body">
                <div class="pill-group mb-3" role="tablist" aria-label="วิธีลงลายเซ็น">
                    <button type="button"
                        class="pill-option btn-sig-tab <?= $existingSigType !== 'system' ? 'is-active' : '' ?>"
                        data-sig-type="canvas" role="tab" aria-selected="<?= $existingSigType !== 'system' ? 'true' : 'false' ?>">
                        เซ็นสด
                    </button>
                    <button type="button"
                        class="pill-option btn-sig-tab <?= $existingSigType === 'system' ? 'is-active' : '' ?>"
                        data-sig-type="system" role="tab" aria-selected="<?= $existingSigType === 'system' ? 'true' : 'false' ?>">
                        ลายเซ็นในระบบ
                    </button>
                </div>

                <div id="sig-panel-canvas" class="<?= $existingSigType === 'system' ? 'd-none' : '' ?>">
                    <div class="lr-sig-canvas-wrap">
                        <canvas id="sig-canvas" width="560" height="180" aria-label="พื้นที่วาดลายเซ็น"></canvas>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 mt-2" id="sig-clear-btn">
                        <i data-lucide="eraser" class="mi-xs mi-baseline me-1"></i> ล้างลายเซ็น
                    </button>
                </div>

                <div id="sig-panel-system" class="<?= $existingSigType !== 'system' ? 'd-none' : '' ?>">
                    <?php if ($signatureSystemUrl): ?>
                        <div class="lr-sig-system-preview">
                            <img src="<?= Html::encode($signatureSystemUrl) ?>" alt="ลายเซ็นจากระบบ HR">
                            <p class="small text-body-secondary mt-2 mb-0">ลายเซ็นจากระบบ HR จะถูกแนบในใบลาโดยอัตโนมัติ</p>
                        </div>
                    <?php else: ?>
                        <div class="lr-annual-alert is-warn" style="display: flex;">
                            <i data-lucide="alert-triangle" class="mi-sm"></i>
                            ไม่พบลายเซ็นในระบบ HR กรุณาใช้การเซ็นสดแทน
                        </div>
                    <?php endif; ?>
                </div>

                <?= $form->field($model, 'data_json[signature_type]')->hiddenInput(['id' => 'sig-type-input', 'value' => $existingSigType])->label(false) ?>
                <?= $form->field($model, 'data_json[signature_data]')->hiddenInput(['id' => 'sig-data-input'])->label(false) ?>
            </div>
        </div>
    </section>

    <!-- Sticky submit -->
    <div class="mobile-form-actions">
        <?= Html::submitButton(
            '<i data-lucide="send" class="mi-sm mi-baseline me-2"></i> ส่งคำขอลา',
            [
                'class' => 'btn btn-primary',
                'id' => 'leave-submit-btn',
                'name' => 'action',
                'value' => 'submit',
            ]
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
\app\widgets\datepicker\Assets::register($this);
$this->registerJs("if (typeof thaiDatepicker === 'function') thaiDatepicker('#leave-date_start,#leave-date_end');", View::POS_END);

$calDaysUrl     = Url::to(['/leave/leave/cal-days']);
$updateShiftUrl = Url::to(['/leave/leave/update-work-shift']);
$csrfParam      = \Yii::$app->request->csrfParam;
$csrfToken      = \Yii::$app->request->csrfToken;
$remainingAnnualLeaveJs = json_encode((float) $remainingAnnualLeave);

$js = <<<JS
(function(){
    var calDaysUrl     = "{$calDaysUrl}";
    var updateShiftUrl = "{$updateShiftUrl}";
    var csrfParam      = "{$csrfParam}";
    var csrfToken      = "{$csrfToken}";
    var annualLeaveTypeCode = 'LT4';
    var annualLeaveRemaining = {$remainingAnnualLeaveJs};

    // ── pill groups → hidden field mirror ────────────────────────────────
    function bindPillGroups() {
        document.querySelectorAll('.pill-group').forEach(function(group) {
            var radios = group.querySelectorAll('input[type="radio"]');
            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    group.querySelectorAll('.pill-option').forEach(function(opt) {
                        opt.classList.remove('is-active');
                    });
                    var label = radio.closest('label');
                    if (label) label.classList.add('is-active');
                    var targetId = radio.dataset.pillTarget;
                    if (targetId) {
                        var t = document.getElementById(targetId);
                        if (t) {
                            t.value = radio.value;
                            t.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                });
            });
        });
    }

    // ── helpers ───────────────────────────────────────────────────────────
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

    function isAnnualLeaveSelected() {
        return getLeaveTypeId() === annualLeaveTypeCode;
    }

    function setSubmitBlocked(blocked) {
        var btn = document.getElementById('leave-submit-btn');
        if (!btn) return;
        btn.disabled = !!blocked;
        btn.setAttribute('aria-disabled', blocked ? 'true' : 'false');
        btn.classList.toggle('opacity-75', !!blocked);
    }

    function updateAnnualLeaveWarning(total) {
        var alertEl = document.getElementById('leave-annual-leave-alert');
        if (!alertEl) return;
        alertEl.className = 'lr-annual-alert';

        // Sentinel -1 = stats unavailable; never block, never warn.
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
            icon = 'info';
            cls  = 'is-info';
            text = 'วันลาพักผ่อนคงเหลือ ' + formatLeaveDays(remaining) + ' วัน';
        }
        alertEl.className = 'lr-annual-alert ' + cls;
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
            totalEl.removeAttribute('readonly');
            totalEl.classList.remove('d-none');
            totalEl.tabIndex = 0;
            showEl.classList.add('d-none');
            if (hintEl) hintEl.classList.remove('d-none');
        } else {
            totalEl.setAttribute('readonly', 'readonly');
            totalEl.classList.add('d-none');
            totalEl.tabIndex = -1;
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
        if (s) s.textContent = satsun  != null ? satsun  : 0;
        if (h) h.textContent = holiday != null ? holiday : 0;
        if (t) t.value = total != null ? total : 0;
        if (tShow) tShow.textContent = total != null ? total : 0;
        if (totalDaysInput) totalDaysInput.value = total != null ? total : 0;
    }

    function toggleDateEndType() {
        var s = getDateStart();
        var e = getDateEnd();
        var endTypeEl = document.getElementById('leave-date_end_type');
        if (!endTypeEl) return;
        if (s && e && s.length >= 8 && e.length >= 8 && s === e) {
            endTypeEl.value = '0';
            endTypeEl.disabled = true;
        } else {
            endTypeEl.disabled = false;
        }
    }

    function calDays() {
        var s = getDateStart();
        var e = getDateEnd();
        if (!s || !e || s.length < 8 || e.length < 8) {
            updateSummary(0, 0, 0);
            updateAnnualLeaveWarning(0);
            return;
        }
        var params = new URLSearchParams({
            date_start:      s,
            date_end:        e,
            date_start_type: getDateStartType(),
            date_end_type:   getDateEndType(),
            leave_type_id:   getLeaveTypeId(),
            work_shift:      getWorkShift(),
        });
        fetch(calDaysUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'error') {
                    updateSummary(0, 0, 0);
                    updateAnnualLeaveWarning(0);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message, timer: 4000, showConfirmButton: false });
                    }
                    return;
                }
                if (typeof res.remaining_annual_leave !== 'undefined') {
                    annualLeaveRemaining = parseFloat(res.remaining_annual_leave) || 0;
                }
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
                updateSummary(0, 0, 0);
                updateAnnualLeaveWarning(0);
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

        if (shiftEl) {
            shiftEl.addEventListener('change', function(){
                toggleTotalEditable();
                updateWorkShift(this.value);
            });
        }

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
            function onPickerChange() {
                setTimeout(function() { toggleDateEndType(); calDays(); }, 50);
            }
            try {
                jQuery('#leave-date_start, #leave-date_end').datetimepicker('setOptions', { onSelectDate: onPickerChange });
            } catch (e) {}
            jQuery(document).on('changedatetime.xdsoft', '#leave-date_start,#leave-date_end', onPickerChange);
        }

        var leaveTypeEl = document.getElementById('leave-create-form-leave_type_id');
        if (leaveTypeEl) {
            leaveTypeEl.addEventListener('change', function(){ calDays(); });
        }
    }

    bindPillGroups();
    bindEvents();
    toggleDateEndType();
    toggleTotalEditable();
    calDays();

    // ── form submit: confirm + ajax (matches desktop _form pattern) ──────
    function ajaxSubmit(form, totalVal, successKey) {
        if (totalVal <= 0) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0' });
            return false;
        }
        if (isAnnualLeaveSelected() && annualLeaveRemaining >= 0 && (annualLeaveRemaining <= 0 || totalVal > annualLeaveRemaining)) {
            updateAnnualLeaveWarning(totalVal);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'วันลาพักผ่อนไม่เพียงพอ',
                    text: annualLeaveRemaining <= 0
                        ? 'คงเหลือ 0 วัน ไม่สามารถยื่นลาได้'
                        : 'คงเหลือ ' + formatLeaveDays(annualLeaveRemaining) + ' วัน, ขอใช้ ' + formatLeaveDays(totalVal) + ' วัน',
                });
            }
            return false;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันส่งคำขอลา?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ส่งคำขอ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#0d6efd',
                reverseButtons: true,
            }).then(function(r){
                if (!r.isConfirmed) return;
                Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                jQuery.ajax({ url: form.attr('action'), type: 'post', data: form.serialize(), dataType: 'json' })
                    .done(function(res){
                        var ok = successKey ? res[successKey] : (res.status === 'success' || res.success);
                        if (ok) {
                            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 1500 }).then(function(){
                                if (res.redirect) location.href = res.redirect; else location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', text: res.message || 'เกิดข้อผิดพลาด' });
                        }
                    })
                    .fail(function(){ Swal.fire({ icon: 'error', text: 'เชื่อมต่อไม่สำเร็จ' }); });
            });
        } else {
            form.off('beforeSubmit').submit();
        }
        return false;
    }

    var formUpdate = document.getElementById('form-leave-update');
    if (formUpdate && typeof jQuery !== 'undefined') {
        jQuery(formUpdate).on('beforeSubmit', function(e){
            e.preventDefault();
            var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
            return ajaxSubmit(jQuery(this), total, 'status');
        });
    }

    var formCreate = document.getElementById('mobile-leave-request-form');
    if (formCreate) {
        // For mobile create flow: fall back to native submit + mobileConfirm if Swal absent.
        formCreate.addEventListener('submit', function(e) {
            var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
            if (total <= 0) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0' });
                else alert('วันลาต้องมากกว่า 0');
                return false;
            }
            if (isAnnualLeaveSelected() && annualLeaveRemaining >= 0 && (annualLeaveRemaining <= 0 || total > annualLeaveRemaining)) {
                e.preventDefault();
                updateAnnualLeaveWarning(total);
                return false;
            }
            if (window.mobileConfirm && !window.mobileConfirm(this, 'ยืนยันส่งคำขอลา?')) {
                e.preventDefault();
            }
        });
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, View::POS_END);

// Signature canvas JS (carried over from desktop _form.php)
$existingSigDataJs = json_encode($existingSigData);
$sigJs = <<<JS
(function(){
    var canvas  = document.getElementById('sig-canvas');
    var typeInp = document.getElementById('sig-type-input');
    var dataInp = document.getElementById('sig-data-input');
    if (!canvas || !typeInp || !dataInp) return;

    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasDrawn = false;

    function scaleXY(clientX, clientY) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width  / rect.width;
        var scaleY = canvas.height / rect.height;
        return [(clientX - rect.left) * scaleX, (clientY - rect.top) * scaleY];
    }

    function startDraw(x, y) { drawing = true; ctx.beginPath(); ctx.moveTo(x, y); }
    function moveDraw(x, y) {
        if (!drawing) return;
        ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        ctx.strokeStyle = '#1a1a2e';
        ctx.lineTo(x, y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(x, y);
        hasDrawn = true;
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
    if (clearBtn) clearBtn.addEventListener('click', function(){
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        dataInp.value = '';
        hasDrawn = false;
    });

    document.querySelectorAll('.btn-sig-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var t = this.dataset.sigType;
            typeInp.value = t;
            document.querySelectorAll('.btn-sig-tab').forEach(function(b){
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');
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
        if (typeInp.value === 'canvas' && hasDrawn) {
            dataInp.value = canvas.toDataURL('image/png');
        }
    }

    var formCreate = document.getElementById('mobile-leave-request-form');
    var formUpdate = document.getElementById('form-leave-update');
    [formCreate, formUpdate].forEach(function(f){
        if (f) f.addEventListener('submit', captureSignature, true);
    });
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('beforeSubmit', '#mobile-leave-request-form, #form-leave-update', captureSignature);
    }
})();
JS;
$this->registerJs($sigJs, View::POS_END);
