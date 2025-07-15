<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

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

.field-employeessearch-position_name {
    width: 300px !important;
}

</style>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'id' => 'employees-filter',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>


<div class="d-flex gap-2  align-items-center">

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหาบุคลากร...'])->label(false) ?>

    <div style="width:180px">
      <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
                    'data' => $model->ListPositionType(),
                    'options' => ['placeholder' => 'ประเภททั้งหมด ...'],
                    'pluginOptions' => [
                         'width' => '100%',
                        // 'dropdownParent' => '#offcanvasExample',
                        'allowClear' => true
                    ],
    //                   'pluginEvents' => [
    //     'select2:select' => 'function() { $(this).closest("form").submit(); }',
    //     'select2:clear' => 'function() { $(this).closest("form").submit(); }'
    // ]
                ])->label(false) ?>

</div>
    <?php
echo $form->field($model, 'position_name')->widget(DepDrop::classname(), [
    'options' => [
        'placeholder' => 'ตำแหน่งทั้งหมด ...',
    ],
    'type' => DepDrop::TYPE_SELECT2,
    'select2Options' => ['pluginOptions' => ['allowClear' => true]],
    'pluginOptions' => [
        'width' => '100%', // หรือใช้ค่าอื่น เช่น '300px', '50%'
        'depends' => ['employeessearch-position_type'],
        'url' => Url::to(['/hr/employees/get-position-name']),
        'loadingText' => 'กำลังโหลด ...',
        'params' => ['depdrop_all_params' => 'employeessearch-position_type'],
        'initDepends' => ['employeessearch-position_type'],
        'initialize' => true,
    ],
    //                   'pluginEvents' => [
    //     'select2:select' => 'function() { $(this).closest("form").submit(); }',
    //     'select2:clear' => 'function() { $(this).closest("form").submit(); }'
    // ]
        
        ])->label(false);?>
 <div style="width: 300px;">
<?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'id' => 'treeID',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'value' => null,  // ไม่ตั้งค่าเริ่มต้น
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => [
        'class' => 'close',
        'allowClear' => true,
    ],
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => 'เลือกหน่วยงาน...',
    ],
])->label(false); ?>

                </div>
                  <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
        <i class="fa-solid fa-filter"></i>
    </button>

</div>
    
        

<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
    aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body position-relative">
        
        <?= $form->field($model, 'show')->hiddenInput(['placeholder' => 'ค้นหา...','id' => 'show'])->label(false) ?>
            <?php //  $form->field($model, 'show')->textInput(['placeholder' => 'ค้นหา...','id' => 'show'])->label(false) ?>
            <?= $form->field($model, 'gender')->widget(Select2::classname(), [
                'data' => ['ชาย'=> 'ชาย','หญิง' => 'หญิง'],
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'dropdownParent' => '#offcanvasExample',
                        'allowClear' => true
                    ],
            ])->label('เพศ') ?>


            <?php $form->field($model, 'department')->widget(Select2::classname(), [
                    'data' => $model->ListDepartment(),
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'dropdownParent' => '#offcanvasExample',
                        'allowClear' => true
                    ],
                ])->label('หน่วยงาน') ?>

            <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => $model->ListStatus(),
                'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'dropdownParent' => '#offcanvasExample',
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
                        'dropdownParent' => '#offcanvasExample',
                        'allowClear' => true
                    ],
            ])->label('สถานนะการลงทะเบียน') ?>
                </div>
            </div>

            <?= $form->field($model, 'all_status')->checkBox()->label('แสดงสถานะทั้งหมด') ?>

            <?=AppHelper::BtnSave('ค้นหา')?>
            
    </div>
</div>


<?php ActiveForm::end(); ?>

<?php
$js = <<< JS



// $('#offcanvasExample').on('shown.bs.offcanvas', function () {
//     $('#model-position_name').select2({
//         dropdownParent: $('#offcanvasExample') // ให้ dropdown อยู่ภายใน offcanvas
//     });
// });

// $("#w0-tree-input").treeview("expandAll");
// $("#treeID").treeview("collapseAll");
// $("#w0-tree-input").treeview("uncheckAll");
// $("#w0-tree").treeview("uncheckAll");
// $("#w0-tree-input-menu").treeview("uncheckAll");

 
// $("#treeID").on('treeview:change', function(event, key, name) {
//                         $('body').find('.kv-tree-input').removeClass('show')
//                         $('body').find('.kv-tree-dropdown').removeClass('show')

// });


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