<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\DateFilterHelper;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .right-setting {
        width: 500px !important;
    }
</style>
<div class="stock-in-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">

        <div class="col-lg-2 col-md-2 col-sm-12">

            <?= $form->field($model, 'q')->label(false) ?>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <?= $form->field($model, 'asset_type_name')->widget(Select2::classname(), [
                'data' => ArrayHelper::map($model->ListOrderType(), 'id', 'name'),
                'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
                'pluginOptions' => [
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
            ])->label(false);
            ?>
        </div>
        <div class="col-2">
            <?php
            echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
                'data' =>  DateFilterHelper::getDropdownItems(),
                'options' => ['placeholder' => 'ช่วงเวลาทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '130px',
                ],
            ])->label(false);
            ?>

        </div>

              <div class="col-lg-1 col-md-1 col-sm-12">
                  <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-12">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
            <?= $form->field($model, 'order_status')->widget(Select2::classname(), [
                'data' => $model->listStatus(),
                'options' => ['placeholder' => 'สถานะทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '150px',
                ],
            ])->label(false); ?>
        </div>
        <div class="col-lg-1 col-md-1 col-sm-12">
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

        <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
                'data' => $model->listVendor(),
                'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
                'pluginOptions' => [
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
            ])->label(false);
            ?>
</div>

<?php ActiveForm::end(); ?>




<?php


$js = <<<JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end')

    $("#stockeventsearch-date_start").on('change', function() {
            $('#stockeventsearch-thai_year').val(null).trigger('change');
            $('#stockeventsearch-date_filter').val(null).trigger('change');
            // $(this).submit();
        });
        $("#stockeventsearch-date_end").on('change', function() {
            $('#stockeventsearch-thai_year').val(null).trigger('change');
            $('#stockeventsearch-date_filter').val(null).trigger('change');
            // $(this).submit();
    });

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