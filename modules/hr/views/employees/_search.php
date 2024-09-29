<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\widgets\Select2;
use app\components\AppHelper;
use yii\widgets\Pjax;
use app\modules\hr\models\Organization;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<style>
.field-employeessearch-q {
    margin-bottom: 0px !important;
}
.right-setting {
    width: 500px !important;
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


<div class="d-flex gap-3">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
    <span class="filter-emp btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
        <i class="fa-solid fa-filter"></i>
    </span>
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
            <?php //  $form->field($model, 'show')->textInput(['placeholder' => 'ค้นหา...','id' => 'show'])->label(false) ?>
            <?= $form->field($model, 'gender')->widget(Select2::classname(), [
                'data' => ['ชาย'=> 'ชาย','หญิง' => 'หญิง'],
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
            ])->label('เพศ') ?>

            <?= $form->field($model, 'position_name')->widget(Select2::classname(), [
                'data' => $model->ListPositionName(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                ])->label('ตำแหน่ง') ?>

            <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
                    'data' => $model->ListPositionType(),
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('ประเภท') ?>


<?php $form->field($model, 'department')->widget(Select2::classname(), [
                    'data' => $model->ListDepartment(),
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('หน่วยงาน') ?>
                

                <?=$form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'id' => 'treeID',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'value' => 1,
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => ['disabled' => false, 'allowClear' => true,'class' => 'close'],
    'pluginOptions' => [
        'allowClear' => true
    ],
])->label('หน่วยงานภายในตามโครงสร้าง');?>



            <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => $model->ListStatus(),
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
            ])->label('สถานะ') ?>

<div class="row">
<div class="col-6">

<?= $form->field($model, 'range1')->textInput(['type' => 'number'])->label('อายุ') ?>
</div>
<div class="col-6">

<?= $form->field($model, 'range2')->textInput(['type' => 'number'])->label('จนถึงอายุ') ?>
</div>
<div>
<?= $form->field($model, 'user_register')->widget(Select2::classname(), [
                'data' => [1=>'ลงทะเบียนสำเร็จ', 0=>'ยังไม่ลงทะเบียน'],
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
            ])->label('สถานนะการลงทะเบียน') ?>
</div>
</div>

<?= $form->field($model, 'all_status')->checkBox()->label('แสดงสถานะทั้งหมด') ?>

            <?=AppHelper::BtnSave('ค้นหา')?>
            <?php // Html::a('ทั้งหมด',['/hr/employees','all-status' => true],['class' => 'btn btn-warning']);?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS


// $("#w0-tree-input").treeview("expandAll");
// $("#treeID").treeview("collapseAll");
$("#w0-tree-input").treeview("uncheckAll");
$("#w0-tree").treeview("uncheckAll");
$("#w0-tree-input-menu").treeview("uncheckAll");

 
$("#treeID").on('treeview:selected', function(event, key, data, textStatus, jqXHR) {
    console.log('treeview:selected');
});


$("#treeID").on('treeview:beforeselect', function(event, key, jqXHR, settings) {
    console.log('treeview:beforeselect');
});

$("#treeID").on('treeview:selecterror', function(event, key, data, textStatus, jqXHR) {
    console.log('treeview:selecterror');
});

$("#treeID").on('treeview:selectajaxerror', function(event, key, jqXHR, textStatus, errorThrown) {
    console.log('treeview:selectajaxerror');
});

$("#treeID").on('treeview:selectcomplete', function(event, jqXHR) {
    console.log('treeview:selectcomplete');
});

$("#treeID").on('treeview:expand', function(event, nodeKey) {
    console.log('treeview:expand');
});

$("#treeID").on('treeview:collapse', function(event, key) {
    console.log('treeview:collapse');
});

$("#treeID").on('treeview:expandall', function(event) {
    console.log('treeview:expandall');
});

$("#treeID").on('treeview:collapseall', function(event) {
    console.log('treeview:collapseall');
});

$("#treeID").on('treeview:search', function(event) {
    console.log('treeview:search');
});

$("#treeID").on('treeview:checked', function(event, key) {
    console.log('treeview:checked');
    $("#treeID").treeview("uncheckAll");
});

$("#treeID").on('treeview:unchecked', function(event, key) {
    console.log('treeview:unchecked');
});

$("#treeID").on('treeview:change', function(event, key, name) {
    console.log('treeview:change');
});


$('#show').val(localStorage.getItem('right-setting'))
console.log(localStorage.getItem('right-setting'));
$("#filter-emp").addClass(localStorage.getItem('right-setting'));

$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

$(".filter-emp-close").on("click", function(){
    $(".right-setting").removeClass("show");
    localStorage.setItem('right-setting','hide')
})

// const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
// const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);
      
      ?>