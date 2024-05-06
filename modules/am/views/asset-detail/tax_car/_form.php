<?php

use app\components\AppHelper;
use kartik\form\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use yii\web\View;

?>
<style>
.col-form-label {
    text-align: end;
}

/* #assetdetail-data_json-date_start,
#assetdetail-data_json-date_start2 {
    width: 130px;
} */
</style>
<div class="form-tax">

    <?php $form = ActiveForm::begin([
    'id' => 'form-tax',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_X_LARGE],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']],
    'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-detail/validator']
]);?>

    <?=$form->field($model, 'ref')->hiddenInput()->label(false)?>
    <?=$form->field($model, 'name')->hiddenInput()->label(false)?>

    <h5><i class="fa-solid fa-tag"></i> ข้อมูลการต่อภาษี</h5>
    <div class="row">
        <div class="col-6">
            <?php echo $form->field($model, 'date_start')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่ต่อภาษี') ?>

            <?=$form->field($model, 'data_json[price]', ['labelSpan' => 4])->textInput(['type' => 'number','step' => 0.01])->label('ค่าภาษี')?>
        </div>
        <div class="col-6">
            <?php echo $form->field($model, 'date_end',['labelSpan' => 4])->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ครบกำหนด') ?>
        </div>

    </div>
    <hr>
    <h5><i class="fa-solid fa-user-injured"></i> พรบ.</h5>
    <div class="row">
        <div class="col-6">
            <?=$form->field($model, 'data_json[company1]')->textInput(['maxlength' => true])->label('บริษัท')?>
            <?=$form->field($model, 'data_json[number1]')->textInput(['maxlength' => true])->label('กรมธรรม์เลขที่')?>
            <?=$form->field($model, 'data_json[price1]', ['labelSpan' => 4])->textInput(['type' => 'number','step' => 0.01])->label('เบี้ยประกัน')?>

        </div>
        <div class="col-6">
            <?php echo $form->field($model, 'data_json[date_start1]', ['labelSpan' => 4],
                        )->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label('วันที่') ?>

            <?php echo $form->field($model, 'data_json[date_end1]')->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label('ถึง') ?>
            <?=$form->field($model, 'data_json[sale1]')->textInput(['maxlength' => true])->label('ตัวแทน')?>
            <?=$form->field($model, 'data_json[phone1]', ['labelSpan' => 4])->textInput(['type' => 'number','step' => 0.01])->label('โทร')?>

        </div>
    </div>

    <hr>
    <h5><i class="fa-solid fa-car-burst"></i> ประกันภัยรถ</h5>
    <div class="row">

        <div class="col-6">
            <?=$form->field($model, 'data_json[company2]')->textInput(['maxlength' => true])->label('บริษัท')?>
            <?=$form->field($model, 'data_json[price2]', ['labelSpan' => 4])->textInput(['type' => 'number','step' => 0.01])->label('เบี้ยประกัน')?>
            <?=$form->field($model, 'data_json[number2]')->textInput(['maxlength' => true])->label('กรมธรรม์เลขที่')?>
        </div>
        <div class="col-6">

            <?php echo $form->field($model, 'data_json[date_start2]',['labelSpan' => 4],
                        )->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label('วันที่') ?>

            <?php echo $form->field($model, 'data_json[date_end2]')->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label('ถึง') ?>
            <?=$form->field($model, 'data_json[sale2]')->textInput(['maxlength' => true])->label('ตัวแทน')?>
            <?=$form->field($model, 'data_json[phone2]', ['labelSpan' => 4])->textInput(['maxlength' => true])->label('โทร')?>

        </div>
    </div>
    <hr>
    <div class="form-group d-flex justify-content-center">
        <?=AppHelper::btnSave();?>
    </div>
</div>
<hr>
<div class="col-sm-12">
    <?=$model->Upload($model->ref, 'asset_pic')?>
</div>

<?php ActiveForm::end();?>

</div>


<?php
$js = <<<JS

$('#form-tax').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                await  $.pjax.reload({ container:"#am-container", history:false,replace: false,timeout: false});
            }
        }
    });
    return false;
});

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
     
    $("#assetdetail-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
 
    $("#assetdetail-date_end").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
 
    $("#assetdetail-data_json-date_start1").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
    $("#assetdetail-data_json-date_end1").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
 
    $("#assetdetail-data_json-date_start2").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
    $("#assetdetail-data_json-date_end2").datetimepicker({
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