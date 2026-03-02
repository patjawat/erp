<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var app\modules\health\models\HealthOption $model */
?>

<?php $form = ActiveForm::begin(['id' => 'health-option-form', 'type' => ActiveForm::TYPE_VERTICAL]); ?>

<?= Html::activeHiddenInput($model, 'name') ?>

<div class="row g-3">

    <div class="col-12">
        <div class="p-3 rounded-3 border border-danger-subtle d-flex align-items-center gap-3 mb-2">
            <i class="fas fa-heartbeat text-danger fs-4"></i>
            <div>
                <div class="fw-bold text-dark small">โรคประจำตัว</div>
                <div class="text-muted small">รหัสโรคใช้เป็น key เก็บใน data_json — ห้ามเปลี่ยนหลังมีข้อมูลแล้ว</div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <?= $form->field($model, 'code')->textInput([
            'maxlength'   => true,
            'placeholder' => 'เช่น DM, HT, Heart',
            'class'       => 'form-control',
            'id'          => 'opt-code',
        ])->label('รหัสโรค <span class="text-danger">*</span>') ?>
    </div>

    <div class="col-md-7">
        <?= $form->field($model, 'title')->textInput([
            'maxlength'   => true,
            'placeholder' => 'เช่น เบาหวาน, โรคหัวใจ',
            'class'       => 'form-control',
            'id'          => 'opt-title',
        ])->label('ชื่อโรค <span class="text-danger">*</span>') ?>
    </div>

    <div class="col-12 d-flex align-items-center pt-1">
        <div class="form-check form-switch ms-1">
            <?= Html::activeCheckbox($model, 'active', [
                'class'   => 'form-check-input',
                'id'      => 'opt-active',
                'value'   => 1,
                'uncheck' => 0,
                'label'   => false,
            ]) ?>
            <label class="form-check-label fw-medium" for="opt-active">เปิดใช้งาน</label>
        </div>
    </div>

</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
handleFormSubmit('#health-option-form', null, async function(response) {
    if (response.status === 'success') {
        location.reload();
    }
});
JS);
?>
