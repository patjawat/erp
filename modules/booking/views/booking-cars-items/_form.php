<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="booking-cars-items-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
                echo $form->field($model, 'car_type')->widget(Select2::classname(), [
                    'data' => [
                        'general' => 'รถใช้งานทั่วไป',
                        'ambulance' => 'รถพยาบาล'
                    ],
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประรถยนต์');
                ?>
                    <?php
                echo $form->field($model, 'asset_item_id')->widget(Select2::classname(), [
                    'data' => $model->listCars(),
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('รถยนต์');
                ?>

    <?= $form->field($model, 'asset_item_id')->textInput() ?>

    <?= $form->field($model, 'license_plate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'active')->textInput() ?>

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
