<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\WhcheckSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="whcheck-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'check_code') ?>

    <?= $form->field($model, 'check_date') ?>

    <?= $form->field($model, 'check_type') ?>

    <?php // echo $form->field($model, 'check_store') ?>

    <?php // echo $form->field($model, 'check_from') ?>

    <?php // echo $form->field($model, 'check_hr') ?>

    <?php // echo $form->field($model, 'check_status') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
