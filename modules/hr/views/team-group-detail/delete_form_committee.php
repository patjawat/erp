<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroupDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="team-group-detail-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'team_group_id')->hiddenInput()->label(false) ?>
    <div class="row">
         <div class="col-2"><?= $form->field($model, 'thai_year')->textInput()->label('พ.ศ.') ?></div>
         <div class="col-10"><?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อคำสั่ง') ?></div>
    </div>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
