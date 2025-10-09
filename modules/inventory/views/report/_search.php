<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>


<div class="row">

    <div class="col-5">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'placeholder' => 'เริ่มจากวันที่'])->label(false); ?>
    </div>
    <div class="col-5">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'placeholder' => 'ถึงวีนที่'])->label(false); ?>
    </div>

    <div class="col-2">
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
        <div class="col-3">

            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListThaiYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '120px',
                ],
            ])->label(false); ?>

        </div>


    </div>
    <?php ActiveForm::end(); ?>

    <?php
    $js = <<< JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end')

    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $('#stockeventsearch-date_filter').val(null).trigger('change');
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $('#stockeventsearch-date_filter').val(null).trigger('change');
    });



 
JS;
    $this->registerJS($js);
    ?>