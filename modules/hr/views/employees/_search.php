<?php

use app\modules\hr\models\Organization;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */

$hasAdvancedFilters = !empty($model->q_department)
    || !empty($model->gender)
    || !empty($model->range1)
    || !empty($model->range2)
    || !empty($model->work_shift)
    || !empty($model->status)
    || ($model->hasAttribute('employee_type_id') && !empty($model->employee_type_id))
    || ($model->hasAttribute('employee_position_id') && !empty($model->employee_position_id))
    || !empty($model->position_type)
    || !empty($model->position_name);

?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'id' => 'employees-filter',
            'options' => [
                'data-pjax' => 1
            ],
            'fieldConfig' => ['options' => ['class' => 'form-group mb-0']]
        ]); ?>

        <!-- 1. แถวค้นหาหลัก -->
        <div class="row g-3 align-items-end">
            <div class="col-lg-10 col-md-12">
                <label class="form-label small fw-semibold text-primary mb-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>ค้นหาบุคลากร
                </label>
                <?= $form->field($model, 'q')->textInput(['placeholder' => 'พิมพ์ชื่อ-นามสกุล หรือรหัสบุคลากร...'])->label(false) ?>
            </div>

            <div class="col-lg-2 col-md-12">
                <div class="d-flex flex-column flex-md-row gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">ค้นหา</span>', [
                        'class' => 'btn btn-primary w-md-auto',
                        'id' => 'summit'
                    ]) ?>
                    <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                        aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>" aria-controls="collapseFilter" id="btnToggleFilter">
                        <i class="fa-solid fa-sliders me-1"></i> ตัวกรองเพิ่มเติม
                    </button>
                </div>
            </div>
        </div>

        <!-- ตัวกรองเพิ่มเติม: แสดงเมื่อกด "ตัวกรองเพิ่มเติม" (หรือเปิดอัตโนมัติถ้ามีค่ากรองอยู่) -->
        <div class="collapse mt-3 <?= $hasAdvancedFilters ? 'show' : '' ?>" id="collapseFilter">

            <!-- 2. กลุ่มข้อมูลงาน -->
            <div class="border-top pt-3 mt-2">
                <div class="row g-3 mb-3">
                    <?php if ($model->hasAttribute('employee_type_id')): ?>
                        <div class="col-lg-4 col-md-6">
                            <?= $form->field($model, 'employee_type_id')->widget(Select2::classname(), [
                                'data' => $model->ListEmployeeType(),
                                'options' => ['placeholder' => '--ประเภทพนักงาน (ใหม่)--'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label('ประเภทพนักงาน (ใหม่)') ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->hasAttribute('employee_position_id')): ?>
                        <div class="col-lg-4 col-md-6">
                            <?= $form->field($model, 'employee_position_id')->widget(Select2::classname(), [
                                'data' => $model->ListEmployeePosition(),
                                'options' => ['placeholder' => '--ชื่อตำแหน่ง (ใหม่)--'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label('ชื่อตำแหน่ง (ใหม่)') ?>
                        </div>
                    <?php endif; ?>
                   <div class="col-lg-4 col-md-6">
                        <?= $form->field($model, 'gender')->widget(Select2::classname(), [
                            'data' => ['ชาย' => 'ชาย', 'หญิง' => 'หญิง'],
                            'options' => ['placeholder' => '---เพศทั้งหมด---'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label('เพศ') ?>
                    </div>

                </div>

                <div class="row g-3">
                    <div class="col-lg-2 col-md-6">
                        <?= $form->field($model, 'range1')->textInput(['type' => 'number', 'placeholder' => 'ช่วงอายุเริ่มตั้น'])->label('ช่วงอายุเริ่มตั้น') ?>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <?= $form->field($model, 'range2')->textInput(['type' => 'number', 'placeholder' => 'จนถึงอายุ'])->label('จนถึงอายุ') ?>
                    </div>
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-end align-items-center gap-2">
                            <div class="flex-grow-1">
                                <?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
                                    'name' => 'department',
                                    'query' => Organization::find()->addOrderBy('root, lft'),
                                    'value' => !empty($model->q_department) ? $model->q_department : null,
                                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                                    'fontAwesome' => true,
                                    'asDropdown' => true,
                                    'multiple' => false,
                                    'options' => ['disabled' => false],
                                    'dropdownConfig' => [
                                        'input' => [
                                            'placeholder' => '---เลือกหน่วยงาน---',
                                        ],
                                    ],
                                    'pluginEvents' => [
                                        'treeview:change' => new JsExpression('function() {
                                    var $container = $(this).closest(".kv-tree-dropdown-container");
                                    setTimeout(function() {
                                        var $toggle = $container.find(".kv-tree-input");
                                        $toggle.removeClass("show open").attr("aria-expanded", "false");
                                        $container.find(".kv-tree-dropdown").removeClass("show open");
                                        if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
                                            try {
                                                var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                                                if (instance) {
                                                    instance.hide();
                                                }
                                            } catch (e) {}
                                        }
                                    }, 0);
                                }'),
                                    ],
                                ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>
                            </div>
                            <button type="button" class="btn btn-outline-secondary flex-shrink-0 mt-3" id="clear-q-department">
                                <i class="fa-solid fa-eraser me-1"></i> ล้าง
                            </button>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 4. กลุ่มการปฏิบัติงาน/สังกัด -->
            <div class="border-top pt-3 mt-3">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <?= $form->field($model, 'work_shift')->widget(Select2::classname(), [
                            'data' => ['normal' => 'ปกติ', 'shift' => 'เวร 8 ชั่วโมง'],
                            'options' => ['placeholder' => '---ประเภทของเวรทั้งหมด---'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label('การปฏิบัติงาน') ?>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <?= $form->field($model, 'branch')->widget(Select2::classname(), [
                            'data' => [
                                'MAIN' => 'โรงพยาบาล',
                                'BRANCH' => 'รพ.สต.',
                            ],
                            'options' => ['placeholder' => '---สาขาทั้งหมด---'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label('สาขา') ?>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <?= $form->field($model, 'status')->widget(Select2::classname(), [
                            'data' => $model->ListStatus(),
                            'options' => ['placeholder' => '---สถานะทั้งหมด---'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label('สถานะ') ?>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <?= $form->field($model, 'user_register')->widget(Select2::classname(), [
                            'data' => [1 => 'ลงทะเบียนสำเร็จ', 0 => 'ยังไม่ลงทะเบียน'],
                            'options' => ['placeholder' => '---สถานะการลงทะเบียนทั้งหมด---'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label('สถานะการลงทะเบียน') ?>
                    </div>
                </div>
            </div>

            <!-- 5. กลุ่มตัวเลือกเพิ่มเติม -->
            <div class="border-top pt-3 mt-3">
                <h6 class="small fw-semibold text-primary mb-2">
                    <i class="fa-solid fa-gear me-1"></i>สถานะระบบ
                </h6>
                <div class="bg-light rounded-3 p-3">
                    <?= $form->field($model, 'all_status')->checkBox()->label('แสดงสถานะทั้งหมด') ?>
                </div>
            </div>

        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
$js = <<< JS

    $('body').on('click', '#clear-department', function () {
        var container = $('#treeID').closest('.form-group');
        // เคลียร์ค่า hidden ที่ส่งไป filter และข้อความที่แสดงใน dropdown
        container.find('input').val('');
        container.find('.kv-selected-text, .kv-tree-input').text('');
        $('#treeID').trigger('change');
    });

JS;
$this->registerJS($js);

?>
