<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Holiday $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="holiday-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'data_json[thai_year]')->textInput(['maxlength' => true])->label('ปีงบ') ?>
    <?= $form->field($model, 'data_json[date]')->textInput(['maxlength' => true])->label('วันที่') ?>


    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>
