<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveRole $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-role-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <?= $form->field($model, 'emp_id')->textInput() ?>

    <?= $form->field($model, 'thai_year')->textInput() ?>

    <?= $form->field($model, 'work_year')->textInput() ?>

    <?= $form->field($model, 'position_type_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_days')->textInput() ?>

    <?= $form->field($model, 'max_days')->textInput() ?>

    <?= $form->field($model, 'use_days')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
