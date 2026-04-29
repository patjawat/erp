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


    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-sx-12">
        <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
            'data' => $model->ListPositionType(),
            'options' => ['placeholder' => 'ประเภททั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-sx-12">
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
    <div class="col-xl-4 col-lg-4 col-md-3 col-sm-12 col-sx-12 d-flex align-items-center align-self-center">
        <?= $form->field($model, 'all_status')->checkBox()->label('แสดงสถานะทั้งหมด') ?>
    </div>

    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-sx-12">
        <?= $form->field($model, 'gender')->widget(Select2::classname(), [
            'data' => ['ชาย' => 'ชาย', 'หญิง' => 'หญิง'],
            'options' => ['placeholder' => 'เพศทั้งหมด...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-sx-12">
        <?= $form->field($model, 'work_shift')->widget(Select2::classname(), [
            'data' => ['normal' => 'ปกติ', 'shift' => 'เวร 8 ชั่วโมง'],
            'options' => ['placeholder' => 'ประเภทของเวรทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12">

        <?= $form->field($model, 'range1')->textInput(['type' => 'number', 'placeholder' => 'ช่วงอายุเริ่มตั้น'])->label(false) ?>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12">
        <?= $form->field($model, 'range2')->textInput(['type' => 'number', 'placeholder' => 'จนถึงอายุ'])->label(false) ?>
    </div>


    <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12">
        <?= $form->field($model, 'user_register')->widget(Select2::classname(), [
            'data' => [1 => 'ลงทะเบียนสำเร็จ', 0 => 'ยังไม่ลงทะเบียน'],
            'options' => ['placeholder' => 'สถานะการลงทะเบียนทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>
    </div>

    <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12">
        <?= $form->field($model, 'branch')->widget(Select2::classname(), [
            'data' => [
                'MAIN' => 'โรงพยาบาล',
                'BRANCH' => 'รพ.สต.',
            ],
            'options' => ['placeholder' => 'สาขาทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>

    </div>
    <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12 col-sx-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหาบุคลากร...'])->label(false) ?>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-sx-12">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->ListStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>

    </div>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-sx-12">
        <?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
            'name' => 'department',
            'id' => 'treeID',
            'query' => Organization::find()->addOrderBy('root, lft'),
            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
            'fontAwesome' => true,
            'asDropdown' => true,
            'multiple' => false,

            'options' => [
                'placeholder' => 'เลือกหน่วยงาน...',
            ],

            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false); ?>
    </div>



    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="d-flex flex-column flex-md-row gap-2">

            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">ค้นหา</span>', [
                'class' => 'btn btn-primary w-100 w-md-auto',
                'id' => 'summit'
            ]) ?>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> <span class="d-none d-sm-inline">สร้างใหม่</span>', ['/hr/employees/create'], ['class' => 'btn btn-light open-modal w-100 w-md-auto open-modal', 'data' => ['size' => 'modal-xl']]) ?>

            <div class="dropdown w-100 w-md-auto">
                <button class="btn btn-success dropdown-toggle w-100 w-md-auto" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="d-none d-sm-inline">Excel</span>
                </button>

                <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton1">
                    <li>
                        <a href="#" id="download-button" class="dropdown-item">
                            <i class="fa-solid fa-file-export me-1"></i>ส่งออก</a>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV',
                            ['/hr/employees/import-csv', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'],
                            ['class' => 'dropdown-item open-modal']
                        ) ?>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า',
                            'https://docs.google.com/spreadsheets/d/1ZlqklxVlRZqxFNRrBK74jHHh8y5tgKGrWgXjHY7h8gw/edit#gid=0',
                            ['class' => 'dropdown-item', 'target' => '_blank']
                        ) ?>
                    </li>
                    </a>
                </ul>
            </div>

        </div>
    </div>
</div>

<div class="collapse mt-3" id="collapseFilter">

    <?= $form->field($model, 'show')->hiddenInput(['placeholder' => 'ค้นหา...', 'id' => 'show'])->label(false) ?>


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