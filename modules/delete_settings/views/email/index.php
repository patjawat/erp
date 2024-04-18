<?php

use app\components\SiteHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
?>

<div class="container">

    <h3>ตั้งค่าการส่งอีเมล</h3>
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[email_dsn]')->textInput(['maxlength' => true])->label('DSN') ?>
        </div>
    </div>
    <div class="form-group">
        <?= SiteHelper::BtnSave(); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>