<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="categorise-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'category_id') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'emp_id') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'title') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'active') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
