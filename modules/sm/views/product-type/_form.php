<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductType $model */
/** @var yii\widgets\ActiveForm $form */
?>

    <?php $form = ActiveForm::begin([
        'id' => 'form'
    ]); ?>
    <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('รหัส') ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อของประเภทวัสดุ') ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'submit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
    JS;
$this->registerJS($js)
?>