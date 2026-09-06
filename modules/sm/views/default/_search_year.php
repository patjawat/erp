<?php

use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\OrderSearch $model */
?>

<div class="sm-year-filter d-flex align-items-center gap-2">
    <span class="text-body-secondary small text-nowrap">
        <i class="bi bi-calendar3 me-1"></i>ปีงบประมาณ
    </span>
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => ['data-pjax' => 1, 'class' => 'mb-0'],
        'fieldConfig' => ['options' => ['class' => 'mb-0'], 'template' => '{input}'],
    ]); ?>
    <?php
    echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
        'data' => $model->ListGroupYear(),
        'options' => ['placeholder' => 'เลือกปีงบ'],
        'pluginOptions' => [
            'allowClear' => true,
            'width' => '150px',
        ],
        'pluginEvents' => [
            'select2:select' => "function() { \$(this).closest('form').submit(); }",
            'select2:unselecting' => "function() { \$(this).closest('form').submit(); }",
        ],
    ])->label(false);
    ?>
    <?php ActiveForm::end(); ?>
</div>
