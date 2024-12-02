<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveRoleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-role-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'data_json') ?>

    <?= $form->field($model, 'emp_id') ?>

    <?= $form->field($model, 'thai_year') ?>

    <?= $form->field($model, 'work_year') ?>

    <?php // echo $form->field($model, 'position_type_id') ?>

    <?php // echo $form->field($model, 'last_days') ?>

    <?php // echo $form->field($model, 'max_days') ?>

    <?php // echo $form->field($model, 'use_days') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
