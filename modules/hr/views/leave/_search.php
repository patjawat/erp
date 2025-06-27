<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;

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
    'id' => 'search-leave',
    'options' => [
        'data-pjax' => 1
    ],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="d-flex justify-content-between align-items-center gap-2">

    <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label(false) ?>
            <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
        ])->label(false);
        ?>



            <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '120px',
                    ],
        ])->label(false);?>

    <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>

    <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>


        <?=$form->field($model, 'status')->widget(Select2::classname(), [
                'data' => $model->listStatus(),
                    'options' => ['placeholder' => 'สถานะทั้งหทด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '150px',
                    ],
                ])->label(false);?>

     <?=$form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                'data' => $model->listLeaveType(),
                    'options' => ['placeholder' => 'ประเภททั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '150px',
                    ],
                ])->label(false);?>




    <div class="d-flex flex-row align-items-center gap-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
            aria-controls="offcanvasRight" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม..."><i
                class="fa-solid fa-filter"></i></button>
    </div>


</div>
<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
       
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
                ])->label('หน่วยงานตามโครงสร้าง'); ?>
        <div class="offcanvas-footer">
            <?php echo Html::submitButton(
                        '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                        [
                            'class' => 'btn btn-light',
                            // 'data-bs-backdrop' => 'static',
                            'tabindex' => '-1',
                            'id' => 'offcanvasExample',
                            'aria-labelledby' => 'offcanvasExampleLabel',
                        ]
                    ); ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#leavesearch-date_start,#leavesearch-date_end')
    $("#leavesearch-date_start").on('change', function() {
            $('#leavesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#leavesearch-date_end").on('change', function() {
            $('#leavesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });

JS;
$this->registerJS($js, View::POS_END);

?>