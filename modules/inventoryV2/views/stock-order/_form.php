<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockOrder $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-order-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'order_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'order_type')->dropDownList([ 'IN' => 'IN', 'OUT' => 'OUT', 'TRANSFER' => 'TRANSFER', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'order_date')->textInput() ?>

    <?= $form->field($model, 'warehouse_id')->textInput() ?>

    <?= $form->field($model, 'to_warehouse_id')->textInput() ?>

    <?= $form->field($model, 'contact_id')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
