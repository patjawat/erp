<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>


    <div class="d-flex align-items-center gap-2">
        <?=$this->render('@app/components/Search',['form' => $form,'model' => $model,'showEmp' => false])?>
        <?php echo Html::a('<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน',['/me/holidays','title' => '<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน'],['id' => 'calendar-me','class' => 'btn btn-primary open-modal mt-1','data' => ['size' => 'modal-xl']])?>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <div class="d-flex flex-row gap-4">

                <?php echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false]);?>

                <?php echo $form->field($model, 'leave_type_id')->checkboxList($model->listLeaveType(),['custom' => true, 'inline' => false]); ?>
            </div>
        </div>
    </div>


<?php ActiveForm::end(); ?>


<?php

$js = <<< JS
    thaiDatepicker('#leavesearch-date_start,#leavesearch-date_end')
JS;
$this->registerJS($js, View::POS_END);

?>