<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'leave_type_id') ?>

    <?= $form->field($model, 'leave_time_type') ?>

    <?= $form->field($model, 'data_json') ?>

    <?= $form->field($model, 'start_date') ?>

    <?php // echo $form->field($model, 'start_end') ?>

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
