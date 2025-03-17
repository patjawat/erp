<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['/me/stock-event/reuqest-order'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex justify-content-between gap-3">

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุสิ่งที่ต้องการค้นหา..'])->label('ค้นหา') ?>
    <?php
                                echo $form->field($model, 'order_status')->widget(Select2::classname(), [
                                    'data' => ['pending' => 'รอดำเนินการ','success' => 'สำเร็จ'],
                                    'options' => ['placeholder' => 'เลือกสถานะ ...'],
                                    'pluginOptions' => [
                                        'width' => '200px',
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
                                ])->label('สถานะ');
                        ?>
                         <?php
                                // echo $form->field($model, 'data_json[department]')->widget(Select2::classname(), [
                                //     'data' => ['pending' => 'รอดำเนินการ','success' => 'สำเร็จ'],
                                //     'options' => ['placeholder' => 'เลือกสถานะ ...'],
                                //     'pluginOptions' => [
                                //         'width' => '200px',
                                //     'allowClear' => true,
                                //     ],
                                //     'pluginEvents' => [
                                //         'select2:select' => "function(result) { 
                                //                   $(this).submit()
                                //                 }",
                                //     ]
                                // ])->label('สถานะตรวจสอบ');
                        ?>
    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary mt-1']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

