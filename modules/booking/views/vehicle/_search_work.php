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
    // 'action' => ['/booking/vehicle/work'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']]
]); ?>

<?= $this->render('@app/components/ui/_filter', [
    'form' => $form,
    'model' => $model,
    'label' => false,
    'status' => $model->listStatus()
])
?>


<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false, 'placeholder' => 'พขร.', 'fieldName' => 'driver_id']) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">
                <?= $form->field($model, 'location')->widget(Select2::classname(), [
                    'data' => $model->ListOrg(),
                    'options' => ['placeholder' => 'สถานที่ไปทั้งหมด'],
                    'pluginOptions' => [
                        'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                        'allowClear' => true,
                    ],
                ])->label(false) ?>
            </div>
    <div class="collapse mt-3" id="collapseFilter">
        <div class="row">

            <div class="col-lg-4">
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
            <div class="col-lg-3"></div>
            <div class="col-lg-3"></div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<< JS

    thaiDatepicker('#vehicledetailsearch-date_start,#vehicledetailsearch-date_end')
    $("#vehicledetailsearch-date_start").on('change', function() {
            $('#vehicledetailsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#vehicledetailsearch-date_end").on('change', function() {
            $('#vehicledetailsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


JS;
$this->registerJS($js, View::POS_END);
?>