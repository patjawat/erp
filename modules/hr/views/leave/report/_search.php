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

<?php $form = ActiveForm::begin([
    'action' => ['report'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>



<div class="d-flex gap-2 align-items-center">
    <?php echo $form->field($model, 'data_json[export]')->hiddenInput()->label(false) ?>

    <?=$this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>

    <button class="btn btn-light mt-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
        aria-controls="offcanvasExample"><i class="fa-solid fa-filter"></i></button>
</div>

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