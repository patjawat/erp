<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <?= $form->field($model, 'lot_number')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'unit_price')->textInput(['maxlength' => true]) ?>


    <?php ActiveForm::end(); ?>

</div>

