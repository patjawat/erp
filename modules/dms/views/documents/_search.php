<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => [$model->document_group],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex justify-content-between align-top align-items-center gap-2">
    <?= $form->field($model, 'q')->textInput(['placeholder' =>'คำค้นหา...'])->label(false) ?>
    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => 'function() {
                            $(this).submit()
                        }',
            ]
        ])->label(false);
        ?>

            <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>
    </div>
        <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>
    </div>

<?php
            //         echo $form->field($model, 'status')->widget(Select2::classname(), [
            //             'data' => $model->listStatus(),
            //             'options' => ['placeholder' => 'สถานะทั้งหมด'],
            //             'pluginOptions' => [
            //                 'allowClear' => true,
            //                 'width' => '170px',
            //             ],
            //             'pluginEvents' => [
            //     'select2:select' => 'function(result) { 
            //             $(this).submit()
            //             }',
            //     'select2:unselecting' => 'function() {
            //                 $(this).submit()
            //             }',
            // ]
            //         ])->label(false);
                    ?>
                    
    <?= $form->field($model, 'document_group')->hiddenInput()->label(false) ?>
        <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>


    <?php

$js = <<< JS

    thaiDatepicker('#documentsearch-date_start,#documentsearch-date_end')
    $("#documentsearch-date_start").on('change', function() {
            $('#documentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#documentsearch-date_end").on('change', function() {
            $('#documentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


    JS;
$this->registerJS($js, View::POS_END);

?>
