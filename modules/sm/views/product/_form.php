<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Product $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'asset_group')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'asset_item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fsn_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qty')->textInput() ?>

    <?= $form->field($model, 'receive_date')->textInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'purchase')->textInput() ?>

    <?= $form->field($model, 'department')->textInput() ?>

    <?= $form->field($model, 'repair')->textInput() ?>

    <?= $form->field($model, 'owner')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'life')->textInput() ?>

    <?= $form->field($model, 'device_items')->textInput() ?>

    <?= $form->field($model, 'on_year')->textInput() ?>

    <?= $form->field($model, 'dep_id')->textInput() ?>

    <?= $form->field($model, 'depre_type')->textInput() ?>

    <?= $form->field($model, 'budget_year')->textInput() ?>

    <?= $form->field($model, 'asset_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
