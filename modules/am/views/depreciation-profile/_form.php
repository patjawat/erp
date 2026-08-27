<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\datepicker\DatepickerThai;
use app\modules\am\models\DepreciationProfile;

/** @var yii\web\View $this */
/** @var app\modules\am\models\DepreciationProfile $model */
/** @var yii\widgets\ActiveForm $form */

// รองรับทั้งเปิดผ่าน .open-modal (submit แบบ ajax + reload pjax) และเปิดหน้าเต็ม (fallback)
$formId = 'dp-form';
?>
<?php $form = ActiveForm::begin([
    'id' => $formId,
    'options' => ['data-list-url' => Url::to(['index'])],
]); ?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>

    <div class="col-md-4">
        <div class="mb-3">
            <span class="form-label d-block"><?= Html::encode($model->getAttributeLabel('method')) ?></span>
            <div class="form-control dp-readonly-value" aria-readonly="true">
                <?= Html::encode(DepreciationProfile::methodOptions()[$model->method] ?? $model->method) ?>
            </div>
            <?= Html::activeHiddenInput($model, 'method') ?>
        </div>
    </div>
    <div class="col-md-4"><?= $form->field($model, 'useful_life_months')->textInput(['type' => 'number', 'min' => 1]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'annual_rate')->textInput([
        'type' => 'number',
        'step' => '0.0001',
        'aria-describedby' => 'dp-annual-rate-hint',
    ])->hint('เว้นว่างได้ ถ้าคิดจากอายุการใช้งาน', ['id' => 'dp-annual-rate-hint']) ?></div>

    <div class="col-12"><?= $form->field($model, 'salvage_value_type')->radioList(
        DepreciationProfile::salvageTypeOptions(),
        ['class' => 'dp-segmented', 'role' => 'radiogroup', 'aria-labelledby' => 'dp-salvage-type-label']
    )->label(null, ['id' => 'dp-salvage-type-label']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'salvage_value')->textInput([
        'type' => 'number',
        'step' => '0.0001',
        'aria-describedby' => 'dp-salvage-value-hint',
    ])->hint('จำนวนเงิน หรือ % ตามชนิดที่เลือก', ['id' => 'dp-salvage-value-hint']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'rounding_scale')->textInput(['type' => 'number', 'min' => 0, 'max' => 6]) ?></div>

    <div class="col-12"><?= $form->field($model, 'calculation_basis')->radioList(
        DepreciationProfile::basisOptions(),
        ['class' => 'dp-segmented', 'role' => 'radiogroup', 'aria-labelledby' => 'dp-basis-label']
    )->label(null, ['id' => 'dp-basis-label']) ?></div>
    <div class="col-12"><?= $form->field($model, 'start_rule')->radioList(
        DepreciationProfile::startRuleOptions(),
        ['class' => 'dp-segmented', 'role' => 'radiogroup', 'aria-labelledby' => 'dp-start-rule-label']
    )->hint('แนะนำสำหรับหน่วยงานภาครัฐ: “รายเดือน ตัดรอบวันที่ 15” — วันที่ 1–15 คิดเดือนนั้น วันที่ 16 เป็นต้นไปเริ่มเดือนถัดไป')
      ->label(null, ['id' => 'dp-start-rule-label']) ?></div>
    <div class="col-12"><?= $form->field($model, 'status')->radioList(
        DepreciationProfile::statusOptions(),
        ['class' => 'dp-segmented', 'role' => 'radiogroup', 'aria-labelledby' => 'dp-status-label']
    )->label(null, ['id' => 'dp-status-label']) ?></div>

    <div class="col-md-6"><?= $form->field($model, 'effective_from')->widget(DatepickerThai::class, [
        'options' => ['placeholder' => 'วว/ดด/ปปปป', 'autocomplete' => 'off'],
    ]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'effective_to')->widget(DatepickerThai::class, [
        'options' => ['placeholder' => 'วว/ดด/ปปปป', 'autocomplete' => 'off'],
    ]) ?></div>
</div>

