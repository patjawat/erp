<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.card {
    border: 1px solid #ededed;
    margin-bottom: 30px;
    -webkit-box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 4px 0px rgb(126 114 114 / 7%);
}

.select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 6px) !important;
}
</style>



<?php $form = ActiveForm::begin([
    'id' => 'form-position',
]); ?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
            
        <?=$form->field($model, 'data_json[education_success]')->widget(Datetimepicker::className(),[
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
            <div class="col-lg-6 col-md-6 col-sm-12">
                <?=$form->field($model, 'data_json[edu_name]')->textInput(['maxlength' => true])->label('ชื่อคุณวุฒิ') ?>
            </div>
        </div>


            <?=$form->field($model, 'data_json[edu_level]')->textInput(['maxlength' => true])->label('ระดับการศึกษา') ?>
            <?=$form->field($model, 'data_json[edu_major]')->textInput(['maxlength' => true])->label('สาขาวิชาเอก') ?>
            <?=$form->field($model, 'data_json[edu_institution]')->textInput(['maxlength' => true])->label('สถาบันการศึกษา') ?>
            <?=$form->field($model, 'data_json[edu_contry]')->textInput(['maxlength' => true])->label('ประเทศ') ?>
            <?=$form->field($model, 'data_json[edu_type]')->textInput(['maxlength' => true])->label('การบันทึก') ?>
            <?=$form->field($model, 'data_json[edu_ref1]')->textInput(['maxlength' => true])->label('หนังสือตรวจสอบ') ?>
            <?=$form->field($model, 'data_json[edu_ref2]')->textInput(['maxlength' => true])->label('หนังสือตอบรับ') ?>
            <?=$form->field($model, 'data_json[edu_note]')->textInput(['maxlength' => true])->label('หมายเหตุ') ?>

</div>
<div class="col-lg-6 col-md-6 col-sm-12">
    <?=$form->field($model, 'data_json[date_end]')->widget(Datetimepicker::className(),[
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
        <?=$form->field($model, 'data_json[position_rate]')->textInput(['maxlength' => true])->label('เลขประจำตำแหน่ง') ?>
    </div>
    
</div>
<?=$form->field($model, 'data_json[action_name]')->textInput(['maxlength' => true])->label('รายการเคลื่อนไหว') ?>



<div class="d-flex justify-content-center">
    <?= SiteHelper::btnSave() ?>
</div>
<?=$form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
<?=$form->field($model, 'emp_id')->hiddenInput(['maxlength' => true])->label(false) ?>
<?=$form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>

<?php ActiveForm::end(); ?>




<?php
$js = <<<JS



 $('#form-position').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                // success()
                await  $.pjax.reload({ container:"#emp-position-container", history:false,replace: false,timeout: false});                               
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
     
    $("#categorise-data_json-education_success").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });   
    $("#categorise-data_json-date_ำืก").datetimepicker({
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