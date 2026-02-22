<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Categorise $model */
?>
<div class="categorise-form">
    <?php $form = ActiveForm::begin(['id' => 'form-item-type']); ?>
    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น M1-01'])->label('รหัสประเภท') ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อประเภทวัสดุ') ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 2])->label('รายละเอียด (ไม่บังคับ)') ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary me-2']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
