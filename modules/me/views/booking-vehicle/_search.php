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