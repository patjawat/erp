<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documents-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'doc_type_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'topic')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'org_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'urgent')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'secret')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_date')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_expire')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_receive')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
