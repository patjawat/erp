<?php
use app\components\AppHelper;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
?>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 5],
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    // 'validationUrl' => ['/am/asset/validator'],
]);?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<div class="row">
<div class="col-8">
<?php
echo $form->field($model, 'data_json[brand]')->widget(Select2::classname(), [
    'data' => [],
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        'tags' => true,
    ],
    'pluginEvents' => [
        "select2:select" => "function(result) {
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
    ],
])->label('ยี่ห้อ');
?>
<?=$form->field($model, 'data_json[ram]')->textInput(['maxlength' => true])->label('RAM')?>

<?php
echo $form->field($model, 'data_json[model]')->widget(Select2::classname(), [
    'data' => [],
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        'tags' => true,
    ],
    'pluginEvents' => [
        "select2:select" => "function(result) {
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
    ],
])->label('รุ่น');
?>
<?php
echo $form->field($model, 'data_json[os]')->widget(Select2::classname(), [
    'data' => [],
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        'tags' => true,
    ],
    'pluginEvents' => [],
])->label('OS');
?>

<?php
echo $form->field($model, 'data_json[cpu]')->widget(Select2::classname(), [
    'data' => [],
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        'tags' => true,
    ],
    'pluginEvents' => [],
])->label('CPU');
?>



<?php
echo $form->field($model, 'data_json[storage_type]')->widget(Select2::classname(), [
    'data' => [
        'HDD' => 'HDD',
        'SSD' => 'SSD',
    ],
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        'tags' => true,
    ],
    'pluginEvents' => [],
])->label('Storage');
?>
<?=$form->field($model, 'data_json[storage_size]')->textInput(['maxlength' => true])->label('ขนาดพื้นที่เก็บ')?>

</div>
</div>

<div class="form-group mt-4 d-flex justify-content-center">
    <?=AppHelper::BtnSave();?>
</div>
<?php ActiveForm::end();?>


<?php
$js = <<< JS

if(keycode == '13'){
alert('You pressed a "enter" key in textbox');
}
$('#form-asset').on('beforeSubmit', function (e) {
    
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (res) {
            form.yiiActiveForm('updateMessages', res, true);
            if (form.find('.invalid-feedback').length) {
                // validation failed
            } else {
                // validation succeeded
            }
            if(res.status == 'success') {
                // alert(data.status)
                console.log(res.container);
                // $('#main-modal').modal('toggle');
                success()
                 $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        }
    });
    return false;
});


JS;
$this->registerJs($js);
?>