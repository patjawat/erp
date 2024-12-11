<?php

use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.offcanvas-footer {
    padding: 1rem 1rem;
    border-top: 1px solid #dee2e6;
}
</style>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex gap-2">
    <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => 'function() {
                            $(this).submit()
                        }',
            ]
        ])->label('ปีงบประมาณ');
        ?>
    <?php
        echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
            'data' => $model->listEmployees(),
            'options' => ['placeholder' => 'เลือกบุคลากร ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '230px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                $(this).submit()
                }',
                'select2:unselecting' => 'function() {
                    $(this).submit()
                }',
            ]
        ])->label('บุคลากร');
        ?>

    <div class="d-flex flex-row mb-3 mt-4">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>



    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body position-relative">

            <div class="d-flex justify-content-between">
                <?php
                    echo $form->field($model, 'date_start')->widget(Datetimepicker::className(), [
                        'options' => [
                            'timepicker' => false,
                            'datepicker' => true,
                            'mask' => '99/99/9999',
                            'lang' => 'th',
                            'yearOffset' => 543,
                            'format' => 'd/m/Y',
                        ],
                    ])->label('ตั้งแต่วันที่');
                    ?>
                <?php
                    echo $form->field($model, 'date_end')->widget(Datetimepicker::className(), [
                        'options' => [
                            'timepicker' => false,
                            'datepicker' => true,
                            'mask' => '99/99/9999',
                            'lang' => 'th',
                            'yearOffset' => 543,
                            'format' => 'd/m/Y',
                        ],
                    ])->label('ถึงวันที่');
                    ?>
            </div>


            <?php echo $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
                    'name' => 'department',
                    'id' => 'treeID',
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'value' => 1,
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => false,
                    'options' => ['disabled' => false, 'allowClear' => true, 'class' => 'close'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>


            <div class="d-flex flex-row gap-4">
                <?php echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>

                <?php echo $form->field($model, 'leave_type_id')->checkboxList($model->listLeaveType(),
                        ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
            </div>

            <?php echo $form->field($model, 'status')->hiddenInput()->label(false) ?>


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


    <div class="mb-3 mt-4">
        <!-- Trigger button -->
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
            aria-controls="offcanvasExample">
            <i class="fa-solid fa-filter"></i> เพิ่มเติม
        </button>


    </div>

</div>

<?php ActiveForm::end(); ?>