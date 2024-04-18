<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-user',
]); ?>
<?= $form->field($model, 'fullname')->hiddenInput(['maxlength' => true])->label(false) ?>

<div class="row" class="shadow">
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'status')->radioList($model->getItemStatus())->label('สถานะ') ?>
    </div>
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'roles')->checkboxList($model->getAllRoles())->label('สิทธิเข้าถึงข้อมูล') ?>
    </div>
</div>

<!-- /.card-footer -->
</div>


<div class="form-group d-flex justify-content-center">
            <?=app\components\AppHelper::BtnSave()?>
        </div>

<?php ActiveForm::end(); ?>
<br>

<?php
$js = <<< JS

$('#form-user').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                // closeModal()
                success()
                $('#main-modal').modal('toggle');
                // await  $.pjax.reload({ container:"#employee-container", history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});



JS;
$this->registerJS($js);
?>