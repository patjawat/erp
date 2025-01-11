<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectStatus $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="project-status-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'project_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
