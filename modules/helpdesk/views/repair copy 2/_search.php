<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.right-setting {
    width: 500px !important;
}
</style>

<?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 0
        ],
    ]); ?>



<div class="row">
    <div class="col-3">
        <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ช่วงเวลาทั้งหทด'],
            'pluginOptions' => [
                'allowClear' => true,
                // 'width' => '130px',
            ],
            ])->label(false);
            ?>

    </div>

    <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => $model->listRepairStatus(),
        'options' => ['placeholder' => 'สถานะทั้งหทด'],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width' => '150px',
        ],
        ])->label(false);?>
    </div>
    <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>

</div>



<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    <div class="row">
        <div class="col-3">

            <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width' => '120px',
                    ],
        ])->label(false);?>

        </div>
    </div>


</div>


<?php ActiveForm::end(); ?>

<?php


$js = <<<JS

thaiDatepicker('#helpdesksearch-date_start,#helpdesksearch-date_end')

$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

$(".filter-emp-close").on("click", function(){
    $(".right-setting").removeClass("show");
    localStorage.setItem('right-setting','hide')
})


JS;
$this->registerJS($js, View::POS_END)
?>