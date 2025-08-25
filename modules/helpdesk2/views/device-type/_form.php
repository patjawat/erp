<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\DeviceType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="device-type-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('รหัส') ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อรายการ') ?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
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
