
<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>

<div class="row">
<div class="col-6">
<?= $form->field($model, 'data_json[date_start]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('ตั้งแต่วันที่');
?>
</div>
<div class="col-6">
<?= $form->field($model, 'data_json[date_end]')->widget(yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('ถึงวันที่');
?>

</div>

<div class="col-6">
    <?= $form->field($model, 'data_json[license_name]')->textInput()->label('ชื่อใบอนุญาต') ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[license_company]')->textInput()->label('หน่วยงานผู้ออก') ?>
    
</div>
<div class="col-12">
<?= $form->field($model, 'data_json[doc_ref]')->textInput()->label('เลขที่อ้างอิง ') ?>

</div>


    <div class="col-12">
        <?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>
        
    </div>
</div>

<?= $model->upload($model->ref,'license') ?>


<?php
$js = <<<JS



JS;
$this->registerJS($js, View::POS_END)
    ?>
