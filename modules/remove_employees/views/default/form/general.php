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

</style>



<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
<?= $form->field($model, 'user_id')->hiddenInput(['value' => 0])->label(false) ?>


<div class="row">
   
    <div class="col-md-6">
     

       
<div class="row">

<div class="col-12">
    <?php
            $prefix = AppHelper::prefixName();
            echo $form->field($model, 'prefix')->widget(Select2::classname(), [
                'data' => [
                    'นาย' => 'นาย',
                    'นาง' => 'นาง',
                    'นางสาว' => 'นางสาว',
                ],
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('คำนำหน้า');?>




</div>


    <div class="col-lg-6">
        <?= $form->field($model, 'fname')->textInput(['autofocus' => true])->label('ชื่อ') ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($model, 'lname')->textInput(['autofocus' => true])->label('นามสกุล') ?>
    </div>


    <div class="col-6">
        <?= $form->field($model, 'fname_en')->textInput(['autofocus' => true])->label('ชื่อ(อังกฤษ)') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'lname_en')->textInput(['autofocus' => true])->label('นามสกุล(อังกฤษ)') ?>
    </div>



<div class="col-6">

    <?=$form->field($model, 'birthday')->widget(Datetimepicker::className(),[
                                        'options' => [
                                            'timepicker' => false,
                                            'datepicker' => true,
                                            'mask' => '99/99/9999',
                                            'lang' => 'th',
                                            'yearOffset' => 543,
                                            'format' => 'd/m/Y', 
                                        ],
                                        ]);
                                    ?>
</div>
<div class="col-6">
    <?=$form->field($model, 'cid')->widget(MaskedInput::className(),[
            'mask'=>'9-9999-99999-99-9'
        ])?>
</div>
<div class="col-6">
            <?= $form->field($model, 'data_json[hometown]')->label('ภูมิลำเนาเดิม') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[ethnicity]')->label('เชื้อชาติ') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[marital_status]')->label('สถานภาพสมรส') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[blood_group]')->label('หมู่โลหิต') ?>

        </div>
</div>




    </div>
    <div class="col-md-6">
       
        
      



<div class="row">
    <div class="col-12">
        <?= $form->field($model, 'address')->textarea(['maxlength' => true,'style'=> 'height:135px'])->label('ที่อยู่ตามบัตรประชาชน') ?>

    </div>
    <div class="col-12">
        <?php
                                    $url = Url::to('/depdrop/address');
                                    echo $form->field($model, 'zipcode')->widget(Select2::className(), [
                                        'initValueText'=>'',//กำหนดค่าเริ่มต้น
                                        'options'=>['placeholder'=>'เลือกคำนำหน้า...'],
                                        'theme' => Select2::THEME_KRAJEE_BS5,
                                        'pluginOptions'=>[
                                            'dropdownParent' => '#main-modal',
                                            'allowClear'=>true,
                                            'minimumInputLength'=>4,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                                            'ajax'=>[
                                                'url'=>$url,
                                                'dataType'=>'json',//รูปแบบการอ่านคือ json
                                                'data'=>new JsExpression('function(params) { return {q:params.term};}')
                                            ],
                                            'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                                            'templateResult'=>new JsExpression('function(prefix){ return prefix.text;}'),
                                            'templateSelection'=>new JsExpression('function(prefix) {return prefix.id;}'),
                                            
                                        ],
                                        'pluginEvents' => [
                                            "select2:select" => "function(result) { 
                                                var data = $(this).select2('data')[0]
                                                console.log(data);
                                                $('#employees-province').val(data.province_id)
                                                $('#employees-amphure').val(data.amphure_id)
                                                $('#employees-district').val(data.district_id)
                                                $('.address2').html('ที่อยู่ : '+data.text)
                                                $('#employees-data_json-address2').val(data.text)
                                             }",
                                        ]
                                    ]);
                                    ?>
    </div>


    <div class="col-12">
        <div class="alert alert-primary mt-3" role="alert">
            <span class="address2"><?=isset($model->data_json['address2']) ? $model->data_json['address2'] : '-'?></span>
        </div>
    </div>
    <div class="col-6">
                <?= $form->field($model, 'data_json[nationality]')->label('สัญชาติ') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[religion]')->label('ศาสนา') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'phone')->textInput(['type' => 'number'])->label('โทรศัพท์') ?>

            </div>
    <div class="col-6">
                <?= $form->field($model, 'email')->label('อีเมลย์') ?>
            </div>
    
          

</div>

<?=$form->field($model, 'province')->hiddenInput(['maxlength' => true])->label(false) ?>
<?=$form->field($model, 'amphure')->hiddenInput(['maxlength' => true])->label(false)  ?>
<?=$form->field($model, 'district')->hiddenInput(['maxlength' => true])->label(false)  ?>
<?=$form->field($model, 'data_json[address2]')->hiddenInput(['maxlength' => true])->label(false)  ?>



    </div>
    
    <!-- End col-6 -->
</div>
<!-- End Row-->







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