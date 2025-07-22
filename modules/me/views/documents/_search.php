<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => [$action],
    'id' => 'document-search',
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>


    </div>

    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'ตั้งแต่วันที่'])->label(false);?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวันที่'])->label(false);?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php
                                $status = ArrayHelper::merge( $model->listStatus(), ['Y' => 'บันทึกไว้']);
                                echo $form->field($model, 'q_status')->widget(Select2::classname(), [
                                    'data' =>$status,
                                    'options' => ['placeholder' => 'สถานะทั้งหมด'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ])->label(false);?>
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
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">

            <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                    $(this).submit()
                    }',
                    'select2:unselecting' => 'function() {
                        }',
                        ]
                        ])->label(false);
                        ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#documentsearch-date_start,#documentsearch-date_end')

    $('#documentsearch-date_start, #documentsearch-date_end').on('change', function () {
        $('#documentsearch-thai_year, #documentsearch-date_filter').val(null).trigger('change');
    });

    $( "#documentsdetailsearch-show_reading" ).prop( "checked", localStorage.getItem('show_reading') == 1 ? true : false );
    $("body").on("change", "#documentsdetailsearch-show_reading", function (e) {
        
                            if (\$(this).is(':checked')) {
                                // alert('Checkbox is checked!');
                                localStorage.setItem('show_reading',1);
                                
                            } else {
                                // alert('Checkbox is unchecked!');
                                localStorage.setItem('show_reading',0);
                            }
                            \$(this).submit();
                        });

                  
    JS;
$this->registerJS($js)
?>