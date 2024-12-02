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

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
