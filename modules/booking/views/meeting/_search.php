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

<div class="d-flex gap-2">
<div class="d-flex justify-content-between gap-2">
                <?php echo $form->field($model, 'date_start')->textInput()->label('ตั้งแต่วันที่');?>
                <?php echo $form->field($model, 'date_end')->textInput()->label('ถึงวันที่');?>
                <?php
                    echo $form->field($model, 'status')->widget(Select2::classname(), [
                        'data' => [
                            'pending' => 'ร้องขอ',
                            'approve' => 'จัดสรร',
                            'allow' => 'อนุมัติ',
                            'cancel' => 'ยกเลิก',
                        ],
                        // 'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '250px',
                        ],
                        'pluginEvents' => [
                            'select2:unselect' => 'function() {
                                    $(this).submit();
                                    }',
                                    'select2:select' => 'function() {
                                        $(this).submit();
                                    }',
                        ],
                    ])->label('ประเภท');
                    ?>
            </div>

    <div class="d-flex flex-row mb-3 mt-4">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btm-sm btn-primary']) ?>
    </div>

</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#leavesearch-date_start,#leavesearch-date_end')


    JS;
$this->registerJS($js, View::POS_END);

?>