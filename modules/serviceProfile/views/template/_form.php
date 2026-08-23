<?php
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$formId = 'sp-template-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['data-list-url' => Url::to(['index'])]]);
?>
<div class="row g-3">
    <div class="col-12"><?= $form->field($model, 'org_unit_id')->dropDownList($ownerOptions, ['class' => 'form-select', 'prompt' => 'เลือกหน่วยงานหรือทีมประสาน', 'disabled' => !$model->isNewRecord]) ?><?php if (!$model->isNewRecord): ?><?= Html::activeHiddenInput($model, 'org_unit_id') ?><?php endif; ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น Service Profile งานศูนย์ประกันสุขภาพ']) ?></div>
    <div class="col-6"><?= $form->field($model, 'revision_no')->textInput(['type' => 'number', 'min' => 1, 'readonly' => !$model->isNewRecord]) ?></div>
    <div class="col-6"><?= $form->field($model, 'effective_fiscal_year')->textInput(['type' => 'number', 'min' => 2500, 'max' => 2700]) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'อธิบายขอบเขตหรือเงื่อนไขการใช้ Template']) ?></div>
</div>
<div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1" aria-hidden="true"></i> บันทึก Template', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){ if(r.redirect_url){window.location.href=r.redirect_url;return;} if(r.container&&typeof erpReloadPjax==='function'){erpReloadPjax(r.container);} });");
?>
