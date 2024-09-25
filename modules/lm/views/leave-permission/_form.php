<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermission $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-permission-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'emp_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'service_time')->textInput() ?>

    <?= $form->field($model, 'point')->textInput() ?>

    <?= $form->field($model, 'last_point')->textInput() ?>

    <?= $form->field($model, 'new_point')->textInput() ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <?= $form->field($model, 'thai_year')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'deleted_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
