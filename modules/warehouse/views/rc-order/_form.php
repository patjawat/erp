<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<div class="order-form">

    <?php $form = ActiveForm::begin([
        'id' => 'rc',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'fieldConfig' => ['options' => ['class' => 'form-group mb-1']]
    ]); ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->textInput()->label('ใบสั่งซื้อ') ?>
    <?= $form->field($model, 'code')->textInput() ?>


    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;
$js = <<< JS

                    \$('#form-rc').on('beforeSubmit', function (e) {
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
$this->registerJS($js)
?>