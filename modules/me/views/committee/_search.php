<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-type-search mt-3">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
   <div class="d-flex flex-row gap-3">

        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำที่ต้องการค้นหา...'])->label('ค้นหา') ?>
        <?php
echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
    'data' =>$model->ListThaiYear(),
    'options' => ['placeholder' => 'ปีงบประมาณ'],
    'pluginOptions' => [
        'allowClear' => true,
        'width' => '100px',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
            $(this).submit()
            }",
            "select2:unselecting" => "function() {
                $(this).submit()
                }",
                ]
                ])->label('ปีงบประมาณ');
                ?>
</div>
   
   <?php ActiveForm::end(); ?>

</div>
