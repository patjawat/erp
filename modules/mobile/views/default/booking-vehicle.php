<?php

use app\widgets\datepicker\DatepickerThai;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Vehicle $model */
/** @var \app\modules\hr\models\Employees|null $employee */
/** @var array $saveErrors */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'จองรถราชการ';
$this->params['mobileSubtitle'] = 'กรอกข้อมูลเดินทางและส่งคำขอ';

$saveErrors = $saveErrors ?? [];

// Existing values for pill groups (for update mode / failed POST re-render)
$existingGoType = (int) ($model->go_type ?? 1);
$existingDriver = is_array($model->data_json ?? null) ? (string) ($model->data_json['driver'] ?? '') : '';
?>

<style>
/* Full-bleed escape so the shared .app-shell can reach screen edges. */
.bv-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
}

/* Form scroll area: shared .app-scroll handles the top offset for the
   fixed Hero. Extra bottom padding reserves space so the last section
   (24px gap) breathes above the sticky action bar (48px button +
   32px padding + up to 34px safe-area ≈ 7rem). */
.bv-scroll {
    padding-bottom: 7rem;
}

/* ── Body stack ────────────────────────────────────────────────────
   Body padding-top adds breathing room between hero curve and content.
   Both .bv-body and the inner form become flex columns so sections
   spread evenly (24px), giving each card its own breath zone. */
.bv-body {
    padding: var(--space-xl) var(--space-md) 0;
    display: flex; flex-direction: column;
    gap: var(--space-lg);
}
.bv-body form {
    display: flex; flex-direction: column;
    gap: var(--space-lg); /* 24px between Section 1/2/3 cards */
}

/* Section heading (outside card) — optional badge replaces inconsistent req-dot.
   Icon-text gap 12px (within brief 8-12px range), heading-to-card 12px. */
.bv-section-head {
    display: flex; align-items: center; gap: var(--space-sm);
    font-size: var(--fs-sm); font-weight: 600; color: var(--ink-3);
    margin: 0 0 var(--space-sm); padding: 0 var(--space-2xs);
    letter-spacing: -0.005em;
}
.bv-section-head svg { color: var(--mobile-primary); width: 1rem; height: 1rem; }
.bv-section-head .badge-optional {
    margin-left: auto;
    font-size: var(--fs-xs); font-weight: 500;
    color: var(--ink-4);
}

/* Card padding 20px (upper end of brief 16-20px range) for comfortable feel.
   Form groups inside use Bootstrap mb-3 = 16px. */
.bv-card {
    background: var(--surface); border-radius: 16px;
    padding: 1.25rem;
    box-shadow: var(--shadow-md);
}
.bv-card .form-control, .bv-card .form-select { border-radius: 12px; padding: 0.75rem 1rem; min-height: 3rem; }
.bv-card .form-label {
    font-weight: 500; font-size: var(--fs-sm); color: var(--ink-2);
    margin-bottom: var(--space-xs); /* 8px label→input — comfortable read */
}
.bv-card .invalid-feedback {
    display: block; font-size: var(--fs-xs);
    margin-top: var(--space-2xs); /* 4px input→error message — tight pair */
    color: var(--danger-strong);
}

/* Required asterisk via pseudo-element — applied to label class, not inline HTML */
.form-label.is-req::after { content: ' *'; color: var(--danger); font-weight: 700; }

/* ── Select2 (Krajee) inside .bv-card — match form-control sizing/feel ───── */
.bv-card .select2-container--krajee-bs5 .select2-selection,
.bv-card .select2-container--krajee .select2-selection {
    border-radius: 12px;
    min-height: 3rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid #dee2e6;
    box-shadow: none;
}
.bv-card .select2-container--krajee-bs5 .select2-selection__rendered,
.bv-card .select2-container--krajee .select2-selection__rendered {
    line-height: 2.15rem;
    padding-left: 0;
    color: var(--ink);
}
.bv-card .select2-container--krajee-bs5 .select2-selection__placeholder,
.bv-card .select2-container--krajee .select2-selection__placeholder {
    color: var(--ink-4);
}
.bv-card .select2-container--krajee-bs5.select2-container--focus .select2-selection,
.bv-card .select2-container--krajee.select2-container--focus .select2-selection {
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
}
.bv-card .select2-selection__arrow { height: 3rem !important; }
/* Dropdown — touch-friendly options + search box */
.select2-dropdown {
    border-radius: 12px !important;
    box-shadow: var(--shadow-lg);
    border-color: var(--ink-line) !important;
    overflow: hidden;
}
.select2-search--dropdown .select2-search__field {
    border-radius: 10px !important;
    border: 1px solid #dee2e6 !important;
    padding: 0.6rem 0.75rem !important;
    font-size: var(--fs-md);
    min-height: 2.75rem;
}
.select2-results__option {
    padding: 0.75rem 1rem !important;
    font-size: var(--fs-sm);
    min-height: 2.75rem;
    display: flex; align-items: center;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted,
.select2-container--krajee .select2-results__option--highlighted {
    background: var(--mobile-primary-soft) !important;
    color: var(--mobile-primary) !important;
}

/* Two-col date+time on wider widths */
/* Date+time paired field — 16px between the two columns / rows */
.bv-row { display: grid; gap: var(--space-md); }
@media (min-width: 360px) {
    .bv-row.bv-row-2 { grid-template-columns: 1fr 1fr; }
}

/* Sticky bottom submit bar — 16px breath top/bottom around the button */
.bv-actions {
    position: fixed; left: 0; right: 0; bottom: 0;
    background: var(--surface);
    padding: var(--space-md) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-md));
    box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
    border-top: 1px solid var(--ink-line);
    z-index: var(--z-sticky);
}
.bv-actions .btn {
    width: 100%; min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    transition: opacity 150ms cubic-bezier(0.16, 1, 0.3, 1);
}
.bv-actions .btn[disabled], .bv-actions .btn.is-busy { opacity: 0.7; cursor: wait; }

