<?php
use yii\helpers\Url;
use app\models\Category;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use kartik\widgets\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\datecontrol\DateControl;
?>

<?php $form = ActiveForm::begin([
        // 'layout' => 'horizontal',
        // 'type' => ActiveForm::TYPE_HORIZONTAL,
        'type' => ActiveForm::TYPE_FLOATING,
        'options' => ['enctype' => 'multipart/form-data'],
        'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL]
    ]);?>
    <?php if(isset($model->data_json['token'])):?>
<div class="alert alert-success" role="alert">
 key  :  <?=isset($model->data_json['token']) ? $model->data_json['token'] : ''?>
</div>
<?php endif;?>

<?= $form->field($model, 'data_json[dashbroad_url]')->textInput(['maxlength' => true])->label('dashbroad url') ?>
<?= $form->field($model, 'data_json[logo]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[site_name]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[manual_url]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[token]')->hiddenInput(['maxlength' => true])->label(false) ?>



<div class="form-group mt-4 mb-4">
    <?=Html::submitButton('บันทึก', ['class' => 'btn btn-success elevation-2 shadow'])?>
    <?= Html::a('ยกเลิก',['index'],['class' => 'btn btn-secondary elevation-2 shadow'])?>
    <?=Html::a('Gennerate Key',['/setting/genkey'],['class' => 'btn btn-warning']);?>
</div>

<?php ActiveForm::end();?>
