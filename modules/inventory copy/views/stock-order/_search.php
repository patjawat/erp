<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'stock_order_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'po_number') ?>

    <?= $form->field($model, 'rc_number') ?>

    <?= $form->field($model, 'product_id') ?>

    <?php // echo $form->field($model, 'from_warehouse_id') ?>

    <?php // echo $form->field($model, 'to_warehouse_id') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'movement_type') ?>

    <?php // echo $form->field($model, 'movement_date') ?>

    <?php // echo $form->field($model, 'lot_number') ?>

    <?php // echo $form->field($model, 'expiry_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
