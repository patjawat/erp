<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/warehouse/order-request'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex justify-content-between gap-3">

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุสิ่งที่ต้องการค้นหา..'])->label(false) ?>
    <?php
                                echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
                                    'data' => $model->listFormWarehouse(),
                                    'options' => ['placeholder' => 'เลือกคลัง ...'],
                                    'pluginOptions' => [
                                        'width' => '400px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                                'select2:unselect' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                    ]
                                ])->label(false);
                        ?>
                         <?php
                                echo $form->field($model, 'order_status')->widget(Select2::classname(), [
                                    'data' => $model->listStatus(),
                                    'options' => ['placeholder' => 'เลือกสถานะ ...'],
                                    'pluginOptions' => [
                                        'width' => '200px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { $(this).submit() }",
                                        'select2:unselect' => "function(result) { $(this).submit() }",
                                    ]
                                ])->label(false);
                        ?>


<div class="form-group">
        <!-- Trigger button -->
        <!-- <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
            aria-controls="offcanvasExample">
            <i class="fa-solid fa-filter"></i> เพิ่มเติม
        </button> -->
        
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>
</div>


 <!-- Offcanvas -->
 <div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body position-relative">

            <div class="d-flex flex-row gap-4">
                <?php // echo $form->field($model, 'order_status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline'])->label('สถานะ'); ?>
            </div>

            <div class="offcanvas-footer">
                <?php echo Html::submitButton(
                        '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                        [
                            'class' => 'btn btn-primary',
                            'data-bs-backdrop' => 'static',
                            'tabindex' => '-1',
                            'id' => 'offcanvasExample',
                            'aria-labelledby' => 'offcanvasExampleLabel',
                        ]
                    ); ?>
            </div>

        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

