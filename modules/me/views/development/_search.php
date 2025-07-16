<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\components\DateFilterHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
?>

<div class="development-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
          'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>


<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label(false) ?>
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

    <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
      <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => $model->listStatus(),
        'options' => ['placeholder' => 'สถานะทั้งหมด'],
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
    <div class="row">
<div class="col-lg-3 col-md-3 col-sm-12">

        <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => "function() {
                             $(this).submit()
                            $('#developmentsearch-date_start').val('');
                            $('#developmentsearch-date_end').val('');
                            
                        }",
            ]
        ])->label(false);
        ?>
            
</div>
    </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

$js = <<< JS

    thaiDatepicker('#developmentsearch-date_start,#developmentsearch-date_end')
    $("#developmentsearch-date_start").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#developmentsearch-date_end").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


    JS;
$this->registerJS($js, View::POS_END);

?>
