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
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="fa-solid fa-magnifying-glass"></i>
                ค้นหาเพิ่มเติม...</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            ...
        </div>
    </div>

    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
        aria-controls="offcanvasRight"><i class="fa-solid fa-filter"></i></button>

</div>

    <?php ActiveForm::end(); ?>
    
</div>
