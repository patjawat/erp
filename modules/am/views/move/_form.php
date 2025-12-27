<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-detail-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form'
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'asset_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขทรัพย์สิน/ครุภัณฑ์') ?>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'วันที่ดำเนินการ'])->label('วันที่ดำเนินการ'); ?>
        </div>
    </div>


    <?= $form->field($model, 'data_json[title]')->textInput()->label('หัวข้อการบำรุงรักษา') ?>
    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-date_start')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>