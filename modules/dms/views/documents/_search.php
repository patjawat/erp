<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\components\DateFilterHelper;

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

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <?= $form->field($model, 'q')->textInput(['placeholder' => 'คำค้นหา...'])->label(false) ?>
        </div>

        <div class="col-lg-2 col-md-3 col-sm-12">
            <?= $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' => DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
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
        </div>
       
        <div class="col-lg-2 col-md-2 col-sm-12">
            <?= $form->field($model, 'date_start')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '__/__/____'
                    ])->label(false) ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
            <?= $form->field($model, 'date_end')->textInput([
                'class' => 'form-control',
                'placeholder' => '__/__/____'
                ])->label(false) ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
            <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false) ?>
        </div>

        <div class="col-lg-1 col-md-1 col-sm-12">
            <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>
    
    <div class="collapse mt-3" id="collapseFilter">
        <div class="row">
 <div class="col-lg-3 col-md-3 col-sm-12">
            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
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
        </div>
        </div>
        </div>
  <?= $form->field($model, 'document_group')->hiddenInput()->label(false) ?>
    <?php ActiveForm::end(); ?>

<?php

$js = <<<JS
thaiDatepicker('#documentsearch-date_start,#documentsearch-date_end')
$("#documentsearch-date_start").on('change', function() {
    $('#documentsearch-thai_year').val(null).trigger('change');
    $('#documentsearch-date_filter').val(null).trigger('change');
    // $(this).submit();
});
$("#documentsearch-date_end").on('change', function() {
    $('#documentsearch-thai_year').val(null).trigger('change');
    $('#documentsearch-date_filter').val(null).trigger('change');
    // $(this).submit();
});
JS;
$this->registerJS($js, View::POS_END);

?>