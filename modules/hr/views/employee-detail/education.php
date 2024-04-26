
<?php
use kartik\widgets\Select2;
use yii\web\View;
use iamsaint\datetimepicker\Datetimepicker;
?>
    <?=$form->field($model, 'data_json[education_name]')->hiddenInput()->label(false)?>
<?=$form->field($model, 'data_json[education]')->widget(Select2::classname(), [
    'data' => $model->GetEducationItems(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
    ],
    'pluginEvents' => [
        "change" => "function() { 
            var selectedText = $(this).find('option:selected').text();
            $('#employeedetail-data_json-education_name').val(selectedText)
         }",
    ]
])->label('ระดับการศึกษา')?>


    <?=$form->field($model, 'data_json[major]')->textInput()->label('สาขาวิชาเอก')?>
    <?=$form->field($model, 'data_json[institute]')->textInput()->label('สำเร็จจากสถาบัน')?>

<div class="row">
<div class="col-6">
<?= $form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('เข้ารับการศึกษา (ว/ด/พ.ศ.)');
?>


</div>
<div class="col-6">
<?= $form->field($model, 'data_json[date_end]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('สำเร็จการศึกษา (ว/ด/พ.ศ.)');
?>
</div>
</div>

<?= $model->upload($model->ref,'education') ?>

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
