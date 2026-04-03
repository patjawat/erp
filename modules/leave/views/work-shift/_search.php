<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>



<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'id' => 'employees-filter',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-2 mr-2 me-2']] // spacing form field groups
]); ?>


<div class="row">

    <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12 col-sx-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหาบุคลากร...'])->label(false) ?>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-sx-12">
        <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
            'data' => $model->ListPositionType(),
            'options' => ['placeholder' => 'ประเภททั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-sx-12">
        <?php
        echo $form->field($model, 'position_name')->widget(DepDrop::classname(), [
            'options' => [
                'placeholder' => 'ตำแหน่งทั้งหมด ...',
            ],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => ['pluginOptions' => ['allowClear' => true]],
            'pluginOptions' => [
                'depends' => ['employeessearch-position_type'],
                'url' => Url::to(['/hr/employees/get-position-name']),
                'loadingText' => 'กำลังโหลด ...',
                'params' => ['depdrop_all_params' => 'employeessearch-position_type'],
                'initDepends' => ['employeessearch-position_type'],
                'initialize' => true,
            ],

        ])->label(false); ?>
    </div>


    <div class="col-xl-1 col-lg-1 col-md-1 col-sm-12 col-sx-12">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-filter"></i>
        </button>

    </div>
</div>
<div class="row mt-2">
    <div class="col-5">
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

    
    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-sx-12">
        <?= $form->field($model, 'work_shift')->widget(Select2::classname(), [
            'data' => ['normal' => 'ปกติ', 'shift' => 'เวร 8 ชั่วโมง'],
            'options' => ['placeholder' => 'ประเภทของเวรทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>

</div>

<div class="collapse mt-3" id="collapseFilter">

</div>


<?php ActiveForm::end(); ?>

<?php
$js = <<< JS


// $('#show').val(localStorage.getItem('right-setting'))
// console.log(localStorage.getItem('right-setting'));
// $("#filter-emp").addClass(localStorage.getItem('right-setting'));

// $(".filter-emp").on("click", function(){
//   $("#filter-emp").addClass("show");
//   localStorage.setItem('right-setting','show')
// })

// $(".filter-emp-close").on("click", function(){
//     $(".right-setting").removeClass("show");
//     localStorage.setItem('right-setting','hide')
// })

// const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
// const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);

?>