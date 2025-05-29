<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(['id' => 'form']); ?>
<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
<div class="row">
    <div class="col-4">
        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-8">
        <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อกลุ่ม') ?>
    </div>
    <div class="col-12">
    <?= $form->field($model, 'data_json[chat_id]')->textInput(['maxlength' => true])->label('Chat ID ของกลุ่ม Telegram') ?>
    <?= $form->field($model, 'data_json[token]')->textInput(['maxlength' => true])->label('Token') ?>
    </div>
</div>
<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;
$js = <<< JS
                    \$('#form').on('beforeSubmit', function (e) {
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
                                    \$("#main-modal-label").html(response.title);
                                    \$(".modal-body").html(response.content);
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