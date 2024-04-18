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
    'id' => 'form-employee',
]); ?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
<?= $form->field($model, 'user_id')->hiddenInput(['value' => 0])->label(false) ?>

<h4 class="card-title mb-2"><i class="fa-solid fa-user-large"></i> ข้อมูลตามบัตรประชาชน</h4>
        <hr>
<div class="row">
   
    <div class="col-md-6">
     

        <?= $this->render('_form_general', ['form' => $form, 'model' => $model]) ?>
    </div>
    <div class="col-md-6">
       
        
        <?=  $this->render('_form_work', ['form' => $form, 'model' => $model]) ?>
    </div>
    
    <!-- End col-6 -->
</div>
<!-- End Row-->





<div class="d-flex justify-content-center">
    <?= SiteHelper::btnSave() ?>
</div>

<?php ActiveForm::end(); ?>



<?php
$urlUpload = Url::to('/patient/upload');
$js = <<<JS



 $('#form-employee').on('beforeSubmit', function (e) {
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
                await  $.pjax.reload({ container:"#employee-container", history:false,replace: false,timeout: false});                               
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
     
    $("#employees-birthday").datetimepicker({
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