<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\HolidaySearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex gap-3">
        <?= $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหาชื่อ'])->label(false) ?>
        <?php // $form->field($model, 'thai_year')->textInput(['placeholder' => 'ค้นหาปีงบประมาน'])->label(false) ?>
        
        <div class="form-group">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
            <?=html::a('<i class="fa-solid fa-rotate-right"></i>',['/lm/holiday'], ['class' => 'btn btn-warning'])?>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

