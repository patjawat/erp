<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
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

<div class="d-flex justify-content-between gap-3 align-items-center align-middle">
  <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>
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


    <div class="d-flex flex-row align-items-center gap-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
            aria-controls="offcanvasRight" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม..."><i
                class="fa-solid fa-filter"></i></button>
    </div>
    
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?=$form->field($model, 'urgency')->widget(Select2::classname(), [
                                    'data' => $model->listUrgency(),
                                    'options' => ['placeholder' => 'เลือกความเร่งด่วน ...'],
                                    'pluginOptions' => [
                                        'width' => '200px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                                'select2:unselecting' => "function(result) { 
                                                    $(this).submit()
                                                  }", 
                                    ]
                                ])->label('ความเร่งด่วน');
                        ?>

            <?php echo $form->field($model, 'status')->checkboxList($model->listRepairStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline'])->label('สถานะ'); ?>
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-light mt-3']);?>
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