<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetSequence $model */
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-fsn-sequence',
    'options' => [
        'data-confirm-title' => 'ยืนยันบันทึกลำดับ',
        'data-confirm-text' => 'หมายเลขครุภัณฑ์ถัดไปในหมวดและปีนี้จะเริ่มจากค่าที่กำหนด + 1',
    ],
]); ?>

<div class="modal-body">
    <p class="text-body-secondary small mb-3">
        กำหนดลำดับล่าสุดสำหรับรหัสหมวด FSN และปี พ.ศ. — หมายเลขถัดไปจะเริ่มจากค่านี้ + 1<br>
        รหัสหมวดใช้รูปแบบเดียวกับ FSN ในรายการครุภัณฑ์ (เช่น <code>7910-003-0003</code>)
    </p>

    <div class="row g-3">
        <div class="col-12">
            <?= $form->field($model, 'category_id')->textInput([
                'maxlength' => true,
                'class' => 'form-control font-monospace',
                'placeholder' => 'เช่น 7910-003-0003',
                'autofocus' => true,
            ])->label('รหัสหมวด (FSN)') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'year')->textInput([
                'type' => 'number',
                'class' => 'form-control',
                'placeholder' => 'เช่น 2568',
            ])->label('ปี พ.ศ.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'current_sequence')->textInput([
                'type' => 'number',
                'min' => 0,
                'class' => 'form-control',
                'placeholder' => '0',
            ])->label('ลำดับล่าสุด')->hint('หมายเลขถัดไปจะได้ลำดับนี้ + 1') ?>
        </div>
    </div>
</div>

<div class="modal-footer border-top">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php $this->registerJs("handleFormSubmit('#form-fsn-sequence');"); ?>
