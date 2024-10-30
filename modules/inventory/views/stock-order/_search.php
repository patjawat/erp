<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.right-setting {
    width: 500px !important;
}
</style>
<div class="stock-in-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex justify-content-between gap-3">

        <?= $form->field($model, 'q')->label(false) ?>
        <?=$form->field($model, 'transaction_type')->widget(Select2::classname(), [
        'data' => ['IN' => 'รับ','OUT' =>'จ่าย'],
        'options' => ['placeholder' => 'ความเคลื่อนไหว'],
        'pluginOptions' => [
            'width' => '100px',
            'allowClear' => true,
        ],
        'pluginEvents' => [
            'select2:select' => "function(result) { $(this).submit()}",
            'select2:unselecting' => "function(result) { $(this).submit()}",
            ]
        ])->label(false);?>
                    
        <?=$form->field($model, 'asset_type_name')->widget(Select2::classname(), [
        'data' => ArrayHelper::map($model->ListOrderType(),'id','name'),
        'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
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
                        <?=$form->field($model, 'date_start')->widget(Datetimepicker::className(),[
                        'options' => [
                            'timepicker' => false,
                            'datepicker' => true,
                            'mask' => '99/99/9999',
                            'lang' => 'th',
                            'yearOffset' => 543,
                            'format' => 'd/m/Y', 
                        ],
                        ])->label('ช่วงวันที่');
                        ?>
                        <?=$form->field($model, 'date_end')->widget(Datetimepicker::className(),[
                                    'options' => [
                                        'timepicker' => false,
                                        'datepicker' => true,
                                        'mask' => '99/99/9999',
                                        'lang' => 'th',
                                        'yearOffset' => 543,
                                        'format' => 'd/m/Y', 
                                    ],
                                    ])->label('ถึงวันที่');
                                ?>
                    </div>
                    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary mt-3']);?>
                </div>
            </div>
        </div>
        <span class="filter-emp btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
            data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
            <i class="fa-solid fa-filter"></i>
        </span>
    </div>

    <?php ActiveForm::end(); ?>

</div>


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
 

$("#stockeventsearch-date_start").datetimepicker({
    timepicker:false,
    format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
    lang:'th',  // แสดงภาษาไทย
    onChangeMonth:thaiYear,          
    onShow:thaiYear,                  
    yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
    closeOnDateSelect:true,
});   

$("#stockeventsearch-date_end").datetimepicker({
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