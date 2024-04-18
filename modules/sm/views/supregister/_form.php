<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Supregister $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card" style="color: #000000;">
    <div class="card-body"> 
        <div class="supregister-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>

    <?= $form->field($model, 'regisnumber')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_date')->textInput() ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dep_code')->textInput(['maxlength' => true]) ?>
<!-- 
    <?= $form->field($model, 'data_json')->textInput() ?> -->

    

    <div class="form-group mt-4 d-flex justify-content-center">
        <?= AppHelper::BtnSave(); ?>
    </div>
    <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

