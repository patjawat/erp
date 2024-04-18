<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whcheck $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card" style="color: #000000;">
    <div class="card-body"> 
        <div class="whcheck-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>

    <?= $form->field($model, 'check_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_date')->textInput() ?>

    <?= $form->field($model, 'check_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_store')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_from')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_hr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_status')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'data_json')->textInput() ?> -->


    <div class="form-group mt-4 d-flex justify-content-center">
        <?= AppHelper::BtnSave(); ?>
     </div>

    <?php ActiveForm::end(); ?>

    </div>
        </div>
    </div>
</div>
