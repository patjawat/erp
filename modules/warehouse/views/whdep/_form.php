<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whdep $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card" style="color: #000000;">
    <div class="card-body"> 
        <div class="whdep-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>

    <?= $form->field($model, 'depsup_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'depsup_detail')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'depsup_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'depsup_store')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'depsup_unit')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'data_json')->textInput() ?> -->

 

    <div class="form-group mt-4 d-flex justify-content-center">
                <?= AppHelper::BtnSave(); ?>
    </div>

    <?php ActiveForm::end(); ?>

    </div>
        </div>
    </div>
</div>
