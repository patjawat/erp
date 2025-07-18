<?php

use yii\web\View;
use yii\helpers\Html;
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

<div class="d-flex justify-content-between gap-3 align-items-center align-middle">
    <div class="d-flex gap-3 mb-3">
        
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'เลือกสถานะ'],
            'pluginOptions' => [
                'width' => '200px',
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                    $(this).submit()
                    }",
                    'select2:unselecting' => "function(result) { 
                        $(this).submit()
                        }",
                        
                        ]
                        ])->label(false);
                        ?>

</div>
   

    <div class="right-setting" id="filter-emp">
        <div class="card mb-0 w-100">
            <div class="card-header">
                <h5 class="card-title d-flex justify-content-between">
                    ค้นหาข้อมูล
                    <a href="javascript:void(0)"><i class="bi bi-x-circle filter-emp-close"></i></a>
                </h5>
            </div>
            <div class="p-2">

                <div class="d-flex justify-content-between gap-3">


                </div>

                <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary mt-3']);?>

            </div>
        </div>
    </div>


    <?php //  Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
    <?php // Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php


$js = <<<JS

$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

$(".filter-emp-close").on("click", function(){
    $(".right-setting").removeClass("show");
    localStorage.setItem('right-setting','hide')
})

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
 

$("#ordersearch-date_start").datetimepicker({
    timepicker:false,
    format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
    lang:'th',  // แสดงภาษาไทย
    onChangeMonth:thaiYear,          
    onShow:thaiYear,                  
    yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
    closeOnDateSelect:true,
});   

$("#ordersearch-date_end").datetimepicker({
    timepicker:false,
    format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
    lang:'th',  // แสดงภาษาไทย
    onChangeMonth:thaiYear,          
    onShow:thaiYear,                  
    yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
    closeOnDateSelect:true,
});       


JS;
$this->registerJS($js, View::POS_END)
?>