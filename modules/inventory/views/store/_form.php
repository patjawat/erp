<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Store $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="store-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'movement_type')->hiddenInput()->label(false);?>

    <?= $form->field($model, 'from_warehouse_id')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'to_warehouse_id')->hiddenInput()->label(false);?>

    <?= $form->field($model, 'data_json[checker]')->textInput() ?>
    <?= $form->field($model, 'data_json[checker_name]')->textInput() ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