<div class="mt-3 d-flex flex-wrap justify-content-end gap-2 dp-form-actions">
    <?= Html::button('ยกเลิก', [
        'class' => 'btn btn-light js-dp-cancel',
        'type' => 'button',
        'data' => Yii::$app->request->isAjax
            ? ['bs-dismiss' => 'modal', 'fallback-url' => Url::to(['index'])]
            : ['fallback-url' => Url::to(['index'])],
    ]) ?>
    <?= Html::submitButton('<i data-lucide="save"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerCss(<<<'CSS'
#dp-form {
    --dp-primary: #0d6efd;
    --dp-primary-ink: #0a58ca;
    --dp-primary-soft: rgba(13, 110, 253, .08);
    --dp-surface-2: #f7f9fc;
    --dp-surface-hover: #f1f5f9;
    --dp-ink-1: #1a202c;
    --dp-ink-2: #4a5568;
    --dp-line-strong: rgba(15, 23, 42, .14);
    --dp-ease: cubic-bezier(.16, 1, .3, 1);
}
[data-bs-theme="dark"] #dp-form {
    --dp-primary-soft: rgba(110, 168, 254, .2);
    --dp-surface-2: #2b3035;
    --dp-surface-hover: #343a40;
    --dp-ink-1: #f1f5f9;
    --dp-ink-2: #e2e8f0;
    --dp-line-strong: rgba(255, 255, 255, .2);
}
.dp-readonly-value {
    display: flex;
    align-items: center;
    min-height: 44px;
    background: var(--bs-secondary-bg);
    color: var(--bs-body-color);
}
#dp-form .form-control {
    min-height: 44px;
}
.dp-segmented {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
    overflow: clip;
    padding: 0;
    border: 1px solid var(--bs-border-color);
    border-radius: .5rem;
}
.dp-segmented > div {
    min-width: 0;
}
.dp-segmented > div + div {
    border-inline-start: 1px solid var(--bs-border-color);
}
.dp-segmented label {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    height: 100%;
    padding: .55rem .75rem;
    color: var(--bs-body-color);
    font-weight: 500;
    line-height: 1.3;
    text-align: center;
    cursor: pointer;
}
.dp-segmented input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}
.dp-segmented label:has(input:checked) {
    background: var(--bs-primary);
    color: var(--bs-white);
}
.dp-segmented label:has(input:focus-visible) {
    z-index: 1;
    box-shadow: inset 0 0 0 3px rgba(var(--bs-primary-rgb), .28);
}
.dp-form-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 120ms var(--dp-ease),
        border-color 120ms var(--dp-ease),
        color 120ms var(--dp-ease),
        box-shadow 120ms var(--dp-ease),
        transform 80ms var(--dp-ease);
}
.dp-form-actions .btn-primary {
    border-color: var(--dp-primary);
    background: var(--dp-primary);
    color: #fff;
}
.dp-form-actions .btn-primary:hover:not(:disabled) {
    border-color: var(--dp-primary-ink);
    background: var(--dp-primary-ink);
    color: #fff;
}
.dp-form-actions .btn-primary:active:not(:disabled) {
    transform: translateY(1px);
}
.dp-form-actions .btn-primary:focus-visible {
    box-shadow: 0 0 0 3px var(--dp-primary-soft);
}
.dp-form-actions .btn-light {
    border-color: var(--dp-line-strong);
    background: var(--dp-surface-2);
    color: var(--dp-ink-2);
}
.dp-form-actions .btn-light:hover {
    border-color: var(--dp-line-strong);
    background: var(--dp-surface-hover);
    color: var(--dp-ink-1);
}
.dp-form-actions .btn-light:focus-visible {
    box-shadow: 0 0 0 3px var(--dp-primary-soft);
}
.dp-form-actions .btn:disabled {
    opacity: .55;
    cursor: not-allowed;
}
@media (max-width: 575.98px) {
    .dp-segmented {
        grid-template-columns: 1fr;
    }
    .dp-segmented > div + div {
        border-inline-start: 0;
        border-top: 1px solid var(--bs-border-color);
    }
    .dp-form-actions .btn {
        flex: 1 1 8rem;
    }
}
@media (prefers-reduced-motion: reduce) {
    .dp-segmented label,
    .dp-form-actions .btn {
        transition: none !important;
    }
}
CSS);

$js = <<<JS
handleFormSubmit('#{$formId}', null, async function (response) {
    var container = response && response.container;
    if (container && document.querySelector(container) && typeof erpReloadPjax === 'function' && erpReloadPjax(container)) {
        return; // reload เฉพาะตารางผ่าน pjax (โหมด modal)
    }
    var url = document.querySelector('#{$formId}').getAttribute('data-list-url');
    if (url) { window.location.href = url; } else { location.reload(); }
});
var dpForm = document.querySelector('#{$formId}');
if (dpForm) {
    var dpCancelButton = dpForm.querySelector('.js-dp-cancel');
    if (dpCancelButton) {
        dpCancelButton.addEventListener('click', function (event) {
            if (!this.closest('.modal')) {
                event.preventDefault();
                event.stopPropagation();
                window.location.href = this.getAttribute('data-fallback-url');
            }
        });
    }
}
JS;
$this->registerJs($js);
?>
