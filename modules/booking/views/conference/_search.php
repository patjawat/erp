<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="booking-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'thai_year') ?>

    <?= $form->field($model, 'car_type') ?>

    <?php // echo $form->field($model, 'document_id') ?>

    <?php // echo $form->field($model, 'urgent') ?>

    <?php // echo $form->field($model, 'license_plate') ?>

    <?php // echo $form->field($model, 'conference_room_id') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'reason') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'date_start') ?>

    <?php // echo $form->field($model, 'time_start') ?>

    <?php // echo $form->field($model, 'date_end') ?>

    <?php // echo $form->field($model, 'time_end') ?>

    <?php // echo $form->field($model, 'driver_id') ?>

    <?php // echo $form->field($model, 'leader_id') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'deleted_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
