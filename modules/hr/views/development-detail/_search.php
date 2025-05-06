<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentDetailSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="development-detail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'development_id') ?>

    <?= $form->field($model, 'category_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'emp_id') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'price') ?>

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
