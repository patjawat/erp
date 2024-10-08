<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-in-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex justify-content-between gap-3">

    <?= $form->field($model, 'q')->label(false) ?>
    <?=$form->field($model, 'asset_type_name')->widget(Select2::classname(), [
        'data' => ArrayHelper::map($model->ListOrderType(),'id','name'),
        'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
        'pluginOptions' => [
            'width' => '200px',
            'allowClear' => true,
        ],
        'pluginEvents' => [
            'select2:select' => "function(result) { 
                $(this).submit()
                }",
                'select2:unselecting' => "function(result) { 
                    $(this).submit()
                    }",
                    
                    ]
                    ])->label(false);
                    ?>
</div>
    <?php ActiveForm::end(); ?>
    
</div>
