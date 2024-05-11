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

<?php if($model->isNewRecord):?>
<?= $form->field($model, 'data_json[title]')->textInput(['placeholder' => 'ระบุอาการเสีย...'])->label('อาการเสีย') ?>
    <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5,'placeholder' => 'ระบุรายละเอียดเพิ่มเติมของอาการเสีย...'])->label('เพิ่มเติม') ?>
    <?php else:?>


    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[repair_type]')->radioList(['ซ่อมภายใน' => 'ซ่อมภายใน','ซ่อมภายนอก' => 'ซ่อมภายนอก'],['inline'=>true,'custom' => true])->label('ประเภทการส่งซ่อม') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[repair_status]')->widget(Select2::classname(), [
    'data' => [
        'ร้องขอ' => 'ร้องขอ',
        'รับเรื่อง' => 'รับเรื่อง',
        'เสร็จสิ้น' => 'เสร็จสิ้น'
    ],
    'options' => ['placeholder' => 'Select a state ...'],
    'pluginOptions' => [
        'allowClear' => true
    ],
])->label('สถานะ') ?>
        </div>
    </div>

    <?= $form->field($model, 'data_json[repair_note]')->textArea(['rows' => 6,'placeholder' => 'ระบุการแก้ไข/อื่นๆ...'])->label('การแก้ไข') ?>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี','type' => 'number'])->label('มูลค่าการซ่อม') ?>
        </div>

    </div>
    <?=$model->Upload('repair')?>

<?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[note]')->hiddenInput()->label(false) ?>
    <?php endif;?>
    
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