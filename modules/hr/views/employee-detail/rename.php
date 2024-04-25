
<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>
<div class="row">
<div class="col-6">
<?=$form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ตั้งแต่วันที่');
?>
</div>
<div class="col-6">
<?= $form->field($model, 'data_json[rename_type]')->widget(Select2::classname(), [
                'data' =>$model->employee->ListRenameType(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('รายการที่เปลี่ยน') ?>
</div>
</div>

<div class="row">
  <div class="col-3">
      <?= $form->field($model, 'data_json[old_prefix]')->widget(Select2::classname(), [
                'data' =>$model->employee->ListPrefixTh(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('คำนำหน้าชื่อเดิม') ?>

  </div>
  <div class="col-4">
      <?= $form->field($model, 'data_json[old_fname]')->textInput()->label('ชื่อเดิม') ?>

  </div>
  <div class="col-5">
      <?= $form->field($model, 'data_json[old_lname]')->textInput()->label('สกุลเดิม') ?>

  </div>
</div>


<div class="row">
  <div class="col-3">
      <?= $form->field($model, 'data_json[new_prefix]')->widget(Select2::classname(), [
                'data' =>$model->employee->ListPrefixTh(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('คำนำหน้าชื่อใหม่') ?>

  </div>
  <div class="col-4">
      <?= $form->field($model, 'data_json[new_fname]')->textInput()->label('ชื่อใหม่') ?>

  </div>
  <div class="col-5">
      <?= $form->field($model, 'data_json[new_lname]')->textInput()->label('สกุลใหม่') ?>

  </div>
</div>

<?= $form->field($model, 'data_json[update_lname]')->textInput()->label('เอกสารอ้างอิง') ?>
<?= $form->field($model, 'data_json[rename_note]')->textInput()->label('หมายเหตุ') ?>
<?= $form->field($model, 'data_json[rename_note]')->textInput()->label('File') ?>

<?= $model->upload($model->ref,'rename') ?>
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

JS;
$this->registerJS($js, View::POS_END)
    ?>
