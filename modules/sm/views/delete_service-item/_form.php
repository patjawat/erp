<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-type-form">

    <?php $form = ActiveForm::begin([
'id' => 'form-productType'
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->textInput()->label(false) ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อรายการ') ?>


    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'submit']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>



<?php
$js = <<< JS

                    \$('#form-productType').on('beforeSubmit', function (e) {
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
