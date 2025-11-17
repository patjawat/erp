<?php

use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .right-setting {
        width: 500px !important;
    }
</style>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
    'options' => [
        'data-pjax' => 0
    ],
]); ?>
<div class="row">
    <div class="col-3">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title'),
            'options' => ['placeholder' => 'ประเภททั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-2">
        <?php

        echo $form->field($model, 'date_between')->widget(Select2::classname(), [
            'data' => [
                'pr_create_date' => 'วันที่ขอซื้อ',
                'po_date' => 'วันที่สั่งซื้อ',
                'gr_date' => 'วันที่ตรวจรับ'
            ],
            'options' => ['placeholder' => 'ประเภทการค้นหา...'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => ArrayHelper::map($model->ListStatus(), 'code', 'title'),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>

</div>



<div class="row mt-2">
    <div class="col-3">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    </div>
    <div class="col-4">
        <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'ผู้ขายทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
     <div class="col-4">
        <?= $form->field($model, 'q_budget_type')->widget(Select2::classname(), [
            'data' => $model->ListBudgetdetail(),
            'options' => ['placeholder' => 'ประเภทเงินทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
     


    <div class="col-1">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']); ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-filter"></i>
        </button>
    </div>



</div>

<div class="collapse mt-3" id="collapseFilter">



</div>
<?php ActiveForm::end(); ?>

<?php


$js = <<<JS

thaiDatepicker('#ordersearch-date_start,#ordersearch-date_end')
$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

// $(".filter-emp-close").on("click", function(){
//     $(".right-setting").removeClass("show");
//     localStorage.setItem('right-setting','hide')
// })

var thaiYear = function (ct) {
    var leap=3;  
    var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
    if(ct){  
        var yearL=new Date(ct).getFullYear()-543;  
        leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
        if(leap==2){  
            dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
        }  
    }              
    this.setOptions({  
        i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
    })                
};    
 


JS;
$this->registerJS($js, View::POS_END)
?>