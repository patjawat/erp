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
        'action' => ['dashboard'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row align-items-center">
                        <div class="col-12 col-md-3 mb-3 mb-md-0">
                            <h5 class="fw-semibold text-dark">ตัวกรองข้อมูล</h5>
                        </div>
                        <div class="col-12 col-md-9">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                   <?php
                                        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
                                            'data' => $model->groupYear(),
                                            'options' => ['placeholder' => 'ปีงบประมาณ'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                                'width' => '100%',
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
                                <div class="col-12 col-sm-4">
                                    <select id="departmentFilter" class="form-select">
                                        <option value="all">ทุกหน่วยงาน</option>
                                        <option value="dept1">สำนักงานใหญ่</option>
                                        <option value="dept2">สำนักงานภูมิภาค</option>
                                        <option value="dept3">ฝ่ายวิชาการ</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <button id="applyFilter" class="btn btn-primary w-100">
                                        กรองข้อมูล
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    

    <?php ActiveForm::end(); ?>

</div>
