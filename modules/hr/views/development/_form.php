<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="development-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'document_id')->textInput() ?>

    <?= $form->field($model, 'topic')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thai_year')->textInput() ?>

    <?= $form->field($model, 'date_start')->textInput() ?>

    <?= $form->field($model, 'time_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_end')->textInput() ?>

    <?= $form->field($model, 'time_end')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vehicle_type_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vehicle_date_start')->textInput() ?>

    <?= $form->field($model, 'vehicle_date_end')->textInput() ?>

    <?= $form->field($model, 'driver_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'leader_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'leader_group_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assigned_to')->textInput() ?>

    <?= $form->field($model, 'emp_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

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
