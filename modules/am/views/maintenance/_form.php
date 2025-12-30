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
  <div class="col-md-6">
              <label class="form-label fw-bold">หน่วยงานที่รับผิดชอบ</label>
              <input type="text" class="form-control" name="department" value="<?= $model->asset?->departmentName() ?? ''?>" readonly="">
            </div>

  <div class="col-lg-6 col-md-6 col-sm-12">
    <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'ระบุบวันที่กำหนดแผน calibration'])->label('วันที่ตามแผน'); ?>
  </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
    <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'วันที่ดำเนินการ'])->label('วันที่ดำเนินการ'); ?>
  </div>
  
<div class="col-lg-12 col-md-12 col-sm-12">
    <?= $form->field($model, 'provider_type')->dropDownList([
      'external' => 'หน่วยงานภายนอก (Outsource)',
      'internal' => 'ดำเนินการเอง (In-house)',
    ], [
      'class' => 'form-select',
      'prompt' => 'เลือกผู้ให้บริการ...', // Optional: adds a blank first option
    ])->label('ผู้ให้บริการ', [
      'class' => 'form-label fw-bold'
    ]) ?>
  </div>

    <div class="col-lg-12 col-md-12 col-sm-12">
    <?= $form->field($model, 'data_json[remark]')->textArea(['rows' => 4,'placeholder' => 'ระบุวิธีดำเนินการหรือรายละเอียดเพิ่มเติม'])->label('รายละเอียด/หมายเหตุ') ?>
  </div>
</div>

    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-date_start,#assetdetail-date_end')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>