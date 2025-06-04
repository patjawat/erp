<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex justify-content-between gap-3">

    <?= $form->field($model, 'warehouse_name')->textInput(['placeholder' => 'ระบุชื่อคลังที่ต้องการค้นหา...'])->label('ชื่อคลัง') ?>
    <?php
                                echo $form->field($model, 'warehouse_type')->widget(Select2::classname(), [
                                    'data' => ['MAIN' => 'คลังหลัก','SUB' => 'คลังย่อย','BRANCH' => 'รพ.สต.'],
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                        'width' => '200px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                    ]
                                ])->label('ประเภทคลัง');
                        ?>
    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary mt-1']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

