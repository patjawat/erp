<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var app\models\Categorise $model */
/* @var yii\widgets\ActiveForm $form */
?>
<style>
.col-form-label {
    text-align: end;
}
</style>

    <?php $form = ActiveForm::begin([
        'id' => 'form-unit',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']],
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
    ]); ?>

    <?php echo $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
    <?php echo $form->field($model, 'name')->hiddenInput()->label(false); ?>

    <?php echo $form->field($model, 'title')->textInput(['maxlength' => true]); ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']); ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

                    \$('#form-unit').on('beforeSubmit', function (e) {
                        var form = \$(this);
                        \$.ajax({
                            url: form.attr('action'),
                            type: 'post',
                            data: form.serialize(),
                            dataType: 'json',
                            success: async function (response) {
                                form.yiiActiveForm('updateMessages', response, true);
                                if(response.status == 'success') {
                                    closeModal()
                                    success()
                                    await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                }
                            }
                        });
                        return false;
                    });
    JS;
$this->registerJS($js);
?>