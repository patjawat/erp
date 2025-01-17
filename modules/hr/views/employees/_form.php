<?php


use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use kartik\widgets\Typeahead;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;
?>

<div class="employees-form p-3">
    <?php $form = ActiveForm::begin([
        'id' => 'form-employee',
        ]); ?>
    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>

    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-9">
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-12">
                            <?php
                                echo $form->field($model, 'prefix')->widget(Select2::classname(), [
                                    'data' =>$model->ListPrefixTh(),
                                    'options' => ['placeholder' => 'เลือก ...'],
                                    'pluginOptions'=>[
                                        'dropdownParent' => '#main-modal',
                                        'tags' => true,
                                        'maximumInputLength' => 10
                                    ],
                                ])->label('คำนำหน้า');?>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <?= $form->field($model, 'fname')->textInput(['autofocus' => true])->label('ชื่อ') ?>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <?= $form->field($model, 'lname')->textInput(['autofocus' => true])->label('นามสกุล') ?>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12">
                            <?php
                                echo $form->field($model, 'data_json[prefix_en]')->widget(Select2::classname(), [
                                    'data' =>$model->ListPrefixEn(),
                                    'options' => ['placeholder' => 'เลือก ...'],
                                    'pluginOptions'=>[
                                        'dropdownParent' => '#main-modal',
                                        'tags' => true,
                                        'maximumInputLength' => 10
                                    ],
                                ])->label('คำนำหน้า');?>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <?= $form->field($model, 'fname_en')->textInput(['autofocus' => true])->label('ชื่อ(อังกฤษ)') ?>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <?= $form->field($model, 'lname_en')->textInput(['autofocus' => true])->label('นามสกุล(อังกฤษ)') ?>
                        </div>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <input type="file" id="my_file" style="display: none;" />

                    <a href="#" class="select-photo">
                        <?php if($model->isNewRecord):?>
                        <?=Html::img('@web/img/placeholder_cid.png',['class' => 'avatar-profile object-fit-cover rounded shadow','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;'])?>
                        <?php else:?>

                        <?=Html::img($model->showAvatar(),['class' => 'avatar-profile object-fit-cover rounded shadow','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;'])?>
                        <?php endif?>
                    </a>
                    <?=$form->field($model, 'province')->hiddenInput(['maxlength' => true])->label(false) ?>
                    <?=$form->field($model, 'amphure')->hiddenInput(['maxlength' => true])->label(false)  ?>
                    <?=$form->field($model, 'district')->hiddenInput(['maxlength' => true])->label(false)  ?>
                    <?=$form->field($model, 'data_json[address2]')->hiddenInput(['maxlength' => true])->label(false)  ?>
                </div>
                <!-- End col-6 -->
            </div>
            <!-- End Row-->

            <div class="row">
                <div class="col-4">

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
                <div class="col-4">
                    <?=$form->field($model, 'cid')->widget(MaskedInput::className(),['mask'=>'9-9999-99999-99-9'])?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[born]')->widget(Select2::classname(), [
                'data' =>$model->ListBorn(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('ภูมิลำเนาเดิม') ?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[ethnicity]')->widget(Select2::classname(), [
                'data' => $model->ListEthnicity(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('เชื้อชาติ') ?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[marry]')->widget(Select2::classname(), [
                'data' => $model->ListMarry(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('สถานภาพสมรส') ?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[blood]')->widget(Select2::classname(), [
                'data' => $model->ListBlood(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('หมู่โลหิต') ?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[nationality]')->widget(Select2::classname(), [
                'data' => $model->ListEthnicity(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('สัญชาติ') ?>
                </div>
                <div class="col-4">
                    <?= $form->field($model, 'data_json[religion]')->widget(Select2::classname(), [
                'data' => $model->ListReligion(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('ศาสนา') ?>
                </div>
                <div class="col-4">
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
            </div>
            <!-- End Row -->

            <!-- Start Row -->
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'address')->textarea(['maxlength' => true,'style'=> 'height:50px'])->label('ที่อยู่ตามบัตรประชาชน') ?>
                    
                </div>
                <div class="col-6">
                        <?= $form->field($model, 'email')->label('อีเมลย์') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'phone')->textInput(['type' => 'number'])->label('โทรศัพท์') ?>
                    </div>
                    <div class="col-12">
                    <?= $form->field($model, 'branch')->radioList(['MAIN' => 'โรงพยาบาล','BRANCH' => 'รพ.สต.'],['inline'=>true])->label('สาขา') ?>
                    <div class="alert alert-primary mt-3" role="alert">
                        <span
                            class="address2"><?=isset($model->data_json['address2']) ? $model->data_json['address2'] : '-'?></span>
                    </div>
                </div>


            </div>
            <!-- End Row -->


        </div>


        

        <div class="form-group d-flex justify-content-center">
            <?=AppHelper::BtnSave()?>
        </div>


        <div class="d-none">

        <?= $form->field($model, 'education')->hiddenInput()->label(false) ?>
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'position_name')->widget(Select2::classname(), [
                    'data' => $model->ListPositionName(),
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'dropdownParent' => '#main-modal',
                        'tags' => true,
                        'maximumInputLength' => 10,
                    ],
                ])->label('ชื่อตำแหน่ง') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'position_number')->textInput()->label('เลขประจำตำแหน่ง') ?>

                </div>
                <div class="col-6">
                    <?= $form->field($model, 'position_type')->widget(Select2::classname(), [
                        'data' => $model->ListPositionType(),
                        'options' => ['placeholder' => 'เลือก ...'],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'tags' => true,
                            'maximumInputLength' => 10,
                        ],
                    ])->label('ประเภท') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'position_level')->widget(Select2::classname(), [
                        'data' => $model->ListPositionLevel(),
                        'options' => ['placeholder' => 'เลือก ...'],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'tags' => true,
                            'maximumInputLength' => 10,
                        ],
                    ])->label('ระดับตำแหน่ง') ?>
                </div>

                <div class="col-6">
                    <?= $form->field($model, 'salary')->textInput()->label('อัตราเงินเดือน') ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'department')->widget(Select2::classname(), [
                        'data' => $model->ListDepartment(),
                        'options' => ['placeholder' => 'เลือก ...'],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'tags' => true,
                            'maximumInputLength' => 10,
                        ],
                    ])->label('แผนก') ?>

                </div>

                <div class="col-12">
                    <?=$form->field($model, 'join_date')->widget(Datetimepicker::className(),[
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
                <div class="col-12">
                    <?= $form->field($model, 'status')->widget(Select2::classname(), [
                        'data' => $model->ListStatus(),
                        'options' => ['placeholder' => 'เลือก ...'],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'tags' => true,
                            'maximumInputLength' => 10,
                        ],
                    ])->label('สถานะ') ?>


                    <div class="col-12">
                        <?= $form->field($model, 'data_json[comment]')->textArea(['style' => 'height: 142px;'])->label('หมายเหตุ') ?>

                    </div>
                   
                </div>


            </div>


        </div>

        
        <?php ActiveForm::end(); ?>



    </div>


    

    <?php
    $ref = $model->ref;
    $urlUpload = Url::to('/filemanager/uploads/single');
    $getAvatar = Url::to(['/filemanager/uploads/show','id' => 1]);
$js = <<<JS
getAvatar()

function getAvatar(){
    console.log('get Avatar');
    $.ajax({
        type: "get",
        url: "$getAvatar",
        dataType: "json",
        success: function (res) {
        console.log('get Avatar success');
        console.log(res);
    }
});

}
$(".select-photo").click(function() {
    $("input[id='my_file']").click();
});


$('#my_file').change(function (e) { 
	e.preventDefault();
	formdata = new FormData();
    if($(this).prop('files').length > 0)
    {
		file =$(this).prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", 'avatar');

        console.log(file);
		$.ajax({
			url: '$urlUpload',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                console.log(res);
				$('.avatar-profile').attr('src', res.img)
                // success('แก้ไขภาพ')
			}
		});
    }
 });
 
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
     
    $("#employees-birthday").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
    $("#employees-join_date").datetimepicker({
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