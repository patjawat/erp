<?php

use yii\helpers\Html;
use yii\helpers\Json;
use kartik\form\ActiveForm;
?>

  <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-type/validator']
    ]); ?>

<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'code')->textInput()->label("รหัส") ?>
<?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุประเภท...'])->label("ประเภท") ?>

<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => "summitxx"]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$js = <<< JS
         // เรียกใช้ function handleFormSubmit
        handleFormSubmit('#form', null, async function(response) {
            await location.reload();
        });

    
JS;
$this->registerJs($js);
?>