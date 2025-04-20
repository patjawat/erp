<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehicle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thai_year')->textInput() ?>

    <?= $form->field($model, 'go_type')->textInput() ?>

    <?= $form->field($model, 'oil_price')->textInput() ?>

    <?= $form->field($model, 'oil_liter')->textInput() ?>

    <?= $form->field($model, 'vehicle_type_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'document_id')->textInput() ?>

    <?= $form->field($model, 'owner_id')->textInput() ?>

    <?= $form->field($model, 'urgent')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_plate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reason')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_start')->textInput() ?>

    <?= $form->field($model, 'time_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_end')->textInput() ?>

    <?= $form->field($model, 'time_end')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'driver_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'leader_id')->textInput(['maxlength' => true]) ?>

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
