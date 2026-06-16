<?php

use app\modules\filemanager\components\FileManagerHelper;
use app\widgets\datepicker\DatepickerThai;
use kartik\widgets\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var \app\modules\leave\models\Leave $model */
/** @var bool $isUpdate */
/** @var array $typeOptions */
/** @var array $approveChain */
/** @var string $draftRef */
/** @var string $leaveSendInitAvatar */
/** @var string $leaveWorkSendInitText */
/** @var string $existingWorkShift */
/** @var string $existingDateStartT */
/** @var string $existingDateEndT */
/** @var string $existingPlaceGo */
/** @var string $existingSigType */
/** @var string|null $signatureSystemUrl */
/** @var int $initSatsun */
/** @var int $initHoliday */
/** @var float $initTotal */
/** @var string $resultsJs */

$approveChain          = $approveChain ?? [];
$leaveSendInitAvatar   = $leaveSendInitAvatar ?? '';
$leaveWorkSendInitText = $leaveWorkSendInitText ?? '';

// attr names ของ data_json sub-fields
$attrWorkShift     = 'data_json[work_shift]';
$attrReason        = 'data_json[reason]';
$attrPhone         = 'data_json[leave_contact_phone]';
$attrPlaceGo       = 'data_json[place_go]';
$attrAddress       = 'data_json[address]';
$attrLeaveSend     = 'data_json[leave_work_send_id]';
$attrLeaveName     = 'data_json[leave_work_send_name]';
$attrDateStartType = 'data_json[date_start_type]';
$attrDateEndType   = 'data_json[date_end_type]';
?>

