<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'asset_group') ?>

    <?= $form->field($model, 'asset_item') ?>

    <?= $form->field($model, 'code') ?>

    <?php // echo $form->field($model, 'fsn_number') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'receive_date') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'purchase') ?>

    <?php // echo $form->field($model, 'department') ?>

    <?php // echo $form->field($model, 'repair') ?>

    <?php // echo $form->field($model, 'owner') ?>

    <?php // echo $form->field($model, 'life') ?>

    <?php // echo $form->field($model, 'on_year') ?>

    <?php // echo $form->field($model, 'dep_id') ?>

    <?php // echo $form->field($model, 'depre_type') ?>

    <?php // echo $form->field($model, 'budget_year') ?>

    <?php // echo $form->field($model, 'asset_status') ?>

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
