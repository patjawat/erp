<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="plan-type-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form'
    ]); ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'code')->textInput()->label('รหัส') ?>
    <?= $form->field($model, 'title')->textInput()->label('ชื่อของประเภท') ?>


    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
            <?= Html::button('ปิด', [
            'class' => 'btn btn-outline-secondary',
            'data-bs-dismiss' => 'modal'
        ]) ?>
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>
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
