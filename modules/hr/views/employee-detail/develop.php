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
        <?= $form->field($model, 'data_json[course]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('สถานที่') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[name]')->textInput(['placeholder' => 'ระบุ'])->label('ชื่อเรื่อง/รายละเอียด/หลักสูตร/สาขาวิชา') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[dep_to]')->textInput(['placeholder' => 'ระบุ'])->label('รายการ [เพื่อ]') ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ด้วยทุน') ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ประเภทของทุน') ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ประเภทของเงิน') ?>
    </div>
    <div class="col-3">

        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('งบประมาณ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('วัตถุประสงค์') ?>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ลักษณะการไป') ?>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('หลักฐานสำเร็จ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ผลสัมฤทธิ์') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('เนื้อหา') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ประโยชน์ที่ได้รับ ต่อตนเอง') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ประโยชน์ที่ได้รับ ต่อหน่วยงาน') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ประโยชน์ที่ได้รับ อื่น ๆ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ปัญหา-อุปสรรค') ?>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ข้อคิดเห็นและข้อเสนอแนะ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ความเห็นผู้บังคับบัญชา') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('เอกสารอ้างอิง') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('วันเริ่มหยุด') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('วันขยายเวลา') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('วันกลับ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('วันกลับ') ?>
    </div>
    <div class="col-6">





        <?= $form->field($model, 'data_json[busget]')->textInput(['placeholder' => 'ระบุสถานที่'])->label('ลาศึกษาระดับ') ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>

    </div>
</div>


<?= $model->upload($model->ref,'develop') ?>


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
