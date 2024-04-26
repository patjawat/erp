
<?php
// use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;
use yii\widgets\MaskedInput;
// use karatae99\datepicker\DatePicker;
use iamsaint\datetimepicker\Datetimepicker;
?>
<div class="row">
<div class="col-lg-6 col-md-6 col-sm-6">
<?= $form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่ได้รับโทษ') ?>

    <?= $form->field($model, 'data_json[blame_type]')->textInput(['autofocus' => true])->label('ประเภทความผิด') ?>
    </div>
<div class="col-lg-6 col-md-6 col-sm-6">
<?= $form->field($model, 'data_json[date_end]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่สิ้นสุดโทษ') ?>
<?= $form->field($model, 'data_json[blame]')->textInput(['autofocus' => true])->label('การลงโทษ') ?>

                </div>

                <div class="col-12">
        <?=$form->field($model, 'data_json[doc_ref]')->textInput()->label('เอกสารอ้างอิง')?>
    </div>
    <div class="col-12">
        <?=$form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ')?>
    </div>

    <?= $model->upload($model->ref,'license') ?>


<?php
$js = <<<JS




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
     
    $("#employeedetail-data_json-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
    $("#employeedetail-data_json-date_end").datetimepicker({
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
