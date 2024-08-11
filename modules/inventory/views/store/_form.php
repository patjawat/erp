<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Store $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="store-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'movement_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_warehouse_id')->textInput() ?>
    <?= $form->field($model, 'to_warehouse_id')->textInput() ?>

    <?= $form->field($model, 'qty')->textInput() ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
