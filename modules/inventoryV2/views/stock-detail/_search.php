<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockDetailSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-detail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'stock_order_id') ?>

    <?= $form->field($model, 'item_id') ?>

    <?= $form->field($model, 'qty') ?>

    <?= $form->field($model, 'unit_price') ?>

    <?php // echo $form->field($model, 'lot_number') ?>

    <?php // echo $form->field($model, 'expiry_date') ?>

    <?php // echo $form->field($model, 'ref') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