<section class="bl-mode bl-mode-wizard" data-mode-section="wizard">

    <nav class="bl-wizard" id="bl-wizard" aria-label="ขั้นตอนการขอลา">
        <div class="bl-wizard-track" role="progressbar" aria-valuemin="1" aria-valuemax="7" aria-valuenow="1">
            <div class="bl-wizard-fill" id="bl-wizard-fill"></div>
        </div>
        <ol class="bl-wizard-steps">
            <?php foreach ([
                1 => 'ประเภท', 2 => 'ช่วงเวลา', 3 => 'เหตุผล',
                4 => 'มอบหมาย', 5 => 'เอกสาร', 6 => 'ลายเซ็น', 7 => 'ส่งคำขอ',
            ] as $num => $lbl): ?>
                <li class="bl-wizard-step <?= $num === 1 ? 'is-active' : '' ?>" data-step="<?= $num ?>"<?= $num === 1 ? ' aria-current="step"' : '' ?>>
                    <span class="bl-wizard-pip">
                        <span class="bl-wizard-pip-num"><?= $num ?></span>
                        <i data-lucide="check" class="bl-wizard-pip-check"></i>
                    </span>
                    <span><?= Html::encode($lbl) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Compact stepper <380px -->
    <div class="bl-stepper-compact" aria-hidden="true">
        <div class="bl-progress-track">
            <div class="bl-progress-fill" id="bl-progress-fill"></div>
        </div>
        <div class="bl-stepper-compact-label">
            <span class="bl-stepper-compact-num">ขั้นที่ <strong id="bl-step-num">1</strong> จาก 7</span>
            <span class="bl-stepper-compact-name" id="bl-step-name">ประเภทการลา</span>
        </div>
    </div>

    <div class="bl-body px-3">

        <?php $form = ActiveForm::begin([
            'id'      => $isUpdate ? 'form-leave-update' : 'mobile-leave-request-form',
            'action'  => $isUpdate ? ['/leave/leave/update', 'id' => $model->id] : null,
            'options' => ['enctype' => 'multipart/form-data', 'novalidate' => 'novalidate'],
            'enableAjaxValidation' => $isUpdate,
            'validationUrl'        => $isUpdate ? ['/leave/leave/update-validation', 'id' => $model->id] : null,
            'fieldConfig' => [
                'options'      => ['class' => 'mb-0'],
                'labelOptions' => ['class' => 'form-label fw-semibold'],
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ],
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

        <!-- ─── Step 1: ประเภทการลา ─── -->
        <section class="bl-panel" data-step-panel="1">
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 1 จาก 7</p>
                <h2 class="bl-panel-title">ประเภทการลา</h2>
                <p class="bl-panel-desc">เลือกประเภทการลาและประเภทของเวร</p>
            </header>

            <div class="bl-card">
                <div class="mb-3">
                    <label class="form-label fw-semibold req" for="leave-create-form-leave_type_id">ประเภทการลา</label>
                    <?= $form->field($model, 'leave_type_id', ['template' => '{input}{error}'])->dropDownList($typeOptions, [
                        'id'     => 'leave-create-form-leave_type_id',
                        'class'  => 'form-select',
                        'prompt' => 'เลือกประเภทการลา',
                    ])->label(false) ?>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold d-flex align-items-baseline justify-content-between gap-2">
                        <span>ประเภทของเวร</span>
                        <span class="text-body-secondary" style="font-size: var(--fs-xs);">เวร 8: ไม่นับวันหยุด</span>
                    </label>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทของเวร">
                        <?php foreach (['normal' => 'เวรเช้า', 'shift' => 'เวร 8 ชั่วโมง'] as $val => $lab): ?>
                            <?php $checked = $existingWorkShift === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(Html::getInputName($model, $attrWorkShift)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    id="leave-work_shift-<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-work_shift">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-work_shift" value="<?= Html::encode($existingWorkShift) ?>">
                </div>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="1"></div>
        </section>

        <!-- ─── Step 2: ช่วงเวลา ─── -->
        <section class="bl-panel" data-step-panel="2" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 2 จาก 7</p>
                <h2 class="bl-panel-title">ช่วงเวลาที่ลา</h2>
                <p class="bl-panel-desc">เลือกวันเริ่ม/สิ้นสุด + ครึ่งวันหรือเต็มวัน. ระบบจะคำนวณรวมวันลาให้</p>
            </header>

            <div class="bl-card">
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold req">ตั้งแต่วันที่</label>
                        <?= $form->field($model, 'date_start', ['template' => '{input}{error}'])->widget(DatepickerThai::class, [
                            'options' => ['class' => 'form-control', 'id' => 'leave-date_start', 'placeholder' => 'วันเริ่ม', 'autocomplete' => 'off'],
                        ])->label(false) ?>
                        <div class="pill-group mt-2" role="radiogroup" aria-label="ครึ่งเช้า/บ่าย (วันเริ่ม)">
                            <?php foreach (['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'] as $val => $lab): ?>
                                <?php $checked = (string) $existingDateStartT === (string) $val; ?>
                                <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                    <input type="radio"
                                        name="<?= Html::encode(Html::getInputName($model, $attrDateStartType)) ?>"
                                        value="<?= Html::encode($val) ?>"
                                        <?= $checked ? 'checked' : '' ?>
                                        data-pill-target="leave-date_start_type">
                                    <?= Html::encode($lab) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="leave-date_start_type" value="<?= Html::encode($existingDateStartT) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold req">ถึงวันที่</label>
                        <?= $form->field($model, 'date_end', ['template' => '{input}{error}'])->widget(DatepickerThai::class, [
                            'options' => ['class' => 'form-control', 'id' => 'leave-date_end', 'placeholder' => 'วันสิ้นสุด', 'autocomplete' => 'off'],
                        ])->label(false) ?>
                        <div class="pill-group mt-2" role="radiogroup" aria-label="ครึ่งเช้า/บ่าย (วันสิ้นสุด)">
                            <?php foreach (['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'] as $val => $lab): ?>
                                <?php $checked = (string) $existingDateEndT === (string) $val; ?>
                                <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                    <input type="radio"
                                        name="<?= Html::encode(Html::getInputName($model, $attrDateEndType)) ?>"
                                        value="<?= Html::encode($val) ?>"
                                        <?= $checked ? 'checked' : '' ?>
                                        data-pill-target="leave-date_end_type">
                                    <?= Html::encode($lab) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="leave-date_end_type" value="<?= Html::encode($existingDateEndT) ?>">
                    </div>
                </div>

                <div class="bl-summary-stats">
                    <div class="bl-stat-box">
                        <span class="bl-stat-value" id="leave-summary-satsun"><?= (int) $initSatsun ?></span>
                        <span class="bl-stat-label">เสาร์-อาทิตย์</span>
                    </div>
                    <div class="bl-stat-box">
                        <span class="bl-stat-value" id="leave-summary-holiday"><?= (int) $initHoliday ?></span>
                        <span class="bl-stat-label">นักขัตฤกษ์</span>
                    </div>
                    <div class="bl-stat-box" id="leave-summary-total-box">
                        <span class="bl-stat-value" id="leave-summary-total-show"><?= (float) $initTotal ?></span>
                        <input type="text" id="leave-summary-total" class="form-control text-center fw-bold d-none" value="<?= (float) $initTotal ?>" readonly tabindex="-1" inputmode="decimal" aria-label="สรุปวันลา">
                        <span class="bl-stat-label">รวมวันลา <span id="leave-total-hint" class="text-info d-none">(แก้ไขได้)</span></span>
                    </div>
                </div>
                <div id="leave-annual-leave-alert" class="bl-annual-alert" role="alert" aria-live="polite"></div>
                <div id="leave-zero-day-hint" class="bl-zero-day-hint" role="status" aria-live="polite" hidden>
                    <i data-lucide="alert-circle" class="mi-sm"></i>
                    <span>ช่วงเวลาที่เลือกรวมเป็น <strong>0 วัน</strong> โปรดตรวจสอบวันที่ หรือเปลี่ยน เต็มวัน/ครึ่งวัน ใหม่</span>
                </div>
                <?= $form->field($model, 'total_days')->hiddenInput(['id' => 'leave-total_days'])->label(false) ?>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="2"></div>
        </section>

        <!-- ─── Step 3: เหตุผลและสถานที่ ─── -->
        <section class="bl-panel" data-step-panel="3" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 3 จาก 7</p>
                <h2 class="bl-panel-title">เหตุผลและสถานที่</h2>
                <p class="bl-panel-desc">ระบุเหตุผลและที่ที่จะไประหว่างลา</p>
            </header>

            <div class="bl-card">
                <div class="mb-3">
                    <label class="form-label fw-semibold req">สาเหตุการลา</label>
                    <?= $form->field($model, $attrReason, ['template' => '{input}{error}'])->textarea([
                        'class' => 'form-control',
                        'rows' => 4,
                        'placeholder' => 'ระบุสาเหตุการลา เช่น ป่วยเป็นไข้หวัด, ติดธุระทางครอบครัว',
                    ])->label(false) ?>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">สถานที่ไป</label>
                    <div class="pill-group" role="radiogroup" aria-label="สถานที่ไประหว่างลา">
                        <?php foreach (['ภายในจังหวัด' => 'ในจังหวัด', 'ต่างจังหวัด' => 'ต่างจังหวัด', 'ต่างประเทศ' => 'ต่างประเทศ'] as $val => $lab): ?>
                            <?php $checked = $existingPlaceGo === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                    name="<?= Html::encode(Html::getInputName($model, $attrPlaceGo)) ?>"
                                    value="<?= Html::encode($val) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    data-pill-target="leave-place-go">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="leave-place-go" value="<?= Html::encode($existingPlaceGo) ?>">
                </div>

                <?= $form->field($model, $attrPhone)->hiddenInput()->label(false) ?>
                <?= $form->field($model, $attrAddress)->hiddenInput()->label(false) ?>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="3"></div>
        </section>

        <!-- ─── Step 4: มอบหมายงาน ─── -->
        <section class="bl-panel" data-step-panel="4" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 4 จาก 7</p>
                <h2 class="bl-panel-title">มอบหมายงาน</h2>
                <p class="bl-panel-desc">เลือกพนักงานที่จะรับมอบหมายงานระหว่างการลา</p>
            </header>

            <div class="bl-card">
                <label class="form-label fw-semibold">เลือกผู้รับมอบหมาย</label>
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
                            'url' => \yii\helpers\Url::to(['/depdrop/employee-by-id']),
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
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="4"></div>
        </section>

        <!-- ─── Step 5: เอกสารแนบ ─── -->
        <section class="bl-panel" data-step-panel="5" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 5 จาก 7</p>
                <h2 class="bl-panel-title">เอกสารแนบ</h2>
                <p class="bl-panel-desc">แนบใบรับรองแพทย์, รูปถ่าย, PDF, Word หรือ Excel (ไม่บังคับ)</p>
            </header>

            <div class="bl-card">
                <p class="bl-upload-hint" role="note">
                    <i data-lucide="info" class="mi-sm"></i>
                    <span>กรณีลาป่วยเกิน 3 วันให้แนบใบรับรองแพทย์</span>
                </p>
                <div class="mobile-upload bl-upload-wrap">
                    <?= $isUpdate ? $model->Upload('leave_file') : FileManagerHelper::FileUpload($draftRef, 'leave_file') ?>
                </div>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="5"></div>
        </section>

        <!-- ─── Step 6: ลายเซ็น ─── -->
        <section class="bl-panel" data-step-panel="6" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนที่ 6 จาก 7</p>
                <h2 class="bl-panel-title">ลายเซ็น</h2>
                <p class="bl-panel-desc">เลือกลายเซ็นในระบบ HR หรือเซ็นสดผ่านหน้าจอ</p>
            </header>

            <div class="bl-card">
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
                    <div class="bl-sig-canvas-wrap">
                        <canvas id="sig-canvas" width="560" height="180" aria-label="พื้นที่วาดลายเซ็น"></canvas>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 mt-2" id="sig-clear-btn">
                        <i data-lucide="eraser" class="mi-xs mi-baseline me-1"></i> ล้างลายเซ็น
                    </button>
                </div>

                <div id="sig-panel-system" class="<?= $existingSigType !== 'system' ? 'd-none' : '' ?>">
                    <?php if ($signatureSystemUrl): ?>
                        <div class="bl-sig-system-preview">
                            <img src="<?= Html::encode($signatureSystemUrl) ?>" alt="ลายเซ็นจากระบบ HR">
                            <p class="small text-body-secondary mt-2 mb-0">ระบบจะแนบลายเซ็นจาก HR ในใบลาให้อัตโนมัติ</p>
                        </div>
                    <?php else: ?>
                        <div class="bl-annual-alert is-warn" style="display: flex;">
                            <i data-lucide="alert-triangle" class="mi-sm"></i>
                            ไม่พบลายเซ็นในระบบ HR กรุณาใช้การเซ็นสดแทน
                        </div>
                    <?php endif; ?>
                </div>

                <?= $form->field($model, 'data_json[signature_type]')->hiddenInput(['id' => 'sig-type-input', 'value' => $existingSigType])->label(false) ?>
                <?= $form->field($model, 'data_json[signature_data]')->hiddenInput(['id' => 'sig-data-input'])->label(false) ?>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="6"></div>
        </section>

        <!-- ─── Step 7: ตรวจสอบและส่งคำขอ ─── -->
        <section class="bl-panel" data-step-panel="7" hidden>
            <header class="bl-panel-head">
                <p class="bl-panel-eyebrow">ขั้นตอนสุดท้าย</p>
                <h2 class="bl-panel-title">ตรวจสอบและส่งคำขอ</h2>
                <p class="bl-panel-desc">ตรวจสอบข้อมูลและยืนยันก่อนส่งให้ผู้บังคับบัญชา</p>
            </header>

            <div class="bl-summary-card" aria-label="สรุปการลา">
                <div class="bl-summary-row">
                    <span class="bl-summary-label">ประเภทการลา</span>
                    <span class="bl-summary-value" id="bl-summary-type">-</span>
                </div>
                <div class="bl-summary-row">
                    <span class="bl-summary-label">ช่วงเวลา</span>
                    <span class="bl-summary-value" id="bl-summary-period">-</span>
                </div>
                <div class="bl-summary-row">
                    <span class="bl-summary-label">รวมวันลา</span>
                    <span class="bl-summary-value" id="bl-summary-total">-</span>
                </div>
                <div class="bl-summary-row">
                    <span class="bl-summary-label">สาเหตุ</span>
                    <span class="bl-summary-value" id="bl-summary-reason">-</span>
                </div>
                <div class="bl-summary-row">
                    <span class="bl-summary-label">มอบหมายงาน</span>
                    <span class="bl-summary-value" id="bl-summary-send">-</span>
                </div>
            </div>

            <div class="bl-summary-card">
                <header class="bl-summary-head">
                    <h3 class="bl-summary-title"><i data-lucide="git-branch"></i> ขั้นตอนการอนุมัติ</h3>
                </header>
                <div class="bl-summary-body">
                    <?php if (empty($approveChain)): ?>
                        <p class="small text-body-secondary mb-0 py-1">ยังไม่ได้ตั้งค่าผู้อนุมัติสำหรับบุคลากรนี้</p>
                    <?php else: ?>
                        <div class="bl-workflow">
                            <?php foreach ($approveChain as $item):
                                $step = $item['step']; $approveEmployee = $item['employee']; ?>
                                <div class="bl-step-chip">
                                    <div class="bl-step-dot">
                                        <?php if ($approveEmployee): ?>
                                            <?= Html::img($approveEmployee->showAvatar(), ['alt' => $approveEmployee->fullname]) ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="bl-step-name"><?= Html::encode($step['title'] ?? $step['label']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bl-confirm-card">
                <label class="bl-confirm-row" for="bl-confirm-chk">
                    <input type="checkbox" id="bl-confirm-chk">
                    <span class="bl-confirm-text">
                        ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้อง และขอใช้สิทธิ์การลาตามที่ระบุ
                    </span>
                </label>
            </div>
            <div class="bl-step-error" role="alert" aria-live="polite" data-step-error="7"></div>
        </section>

        <?php ActiveForm::end(); ?>
    </div>
</section>
