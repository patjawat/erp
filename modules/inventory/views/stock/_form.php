<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'asset_item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'warehouse_id')->textInput() ?>

    <?= $form->field($model, 'qty')->textInput() ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
