<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentsDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documents-detail-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'document_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'to_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'to_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'to_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'deleted_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
