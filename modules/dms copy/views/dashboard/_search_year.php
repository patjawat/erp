<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-type-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<?php
echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
    'data' => $model->ListThaiYear(),
    // 'data' => ['2567' => '2567','2568' => '2568'],
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
    <?php ActiveForm::end(); ?>

</div>
