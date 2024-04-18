
<?php
use yii\helpers\ArrayHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;
use app\modules\hr\models\Organization;

?>

<div class="row">
<div class="col-6">
<?=$form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(), [
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
   <?=  $form->field($model, 'data_json[position_name]')->widget(Select2::classname(), [
    'data' => ArrayHelper::map($model->positionManageList(),'id','position_name'),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => true,
        'maximumInputLength' => 10,
    ],
])->label('ตำแหน่งบริหาร');   ?>

</div>
</div>
<div class="row">
<div class="col-6">
    <?= $form->field($model, 'data_json[work_line]')->widget(Select2::classname(), [
    'data' => $model->employee->ListPositionLevel(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => true,
        'maximumInputLength' => 10,
    ],
])->label('ตำแหน่งสายงาน') ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[type]')->widget(Select2::classname(), [
    'data' => $model->employee->ListPositionLevel(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => true,
        'maximumInputLength' => 10,
    ],
])->label('ประเภท') ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[position_level]')->widget(Select2::classname(), [
    'data' => $model->employee->ListPositionLevel(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => true,
        'maximumInputLength' => 10,
    ],
])->label('ระดับตำแหน่ง') ?>
</div>

<div class="col-6">
    <?=  $form->field($model, 'data_json[department_group]')->widget(\kartik\tree\TreeViewInput::className(),[
                                                    'name' => 'department',
                                                    'query' => Organization::find()->addOrderBy('root, lft'),
                                                    'value' => 1,
                                                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                                    'rootOptions' => ['label'=>'<i class="fa fa-building"></i>'],
                                                    'fontAwesome' => true,
                                                    'asDropdown' => true,
                                                    'multiple' => false,
                                                    // 'autoCloseOnSelect' => true,
                                                    'options' => ['disabled' => false,'id' => 'departmentGroup']
                                                ])->label('หน่วยงานภายในตามโครงสร้าง');   ?>

    </div>
    <div class="col-6">
    <?=  $form->field($model, 'data_json[department]')->widget(\kartik\tree\TreeViewInput::className(),[
                                                    'name' => 'department',
                                                    'query' => Organization::find()->addOrderBy('root, lft'),
                                                    'value' => 1,
                                                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                                    'rootOptions' => ['label'=>'<i class="fa fa-building"></i>'],
                                                    'fontAwesome' => true,
                                                    'asDropdown' => true,
                                                    'multiple' => false,
                                                    'options' => ['disabled' => false]
                                                ])->label('หน่วยงานภายในที่ได้รับมอบหมาย');   ?>

    </div>


<div class="col-6">
        <?= $form->field($model, 'data_json[doc_ref]')->textInput()->label('เอกสารอ้างอิง') ?>
        
    </div>
  


    <div class="col-12">
        <?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>
        
    </div>
    
</div>


<?= $model->upload($model->ref,'position') ?>


<?php
$js = <<<JS


$("#departmentGroup").on('treeview:beforeselect', function(event, key, jqXHR, settings) {
    console.log('treeview:beforeselect');
    alert('select');
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
