<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
    <?php $form = ActiveForm::begin([
        'action' => ['list-fsn'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    
<div class="d-flex justify-content-between align-items-center gap-2">

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหาชื่อ,ชื่อทรัพย์สิน...'])->label(false) ?>


<div class="form-group">
    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
</div>

</div>
    <?php ActiveForm::end(); ?>

