<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Calendar $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="calendar-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'booking_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

   
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
