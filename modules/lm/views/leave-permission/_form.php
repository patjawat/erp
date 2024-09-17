<?php

use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;
// use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermission $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .field-leavepermission-point{
        margin-bottom: 0px !important;
    }
</style>
<div class="leave-permission-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        // 'type' => ActiveForm::TYPE_HORIZONTAL,
        ]); ?>
    <?= $form->field($model, 'leave_type_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'position_type_id')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-12">
        </div>
        <div class="col-6">
            <?= $form->field($model, 'days_point')->textInput()->label('ลาได้') ?>
        </div>
        <div class="col-6">
    <?= $form->field($model, 'point')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label(true);?>
    </div>
</div>
<div>

</div>


<div class="row">
<div class="col-6">
    <?= $form->field($model, 'service_time')->textInput()->label('อายุงาน(ปี)') ?>

</div>
<div class="col-6">
    <?= $form->field($model, 'days_point')->textInput()->label('สะสมวันลา') ?>

</div>
</div>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS

    // \$('#boardId').val(8).trigger('change');
    $('#form').on('beforeSubmit',  function (e) {
        var form = \$(this);
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success:  function (response) {
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
                    $("#main-modal").modal("hide");
                    success()
                      $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});  
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>