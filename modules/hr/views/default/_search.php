<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\widgets\Select2;
use app\components\AppHelper;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<style>
.field-employeessearch-fullname {
    margin-bottom: 0px !important;
}
</style>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'id' => 'employees-filter',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div class="mt-4 mt-md-0 float-md-end form-inline">

    <div class="search-box me-2">
        <div class="d-flex gap-3">
            <?= $form->field($model, 'fullname')->textInput(['placeholder' => 'ปีงบประมาณ...'])->label(false) ?>
            <span class="filter-emp"  data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-custom-class="custom-tooltip"
        data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
                <i class="bi bi-filter-circle-fill text-light fs-4"></i>
            </span>
        </div>
    </div>
</div>


<div class="right-setting <?=$model->show?>" id="filter-emp">
    <div class="card mb-0 w-100">
        <div class="card-header">
            <h5 class="card-title d-flex justify-content-between">
                ค้นหาข้อมูล
                <a href="javascript:void(0)"><i class="bi bi-x-circle filter-emp-close"></i></a>
            </h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'show')->hiddenInput(['placeholder' => 'ค้นหา...','id' => 'show'])->label(false) ?>
            <?= $form->field($model, 'position_name')->widget(Select2::classname(), [
                'data' => $model->ListDepartment(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                ])->label('แผนก') ?>

            <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
                    'data' => $model->ListPositionType(),
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('ประเภท') ?>

            <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => $model->ListStatus(),
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
            ])->label('สถานะ') ?>
            <button id="btn-filter" type="submit" class="btn btn-primary"><i class="bi bi-search"></i>
                ค้นหา</button>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  $('#show').val('show')
})
  
$(".filter-emp-close").on("click", function(){
  $(".right-setting").removeClass("show");
  $('#show').val('')
  $('#employees-filter').submit();
})

$('#btn-filter').click(function (e) { 
    e.preventDefault();
    $('#employees-filter').submit();
    
});

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);
      
      ?>