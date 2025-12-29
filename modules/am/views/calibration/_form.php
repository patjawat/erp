<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

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
    <?= $form->field($model, 'plan_date')->textInput(['placeholder' => 'ระบุบวันที่กำหนดแผน calibration'])->label('วันที่ตามแผน'); ?>
  </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
    <?= $form->field($model, 'actual_date')->textInput(['placeholder' => 'วันที่ดำเนินการ'])->label('วันที่ดำเนินการ'); ?>
  </div>
  
<div class="col-lg-6 col-md-6 col-sm-12">
    <?= $form->field($model, 'provider_type')->dropDownList([
      'external' => 'หน่วยงานภายนอก (Outsource)',
      'internal' => 'ดำเนินการเอง (In-house)',
    ], [
      'class' => 'form-select',
      'prompt' => 'เลือกผู้ให้บริการ...', // Optional: adds a blank first option
    ])->label('ผู้ให้บริการสอบเทียบ (Provider)', [
      'class' => 'form-label fw-bold'
    ]) ?>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12">
    <?= $form->field($model, 'cal_result')->radioList(
      [
        'pass' => 'ผ่าน (Pass)',
        'fail' => 'ไม่ผ่าน (Fail)',
      ],
      [
        'class' => 'mt-2',
        'item' => function ($index, $label, $name, $checked, $value) {
          $colorClass = ($value === 'pass') ? 'text-success' : 'text-danger';
          $check = $checked ? 'checked' : '';

          return "
                <div class=\"form-check form-check-inline\">
                    <input class=\"form-check-input\" type=\"radio\" name=\"{$name}\" 
                           value=\"{$value}\" id=\"res_{$value}\" {$check}>
                    <label class=\"form-check-label {$colorClass} fw-bold\" for=\"res_{$value}\">
                        {$label}
                    </label>
                </div>";
        }
      ]
    )->label('ผลการสอบเทียบ', ['class' => 'form-label fw-bold']) ?>
  </div>

    <div class="col-lg-12 col-md-12 col-sm-12">
    <?= $form->field($model, 'data_json[remark]')->textArea(['rows' => 4,'placeholder' => 'ระบุค่าความคลาดเคลื่อนหรือรายละเอียดเพิ่มเติม'])->label('รายละเอียด/หมายเหตุ') ?>
  </div>
</div>

<label class="form-label fw-bold">แนบใบสอบเทียบ (Calibration Certificate)</label>
<?= $model->Upload() ?>


<?php ActiveForm::end(); ?>



<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-actual_date,#assetdetail-plan_date')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>