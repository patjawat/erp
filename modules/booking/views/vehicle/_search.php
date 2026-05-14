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
        'data-pjax' => 1,
        'class' => 'vehicle-search-form',
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
]); ?>
<?php // $this->render('@app/components/ui/Search',['form' => $form,'model' => $model])
?>

<?= $this->render('@app/components/ui/_filter', [
    'form' => $form,
    'model' => $model,
    'label' => false,
    'status' => $model->listStatus(),
    'placeholder' => 'ผู้ขอใช้รถยนต์'
])
?>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">

        <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา'])->label(false); ?>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'location')->widget(Select2::classname(), [
            'data' => $model->ListOrg(),
            'options' => ['placeholder' => 'สถานที่ไปทั้งหมด'],
            'pluginOptions' => [
                'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                'allowClear' => true,
            ],
        ])->label(false) ?>
    </div>


</div>
<div class="row mt-2">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'not_logged')->dropDownList([
            '' => 'ทั้งหมด',
            '1' => 'ยังไม่บันทึกการเดินทาง',
        ], ['class' => 'form-select'])->label(false) ?>
    </div>
</div>
<div class="collapse mt-3" id="collapseFilter">
    
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

    thaiDatepicker('#vehiclesearch-date_start,#vehiclesearch-date_end')
    $("#vehiclesearch-date_start").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            $('#vehiclesearch-date_filter').val(null).trigger('change');
        });
        $("#vehiclesearch-date_end").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            $('#vehiclesearch-date_filter').val(null).trigger('change');
    });


JS;
$this->registerJS($js, View::POS_END);
?>
