<?php

use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Holiday $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="holiday-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/lm/holiday/validator']
        ]); ?>
    <?= $form->field($model, 'name')->hiddenInput(['value' => 'holiday'])->label(false) ?>
<div class="row">
<div class="col-8">
    
<?php echo $form->field($model, 'data_json[date]')->widget(Datetimepicker::className(),[
        'options' => [
            'timepicker' => false,
            'datepicker' => true,
            'mask' => '99/99/9999',
            'lang' => 'th',
            'yearOffset' => 543,
            'format' => 'd/m/Y', 
        ],
        ])->label('วันที่') ?>
</div>
<div class="col-4">
    <?= $form->field($model, 'data_json[thai_year]')->textInput(['maxlength' => true])->label('ปีงบประมาน') ?>
</div>
</div>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS

$('#form').on('beforeSubmit', function (e) {
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
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});
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
         
    $("#holiday-data_json-date").datetimepicker({
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