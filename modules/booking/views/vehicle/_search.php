<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\vehiclesearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => isset($action) && $action ? $action : ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']]
]); ?>
<?php // $this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>

<div class="d-flex justify-content-between align-top align-items-center gap-2">
    <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>

        <?= $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' => DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                    // $(this).submit()
                }',
                'select2:unselecting' => 'function() {
                    // $(this).submit()
                }',
            ]
        ])->label(false) ?>

        <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                    // $(this).submit()
                }',
                'select2:unselecting' => 'function() {
                    // $(this).submit()
                }',
            ]
        ])->label(false) ?>

        <?= $form->field($model, 'date_start')->textInput([
            'class' => 'form-control',
            'placeholder' => '__/__/____'
        ])->label(false) ?>

        <?= $form->field($model, 'date_end')->textInput([
            'class' => 'form-control',
            'placeholder' => '__/__/____'
        ])->label(false) ?>

        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '170px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 

                }',
                'select2:unselecting' => 'function() {

                }',
            ]
        ])->label(false) ?>

        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
    </div>
    
<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php

$js = <<< JS

    thaiDatepicker('#vehiclesearch-date_start,#vehiclesearch-date_end')
    $("#vehiclesearch-date_start").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#vehiclesearch-date_end").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


JS;
$this->registerJS($js, View::POS_END);
?>