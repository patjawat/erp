<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\vehiclesearch $model */
/** @var yii\widgets\ActiveForm $form */
?>



<?php $form = ActiveForm::begin([
    'action' => ['/booking/vehicle/work'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<?=$this->render('@app/components/ui/Search',['form' => $form,'model' => $model,'label' => 'พขร.'])?>

  <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 

                }',
                'select2:unselecting' => 'function() {

                }',
            ]
        ])->label(false) ?>



<?php ActiveForm::end(); ?>


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