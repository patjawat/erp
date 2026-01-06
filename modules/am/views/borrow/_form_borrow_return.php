<?php

use yii\helpers\Html;
use app\components\AppHelper;
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
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>

<div class="p-3 mb-4 rounded-3 border-0 shadow-sm" style="background-color: #f0f2f5;">
    <div class="row align-items-center">
        <div class="col-md-6 border-end border-2">
            <small class="text-muted d-block mb-1">ผู้ยืมล่าสุด:</small>
            <div class="d-flex align-items-center">
                <span class="fw-bold fs-6"><?= $model->employee->getAvatar(false) ?></span>
            </div>
        </div>
        <div class="col-md-6 ps-md-4">
            <small class="text-muted d-block mb-1">ระยะเวลาการยืม:</small>
            <span class="fw-bold text-primary"><i class="bi bi-calendar3 me-1"></i> <?= $model->date_start ?></span>
            <span class="badge bg-warning text-dark ms-2 rounded-pill fw-normal">(3 วันที่แล้ว)</span>
        </div>
    </div>
</div>

    <div class="row g-3">
        <div class="col-md-6">
        <?= $form->field($model, 'data_json[return_result]')->radioList([
            'normal' => '<i class="bi bi-check-circle me-1"></i> ปกติ',
            'broken' => '<i class="bi bi-exclamation-triangle me-1"></i> ชำรุด'
        ], [
            'item' => function ($index, $label, $name, $checked, $value) {
                $color = ($value == 'broken') ? 'outline-danger' : 'outline-success';
                $active = $checked ? 'checked' : '';
                return '
                    <input type="radio" class="btn-check" name="' . $name . '" value="' . $value . '" id="cond-' . $value . '" autocomplete="off" ' . $active . '>
                    <label class="btn btn-' . $color . ' w-100 py-2 rounded-pill mb-2" for="cond-' . $value . '">
                        ' . $label . '
                    </label>';
            }
        ])->label('สภาพเครื่องมือหลังใช้งาน <span class="text-danger">*</span>', ['class' => 'fw-bold mb-2']) ?>
    </div>
  

        <div class="col-6 mt-4">
            <?= $form->field($model, 'actual_date')->textInput(['placeholder' => 'วันที่ดำเนินการ'])->label('วันที่คืนจริง'); ?>
        </div>


    </div>

    <div class="col-12 mt-3">
        <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 4, 'placeholder' => 'ระบุสภาพเครื่อง หรือปัญหาที่พบหลังการใช้งาน...'])->label('รายละเอียด/ปัญหาที่พบ (ถ้ามี)') ?>
    </div>
    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-actual_date,#assetdetail-plan_date')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>