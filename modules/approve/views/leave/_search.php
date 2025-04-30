<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
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
<div class="d-flex justify-content-between align-items-center gap-2">
<?=$this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>
</div>
    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <div class="d-flex flex-row gap-4">
                <?php echo $form->field($model, 'status')->checkboxList(['Pending' => 'รออนุมัติ', 'Pass' => 'อนุมัติ'], ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
                <?php echo $form->field($model, 'leave_type_id')->checkboxList($model->listLeaveType(),['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
            </div>

            <div class="offcanvas-footer">
                <?php echo Html::submitButton(
                        '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                        [
                            'class' => 'btn btn-light',
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

    thaiDatepicker('#approvesearch-date_start,#approvesearch-date_end')
    $("#approvesearch-date_start").on('change', function() {
            $('#leavesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#approvesearch-date_end").on('change', function() {
            $('#approvesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


    JS;
$this->registerJS($js, View::POS_END);

?>