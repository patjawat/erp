<?php

use yii\helpers\Html;
use app\components\AppHelper;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use app\models\Categorise;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
$listMonth = [
    '01'=>'มกราคม','02'=>'กุมภาพันธ์','03'=>'มีนาคม','04'=>'เมษายน','05'=>'พฤษภาคม','06'=>'มิถุนายน','07'=>'กรกฏาคม','08'=>'สิงหาคม','09'=>'กันยายน','10'=>'ตุลาคม','11'=>'พฤษจิกายน','12'=>'ธันวาคม'
];



$listYear = [
    '2021' => '2021',
    '2022' => '2022',
    '2023' => '2023',
    '2024' => '2024',
    '2025' => '2025',
];
?>
<style>
.field-assetsearch-q {
    margin-bottom: 0px !important;
}

.right-setting {
    width: 500px !important;
}
</style>

<?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">
    <div class="col-7 col-md-7 col-sm-12">
        <?= $form->field($model, 'q_month')->widget(Select2::classname(), [
                                        'data' => $listMonth,
                                        
                                        'options' => ['placeholder' => 'เลือกเดือน'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label(false);
                                    ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?=$form->field($model, 'q_year')->textInput(['placeholder' => 'ระบุปี พ.ศ.'])->label(false);?>
    </div>
    <div class="col-lg-1 col-md-1 col-sm-12">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
    </div>
</div>



<?php ActiveForm::end(); ?>


<?php
$js = <<< JS
$('#show').val(localStorage.getItem('right-setting'))
console.log(localStorage.getItem('right-setting'));
$("#filter-asset").addClass(localStorage.getItem('right-setting'));

$(".filter-asset").on("click", function(){
  $("#filter-asset").addClass("show");
  localStorage.setItem('right-setting','show')
})

$(".filter-asset-close").on("click", function(){
    $(".right-setting").removeClass("show");
    localStorage.setItem('right-setting','hide')
})

JS;
$this->registerJS($js);
      
      ?>