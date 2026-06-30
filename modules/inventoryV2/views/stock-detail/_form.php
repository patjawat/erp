<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-detail-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'stock_order_id')->textInput() ?>

    <?= $form->field($model, 'item_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unit_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lot_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'expiry_date')->textInput() ?>

    <?= $form->field($model, 'ref')->textInput(['maxlength' => true]) ?>

    <?php
    $dataJsonValue = $model->data_json;
    if (is_array($dataJsonValue) || is_object($dataJsonValue)) {
        $dataJsonValue = json_encode($dataJsonValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    ?>
    <?= $form->field($model, 'data_json')->textarea([
        'rows' => 4,
        'value' => $dataJsonValue,
        'placeholder' => 'JSON string (ปล่อยว่างได้)',
    ]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
