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

<div class="d-flex justify-content-between align-top align-items-center gap-1">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
    <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
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

    <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>
    <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>
    <?php
    $status = ArrayHelper::merge( $model->listStatus(), ['Y' => 'บันทึกไว้']);
                    echo $form->field($model, 'q_status')->widget(Select2::classname(), [
                        'data' =>$status,
                        'options' => ['placeholder' => 'สถานะทั้งหมด'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '170px',
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

    <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    \$( "#documentsdetailsearch-show_reading" ).prop( "checked", localStorage.getItem('show_reading') == 1 ? true : false );
    \$("body").on("change", "#documentsdetailsearch-show_reading", function (e) {
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