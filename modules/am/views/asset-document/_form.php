<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-detail-form">

  <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-document/validator']
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'asset_id')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[title]')->textInput()->label('ชื่อเอกสาร') ?>
    <?= $form->field($model, 'data_json[asset_document_type]')->textInput()->label('ประเภท') ?>
    <?= $model->Upload() ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>