/* Error summary alert */
.bv-error-summary {
    background: var(--danger-soft);
    border-radius: 12px; padding: var(--space-sm) var(--space-md);
    color: var(--danger-strong); font-size: var(--fs-sm);
    display: flex; gap: var(--space-xs); align-items: flex-start;
}
.bv-error-summary ul { margin: 0; padding-left: 1.25rem; }

/* Pill option labels: shrink-friendly on narrow widths (<360px Thai labels) */
@media (max-width: 360px) {
    .pill-option { padding: var(--space-xs) 0.4rem; font-size: var(--fs-xs); }
}

/* Motion */
@keyframes bv-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.bv-body > * { animation: bv-enter 400ms cubic-bezier(0.16, 1, 0.3, 1) backwards; }
.bv-body > *:nth-child(1) { animation-delay: 80ms; }
.bv-body > *:nth-child(2) { animation-delay: 140ms; }
.bv-body > *:nth-child(3) { animation-delay: 200ms; }
.bv-body > *:nth-child(4) { animation-delay: 260ms; }

@media (prefers-reduced-motion: reduce) {
    .bv-body > * { animation: none !important; }
}
</style>

<div class="bv-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'car',
        'title'    => 'จองรถราชการ',
        'subtitle' => 'ระบุวัน เวลา และจุดหมาย เจ้าหน้าที่จะจัดสรรรถให้',
    ]) ?>

    <!-- Scroll area: shared .app-scroll offsets fixed Hero; .bv-scroll adds clearance for sticky actions -->
    <div class="app-scroll bv-scroll">
    <div class="bv-body">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success rounded-3 mb-0" role="alert">
                <i data-lucide="check-circle" class="mi-sm mi-baseline me-1"></i>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mb-0" role="alert">
                <i data-lucide="alert-circle" class="mi-sm mi-baseline me-1"></i>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($saveErrors)): ?>
            <div class="bv-error-summary" role="alert">
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
            'id' => 'mobile-booking-vehicle-form',
            'method' => 'post',
            'options' => ['novalidate' => 'novalidate'],
            'fieldConfig' => [
                'options'      => ['class' => 'mb-3'],
                'labelOptions' => ['class' => 'form-label'],
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ],
        ]); ?>

        <!-- Section 1: ช่วงเวลา -->
        <section>
            <h2 class="bv-section-head">
                <i data-lucide="calendar-range"></i>
                ช่วงเวลาเดินทาง
            </h2>
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
                        'template' => '{label}{input}{error}',
                        'options' => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->widget(DatepickerThai::class, [
                        'options' => ['class' => 'form-control', 'placeholder' => 'วันเริ่ม', 'autocomplete' => 'off', 'aria-required' => 'true'],
                    ])->label('วันเริ่ม') ?>
                    <?= $form->field($model, 'time_start', [
                        'template' => '{label}{input}{error}',
                        'options' => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->input('time', ['step' => 300, 'aria-required' => 'true'])->label('เวลาเริ่ม') ?>
                </div>

                <div class="bv-row bv-row-2 mb-0" id="bv-end-row">
                    <?= $form->field($model, 'date_end', [
                        'template' => '{label}{input}{error}',
                        'options' => ['class' => 'mb-0'],
                    ])->widget(DatepickerThai::class, [
                        'options' => ['class' => 'form-control', 'placeholder' => 'วันสิ้นสุด', 'autocomplete' => 'off'],
                    ])->label('วันสิ้นสุด') ?>
                    <?= $form->field($model, 'time_end', [
                        'template' => '{label}{input}{error}',
                        'options' => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->input('time', ['step' => 300, 'aria-required' => 'true'])->label('เวลากลับ') ?>
                </div>
            </div>
        </section>

        <!-- Section 2: รายละเอียดการเดินทาง -->
        <section>
            <h2 class="bv-section-head">
                <i data-lucide="map-pin"></i>
                รายละเอียดการเดินทาง
            </h2>
            <div class="bv-card">
                <?= $form->field($model, 'location', [
                    'labelOptions' => ['class' => 'form-label is-req'],
                ])->widget(Select2::class, [
                    'data'    => $model->ListOrg(),
                    'options' => [
                        'placeholder'    => 'ค้นหาหรือพิมพ์จุดหมายปลายทาง',
                        'aria-required'  => 'true',
                        'aria-label'     => 'จุดหมายปลายทาง',
                    ],
                    'pluginOptions' => [
                        'tags'                  => true,   // อนุญาตให้พิมพ์จุดหมายใหม่ที่ไม่อยู่ในรายการ
                        'allowClear'            => true,
                        'minimumResultsForSearch' => 0,    // แสดง search box เสมอ
                        'tokenSeparators'       => [],
                        'language'              => [
                            'noResults'        => new JsExpression('function(){ return "ไม่พบในรายการ — กด Enter เพื่อใช้ข้อความนี้"; }'),
                            'searching'        => new JsExpression('function(){ return "กำลังค้นหา..."; }'),
                            'inputTooShort'    => new JsExpression('function(){ return "พิมพ์อย่างน้อย 1 ตัวอักษร"; }'),
                        ],
                    ],
                ])->label('จุดหมายปลายทาง') ?>

                <?= $form->field($model, 'reason', [
                    'labelOptions' => ['class' => 'form-label is-req'],
                ])->textarea([
                    'rows' => 3,
                    'placeholder' => 'ระบุวัตถุประสงค์ของการเดินทาง',
                    'aria-required' => 'true',
                ])->label('วัตถุประสงค์') ?>

                <?= $form->field($model, 'data_json[passengers]', ['options' => ['class' => 'mb-0']])->input('number', [
                    'min' => 1, 'max' => 99, 'value' => $model->data_json['passengers'] ?? 1,
                    'placeholder' => 'จำนวนคน',
                ])->label('จำนวนผู้โดยสาร') ?>
            </div>
        </section>

        <!-- Section 3: คนขับ (optional) -->
        <section>
            <h2 class="bv-section-head">
                <i data-lucide="user"></i>
                คนขับ
                <span class="badge-optional">ไม่บังคับ</span>
            </h2>
            <div class="bv-card">
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
        </section>

        <!-- Sticky bottom action -->
        <div class="bv-actions">
            <?= Html::submitButton(
                '<i data-lucide="send" class="mi-sm mi-baseline me-2"></i> ส่งคำขอจองรถ',
                ['class' => 'btn btn-primary', 'id' => 'bv-submit', 'name' => 'action', 'value' => 'submit']
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div><!-- /.bv-body -->
    </div><!-- /.app-scroll.bv-scroll -->
</div>

<?php
$js = <<<'JS'
(function() {
    // ── Pill groups mirror radio value to a hidden input + active state
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

    // ── go_type toggles end-date row (ค้างคืน = แสดง, ไปกลับ = ซ่อน end date)
    var goTypeEl = document.getElementById('bv-go-type');
    var endRow   = document.getElementById('bv-end-row');
    function syncEndDate() {
        if (!goTypeEl || !endRow) return;
        var isOvernight = String(goTypeEl.value) === '2';
        // Always show time_end; only hide the date_end column when same-day trip.
        var dateEndField = endRow.querySelector('.field-vehicle-date_end, [class*="field-vehicle-date_end"]');
        if (dateEndField) dateEndField.style.display = isOvernight ? '' : 'none';
    }
    if (goTypeEl) goTypeEl.addEventListener('change', syncEndDate);
    syncEndDate();

    // ── Double-submit guard. Yii's beforeSubmit fires after client validation,
    //    so between the click and Swal opening there's a small window the user
    //    can double-tap. Flag + visual busy state during that window.
    var submitting = false;
    var formEl    = document.getElementById('mobile-booking-vehicle-form');
    var submitBtn = document.getElementById('bv-submit');
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            if (submitting) { e.preventDefault(); e.stopImmediatePropagation(); return false; }
            submitting = true;
            if (submitBtn) submitBtn.classList.add('is-busy');
            // Reset after Swal has had time to open; handleFormSubmit takes over after that.
            setTimeout(function() {
                submitting = false;
                if (submitBtn) submitBtn.classList.remove('is-busy');
            }, 400);
        });
    }

    // ── handleFormSubmit (erp.js): Swal confirm + AJAX + inline ActiveForm errors
    if (typeof handleFormSubmit === 'function') {
        handleFormSubmit('#mobile-booking-vehicle-form', null, function(response) {
            // handleFormSubmit redirects via response.redirect_url
        });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
