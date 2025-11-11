<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;
use app\components\DateFilterHelper;
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

        <?= $this->render('@app/components/ui/_filter', [
            'form' => $form,
            'model' => $model,
            'label' => false,
            'status' => $model->listStatus()
        ])
        ?>

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

              <div class="col-lg-3 col-md-3 col-sm-12">

              <?php
                            echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentType(),
                                'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
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