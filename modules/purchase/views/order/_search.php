<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex justify-content-between gap-3 align-items-center align-middle">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>

    <?=$form->field($model, 'order_type_name')->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map($model->ListItemTypeOrder(),'id','name'),
                                    'options' => ['placeholder' => 'เลือกประเภท'],
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
    <?=$form->field($model, 'status')->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map($model->ListStatus(),'code','title'),
                                    'options' => ['placeholder' => 'เลือกสถานะ'],
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

    <?php //  Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
    <?php // Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
</div>
<?php ActiveForm::end(); ?>