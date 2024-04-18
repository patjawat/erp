<?php

use app\components\SiteHelper;
use yii\helpers\Html;
// use yii\widgets\ActiveForm;
use kartik\form\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="categorise-form">

<?php $form =ActiveForm::begin([
    'id' => 'form-family',
    'type' => ActiveForm::TYPE_FLOATING
]); ?>


<h6>ความสัมพันธ์</h6>
        <?=$form->field($model, 'data_json[relationship]')->radioList(['บิดา'=> 'บิดา','มารดา' => 'มารดา'],['inline'=>true])->label(false)?>

  
        <?=$form->field($model, 'data_json[fullname]')->textInput(['maxlength' => true])->label('ชื่อ-นามสกุล')?>
        <?=$form->field($model, 'data_json[birthday]')->textInput(['maxlength' => true])->label('วันเกิด')?>

   
        <?=$form->field($model, 'data_json[cid]')->textInput(['maxlength' => true])->label('เลขที่บัตรประชาชน')?>


        <?=$form->field($model, 'data_json[comment]')->textInput(['maxlength' => true])->label('หมายเหตุ')?>



        <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name')->hiddenInput(['value' => 'family'])->label(false) ?>

<?= $form->field($model, 'title')->hiddenInput(['maxlength' => true])->label(false) ?>



<?= $form->field($model, 'active')->hiddenInput()->label(false) ?>



<div class="submit-section">
    <?=SiteHelper::BtnSave();?>
</div>



    <?php ActiveForm::end(); ?>

</div>


<?php
$js  = <<< JS

//  Form submit Ajax
$('#form-family').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (data) {
            form.yiiActiveForm('updateMessages', data, true);
            if (form.find('.has-error').length) {
                // validation failed
            } else {
                // validation succeeded
            }

            if(data.status == 'success') {
                closeModal()
				success()
                await  $.pjax.reload({ container:'#general-container', history:false,replace: false});                                
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js,\yii\web\View::POS_END);
?>