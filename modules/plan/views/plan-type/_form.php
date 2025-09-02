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


    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="d-flex gap-2">
            <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
             <?= Html::button('ปิด', [
            'class' => 'btn btn-secondary',
            'data-bs-dismiss' => 'modal'
        ]) ?>
        </div>
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