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
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<?=$this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>

<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-light mt-3']);?>
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