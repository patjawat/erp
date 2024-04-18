
<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>

<div class="row">
<div class="col-3">
<?=$form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(), [
    'options' => [
        'timepicker' => false,
        'datepicker' => true,
        'mask' => '99/99/9999',
        'lang' => 'th',
        'yearOffset' => 543,
        'format' => 'd/m/Y',
    ],
])->label('วันรับทุน');
?>
</div>
<div class="col-3">
<?=$form->field($model, 'data_json[date_end]')->widget(Datetimepicker::className(), [
    'options' => [
        'timepicker' => false,
        'datepicker' => true,
        'mask' => '99/99/9999',
        'lang' => 'th',
        'yearOffset' => 543,
        'format' => 'd/m/Y',
    ],
])->label('วันสำเร็จ');
?>
</div>
<div class="col-6">
<?= $form->field($model, 'data_json[name]')->textInput(['placeholder' => 'ระบุชื่อทุน'])->label('ชื่อทุน') ?>
</div>
<div class="col-3">
<?= $form->field($model, 'data_json[level]')->textInput(['placeholder' => 'ระบุระดับ'])->label('ระดับ') ?>
</div>
<div class="col-3">
<?= $form->field($model, 'data_json[course]')->textInput(['placeholder' => 'ระบุหลักสูตร'])->label('หลักสูตร') ?>

</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[study]')->textInput(['placeholder' => 'ระบุสาขา'])->label('สาขา') ?>

</div>

<div class="col-6">
    <?= $form->field($model, 'data_json[educational]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('สถานที่') ?>

</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('เงินทุน') ?>

</div>

<div class="col-12">
        <?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>
        
    </div>
</div>


<?= $model->upload($model->ref,'position') ?>


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
     
    $("#employeedetail-data_json-education_begin").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
 
    $("#employeedetail-data_json-education_end").datetimepicker({
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
