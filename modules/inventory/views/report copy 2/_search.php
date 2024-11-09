<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
$months = [
    10 => "ตุลาคม",
    11 => "พฤศจิกายน",
    12 => "ธันวาคม",
    1 => "มกราคม",
    2 => "กุมภาพันธ์",
    3 => "มีนาคม",
    4 => "เมษายน",
    5 => "พฤษภาคม",
    6 => "มิถุนายน",
    7 => "กรกฎาคม",
    8 => "สิงหาคม",
    9 => "กันยายน"
];
?>

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/report/index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex gap-3">

    <?php

echo $form->field($model, 'receive_month')->widget(Select2::classname(), [
    // 'data' => $model->ListGroupYear(),
    'data' => $months,
    'options' => ['placeholder' => 'ปีงบประมาณ'],
    'pluginOptions' => [
        'allowClear' => true,
        'width' => '200px',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
            $(this).submit()
            }",
            "select2:unselecting" => "function() {
                $(this).submit()
                }",
                ]
                ])->label(false);
                ?>


<?php
echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
    // 'data' => $model->ListGroupYear(),
    'data' => [2567 => '2567',2568 => '2568'],
    'options' => ['placeholder' => 'ปีงบประมาณ'],
    'pluginOptions' => [
        'allowClear' => true,
        'width' => '200px',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
            $(this).submit()
            }",
            "select2:unselecting" => "function() {
                $(this).submit()
                }",
                ]
                ])->label(false);
                ?>
</div>
    <?php ActiveForm::end(); ?>
