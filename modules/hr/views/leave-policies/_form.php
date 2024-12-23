<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePolicies $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-policies-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'additional_rules')->textInput(['maxlength' => true])->label('คำอธิบาย') ?>


    <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
