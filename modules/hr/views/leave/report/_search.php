<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => ['report'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>



<div class="d-flex gap-2 align-items-center">
    <div class="d-flex justify-content-between align-items-center gap-2">
        <?php echo $form->field($model, 'data_json[export]')->hiddenInput()->label(false) ?>


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

        <div style="width:300px">
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
        ])->label(false); ?>
        </div>



        <div class="d-flex flex-row align-items-center gap-2 mb-3">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
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