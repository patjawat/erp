<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupRequestSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="sup-request-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'req_code') ?>

    <?= $form->field($model, 'req_date') ?>

    <?= $form->field($model, 'req_detail') ?>

    <?php // echo $form->field($model, 'req_vendor') ?>

    <?php // echo $form->field($model, 'req_amount') ?>

    <?php // echo $form->field($model, 'req_status') ?>

    <?php // echo $form->field($model, 'req_dep') ?>

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
