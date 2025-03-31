<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
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

:not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
}

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #e5e5e5;
    color: #000;
}
</style>
<?php $form = ActiveForm::begin([
    'action' => ['report'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex align-items-center gap-2">

    <?php //echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
    <?php echo $form->field($model, 'data_json[export]')->hiddenInput()->label(false) ?>

    <?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->emp_id])->one();
            $initEmployee = empty($model->emp_id) ? '' : Employees::findOne($model->emp_id)->getAvatar(false);//กำหนดค่าเริ่มต้น

            echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                'size' => Select2::LARGE,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    'width' => '300px',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                            'url'=>$url,
                            'dataType'=>'json',//รูปแบบการอ่านคือ json
                            'data'=>new JsExpression('function(params) { return {q:params.term};}')
                        ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                    ],
                'pluginEvents' => [
                    'select2:select' => new JsExpression('function() { $(this).closest("form").submit(); }'),
                    'select2:unselecting' => new JsExpression('function() { $(this).closest("form").submit(); }'),
                ]
            ])->label('บุคลากร');

            ?>
    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'size' => Select2::LARGE,
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => "function() {
                             $(this).submit()
                            $('#leavesearch-date_start').val('__/__/____');
                            $('#leavesearch-date_end').val('__/__/____');
                        }",
            ]
        ])->label('ปีงบประมาณ');
        ?>

    <div class="d-flex justify-content-between gap-2">
        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่','class' => 'form-control form-control-lg']); ?>
        <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่','class' => 'form-control form-control-lg']); ?>
    </div>
    <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>

    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
        aria-controls="offcanvasExample">
        <i class="fa-solid fa-filter"></i> เพิ่มเติม
    </button>




    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body position-relative">
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

</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS
   thaiDatepicker('#leavesearch-date_start,#leavesearch-date_end')

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