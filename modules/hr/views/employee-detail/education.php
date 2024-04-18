
<?php
use kartik\widgets\Select2;
use yii\web\View;
?>
    <?=$form->field($model, 'data_json[education_name]')->hiddenInput()->label(false)?>
<?=$form->field($model, 'data_json[education]')->widget(Select2::classname(), [
    'data' => $model->GetEducationItems(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
    ],
    'pluginEvents' => [
        "change" => "function() { 
            var selectedText = $(this).find('option:selected').text();
            $('#employeedetail-data_json-education_name').val(selectedText)
         }",
    ]
])->label('ระดับการศึกษา')?>


    <?=$form->field($model, 'data_json[major]')->textInput()->label('สาขาวิชาเอก')?>
    <?=$form->field($model, 'data_json[institute]')->textInput()->label('สำเร็จจากสถาบัน')?>

<div class="row">
<div class="col-6">
<?= $form->field($model, 'data_json[date_start]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('เข้ารับการศึกษา (ว/ด/พ.ศ.)');
?>
</div>
<div class="col-6">
<?= $form->field($model, 'data_json[date_end]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('สำเร็จการศึกษา (ว/ด/พ.ศ.)');
?>
</div>
</div>

<?= $model->upload($model->ref,'education') ?>

<?php
$js = <<<JS


JS;
$this->registerJS($js, View::POS_END)
    ?>
