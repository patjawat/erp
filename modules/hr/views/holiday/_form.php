<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Holiday $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="holiday-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/hr/holiday/validator']
        ]); ?>
    <?= $form->field($model, 'name')->hiddenInput(['value' => 'holiday'])->label(false) ?>
<div class="row">
<div class="col-12">
    
<?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class)->label('วันที่') ?>
</div>

</div>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
$('#form').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});
            }
        }
    });
    return false;
});
JS;
$this->registerJS($js, View::POS_END)
?>