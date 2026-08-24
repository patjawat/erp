<?php
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
$formId = 'sp-section-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['data-list-url' => Url::to(['structure', 'id' => $template->id])]]);
?>
<div class="row g-3">
    <div class="col-12 col-md-5"><?= $form->field($model, 'section_code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น service_scope']) ?></div>
    <div class="col-12 col-md-7"><?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?></div>
    <div class="col-12 col-md-8"><?= $form->field($model, 'block_type')->dropDownList(ServiceProfileTemplateSection::blockTypeLabels(), ['class' => 'form-select']) ?></div>
    <div class="col-12 col-md-4"><?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0, 'step' => 10]) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'คำแนะนำที่ผู้จัดทำจะเห็นขณะกรอก']) ?></div>
    <div class="col-12 d-flex flex-wrap gap-4"><?= $form->field($model, 'is_required')->checkbox() ?><?= $form->field($model, 'is_enabled')->checkbox() ?></div>
</div>
<div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1" aria-hidden="true"></i> บันทึกหัวข้อ', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(){window.location.href='" . Url::to(['structure', 'id' => $template->id]) . "';});");
?>
