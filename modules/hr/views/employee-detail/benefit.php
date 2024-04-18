
<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;
use yii\widgets\MaskedInput;


?>
<div class="row">
<div class="col-lg-6 col-md-6 col-sm-6">
<?= $form->field($model, 'data_json[date_start]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่ได้รับโทษ') ?>
    <?= $form->field($model, 'data_json[blame_type]')->textInput(['autofocus' => true])->label('ประเภทความผิด') ?>
    </div>
<div class="col-lg-6 col-md-6 col-sm-6">
<?= $form->field($model, 'data_json[date_end]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่สิ้นสุดโทษ') ?>
<?= $form->field($model, 'data_json[blame]')->textInput(['autofocus' => true])->label('การลงโทษ') ?>

                </div>

                <div class="col-12">
        <?=$form->field($model, 'data_json[doc_ref]')->textInput()->label('เอกสารอ้างอิง')?>
    </div>
    <div class="col-12">
        <?=$form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ')?>
    </div>

    <?= $model->upload($model->ref,'license') ?>


<?php
$js = <<<JS

JS;
$this->registerJS($js, View::POS_END)
?>
