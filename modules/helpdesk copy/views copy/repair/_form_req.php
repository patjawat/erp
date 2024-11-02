<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="repair-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-repair',
        // 'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[title]')->textInput(['placeholder' => 'ระบุเรื่อง'])->label('เรื่อง') ?>
    <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 3,'placeholder' => 'ระบุอาการ/อาการ/ปัญหา หรือ รายละเอียดเพิ่มเติม...'])->label('ss') ?>
    <hr>
    บันทึกการซ่อม
    <?= $form->field($model, 'data_json[repair_note]')->textArea(['rows' => 3,'placeholder' => 'ระบุอาการ/อาการ/ปัญหา หรือ รายละเอียดเพิ่มเติม...'])->label('xx') ?>
    <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุราคาถ้ามี'])->label('ราคา') ?>
<?=$model->Upload('repair')?>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
 
$('#form-repair').on('beforeSubmit', function (e) {
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
                try {
                    loadRepairHostory()
                } catch (error) {
                    
                }
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>